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
 * This file contains the config.php page, which is used for editing/viewing all configuration settings in the plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use block_plp\controller;
use block_plp\core\controllers\config_controller;

require_once('../../config.php');

// No require_login() here as it is handled by the controller.

// Which page are we trying to view?
$page = optional_param('page', 'overview', PARAM_TEXT);
$action = optional_param('action', null, PARAM_TEXT);

// Load the config controller.
config_controller::load($page, $action, [
    controller::OPTION_REQUIRE_CAPABILITIES => ['block/plp:configure' => context_system::instance()]
])->run();