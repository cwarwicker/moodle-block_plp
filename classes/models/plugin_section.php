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
 * This file holds the main class for plugin pages sections.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models;

use block_plp\model;
use block_plp\traits\settings;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for plugin pages sections.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class plugin_section extends model {

    // Use the settings table trait.
    use settings;

    /**
     * Plugin page model uses the mdl_block_plp_plugin_pages table.
     */
    const TABLE = 'block_plp_plugin_sections';

    /**
     * Array of valid locations a section can be displayed in
     */
    const LOCATIONS = [
        'centre' => 'centre',
        'left' => 'left',
        'right' => 'right',
    ];

    /**
     * Array of valid section types.
     */
    const TYPES = [
        'single' => 'single',
        'multi' => 'multi',
        'incremental' => 'incremental',
        'db' => 'db',
    ];

    /**
     * Table where the plugin settings are stored.
     */
    const SETTINGS_TABLE = 'block_plp_section_settings';

    /**
     * Field to reference a particular plugin in the settings table.
     */
    const SETTINGS_FIELD = 'sectionid';

    /**
     * ID of the plugin this section resides in
     * @var int
     */
    protected $pluginid;

    /**
     * ID of the page this section is associated with.
     * @var int
     */
    protected $pageid;

    /**
     * Title of the page
     * @var string
     */
    protected $title;

    /**
     * Type of section
     * @var string
     */
    protected $type;

    /**
     * Location on the page to display the section
     * @var string
     */
    protected $location;

    /**
     * Sort order number of the section within the page
     * @var int
     */
    protected $ordernum;

    /**
     * The confidentiality level of this section.
     * @var int Default is public
     */
    protected $confidentiality = confidentiality::CONFIDENTIALITY_PUBLIC;

    /**
     * Integer flag to show enabled status of the section
     * @var int
     */
    protected $enabled;

    /**
     * Array of form fields in this section
     * @var form_field[]
     */
    protected $fields = [];

    /**
     * ID of MIS connection linked to this section.
     * @var int
     */
    protected $mis;

    /**
     * User whose data will be loaded.
     * @var user
     */
    protected $user;

    /**
     * Check if the section is enabled
     * @return bool
     */
    public function is_enabled() : bool {
        return ($this->enabled == 1);
    }

    /**
     * Load the specified user into the page, so that it is their data we retrieve in all the form fields.
     * @param int $id
     */
    public function load_user(int $id) : void {

        $user = user::load($id);
        if ($user) {
            $this->user = $user;
        }

    }

    /**
     * Get the currently loaded user.
     * @return user
     */
    public function get_user() : user {
        return $this->get('user');
    }

    /**
     * Get the plugin object from the plugin ID.
     * @return plugin
     */
    public function get_plugin() : plugin {
        return plugin::load($this->get('pluginid'));
    }



    /**
     * Return the display HTML for this section.
     * @return string
     */
    abstract public function display() : string;

    /**
     * Get the data to be passed into the mustache template for the display method.
     * @return array
     */
    protected function get_display_data() : array {

        $data = [];
        $data['id'] = $this->get('id');
        $data['fields'] = [];

        // Get the form fields to display in this section.
        foreach ($this->get_fields() as $field) {
            $data['fields'][] = ['fieldid' => $field->get('id'), 'edit' => $field->display(), 'value' => $field->display_value()];
        }

        return $data;

    }

    /**
     * Save the plugin_section.
     * @return bool
     */
    public function save() : bool {

        // Save the plugin_section first.
        if (!parent::save()) {
            return false;
        }

        // Store a result variable so we can return false if anything beneath us fails.
        $result = true;

        // Now we should have the object id (if the record was new) and we can use it to save the fields.
        foreach ($this->fields as $field) {
            $field->set('sectionid', $this->id);
            $result = $result && $field->save();
        }

        return $result;

    }

    /**
     * Get the form fields in this section
     * @return array
     */
    public function get_fields() : array {

        if (!$this->fields) {
            $this->load_fields();
        }

        return $this->fields;

    }

    /**
     * Load the form fields into the fields array
     * @return void
     */
    protected function load_fields() : void {

        // Load the form fields into the fields array for this section.
        $this->fields = form_field::all(['sectionid' => $this->id]);

        // Loop through them load the user into them all (if loaded).
        if ($this->user) {
            foreach ($this->fields as $field) {
                $field->set('pluginid', $this->get('pluginid'));
                $field->set('user', $this->get('user'));
            }
        }

    }

    /**
     * Overwrite the standard ORM mapping and create form_field objects to be stored in the fields array
     * @param array $fields
     * @return void
     */
    public function map_fields(array $fields) : void {

        // If we are mapping, we must assume that we are starting from scratch. So clear any existing fields from the array.
        $this->fields = [];

        foreach ($fields as $fielddata) {
            $this->fields[] = form_field::from_array($fielddata);
        }

    }

    /**
     * Return the array of valid section locations
     * @return array
     */
    public static function get_locations() : array {
        return static::LOCATIONS;
    }

    /**
     * Return the array of valid section types
     * @return array
     */
    public static function get_types() : array {
        return static::TYPES;
    }

    /**
     * Overridden load method, to take into account that this is an abstract class and we need to work out which actual
     * class to load dependant on the section type.
     * @param int|null $id
     * @return plugin_section|null
     */
    public static function load(int $id = null) : ?plugin_section {

        global $DB;

        // Get the record from the database to work out which type of field it is.
        $record = $DB->get_record(static::TABLE, ['id' => $id], 'id, type');
        if (!$record) {
            return null;
        }

        // Work out which class we need to try and load.
        $class = '\\block_plp\\models\\sections\\' . $record->type;
        if (!class_exists($class)) {
            return null;
        }

        // Instantiate that class.
        return new $class($id);

    }

    /**
     * Overwrite the standard from_array method to work out which section class should be instantiated.
     * @param array $data
     * @param string|null $class
     * @return plugin_section
     */
    public static function from_array(array $data, string $class = null) : plugin_section {
        return parent::from_array($data, '\\block_plp\\models\\sections\\' . $data['type']);
    }

}