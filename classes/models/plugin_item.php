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
 * This file holds the main class for plugin items (used in multi and incremental sections).
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models;

use block_plp\model;
use block_plp\traits\permissions;

/**
 * This is the class for plugin items.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class plugin_item extends model {

    // Use the permissions trait so plugin items can have their own permissions.
    use permissions;

    /**
     * Reference string for plugin item permissions in the database.
     */
    const PERMISSION_REF = 'plugin_item';

    /**
     * Plugin item model uses the mdl_block_plp_plugin_items table.
     */
    const TABLE = 'block_plp_plugin_items';

    /**
     * ID of plugin_section this item is attached to
     * @var int
     */
    protected $sectionid;

    /**
     * ID of user whose PLP this item is associated with
     * @var int
     */
    protected $userid;

    /**
     * ID of user who created this item
     * @var int
     */
    protected $setbyuserid;

    /**
     * Unix timestamp when this item was created
     * @var int
     */
    protected $settime;

    /**
     * User object for the user who created the item.
     * @var user
     */
    protected $setbyuser;

    /**
     * Plugin ID
     * @var int
     */
    protected $pluginid;

    /**
     * Get the plugin object from the plugin ID.
     * @return plugin
     */
    public function get_plugin() : plugin {
        return plugin::load($this->pluginid);
    }

    /**
     * Get the plugin_section where this item lives in.
     * @return plugin_section
     */
    public function get_section() : plugin_section {
        return plugin_section::load($this->sectionid);
    }

    /**
     * Get the user object of the user who created this item.
     * @return user
     */
    public function get_set_by_user() : user {

        if (!$this->setbyuser) {
            $this->setbyuser = user::load($this->setbyuserid);
        }

        return $this->setbyuser;

    }

    /**
     * Get the name of the user who set this item.
     * @return string
     */
    public function get_set_by_user_name() : string {

        global $DB;
        $user = $DB->get_record('user', ['id' => $this->get('setbyuserid')]);
        return fullname($user);

    }

    /**
     * Create a new plugin_item record.
     * @param int $sectionid
     * @param int $userid
     * @return plugin_item The saved record.
     */
    public static function create(int $sectionid, int $userid) : plugin_item {

        global $USER;

        $obj = new plugin_item();
        $obj->set('sectionid', $sectionid);
        $obj->set('userid', $userid);
        $obj->set('setbyuserid', $USER->id);
        $obj->set('settime', time());
        $obj->save();

        return $obj;

    }


}