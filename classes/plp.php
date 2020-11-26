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
     * Path to the directory within the site's dataroot for storing files.
     * @var string
     */
    protected $dataroot;

    /**
     * Array of plugin configuration settings.
     * @var array
     */
    protected $settings = [];

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
     * Get a named configuration setting
     * @param string $name
     * @return string|null
     */
    public function get_setting(string $name) : ?string {

        // If we have already loaded the settings and we can find this in the settings array, retrieve it from there.
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }

        // Otherwise, load all the settings and then try and find it.
        $this->load_settings();

        $value = ($this->settings[$name]) ?? null;

        // If the value is still null, see if we have a default for this setting.
        if (is_null($value) && array_key_exists($name, $this->defaultsettings)) {
            $value = $this->defaultsettings[$name];
        }

        return $value;

    }

    /**
     * Load all of the PLP's configuration settings into the settings array.
     * @return void
     */
    protected function load_settings() {

        global $DB;

        // Reset settings array.
        $this->settings = [];

        // Get all of the records from the settings table where the userid and pluginid are null - meaning they are system settings.
        $records = $DB->get_records('block_plp_settings', ['userid' => null, 'pluginid' => null]);
        foreach ($records as $record) {
            $this->settings[$record->setting] = $record->value;
        }

    }

    /**
     * Update or insert a setting value for the PLP system.
     * @param string $name Named setting
     * @param mixed $value Value to set
     * @return bool|int
     */
    public function update_setting(string $name, $value) {

        global $DB;

        // Fix the value so it can be inserted into a database column.
        // If it's an array, just get the values in a comma-separated list.
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        // If it's still not scalar, just encode it.
        if (!is_scalar($value) && !is_null($value)) {
            $value = json_encode($value);
        }

        $obj = [];
        $obj['userid'] = null;
        $obj['pluginid'] = null;
        $obj['setting'] = $name;

        // If the value is null or empty, that means we are basically deleting the setting, so let's actually delete it.
        if (is_null($value) || strlen($value) == 0) {
            return $DB->delete_records('block_plp_settings', $obj);
        }

        // Does the setting already exist?
        $setting = $DB->get_record('block_plp_settings', $obj);
        if ($setting) {
            $setting->value = $value;
            return $DB->update_record('block_plp_settings', $setting);
        } else {
            $obj['value'] = $value;
            return $DB->insert_record('block_plp_settings', $obj);
        }

    }

    /**
     * Get the role IDs configured for a particular named role within the PLP.
     * @param string $name E.g. 'tutor', 'student', 'teacher', or 'manager'.
     * @return mixed
     */
    public function get_roles(string $name) {

        global $DB;

        // Have we already loaded this?
        if (array_key_exists($name, $this->roles)) {
            return $this->roles[$name];
        }

        $setting = $this->get_setting('role_' . $name);
        if (is_null($setting)) {
            return null;
        }

        // Explode it by comma, in case they chose more than 1 role for this.
        $setting = explode(',', $setting);

        // Make sure the roles are still valid and haven't been deleted from the system.
        $roles = array_filter($setting, function($id) use ($DB) {
            return $DB->get_record('role', ['id' => $id]);
        });

        // However, if it's only 1 element, we can just return that.
        if (count($roles) == 1) {
            $roles = $roles[0];
        }

        // If at this point we have nothing, set it to null, rather than an empty array.
        if (empty($roles)) {
            $roles = null;
        }

        $this->roles[$name] = $roles;
        return $this->roles[$name];

    }

}