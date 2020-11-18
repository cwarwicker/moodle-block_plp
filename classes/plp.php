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
 * This file holds the main PLP class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp;

use stdClass;

defined('MOODLE_INTERNAL') or die();

/**
 * PLP class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class plp {

    /**
     * Get the dataroot directory we want to store PLP related files in.
     * @return string
     */
    public static function get_dataroot() : string {
        global $CFG;
        return $CFG->dataroot . DIRECTORY_SEPARATOR . 'block_plp';
    }

    /**
     * Get all of the info from the version.php file
     * @return stdClass
     */
    public static function get_version_info() : stdClass {

        global $CFG;

        $plugin = new stdClass();
        require_once($CFG->dirroot . '/blocks/plp/version.php');
        return $plugin;

    }

    /**
     * Get the version information about the Moodle system.
     * @return stdClass
     */
    public static function get_system_info() : stdClass {

        $system = new stdClass();
        $system->version = get_config(null, 'version');
        $system->release = moodle_major_version();
        return $system;

    }

    /**
     * Create a directory within the plugin's own data directory.
     * @param string $name
     * @return bool
     */
    public static function create_data_directory(string $name) : bool {

        global $CFG;
        return mkdir(static::get_dataroot() . DIRECTORY_SEPARATOR . $name, $CFG->directorypermissions, true);

    }

}