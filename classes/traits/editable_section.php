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
 * Editable Section trait.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait includes methods which can be re-used across different plugin sections where they can be edited and saved.
 * E.g. Single, Multi, Incremental.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait editable_section {

    /**
     * Save the submitted data for the current user.
     * @return void
     */
    public function save_user_data() : void {

        // If a user has not been loaded in, there is nothing for us to do.
        if (!$this->user) {
            return;
        }

        // Loop through all the form fields in this section.
        foreach ($this->get_fields() as $field) {

            // If this field is editable.
            if ($field->is_editable()) {

                // Save this value for the user.
                $field->save_user_data();

            }

        }

    }

}