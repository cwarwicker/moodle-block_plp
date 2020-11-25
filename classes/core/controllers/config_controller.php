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
 * This file holds the config_controller class. For handling everything related to the config.php page.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\core\controllers;

use block_plp\controller as base_controller;
use block_plp\core\forms\settings_form;
use block_plp\plp;
use core\notification;

defined('MOODLE_INTERNAL') or die();

/**
 * This is the config controller class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class config_controller extends base_controller {

    /**
     * Controller action to run on the settings config page.
     * @return bool
     */
    public function call_settings() {

        $form = new settings_form();

        // If the form is submitted.
        if ($data = $form->get_data()) {

            $plp = new plp();

            // Update settings.
            $plp->update_setting('layout', $data->layout);
            $plp->update_setting('dock', $data->dock);
            $plp->update_setting('logo', $data->logo);
            $plp->update_setting('category_usage', $data->category_usage);
            $plp->update_setting('categories', $data->categories);
            $plp->update_setting('academic_year_enabled', $data->academic_year_enabled);
            $plp->update_setting('academic_year', $data->academic_year);
            $plp->update_setting('email_alerts_enabled', $data->email_alerts_enabled);
            $plp->update_setting('role_student', $data->role_student);
            $plp->update_setting('role_teacher', $data->role_teacher);
            $plp->update_setting('role_tutor', $data->role_tutor);
            $plp->update_setting('role_manager', $data->role_manager);

            notification::success(get_string('configsaved', 'block_plp'));

        }

        // Pass the form object through to the template for rendering.
        $this->get_template()->set_form($form);

        return true;

    }

}