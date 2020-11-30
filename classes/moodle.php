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
 * This file holds the main moodle class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp;

defined('MOODLE_INTERNAL') or die();

/**
 * This class contains methods used for gathering information about the Moodle system.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class moodle {

    /**
     * Numerical representation of the Moodle version. E.g. '2020110100'.
     * @var mixed
     */
    protected $version;

    /**
     * Text representation of the Moodle version. E.g. '3.10'.
     * @var false|string
     */
    protected $release;

    /**
     * Array of Moodle roles.
     * @var array
     */
    protected $roles = [];

    /**
     * Construct the moodle system information class
     */
    public function __construct() {

        $this->version = get_config(null, 'version');
        $this->release = moodle_major_version();

    }

    /**
     * Get the Moodle version number.
     * @return mixed
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get the Moodle release name.
     * @return false|string
     */
    public function get_release() {
        return $this->release;
    }

    /**
     * Get an array of Moodle role IDs and names.
     * @return array
     */
    public function get_roles() : array {

        // If we have already retrieved the roles, get them from the object.
        if ($this->roles) {
            return $this->roles;
        }

        $this->roles = [];

        // Get the roles then convert the array into a simpler one with just the name.
        $roles = role_get_names();
        foreach ($roles as $role) {
            $this->roles[$role->id] = $role->localname;
        }

        return $this->roles;

    }

    /**
     * Get an array of possible theme layouts
     * @return array
     */
    public function get_layouts() : array {

        global $PAGE;

        $layouts = [];
        foreach (array_keys($PAGE->theme->layouts) as $layout) {
            $layouts[$layout] = $layout;
        }

        asort($layouts);

        return $layouts;

    }

}