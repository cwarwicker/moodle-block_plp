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
 * This file holds the main class for plugin pages.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models;

use block_plp\model;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for plugin pages.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class plugin_page extends model {

    /**
     * Plugin page model uses the mdl_block_plp_plugin_pages table.
     */
    const TABLE = 'block_plp_plugin_pages';

    /**
     * ID of the plugin this page is associated with.
     * @var int
     */
    protected $pluginid;

    /**
     * Title of the page
     * @var string
     */
    protected $title;

    /**
     * Sort order number of the page within the plugin
     * @var int
     */
    protected $ordernum;

    /**
     * Integer flag to show enabled status of the page
     * @var int
     */
    protected $enabled;

    /**
     * Array of sections within the page
     * @var plugin_section[]
     */
    protected $sections = [];

    /**
     * User whose data will be loaded.
     * @var user
     */
    protected $user;

    /**
     * Check if the page is enabled
     * @return bool
     */
    public function is_enabled() : bool {
        return ($this->enabled == 1);
    }

    /**
     * Load the specified user into the page, so that it is their data we retrieve in all the form fields.
     * @param int $id
     */
    public function load_user(int $id) : void {

        $user = user::load($id);
        if ($user) {
            $this->user = $user;
        }

    }

    /**
     * Get the data to be passed into the mustache template for the display method.
     * @return array
     */
    protected function get_display_data() : array {

        $data = ['sections' => [], 'has_left' => false, 'has_centre' => false, 'has_right' => false];

        // Loop through the possible locations a section can be in and load those into the template.
        foreach (plugin_section::LOCATIONS as $location) {

            // Get the sections.
            $sections = [];

            // Convert them to arrays so their properties can be accessed in the template.
            foreach ($this->get_sections($location, true) as $section) {
                $array = $section->to_array();
                $array['display'] = $section->display();
                $sections[] = $array;
                $data['has_' . $location] = true;
            }

            // Append to data array.
            $data['sections'][$location] = $sections;

        }

        return $data;

    }

    /**
     * Render the plugin page with its sections and forms
     * @return string
     */
    public function display() : string {

        global $PAGE;
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/core/plugins/page', $this->get_display_data());

    }

    /**
     * Get the sections on this page
     * @param string|null $location (Optional) Page location to load sections from. If left null, then it will be all sections.
     * @param bool $enabledonly (Optional) Only return sections which are enabled.
     * @return array
     */
    public function get_sections(string $location = null, bool $enabledonly = false) : array {

        if (!$this->sections) {
            $this->load_sections($enabledonly);
        }

        if (!is_null($location)) {
            return $this->filter_sections_by_location($location);
        } else {
            return $this->sections;
        }

    }

    /**
     * Filter out any sections from the sections array, which are not in the location specified
     * @param string $location From: plugin_section::LOCATIONS
     * @return array
     */
    protected function filter_sections_by_location(string $location) : array {

        return array_filter($this->sections, function($obj) use ($location) {
            return ($obj->get('location') === $location);
        });

    }

    /**
     * Load the page sections into the sections array
     * @param bool $enabledonly Only return sections which are enabled.
     * @return void
     */
    protected function load_sections($enabledonly = false) : void {

        // Load the section objects into the array of sections on the page.
        $params = ['pageid' => $this->id];
        if ($enabledonly) {
            $params['enabled'] = 1;
        }

        $this->sections = plugin_section::all($params);

        // Loop through them and load the user into them all (if loaded).
        if ($this->user) {
            foreach ($this->sections as $section) {
                $section->set('pluginid', $this->get('pluginid'));
                $section->set('user', $this->get('user'));
            }
        }

    }

    /**
     * Overwrite the standard ORM mapping and create plugin_section objects to be stored in the sections array
     * @param array $sections
     * @return void
     */
    public function map_sections(array $sections) : void {

        // If we are mapping, we must assume that we are starting from scratch. So clear any existing sections from the array.
        $this->sections = [];

        foreach ($sections as $sectiondata) {
            $this->sections[] = plugin_section::from_array($sectiondata);
        }

    }

    /**
     * Save the plugin_page.
     * @return bool
     */
    public function save() : bool {

        // Save the plugin_page first.
        if (!parent::save()) {
            return false;
        }

        // Store a result variable so we can return false if anything beneath us fails.
        $result = true;

        // Now we should have the object id (if the record was new) and we can use it to save the sections.
        foreach ($this->sections as $section) {
            $section->set('pageid', $this->id);
            $result = $result && $section->save();
        }

        return $result;

    }

}