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
 * This file contains the course class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models;

use block_plp\model;
use block_plp\plp;
use block_plp\traits\permissions;
use context_course;
use context_system;
use context_user;

/**
 * Course class for dealing with anything course-related in the plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class course extends model {

    /**
     * Course model uses the mdl_course table.
     */
    const TABLE = 'course';

    /**
     * Course fullname
     * @var string
     */
    protected $fullname;

    /**
     * Course shortname
     * @var string
     */
    protected $shortname;

    /**
     * Course idnumber
     * @var string
     */
    protected $idnumber;

    /**
     * Get the display name of a course, using the format we define in the plugin settings.
     * @return string
     */
    public function get_display_name() : string {

        $plp = new plp();
        $name = $plp->get_setting('course_display_format');

        // Convert placeholders to values.
        $name = str_replace('%full%', $this->fullname, $name);
        $name = str_replace('%short%', $this->shortname, $name);
        $name = str_replace('%idnum%', $this->idnumber, $name);

        return $name;

    }

    /**
     * Get the context of this course.
     * @return context_course
     */
    public function get_context() : context_course {
        return context_course::instance($this->id);
    }

}