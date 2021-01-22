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
 * This file holds the main class for the Description form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\models\form_field;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Description form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class description extends form_field {

    /**
     * No, do not display the Instructions
     * @var bool
     */
    protected $hasinstructions = false;

    /**
     * Can this field type be edited and saved through the PLP?
     * @return bool
     */
    public function is_editable() : bool {
        return false;
    }

    /**
     * Overwrite get_user_data() method to return the description text.
     * Saves changing the whole get_value_html() method.
     * @param bool $format
     * @return string
     */
    public function get_user_data(bool $format = true) {

        $data = $this->to_array();
        $data['options'] = json_decode($data['options']);
        return $data['options']->text;

    }

}