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
 * This file holds the main class for the Checkbox form field.
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
use block_plp\traits\field_options;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Checkbox form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class checkbox extends form_field {

    // This form field type has options.
    use field_options;

    /**
     * Add any extra data to the array passed into the mustache template.
     * @param array $data
     * @return void
     */
    protected function apply_extra_data(array &$data) : void {

        // We need to alter the options array to work better with the mustache template.
        // Use this new array variable to store the new version of the options array.
        $array = [];

        // The value could be multiple options which are checked.
        $value = explode(static::DELIM, $data['value']);

        foreach ($data['options']->options as $key => $option) {
            $array[] = [
                'value' => $key,
                'name' => $option,
                'selected' => (in_array($option, $value)),
                'class' => (isset($data['options']->inline) && $data['options']->inline === true) ? 'form-check-inline' :
                    'form-check'
            ];
        }

        $data['options']->options = $array;

    }

    /**
     * Get the submitted data for this field.
     * @return mixed
     */
    public function get_submitted_value() {
        return helper::optional_param_array_recursive($this->get('elementname'), null, PARAM_RAW);
    }

    /**
     * Convert the array of checked options to a list string.
     * @param mixed $value
     * @return string
     */
    public function format_user_data($value) : string {

        if (is_null($value) || $value === false) {
            return '-';
        }

        // Convert each select option from its key to its actual value for display.
        $values = array_map([$this, 'get_option_display_from_value'], json_decode($value));

        // Then return in a string list.
        return implode(static::DELIM, $values);

    }

}