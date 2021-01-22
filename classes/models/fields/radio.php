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
 * This file holds the main class for the Radio form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\models\form_field;
use block_plp\traits\field_options;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Radio form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class radio extends form_field {

    // This form field type has options.
    use field_options;

    /**
     * Convert the option key to its actual value for display.
     * @param mixed $value
     * @return string
     */
    protected function format_user_data($value) : string {
        return $this->get_option_display_from_value($value);
    }

    /**
     * Add any extra data to the array passed into the mustache template.
     * @param array $data
     * @return void
     */
    protected function apply_extra_data(array &$data) : void {

        // We need to alter the options array to work better with the mustache template.
        // Use this new array variable to store the new version of the options array.
        $array = [];

        foreach ($data['options']->options as $key => $option) {
            $array[] = [
                'value' => $key,
                'name' => $option,
                'selected' => ($key == $data['valueuntouched']),
                'class' => (isset($data['options']->inline) && $data['options']->inline === true) ? 'form-check-inline' :
                    'form-check'
            ];
        }

        $data['options']->options = $array;

    }

}