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
 * Language Strings for block_plp.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

$string['pluginname'] = 'Personal Learning Plan';
$string['pluginname:short'] = 'PLP';
$string['pluginname:my'] = 'My PLP';

// Error messages.
$string['error:class'] = 'Unable to locate class: {$a}. This is probably a coding problem and needs to be fixed by the developer.';
$string['error:form:required'] = 'Field ({$a}) is required.';
$string['error:invalidclassproperty'] = 'Property ({$a}) does not exist on this class.';
$string['error:mis:connection'] = 'Unable to connect to external database.';
$string['error:mis:type'] = 'Database type ({$a}) is not supported.';
$string['error:role:create'] = 'Something went wrong creating the {$a} role.';
$string['error:templates:invalid'] = 'Invalid template file';
$string['error:xml:content'] = 'To output as XML the content provided must be an array. (Found: {$a})';
$string['error:unknown'] = 'Something went wrong. This is probably a coding problem and needs to be fixed by the developer.';

// Capabilities.
$string['plp:addinstance'] = 'Add an instance of the block';
$string['plp:configure'] = 'Configure the settings of the PLP block';
$string['plp:view'] = 'View a user\'s PLP';
$string['plp:view_any'] = 'View any user\'s PLP';

// Page titles.
$string['title:core:config:overview'] = 'Configuration - Overview';
$string['title:core:config:mis'] = 'Configuration - MIS Connections';
$string['title:core:config:mis:edit'] = 'Configuration - MIS Connections - Edit';
$string['title:core:config:mis:enabledisable'] = $string['title:core:config:mis'];
$string['title:core:config:mis:delete'] = $string['title:core:config:mis'];
$string['title:core:config:settings'] = 'Configuration - General Settings';
$string['title:core:config:plugins'] = 'Configuration - Plugins';
$string['title:core:config:plugins:enabledisable'] = $string['title:core:config:plugins'];
$string['title:core:config:plugins:delete'] = $string['title:core:config:plugins'];

// General strings.
$string['bottom'] = 'Bottom';
$string['build'] = 'Build';

$string['config'] = 'Configuration';
$string['configsaved'] = 'Configuration settings saved';
$string['connections'] = 'Connections';
$string['creatednewrole'] = 'Created new Moodle role [%d] %s (%s) - %s';

$string['delete:sure'] = 'Are you sure you want to delete <strong>{$a}</strong>?';
$string['disable'] = 'Disable';
$string['disabled'] = 'Disabled';
$string['displayname'] = 'Display Name';

$string['enable'] = 'Enable';
$string['enabled'] = 'Enabled';
$string['environment'] = 'Environment';

$string['generalsettings'] = 'General Settings';

$string['home'] = 'Home';

$string['install'] = 'Install';

$string['left'] = 'Left';

$string['mis'] = 'MIS';
$string['mis:database'] = 'Database Name';
$string['mis:deleted'] = 'MIS Connection ({$a}) deleted';
$string['mis:host'] = 'Host';
$string['mis:long'] = 'Management Information System(s) - Connections';
$string['mis:newconnection'] = 'Create new connection';
$string['mis:saved'] = 'MIS Connection saved';
$string['mis:test'] = 'Test Connection';
$string['mis:test:ok'] = 'Connection successfully established!';
$string['mis:type:mariadb'] = 'MariaDB';
$string['mis:type:mysqli'] = 'Improved MySQL';
$string['mis:type:oci'] = 'Oracle';
$string['mis:type:pgsql'] = 'PostgreSQL';
$string['mis:type:sqlsrv'] = 'Microsoft SQL Server';

$string['path'] = 'Path';
$string['personaltutor'] = 'Personal Tutor';
$string['personaltutor:role:desc'] = 'This role should be used for assigning Tutors directly to students. It should only assigned at the User context (i.e. you should not add this role to courses). This role should be given the capability block/plp:view.';
$string['plpmanager'] = 'PLP Manager';
$string['plpmanager:role:desc'] = 'This role should be used to define users with the permissions to see the PLP data of every student in the system. This role should be given the capability block/plp:view_any and all PLP Managers should be added to this role on the system context.';
$string['plugin'] = 'Plugin';
$string['plugin:core'] = 'Core Plugin';
$string['plugin:custom'] = 'Create New Custom Plugin';
$string['plugin:deleted'] = 'Plugin ({$a}) deleted';
$string['plugin:external'] = 'External Plugin';
$string['plugin:file'] = 'Plugin File';
$string['plugin:import'] = 'Import Plugin From XML';
$string['plugin:install:type'] = 'Type of plugin installation';
$string['plugins'] = 'Plugins';
$string['plugins:install'] = 'Install New Plugin(s)';
$string['plugins:layouts'] = 'Manage Plugin Layouts';
$string['plugins:manage'] = 'Manage Plugins';

