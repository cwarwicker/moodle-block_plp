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
use block_plp\traits\permissions;
use block_plp\traits\settings;
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

    // Use the permissions trait, so we can check PLP role permissions on the plugin.
    use permissions;

    // Use the settings table trait.
    use settings;

    /**
     * Plugin model uses the mdl_block_plp_plugins table.
     */
    const TABLE = 'block_plp_plugins';

    /**
     * Array of possible settings that the plugin can have.
     */
    const VALID_SETTINGS = ['background_colour', 'font_colour'];

    /**
     * Table where the plugin settings are stored.
     */
    const SETTINGS_TABLE = 'block_plp_plugin_settings';

    /**
     * Field to reference a particular plugin in the settings table.
     */
    const SETTINGS_FIELD = 'pluginid';

    /**
     * Array of default PLP role permissions to be inserted when a new plugin is created.
     */
    const DEFAULT_PERMISSIONS = [

        // User on their own PLP can edit/delete any data they create, but it is up to the institution to define which ones
        // they can 'add' the data to in the first place.
        'user' => ['add' => false, 'edit_own' => true, 'edit_any' => false, 'delete_own' => true, 'delete_any' => false],

        // Teachers and tutors by default can add, edit and delete anything on the PLP of any student they have access to.
        'teacher' => ['add' => true, 'edit_own' => true, 'edit_any' => true, 'delete_own' => true, 'delete_any' => true],
        'tutor' => ['add' => true, 'edit_own' => true, 'edit_any' => true, 'delete_own' => true, 'delete_any' => true],

        // Managers by default don't have any editing access, as they are most likely from a Quality/Reporting area who just
        // need to see the data.
        'manager' => ['add' => false, 'edit_own' => false, 'edit_any' => false, 'delete_own' => false, 'delete_any' => false],

    ];

    /**
     * The string used to identify which permission records are associated with a plugin.
     */
    const PERMISSION_REF = 'plugin';

    /**
     * Unique name of the plugin
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
     * @var int Default: 0 for a new custom plugin
     */
    protected $version = 0;

    /**
     * Integer flag to show enabled status of the plugin
     * @var int
     */
    protected $enabled;

    /**
     * Integer flag to denote if the plugin is a custom-created one
     * @var int
     */
    protected $custom = 0;

    /**
     * Array of pages within this plugin
     * @var plugin_page[]
     */
    protected $pages;

    /**
     * Check if the plugin is enabled
     * @return bool
     */
    public function is_enabled() : bool {
        return ($this->enabled == 1);
    }

    /**
     * Get the array of pages on this plugin.
     * @return array
     */
    public function get_pages() : array {

        if (!$this->pages) {
            $this->load_pages();
        }

        return $this->pages;

    }

    /**
     * Load the pages on this plugin into the pages array
     * @return void
     */
    protected function load_pages() : void {
        $this->pages = plugin_page::all(['pluginid' => $this->id]);
    }

    /**
     * Overwrite the standard ORM mapping and create plugin_page objects to be stored in the pages array
     * @param array $pages
     * @return void
     */
    public function map_pages(array $pages) : void {

        // If we are mapping, we must assume that we are starting from scratch. So clear any existing fields from the array.
        $this->pages = [];

        foreach ($pages as $pagedata) {
            $this->pages[] = plugin_page::from_array($pagedata);
        }

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
     * Save the plugin.
     * @return bool
     */
    public function save() : bool {

        // Save the plugin first.
        if (!parent::save()) {
            return false;
        }

        // Store a result variable so we can return false if anything beneath us fails.
        $result = true;

        // Now we should have the object id (if the record was new) and we can use it to save the fields.
        foreach ($this->pages as $page) {
            $page->set('pluginid', $this->id);
            $result = $result && $page->save();
        }

        return $result;

    }

    /**
     * Return the array of valid settings which can be set against the plugin
     * @return array
     */
    public function get_valid_settings() : array {
        return static::VALID_SETTINGS;
    }

    /**
     * Toggle the value of the enabled setting to enable/disable.
     */
    public function toggle_enabled() {
        $this->set('enabled', !$this->get('enabled'));
        return $this->save();
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

        $handle = @opendir($dir);
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