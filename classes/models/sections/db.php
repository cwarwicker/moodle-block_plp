<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file holds the main class for the DB plugin section type.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models\sections;

use block_plp\exceptions\coding_exception;
use block_plp\exceptions\invalid_data_exception;
use block_plp\exceptions\mis_exception;
use block_plp\helper;
use block_plp\models\chart;
use block_plp\models\mis_connection;
use block_plp\models\plugin_section;

/**
 * This class is the DB plugin section type.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class db extends plugin_section {

    /**
     * Placeholders that can be used in the query, to be replaced by that data from the PLP user.
     */
    const PLACEHOLDERS = [
        '{{id}}',
        '{{username}}',
        '{{email}}',
        '{{idnumber}}',
    ];

    /**
     * Array to store key settings in, for use throughout class.
     * @var array
     */
    protected $data = [];

    /**
     * After loading the database section, load all of its settings that we will need to keep using throughout.
     * @return void
     */
    protected function post_load_actions() : void {
        $this->data = $this->get_settings();
    }

    /**
     * Run the query and return results.
     * @return array
     * @throws coding_exception
     * @throws invalid_data_exception
     */
    protected function query() : array {

        // Make sure that we configured the type of query (internal/external) and the actual query to run.
        if (!isset($this->data['type']) || !isset($this->data['query']) || !isset($this->data['display'])) {
            throw new invalid_data_exception('exception:data:db:missingconfig', $this->id);
        }

        // Make sure that the method for this type of query exists.
        $method = 'query_' . $this->data['type'];
        if (!method_exists($this, $method)) {
            throw new coding_exception('exception:code:missingmethod', ['method' => $method, 'class' => get_class($this)]);
        }

        return $this->$method();

    }

    /**
     * Run the query against the external database, as specified in the MIS connection.
     * @return array
     * @throws invalid_data_exception
     * @throws mis_exception
     */
    protected function query_external() : array {

        // Make sure we set which MIS connection to use and that it exists.
        $mis = mis_connection::load(($this->data['mis_connection_id']) ?? null);
        if (!$mis->exists()) {
            throw new invalid_data_exception('exception:data:db:mis:missing');
        }

        // Make sure it's actually enabled.
        if (!$mis->is_enabled()) {
            throw new invalid_data_exception('exception:data:db:mis:disabled');
        }

        // Make suer we can connect to it.
        if (!$db = $mis->connect()) {
            throw new mis_exception('exception:data:db:mis:connection', null, $mis->get_error());
        }

        // Build the SQL and parameters from the configured query.
        list($sql, $params) = $this->build_query();

        return $db->get_sql_many($sql, $params);

    }

    /**
     * Run the query against the internal Moodle database.
     * @return array
     */
    protected function query_internal() : array {

        global $DB;

        // Build the SQL and parameters from the configured query.
        list($sql, $params) = $this->build_query();

        // Get the results from the internal Moodle database.
        return $DB->get_records_sql($sql, $params);

    }

    /**
     * Build the actual SQL string and parameters to pass into the database query.
     * @return array First element is the SQL string, second element is the array of parameters.
     */
    protected function build_query() : array {

        $sql = $this->data['query'];
        $params = [];

        // Replace any placeholders with actual user data.
        $this->replace_query_placeholders($sql, $params);

        return [$sql, $params];

    }

    /**
     * Replace any placeholders in the SQL query with data from the PLP user being viewed.
     * @param string $sql SQL string to alter
     * @param array $params Parameter array to append to
     */
    protected function replace_query_placeholders(string &$sql, array &$params) : void {

        $user = $this->get('user')->get_info();

        // Loop through the supported placeholders and see if any are present in the SQL.
        foreach (static::PLACEHOLDERS as $placeholder) {

            // The placeholder is present.
            if (strpos($sql, $placeholder) !== false) {

                // Count how many times this placeholder exists, so we know how many sql params to add.
                $count = substr_count($sql, $placeholder);

                // Strip the curly brackets from it, so we can use it as an object property.
                $prop = str_replace(['{', '}'], '', $placeholder);

                // Replace the placeholder with the real SQL placeholder.
                $sql = preg_replace('/\{\{' . $prop . '\}\}/', '?', $sql);

                // For each instance of the placeholder - append the value from the user object to the sql params array.
                for ($i = 1; $i <= $count; $i++) {
                    $params[] = $user->{$prop};
                }

            }

        }

    }

    /**
     * Display the section.
     * @return string
     */
    public function display() : string {

        global $PAGE;

        // Render the mustache template, specific to this display type (e.g. single row, multiple rows, graph, etc...).
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/core/plugins/section_db_' .
            $this->data['display'], $this->get_display_data());

    }

    /**
     * Get the data to be passed into the mustache template for the display method.
     * @return array
     */
    protected function get_display_data() : array {

        $data = [];

        // Run the query and return the results to be displayed.
        $data['results'] = $this->query();

        // Apply any extra data we'll need, depending on the display type. This is not mandatory, so method can be omitted.
        $method = 'apply_display_data_' . $this->data['display'];
        if (method_exists($this, $method)) {
            $this->$method($data);
        }

        return $data;

    }

    /**
     * Common functionality for generating bar, line and any other charts which use the same data format.
     * @param array $data
     * @param string $type
     * @return void
     */
    protected function apply_display_data_chart_common(array &$data, string $type) : void {

        // The first element of each results array will be used as a legend and separates the results into groups.
        $legends = [];
        foreach ($data['results'] as $key => $result) {
            $legends[] = $key;
        }

        // We can then remove the first header, as it is being used as the legend.
        $headers = $this->get_headers($data['results']);
        unset($headers[0]);

        // Now generate an array of column names.
        $columns = [];
        foreach ($headers as $header) {
            $columns[] = $header['name'];
        }

        // Get any relevant settings for this section.
        $colours = $this->get_setting('chart_colours');
        $axes = $this->get_setting('chart_axes');
        $width = $this->get_setting('chart_width');
        $height = $this->get_setting('chart_height');

        // Generate an array of data to pass through to the chart script.
        $chartdata = [
            'type' => $type,
            'title' => $this->title,
            'width' => ($width) ?? null,
            'height' => ($height) ?? null,
            'axes' => ['x' => [
                'title' => ($axes->x) ?? '',
                'columns' => array_map('ucfirst', $columns),
            ], 'y' => [
                'title' => ($axes->y) ?? ''
            ]],
            'groups' => []
        ];

        foreach ($legends as $legend) {

            // Create the row array and set the values.
            $row = [
                'title' => $legend,
                'colour' => ($colours->{$legend}) ?? null,
                'values' => []
            ];

            // Loop through the columns and get each point of data for this row.
            foreach ($columns as $column) {
                $row['values'][] = helper::cast_numeric_string($data['results'][$legend]->{$column});
            }

            $chartdata['groups'][] = $row;

        }

        // Generate the chart image in base64 and pass through to the template, for display.
        // The reason its done this way, is otherwise we'd have to either pass the data to generate the image into a URL
        // or save the file somewhere, and this allows us to just do it on the fly, behind the scenes.
        $chart = chart::load($chartdata);
        $data['image'] = $chart->generate();

    }

    /**
     * Generate a base64 encoded image of a line chart, using the supplied data.
     * @param array $data
     * @return void
     */
    protected function apply_display_data_chart_line(array &$data) : void {
        $this->apply_display_data_chart_common($data, 'line');
    }

    /**
     * Generate a base64 encoded image of a bar chart, using the supplied data.
     * @param array $data
     * @return void
     */
    protected function apply_display_data_chart_bar(array &$data) : void {
        $this->apply_display_data_chart_common($data, 'bar');
    }

    /**
     * Generate a base64 encoded image of a pie chart, using the supplied data.
     * @param array $data
     * @return void
     */
    protected function apply_display_data_chart_pie(array &$data) : void {

        // For a pie chart, there should only be one row of results.
        $data['results'] = reset($data['results']);

        // The first element of each results array will be used as a legend and separates the results into groups.
        $legends = [];
        foreach ($data['results'] as $key => $result) {
            $legends[] = $key;
        }

        // Get any relevant settings for this section.
        $colours = $this->get_setting('chart_colours');
        $width = $this->get_setting('chart_width');
        $height = $this->get_setting('chart_height');

        // Generate an array of data to pass through to the chart script.
        $chartdata = [
            'type' => 'pie',
            'title' => $this->title,
            'width' => ($width) ?? null,
            'height' => ($height) ?? null,
            'slices' => [],
        ];

        foreach ($legends as $legend) {

            // Create the row array and set the values.
            $row = [
                'title' => ucfirst($legend),
                'colour' => ($colours->{$legend}) ?? null,
                'value' => helper::cast_numeric_string($data['results']->{$legend})
            ];

            $chartdata['slices'][] = $row;

        }

        // Generate the chart image in base64 and pass through to the template, for display.
        // The reason its done this way, is otherwise we'd have to either pass the data to generate the image into a URL
        // or save the file somewhere, and this allows us to just do it on the fly, behind the scenes.
        $chart = chart::load($chartdata);
        $data['image'] = $chart->generate();

    }

    /**
     * Alter the data to be passed to the mustache template, for single row results.
     * @param array $data Array of data to be altered.
     * @return void
     */
    protected function apply_display_data_row_single(array &$data) : void {

        // Next get the headers that we'll need.
        $headers = $this->get_headers($data['results']);

        // Next, this is supposed to be displaying a single row's worth of data. So if there's more than one, get rid of it.
        $results = reset($data['results']);

        // Now put them together into an array that will play nicely with mustache.
        $data['results'] = $headers;
        foreach ($data['results'] as &$field) {
            $field['value'] = $results->{$field['name']};
        }

    }

    /**
     * Alter the data to be passed to the mustache template, for multiple row results.
     * @param array $data Array of data to be altered.
     * @return void
     */
    protected function apply_display_data_row_multiple(array &$data) : void {

        // Add the array of headers for the table headers.
        $data['headers'] = $this->get_headers($data['results']);

        // Now alter the results array to play nicely with mustache.
        $data['rows'] = [];

        $results = $data['results'];
        foreach ($results as $row) {

            // Get an array of properties from the results.
            $props = array_keys(get_object_vars($row));

            // Build a new array, so we can store each value on the row as an array element with the key 'value', because mustache.
            $arr = ['fields' => []];
            foreach ($props as $prop) {
                $arr['fields'][] = ['value' => $row->{$prop}];
            }

            $data['rows'][] = $arr;

        }

    }

    /**
     * Get an array of column/section headers from the properties returned by the results.
     * @param array $results
     * @return array
     */
    protected function get_headers(array $results) : array {

        // If there were not results, then there will be no headers.
        if (!$results) {
            return [];
        }

        // Just use the first row from the results.
        $row = reset($results);

        // Get all the returned properties as the headers.
        $headers = array_keys(get_object_vars($row));

        $return = [];
        foreach ($headers as $header) {
            $return[] = ['name' => $header];
        }

        return $return;

    }

}