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

use block_plp\traits\settings;
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

    // Use the settings table trait.
    use settings;

    /**
     * Table the settings are stored in.
     * @var string
     */
    const SETTINGS_TABLE = 'block_plp_settings';

    /**
     * We do not need a field for these settings, but we need the constant for the trait.
     */
    const SETTINGS_FIELD = false;

    /**
     * Path to the directory within the site's dataroot for storing files.
     * @var string
     */
    protected $dataroot;

    /**
     * Default values for some of the configuration settings, to use if there is no value defined.
     * @var array
     */
    protected $defaultsettings = [
        'layout' => 'login',
        'dock' => 'bottom',
        'category_usage' => 'no',
        'academic_year_enabled' => '0',
        'email_alerts_enabled' => '1',
        'role_student' => '5',
        'role_teacher' => '3,4',
        'course_display_format' => '[{{short}}] {{full}}'
    ];

    /**
     * Array of PLP roles and their Moodle role IDs.
     * @var array
     */
    protected $roles = [];

    /**
     * Construct plp object.
     */
    public function __construct() {

        global $CFG;
        $this->dataroot = $CFG->dataroot . DIRECTORY_SEPARATOR . 'block_plp';

    }

    /**
     * Get the plugin dataroot path.
     * @return string
     */
    public function get_dataroot() : string {
        return $this->dataroot;
    }

    /**
     * Get all of the info from the version.php file
     * @return stdClass
     */
    public function get_version_info() : stdClass {

        global $CFG;

        $plugin = new stdClass();
        require_once($CFG->dirroot . '/blocks/plp/version.php');
        return $plugin;

    }

    /**
     * Create a directory within the plugin's own data directory.
     * @param string $name
     * @return bool
     */
    public function create_data_directory(string $name) : bool {

        global $CFG;
        return mkdir($this->get_dataroot() . DIRECTORY_SEPARATOR . $name, $CFG->directorypermissions, true);

    }

    /**
     * Get the role IDs configured for a particular named role within the PLP.
     * @param string $name E.g. 'tutor', 'student', 'teacher', or 'manager'.
     * @return array
     */
    public function get_roles(string $name) : ?array {

        global $DB;

        // Have we already loaded this?
        if (array_key_exists($name, $this->roles)) {
            return $this->roles[$name];
        }

        $setting = $this->get_setting('role_' . $name);
        if (is_null($setting) || empty($setting)) {
            return null;
        }

        // Make sure the roles are still valid and haven't been deleted from the system.
        $roles = array_filter($setting, function($id) use ($DB) {
            return $DB->get_record('role', ['id' => $id]);
        });

        // If at this point we have nothing, set it to null, rather than an empty array.
        if (empty($roles)) {
            $roles = null;
        }

        $this->roles[$name] = $roles;
        return $this->roles[$name];

    }

}