$string['notwriteable'] = 'Not Writeable';

$string['overview'] = 'Overview';

$string['recentactivity'] = 'Recent Activity';

$string['settings'] = 'Settings';
$string['settings:academicyear'] = '<strong>Academic Year</strong>';
$string['settings:academicyear:info'] = $string['settings:academicyear'] . '<br>If you set a date for the start of the academic year, then if there is a date associated with any data in the PLP, only data after this start date will be shown.';
$string['settings:alerts'] = '<strong>Email Alerts</strong>';
$string['settings:alerts:info'] = $string['settings:alerts'] . '<br>If enabled, users can configure which alerts they wish to receive from the PLP system.';
$string['settings:categories'] = '<strong>Course Categories</strong>';
$string['settings:categories:info'] = $string['settings:categories'] . '<br>Selected course categories will be either excluded or exclusively included, depending on the setting above.';
$string['settings:categoryuse'] = '<strong>Course Categories Inclusion/Exclusion</strong>';
$string['settings:categoryuse:info'] = $string['settings:categoryuse'] . '<br>By default, all courses a user is enrolled on in the student role will be included in the PLP, for plugins such as Course Reports, Attendance, etc... If you want to limit this to certain courses, you can either choose to <strong>Only Include</strong> courses on certain categories, or <strong>Exclude</strong> courses in certain categories.';
$string['settings:categoryuse:exc'] = 'Exclusion';
$string['settings:categoryuse:inc'] = 'Inclusion';
$string['settings:categoryuse:neither'] = 'Neither (Default)';
$string['settings:dock'] = '<strong>Dock Position</strong>';
$string['settings:dock:info'] = $string['settings:dock'] . '<br>The Dock allows you to re-open plugins in their most recent saved state. Do you want this to be at the bottom of the PLP, or on the left?';
$string['settings:general'] = 'General';
$string['settings:layout'] = '<strong>Page Layout</strong>';
$string['settings:layout:info'] = $string['settings:layout'] . '<br>The PLP looks and functions best in a page layout which allows it to use the full width of the page, with no blocks on either side. Depending on your theme, you may have to experiment with the various layout options to find one which suits you. On a standard <strong>clean</strong> theme, the layout \'login\' should be sufficient.';
$string['settings:logo'] = '<strong>Institution Logo</strong>';
$string['settings:logo:info'] = $string['settings:logo'] . '<br>Some sections can be printed out. If you upload an institution logo here, it will be included in the top left corner of all printable sections.';
$string['settings:role:manager'] = '<strong>PLP Manager Role</strong>';
$string['settings:role:manager:info'] = $string['settings:role:manager'] . '<br>Which role(s) in your system do you wish to use for PLP Managers?';
$string['settings:role:student'] = '<strong>Student Role</strong>';
$string['settings:role:student:info'] = $string['settings:role:student'] . '<br>Which role(s) in your system do you use to define a "student" on a course?';
$string['settings:role:teacher'] = '<strong>Teacher Role</strong>';
$string['settings:role:teacher:info'] = $string['settings:role:teacher'] . '<br>Which role(s) in your system do you use to define a "teacher" on a course?';
$string['settings:role:tutor'] = '<strong>Personal Tutor Role</strong>';
$string['settings:role:tutor:info'] = $string['settings:role:tutor'] . '<br>Which role in your system do you wish to use for Personal Tutor assignments?';


$string['shortname'] = 'Short Name';
$string['stats'] = 'Stats';
$string['systeminfo'] = 'System Information';
$string['system:moodleversion'] = 'Moodle Version';
$string['system:pluginversion'] = 'Plugin Version';
$string['system:updatesavailable'] = 'Update(s) Available';
$string['system:plugindataroot'] = 'Plugin Data Directory';

$string['title'] = 'Title';
$string['type'] = 'Type';

$string['writeable'] = 'Writeable';