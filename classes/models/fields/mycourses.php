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
 * This file holds the main class for the My Courses Picker form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\models\course;
use block_plp\models\form_field;
use block_plp\plp;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the My Courses Picker form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mycourses extends form_field {

    /**
     * Add any extra data to the array passed into the mustache template.
     * @param array $data
     * @return void
     */
    protected function apply_extra_data(array &$data) : void {

        $courses = [];
        if ($this->has_user()) {

            $plp = new plp();
            foreach ($this->user->get_courses($plp->get_roles('student')) as $course) {
                $courses[] = [
                    'id' => $course->get('id'),
                    'name' => $course->get_display_name(),
                    'selected' => ($course->get('id') == $data['valueuntouched'])
                ];
            }

        }

        $data['courses'] = $courses;

    }

    /**
     * Return the name of the course based on the ID saved.
     * @param mixed $value
     * @return string
     */
    protected function format_user_data($value) : string {

        $course = course::load($value);
        return ($course->exists()) ? $course->get_display_name() : '-';

    }

    /**
     * Process the file uploads.
     * @param mixed $value
     * @return void
     */
    protected function pre_save_user_data(&$value) : void {

        // If the course ID is not valid, revert the value to null.
        // We do not check here if they have access to the course, simply if it exists.
        $course = course::load($value);
        if (!$course->exists()) {
            $value = null;
        }

    }

}