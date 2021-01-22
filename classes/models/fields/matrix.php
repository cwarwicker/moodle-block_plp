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
 * This file holds the main class for the Matrix form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\helper;
use block_plp\models\form_field;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Matrix form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class matrix extends form_field {

    /**
     * Get the submitted data for this field.
     * @return mixed
     */
    public function get_submitted_value() {
        return helper::optional_param_array_recursive($this->get('elementname'), null, PARAM_RAW);
    }

    /**
     * Apply boolean values to an array of rows and columns, for easier use in mustache templates.
     * @param array $data Array of data
     * @return void
     */
    protected function apply_matrix_values(array &$data) : void {

        $userdata = (array)json_decode($this->get_user_data());
        $data['value'] = ['rows' => []];

        // Loop through the configured rows and build up an array of column values.
        foreach ($data['options']->rows as $key => $row) {

            $data['value']['rows'][$key] = ['row_id' => $row->row_id, 'row_name' => $row->row_name, 'cols' => []];

            // Loop through the columns and set the boolean value, based on the value in $userdata.
            foreach ($data['options']->columns as $column) {
                $data['value']['rows'][$key]['cols'][] = [
                    'col_id' => $column->col_id,
                    'col_name' => $column->col_name,
                    'value' => (array_key_exists($row->row_id, $userdata) && $userdata[$row->row_id] == $column->col_id)
                ];
            }

        }

    }

    /**
     * Return the simple HTML for displaying the value of the field, in non-editing mode.
     * This should be overridden by any form fields which do not use the simple template, of just display a text value.
     * @return string
     */
    protected function get_value_html() : string {

        global $PAGE;

        $template = 'block_plp/fields/value/matrix';

        $data = $this->to_array();
        $data['options'] = json_decode($data['options']);

        $this->apply_matrix_values($data);

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

    /**
     * Apply extra data to the array before passing to mustache template for editing mode rendering.
     * @param array $data
     * @return void
     */
    public function apply_extra_data(array &$data) : void {
        $this->apply_matrix_values($data);
    }

}