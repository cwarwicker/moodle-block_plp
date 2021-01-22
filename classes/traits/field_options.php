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
 * This file holds the trait which can be used by form fields which have options, e.g. select, checkbox, radio.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;


/**
 * This file holds the trait which can be used by form fields which have options, e.g. select, checkbox, radio.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait field_options {

    /**
     * Given the 'value' saved for an option, get the corresponding display value that appears in the select menu.
     * @param string $value
     * @return false
     */
    protected function get_option_display_from_value(string $value) : string {

        // Get the select menu from the general form_field options.
        $options = (array)$this->get_options()->options;

        // If this value exists as a key, return it's display value. Otherwise return empty string.
        return (array_key_exists($value, $options)) ? $options[$value] : '';

    }

}