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
 * This file holds the trait which can be used across different types of model which support having settings stored in a db table.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use block_plp\helper;
use block_plp\model;

/**
 * This is the trait which can be used across different types of model which support having settings stored in a db table.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait settings {

    /**
     * Array of settings from the database
     * @var array
     */
    protected $settings = [];

    /**
     * Checks if the class implementing this trait, is using defaultsettings.
     * @return bool
     */
    protected function uses_default_settings() : bool {
        return (isset($this->defaultsettings));
    }

    /**
     * Load all of the settings into the settings array.
     * @return void
     */
    protected function load_settings() {

        global $DB;

        // Reset settings array.
        $this->settings = [];

        // Generate parameters to find settings for this particular instance of model.
        $params = [];
        if (static::SETTINGS_FIELD !== false) {
            $params[static::SETTINGS_FIELD] = $this->id;
        }

        // Get all of the records from the settings table and add them to the array.
        $records = $DB->get_records(static::SETTINGS_TABLE, $params);
        foreach ($records as $record) {
            // Run it through helper::decode_json to decode any JSON settings back to their original states.
            $this->settings[$record->setting] = helper::decode_json($record->value);
        }

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
        if ($this->uses_default_settings() && is_null($value) && array_key_exists($name, $this->defaultsettings)) {
            $value = $this->defaultsettings[$name];
        }

        return $value;

    }

    /**
     * Get all of the settings.
     * @param bool $includedefaults Should we include default values if none are found for those keys?
     * @return array
     */
    public function get_settings($includedefaults = false) : array {

        if (!$this->settings) {
            $this->load_settings();
        }

        $settings = $this->settings;

        // If we want to include the defaults, add those in if they are missing from the database.
        if ($this->uses_default_settings() && $includedefaults) {
            foreach ($this->defaultsettings as $default => $value) {
                if (!array_key_exists($default, $settings)) {
                    $settings[$default] = $value;
                }
            }
        }

        return $settings;

    }

    /**
     * Add or update the value of a setting on the object.
     * This does NOT save that data into the database, it only adds it to the object to be saved later.
     * @param string $name Named setting
     * @param mixed $value Value to set
     * @return model
     */
    public function add_setting(string $name, $value) : model {
        $this->settings[$name] = $value;
        return $this;
    }

    /**
     * Save all the settings into the database.
     * @return void
     */
    public function save_settings() {

        // Loop through all the settings and save them.
        // We don't need to include defaults here, because if the name is not in the settings array, then it hasn't changed.
        foreach ($this->get_settings() as $name => $value) {
            $this->update_setting($name, $value);
        }

    }

    /**
     * Update or insert a setting value for the plugin
     * @param string $name Named setting
     * @param mixed $value Value to set
     * @return bool|int
     */
    public function update_setting(string $name, $value) {

        global $DB;

        // We can only update the setting, if the model exists.
        if (static::SETTINGS_FIELD !== false && !$this->exists()) {
            return false;
        }

        // Fix the value so it can be inserted into a database column.
        // If it's not scalar, just encode it.
        if (!is_scalar($value) && !is_null($value)) {
            $value = json_encode($value);
        }

        $obj = [];
        $obj['setting'] = $name;

        // Do we need to identify an instance of this model?
        if (static::SETTINGS_FIELD !== false) {
            $obj[static::SETTINGS_FIELD] = $this->id;
        }

        // If the value is null or empty, that means we are basically deleting the setting, so let's actually delete it.
        if (is_null($value) || strlen($value) == 0) {
            return $DB->delete_records(static::SETTINGS_TABLE, $obj);
        }

        // Does the setting already exist?
        $setting = $DB->get_record(static::SETTINGS_TABLE, $obj);
        if ($setting) {
            $setting->value = $value;
            return $DB->update_record(static::SETTINGS_TABLE, $setting);
        } else {
            $obj['value'] = $value;
            return $DB->insert_record(static::SETTINGS_TABLE, $obj);
        }

    }

}