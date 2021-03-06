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
     * User's username
     * @var string
     */
    protected $username;

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
     * Return a user instance of whichever user is associated with this object instance.
     *
     * This is overridden from the permissions trait, as we don't want to store $this in a property on itself.
     * @return user
     */
    public function get_user() : user {
        return $this;
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
        $categories = explode(',', $plp->get_setting('categories'));
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
    public function get_permission_contexts(int $userid) : array {

        $plp = new plp();

        // If we have already loaded this, just retrieve it.
        if (array_key_exists($userid, $this->permissions)) {
            return $this->permissions[$userid];
        }

        // These are the capabilities we check to see if a user has general 'access' to view another user's PLP.
        $capabilities = ['view' => 'block/plp:view', 'manager' => 'block/plp:view_any', 'view_my' => 'block/plp:view_my'];

        // Store the contexts we have the capability in, in this array.
        $contexts = [];

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
            }
        }

        // Personal/Special Tutor - Tutors will be assigned to the user on their user context.
        $context = context_user::instance($userid);
        if (has_capability($capabilities['view'], $context, $this->get('id'))) {
            $contexts[] = $context;
        }

        // PLP Manager - Should be assigned with the specific 'view_any' capability and should be at the system level.
        $context = context_system::instance();
        if (has_capability($capabilities['manager'], $context, $this->get('id'))) {
            $contexts[] = $context;
        }

        // Self - If the current user is the same one as the user they are trying to view, they can always see themselves.
        // For this we will use the frontpage course context, as they should be an Authenticated user there.
        if ($this->get('id') == $userid) {
            $contexts[] = context_course::instance(SITEID);
        }

        // TODO: External users.

        $this->permissions[$userid] = $contexts;
        return $this->permissions[$userid];

    }

    /**
     * Check if the user has a given capability, in relation to a PLP context, such as a user, plugin record, etc...
     * @param string $capability The Moodle capability to check.
     * @param model $instance The model object
     * @return bool
     */
    public function has_permission(string $capability, model $instance) : bool {

        // Find out from the instance, which userid it is associated with.
        // For example, a user will have the userid as it's 'id'. A tutorial record will have it as its 'userid', etc...
        $user = $instance->get_user();

        // First thing to check, is can we view this user at all? If not, no point going any further.
        if (!$this->can_view($user->get('id'))) {
            return false;
        }

        // Find out in which contexts we have access to this user (if any).
        $contexts = $this->get_permission_contexts($user->get('id'));

        // Check if this user has the capability in any of the contexts in which they are able to access the other user's PLP.
        return $this->check_permissions($capability, $contexts);

    }

    /**
     * Check if the current user is able to view the specified user's PLP at all.
     * @param int $userid ID of the Moodle user who we want to view.
     * @return bool
     */
    public function can_view(int $userid) : bool {

        // We can view this user, if there are any contexts returned.
        return (!empty($this->get_permission_contexts($userid)));

    }

    /**
     * Load a user object for the currently logged in user.
     * @return model
     */
    public static function load_by_session() {

        global $USER;
        return static::load($USER->id);

    }

}