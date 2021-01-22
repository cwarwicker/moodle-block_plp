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
 * This file contains the user class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models;

use block_plp\exceptions\permission_exception;
use block_plp\model;
use block_plp\plp;
use block_plp\traits\permissions;
use context_course;
use context_system;
use context_user;

/**
 * User class for dealing with anything user-related in the plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class user extends model {

    // Use the permissions trait in this class.
    use permissions;

    /**
     * User model uses the mdl_user table.
     */
    const TABLE = 'user';

    /**
     * 'PLP Role' for a user on their own PLP
     */
    const ROLE_USER = 'user';

    /**
     * 'PLP Role' for a user who has access to another user, via a course context.
     */
    const ROLE_TEACHER = 'teacher';

    /**
     * 'PLP Role' for a user who has access to another user, via a user context.
     */
    const ROLE_TUTOR = 'tutor';

    /**
     * 'PLP Role' for a user who has access to another user, via a system context.
     */
    const ROLE_MANAGER = 'manager';

    /**
     * Array of courses this user is enrolled on.
     * @var array
     */
    protected $courses = [];

    /**
     * Array to store all of the contexts on which this user can have access ('view') to users, by their id.
     * @var array
     */
    protected $permissions = [];

    /**
     * Array of 'PLP roles' this user has, in relation to the selected user they are viewing.
     * @var array
     */
    protected $roles = [];

    /**
     * Object to store the actual user data from the mdl_user table.
     * @var \stdClass
     */
    protected $info;

    /**
     * Return a user instance of whichever user is associated with this object instance.
     * This is overridden from the permissions trait, as we don't want to store $this in a property on itself.
     * @return user
     */
    public function get_user() : user {
        return $this;
    }

    /**
     * Get the user data from the database
     * @return \stdClass
     */
    public function get_info() : \stdClass {

        global $DB;

        if (!$this->info) {
            $this->info = $DB->get_record('user', ['id' => $this->id]);
        }

        return $this->info;
    }

    /**
     * Get the user's full name.
     * @return string
     */
    public function get_name() : string {
        // TODO: setting to format the name.
        return fullname($this->get_info());
    }

    /**
     * Get an array of all the courses this user is enrolled on in the specified role
     * @param array $roleids Array of role IDs to use.
     * @return array Array of courses.
     */
    public function get_courses(array $roleids = null) {

        global $DB;

        $plp = new plp();

        // See if we have already loaded this.
        if (!empty($this->courses)) {
            return $this->courses;
        }

        $params = ['contextlevel' => CONTEXT_COURSE, 'userid' => $this->get('id')];
        $extrasql = '';

        if (!is_null($roleids)) {
            list($inorequal, $extraparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'roleparam');
            $extrasql .= ' AND r.id ' . $inorequal;
            $params = $params + $extraparams;
        }

        // Exclude any excluded categories, or only include any specifically included categories.
        $categories = $plp->get_setting('categories');
        if ($plp->get_setting('category_usage') == 'exc') {

            list($notinorequal, $extraparams) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED, 'catparam', false);
            $extrasql .= ' AND cc.id ' . $notinorequal;
            $params = $params + $extraparams;

        } else if ($plp->get_setting('category_usage') == 'inc') {

            list($inorequal, $extraparams) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED, 'catparam');
            $extrasql .= ' AND cc.id ' . $inorequal;
            $params = $params + $extraparams;

        }

        $records = $DB->get_records_sql("SELECT DISTINCT c.id
                                           FROM {course} c
                                           JOIN {course_categories} cc ON cc.id = c.category
                                           JOIN {context} x ON x.instanceid = c.id
                                           JOIN {role_assignments} ra ON ra.contextid = x.id
                                           JOIN {role} r ON r.id = ra.roleid
                                           WHERE x.contextlevel = :contextlevel
                                           AND ra.userid = :userid
                                           {$extrasql}
                                           ORDER BY c.shortname ASC", $params);

        $courses = [];
        foreach ($records as $record) {
            $courses[$record->id] = new course($record->id);
        }

        return $courses;

    }

    /**
     * Get all of the permissions contexts we need, when checking permissions on anything related to the specified user.
     * @param int $userid ID of the Moodle user whose we are trying to do something with.
     * @return array Array of contexts
     */
    public function get_capability_contexts(int $userid) : array {

        $plp = new plp();

        // If we have already loaded this, just retrieve it.
        if (array_key_exists($userid, $this->permissions)) {
            return $this->permissions[$userid];
        }

        // These are the capabilities we check to see if a user has general 'access' to view another user's PLP.
        $capabilities = ['view' => 'block/plp:view', 'manager' => 'block/plp:view_any', 'view_my' => 'block/plp:view_my'];

        // Store the contexts and roles we have the capability in, in the related arrays.
        $contexts = [];
        $roles = [];

        // Load the user.
        $student = static::load($userid);

        // Site admin - Site admins can see everything, so we don't need to check anything else.
        if (is_siteadmin($this->get('id'))) {
            $contexts[] = context_system::instance();
        }

        // Course Teacher - Teachers will have the 'view' capability on a course, so we check it on course contexts.
        // So we find every course the specified user is on which this user is also on, then check the capability there.
        $studentcourses = $student->get_courses($plp->get_roles('student'));
        foreach ($studentcourses as $course) {
            // Does our current user have the view capability on this course context?
            $context = $course->get_context();
            if (has_capability($capabilities['view'], $context, $this->get('id'))) {
                $contexts[] = $context;
                $roles[] = static::ROLE_TEACHER;
            }
        }

        // Personal/Special Tutor - Tutors will be assigned to the user on their user context.
        $context = context_user::instance($userid);
        if (has_capability($capabilities['view'], $context, $this->get('id'))) {
            $contexts[] = $context;
            $roles[] = static::ROLE_TUTOR;
        }

        // PLP Manager - Should be assigned with the specific 'view_any' capability and should be at the system level.
        $context = context_system::instance();
        if (has_capability($capabilities['manager'], $context, $this->get('id'))) {
            $contexts[] = $context;
            $roles[] = static::ROLE_MANAGER;
        }

        // Self - If the current user is the same one as the user they are trying to view, they can always see themselves.
        // For this we will use the frontpage course context, as they should be an Authenticated user there.
        if ($this->get('id') == $userid) {
            $contexts[] = context_course::instance(SITEID);
            $roles[] = static::ROLE_USER;
        }

        // TODO: External users.

        // Strip out duplicate roles to assign to the roles array.
        $this->roles[$userid] = array_unique($roles);

        // Assign permissions to the array.
        $this->permissions[$userid] = $contexts;

        return $this->permissions[$userid];

    }

    /**
     * Check if the user has a given capability, in relation to a Moodle context.
     * @param string $capability The Moodle capability to check.
     * @param model $instance The model object
     * @return bool
     */
    public function has_capability(string $capability, model $instance) : bool {

        // Find out from the instance, which userid it is associated with.
        // For example, a user will have the userid as it's 'id'. A tutorial record will have it as its 'userid', etc...
        $user = $instance->get_user();

        // First thing to check, is can we view this user at all? If not, no point going any further.
        if (!$this->can_view($user->get('id'))) {
            return false;
        }

        // Find out in which contexts we have access to this user (if any).
        $contexts = $this->get_capability_contexts($user->get('id'));

        // Check if this user has the capability in any of the contexts in which they are able to access the other user's PLP.
        return $this->check_capabilities($capability, $contexts);

    }

    /**
     * Wrapper method for checking if this user can edit a given plugin section.
     * @param plugin_section $section The plugin_section object.
     * @return bool
     */
    public function can_edit_plugin_section(plugin_section $section) : bool {

        // Do we have the 'edit_own' or 'edit_any' permission on the related plugin?
        return ($this->has_permission(plugin::PERMISSION_REF, 'edit_own', $section)
            || $this->has_permission(plugin::PERMISSION_REF, 'edit_any', $section));

    }

    /**
     * Check if the user can view a specific plugin_item.
     * @param plugin_item $item
     * @return bool
     */
    public function can_view_plugin_item(plugin_item $item) : bool {

        // The only checks we need to do here are for specific plugin_item permissions. As if the user does not have 'view'
        // on the plugin, then they wouldn't get this far.
        if ($item->has_defined_permission('view', $item, $this->get_permission_roles($item->get_user()->get('id')))) {
            return ($this->has_permission(plugin_item::PERMISSION_REF, 'view', $item));
        }

        return true;

    }

    /**
     * Wrapper method for checking if this user can edit a given plugin item.
     * @param plugin_item $item
     * @return bool
     */
    public function can_edit_plugin_item(plugin_item $item) : bool {

        // First, check if any specific permissions have been added to the item itself.
        // If they have, these override plugin permissions and should be used instead.
        if ($item->has_defined_permission('edit', $item, $this->get_permission_roles($item->get_user()->get('id')))) {
            return ($this->has_permission(plugin_item::PERMISSION_REF, 'edit', $item));
        }

        // Do we have the 'edit_own' permission (checking the created by user).
        // Or the 'edit_any' permission (checking the PLP user).
        return ($this->has_permission(plugin::PERMISSION_REF, 'edit_own', $item, [$item->get_set_by_user()])
            || $this->has_permission(plugin::PERMISSION_REF, 'edit_any', $item));

    }

    /**
     * Wrapper method for checking if this user can delete a given plugin item.
     * @param plugin_item $item
     * @return bool
     */
    public function can_delete_plugin_item(plugin_item $item) : bool {

        // First, check if any specific permissions have been added to the item itself.
        // If they have, these override plugin permissions and should be used instead.
        if ($item->has_defined_permission('delete', $item, $this->get_permission_roles($item->get_user()->get('id')))) {
            return ($this->has_permission(plugin_item::PERMISSION_REF, 'delete', $item));
        }

        // Do we have the 'edit_own' permission (checking the created by user).
        // Or the 'edit_any' permission (checking the PLP user).
        return ($this->has_permission(plugin::PERMISSION_REF, 'delete_own', $item, [$item->get_set_by_user()])
            || $this->has_permission(plugin::PERMISSION_REF, 'delete_any', $item));

    }

    /**
     * Check if the user has a given permission within a plugin.
     * @param string $reference The referenced PLP context. E.g. plugin, plugin_item, etc...
     * @param string $permission This is the PLP permission to check for, e.g. 'edit_own', 'delete_any', etc...
     * @param model $model This is the plugin or the item, where we can get the plugin ID from.
     * @param array|null $extradata Any extra data that we need to pass through to the permission check.
     * @return bool
     */
    public function has_permission(string $reference, string $permission, model $model, ?array $extradata = null) : bool {

        // If you are an admin, then you have all permissions.
        if (is_siteadmin($this->id)) {
            return true;
        }

        // Firstly get the roles the current user has, in relation to the selected user.
        $roles = $this->get_permission_roles($model->get_user()->get('id'));

        // Load the referenced object and work out the method name to call.
        $object = $this->get_reference($reference, $model);
        $method = 'can_do_' . $permission;

        return $this->{$method}($object, $roles, $extradata);

    }

    /**
     * Load the correct object from the parent model, for permission checking.
     * @param string $reference
     * @param model $model
     * @return model
     * @throws permission_exception
     */
    protected function get_reference(string $reference, model $model) : model {

        switch ($reference) {
            case plugin::PERMISSION_REF:
                return $model->get_plugin(); // Model will be a plugin_item and we want the plugin object.
            case plugin_item::PERMISSION_REF:
                return $model; // Model will be a plugin_item so can return itself.
            default:
                throw new permission_exception('exception:permission:reference');
        }

    }

    /**
     * Check if any of the given PLP roles are able to do the 'view' action. This will come from the plugin_item permissions.
     * @param plugin_item $item The PLP plugin_item object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata This should be an array containing the user object related to the item.
     * @return bool
     */
    public function can_do_view(plugin_item $item, array $roles, ?array $extradata = null) : bool {
        return ($item->can_roles_do('view', $roles));
    }

    /**
     * Check if any of the given PLP roles are able to do the 'edit' action. This will come from the plugin_item permissions.
     * @param plugin_item $item The PLP plugin_item object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata This should be an array containing the user object related to the item.
     * @return bool
     */
    public function can_do_edit(plugin_item $item, array $roles, ?array $extradata = null) : bool {
        return ($item->can_roles_do('edit', $roles));
    }

    /**
     * Check if any of the given PLP roles are able to do the 'delete' action. This will come from the plugin_item permissions.
     * @param plugin_item $item The PLP plugin_item object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata This should be an array containing the user object related to the item.
     * @return bool
     */
    public function can_do_delete(plugin_item $item, array $roles, ?array $extradata = null) : bool {
        return ($item->can_roles_do('delete', $roles));
    }

    /**
     * Check if any of the given PLP roles are able to do the 'edit_own' action, by checking specified plugin's permissions.
     * @param plugin $plugin The PLP plugin object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata This should be an array containing the user object related to the section/item.
     * @return bool
     */
    public function can_do_edit_own(plugin $plugin, array $roles, ?array $extradata = null) : bool {
        list($user) = $extradata;
        return ($user->get('id') === $this->get('id')) && $plugin->can_roles_do('edit_own', $roles);
    }

    /**
     * Check if any of the given PLP roles are able to do the 'edit_any' action, by checking specified plugin's permissions.
     * @param plugin $plugin The PLP plugin object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata Any extra data that we need to pass through to the permission check.
     * @return bool
     */
    public function can_do_edit_any(plugin $plugin, array $roles, ?array $extradata = null) : bool {
        return ($plugin->can_roles_do('edit_any', $roles));
    }

    /**
     * Check if any of the given PLP roles are able to do the 'delete_own' action, by checking specified plugin's permissions.
     * @param plugin $plugin The PLP plugin object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata This should be an array containing the user object related to the section/item.
     * @return bool
     */
    public function can_do_delete_own(plugin $plugin, array $roles, ?array $extradata = null) : bool {
        list($user) = $extradata;
        return ($user->get('id') === $this->get('id')) && $plugin->can_roles_do('delete_own', $roles);
    }

    /**
     * Check if any of the given PLP roles are able to do the 'delete_any' action, by checking specified plugin's permissions.
     * @param plugin $plugin The PLP plugin object
     * @param array $roles Array of PLP roles as loaded from the user object.
     * @param array|null $extradata Any extra data that we need to pass through to the permission check.
     * @return bool
     */
    public function can_do_delete_any(plugin $plugin, array $roles, ?array $extradata = null) : bool {
        return ($plugin->can_roles_do('delete_any', $roles));
    }

    /**
     * Get the user's PLP roles, as loaded when checking their permissions contexts for the selected user.
     * @param int $userid
     * @return array
     */
    protected function get_permission_roles(int $userid) : array {

        // Load the permission contexts, which also populates the roles.
        $this->get_capability_contexts($userid);
        return $this->roles[$userid];

    }

    /**
     * Check if the current user is able to view the specified user's PLP at all.
     * @param int $userid ID of the Moodle user who we want to view.
     * @return bool
     */
    public function can_view(int $userid) : bool {

        // We can view this user, if there are any contexts returned.
        return (!empty($this->get_capability_contexts($userid)));

    }

    /**
     * Load a user object for the currently logged in user.
     * @return user
     */
    public static function load_by_session() {

        global $USER;
        return static::load($USER->id);

    }

}