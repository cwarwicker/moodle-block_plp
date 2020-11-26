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
 * This CLI script will create any missing roles required for the PLP.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define('CLI_SCRIPT', true);

require_once('../../../config.php');

$plp = new \block_plp\plp();

// This script will create the default role to be used for Personal Tutors and PLP Managers.
// If they have already chosen a role for these, then it will be skipped.

// Personal Tutor role.
$personal_tutor_role = $plp->get_roles('tutor');
if (is_null($personal_tutor_role)) {

    $role = new stdClass();
    $role->name = get_string('personaltutor', 'block_plp');
    $role->shortname = 'plp_tutor';
    $role->desc = get_string('personaltutor:role:desc', 'block_plp');

    // Create the role, using the non-editing teacher archetype.
    // This will be assigned to users on other user contexts, however they may also end up allowing assignments on courses, so
    // using the non-editing teacher role will give them the access they need if they do that.
    $role->id = create_role($role->name, $role->shortname, $role->desc, 'teacher');
    if ($role->id) {

        // Set the role contexts.
        set_role_contextlevels($role->id, array(CONTEXT_USER));

        // Update the capabilities for this role.
        assign_capability('block/plp:view', CAP_ALLOW, $role->id, context_system::instance());

        // Update the config setting.
        $plp->update_setting('role_tutor', $role->id);

        // Print success message
        mtrace(sprintf(get_string('creatednewrole', 'block_plp'), $role->id, $role->name, $role->shortname, $role->desc));

    } else {
        mtrace(sprintf(get_string('error:role:create', 'block_plp'), $role->shortname));

    }

} else {
    mtrace('Personal Tutor role has already been configured, so skipping creation.');
}

// PLP Manager role.
$manager_role = $plp->get_roles('manager');
if (is_null($manager_role)) {

    $role = new stdClass();
    $role->name = get_string('plpmanager', 'block_plp');
    $role->shortname = 'plp_manager';
    $role->desc = get_string('plpmanager:role:desc', 'block_plp');

    // Create the role, using the authenticated user archetype.
    // Users will be assigned to this on the system context, so we don't need any particular archetype, standard user will do.
    $role->id = create_role($role->name, $role->shortname, $role->desc, 'frontpage');
    if ($role->id) {

        // Set the role contexts.
        set_role_contextlevels($role->id, array(CONTEXT_SYSTEM));

        // Update the capabilities for this role.
        assign_capability('block/plp:view_any', CAP_ALLOW, $role->id, context_system::instance());

        // Update the config setting.
        $plp->update_setting('role_manager', $role->id);

        // Print success message
        mtrace(sprintf(get_string('creatednewrole', 'block_plp'), $role->id, $role->name, $role->shortname, $role->desc));

    } else {
        mtrace(sprintf(get_string('error:role:create', 'block_plp'), $role->shortname));

    }

} else {
    mtrace('PLP Manager role has already been configured, so skipping creation.');
}