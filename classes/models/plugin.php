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
use moodle_url;

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
     * Check if the plugin is enabled
     * @return bool
     */
    public function is_enabled() : bool {
        return ($this->enabled == 1);
    }

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
     * Get all of the plugin's settings.
     * @param bool $includedefaults Should we include default values if none are found for those keys?
     * @return array
     */
    public function get_settings($includedefaults = false) : array {

        if (!$this->settings) {
            $this->load_settings();
        }

        $settings = $this->settings;

        // If we want to include the defaults, add those in if they are missing from the database.
        if ($includedefaults) {
            foreach ($this->defaultsettings as $default => $value) {
                if (!array_key_exists($default, $settings)) {
                    $settings[$default] = $value;
                }
            }
        }

        return $settings;

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

        // We can only update the setting, if the plugin exists.
        if (!$this->exists()) {
            return false;
        }

        $plp = new plp();
        return $plp->update_setting($name, $value, $this->id);

    }

    /**
     * Toggle the value of the enabled setting to enable/disable.
     */
    public function toggle_enabled() {
        $this->set('enabled', !$this->get('enabled'));
        return $this->save();
    }

    /**
     * Get the user who has been loaded into the plugin
     * @return user|null
     */
    protected function get_user() : ?user {
        return $this->user;
    }

    /**
     * Display the actions for this row in the plugins table
     * @return string
     */
    public function get_actions() : string {

        global $PAGE;

        $renderer = $PAGE->get_renderer('block_plp');

        return $renderer->render_from_template('block_plp/actions', [
            'enabledisable_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'plugins',
                'action' => 'enabledisable',
                'id' => $this->get('id'),
                'sesskey' => sesskey()
            ]),
            'enabledisable_icon' => ($this->is_enabled()) ? 'eye' : 'eye-slash',
            'enabledisable_title' => ($this->is_enabled()) ? get_string('disable', 'block_plp') : get_string('enable', 'block_plp'),
            'edit_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'plugins',
                'action' => 'edit',
                'id' => $this->get('id')
            ]),
            'delete_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'plugins',
                'action' => 'delete',
                'id' => $this->get('id'),
                'sesskey' => sesskey()
            ]),
        ]);

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

    /**
     * Return an array of all the core plugins found in the blocks/plp/classes/plugin/ directory.
     * This returns only the name of the folder, not plugin objects.
     * @return array
     */
    public static function get_core_plugins() : array {

        global $CFG;

        $results = array();
        $dir = $CFG->dirroot . '/blocks/plp/classes/plugin';

        $handle = opendir($dir);
        if ($handle) {

            // Loop through the /classes/plugin directory and look for plugins.
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..' || !is_dir($dir . '/' . $entry)) {
                    continue;
                }
                $results[$entry] = get_string('plugin:' . $entry . ':title', 'block_plp');
            }

        }

        // Sort them by natural language.
        usort($results, function($a, $b){
            return strnatcasecmp($a, $b);
        });

        return $results;

    }

}