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
 * This file holds the main class for the Single plugin section.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models\sections;

use block_plp\helper;
use block_plp\models\plugin_section;

/**
 * This is the class for the Single plugin section.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class single extends plugin_section {

    /**
     * Return the display HTML for this section.
     * @return string
     */
    public function display() : string {

        global $PAGE;
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/core/plugins/section_single',
            $this->get_display_data());

    }

    /**
     * Save the submitted data for the current user.
     * @return void
     */
    public function save_user_data() : void {

        // Loop through all the form fields in this section.
        foreach ($this->get_fields() as $field) {

            // Find the submitted data for this field.
            $value = $field->get_submitted_value();
            var_dump($value);

        }

    }

}