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
 * This file holds the main Plugin class, which is the parent of all PLP plugins.
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

defined('MOODLE_INTERNAL') or die();

/**
 * This is the base plugin class, which is used by all core plugins and extended for custom or external plugins.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class plugin extends model {

    /**
     * Plugin model uses the mdl_block_plp_plugins table.
     * @var string
     */
    protected static $table = 'block_plp_plugins';

    /**
     * Name of the plugin
     * @var string
     */
    protected $name;

    /**
     * Display name of the plugin
     * @var string
     */
    protected $title;

    /**
     * Path to the plugin's directory within the Moodle code
     * @var string
     */
    protected $path;

    /**
     * Version number of the plugin
     * @var int
     */
    protected $version;

    /**
     * Integer flag to show enabled status of the plugin
     * @var int
     */
    protected $enabled;

    /**
     * Array of plugin settings
     * @var array
     */
    protected $settings = [];

    /**
     * Array of default values for the plugin settings
     * @var array
     */
    protected $defaultsettings = [];

    /**
     * User that is loaded into the plugin
     * @var user
     */
    protected $user;

    /**
     * Method to install the plugin and any tables/data it requires.
     * @return bool
     */
    public function install() : bool {
        return false;
    }

    /**
     * Method to uninstall the plugin and any tables/data contained within.
     * @return bool
     */
    public function uninstall() : bool {
        return false;
    }

    /**
     * Get the value of a setting for this plugin.
     * @param string $name Name of the setting to get the value of
     * @return mixed
     */
    public function get_setting(string $name) {

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
     * Load all of the plugin's settings into the settings array.
     * @return void
     */
    protected function load_settings() {

        global $DB;

        // Reset settings array.
        $this->settings = [];

        // Get all of the records from the settings table where the userid and pluginid are null - meaning they are system settings.
        $records = $DB->get_records('block_plp_settings', ['userid' => null, 'pluginid' => $this->id]);
        foreach ($records as $record) {
            $this->settings[$record->setting] = $record->value;
        }

    }

    /**
     * Update or insert a setting value for the plugin
     * @param string $name Named setting
     * @param mixed $value Value to set
     * @return bool|int
     */
    public function update_setting(string $name, $value) {
        $plp = new plp();
        return $plp->update_setting($name, $value, $this->id);
    }

    /**
     * Get the user who has been loaded into the plugin
     * @return user|null
     */
    protected function get_user() : ?user {
        return $this->user;
    }



    /**
     * Instantiate an instance of a plugin.
     * @param mixed $data This should be either a string (plugin name), or an array (of fields and values to set in the object).
     * @return plugin|null
     */
    public static function instantiate($data) : ?plugin {

        $plugin = null;
        $class = get_called_class();

        // If we passed in a name, look it up and try to load that object.
        if (is_string($data)) {
            $plugin = static::find(['name' => $data]);
        } else {
            $plugin = new $class();
            foreach ($data as $key => $value) {
                $plugin->set($key, $value);
            }
        }

        return $plugin;

    }

}