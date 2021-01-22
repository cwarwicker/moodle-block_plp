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
 * This file holds the permissions trait.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use block_plp\model;
use block_plp\models\user;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait can be added to a model class and then used to check user capabilities of an instance of that model.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait permissions {

    /**
     * Array of role-based permissions for this object, as defined through the config.
     * @var array
     */
    protected $permissions = [];

    /**
     * The Moodle user associated with this instance.
     * @var user
     */
    protected $user;

    /**
     * Return a user instance of whichever user is associated with this object instance.
     * @return user
     */
    public function get_user() : user {
        return $this->user;
    }

    /**
     * Given a capability and an array of contexts, see if the user has it on any of them.
     * @param string $capability Moodle capability
     * @param array $contexts Array of Moodle contexts to check against
     * @return bool
     */
    protected function check_capabilities(string $capability, array $contexts) : bool {

        // Get the user id from the user object associated with this object.
        $userid = $this->get_user()->get('id');

        foreach ($contexts as $context) {
            if (has_capability($capability, $context, $userid)) {
                return true;
            }
        }

        return false;

    }

    /**
     * Check if the object has a specific permission defined in the database for any of the given roles.
     * @param string $permission
     * @param model $model
     * @param array $roles
     * @return bool
     */
    public function has_defined_permission(string $permission, model $model, array $roles) : bool {

        $permissions = $this->get_permissions();
        foreach ($permissions as $role => $permissions) {
            if (in_array($role, $roles) && array_key_exists($permission, $permissions)) {
                return true;
            }
        }

        return false;

    }

    /**
     * Load the permissions from the database.
     * @return void
     */
    protected function load_permissions() : void {

        global $DB;

        $this->permissions = [];
        $permissions = $DB->get_records('block_plp_permissions', ['ref' => static::PERMISSION_REF, 'refid' => $this->id]);
        foreach ($permissions as $permission) {
            if (!isset($this->permissions[$permission->role])) {
                $this->permissions[$permission->role] = [];
            }
            $this->permissions[$permission->role][$permission->permission] = (bool)$permission->value;
        }

    }



    /**
     * Get the PLP role permissions associated with this object.
     * @return array
     */
    public function get_permissions() : array {

        if (!$this->permissions) {
            $this->load_permissions();
        }

        return $this->permissions;

    }

    /**
     * Check if any of the given PLP roles are able to do the specified action on this object.
     * @param string $action E.g. 'edit_own', 'edit_any', etc...
     * @param array $roles Array of PLP roles, as loaded from the user object.
     * @return bool
     */
    public function can_roles_do(string $action, array $roles) : bool {

        // Load the permissions for this plugin.
        $permissions = $this->get_permissions();

        foreach ($roles as $role) {
            if (isset($permissions[$role][$action]) && $permissions[$role][$action] === true) {
                return true;
            }
        }

        return false;

    }

}