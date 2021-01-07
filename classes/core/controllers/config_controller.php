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
use block_plp\core\forms\install_plugin_form;
use block_plp\core\forms\mis_connection_form;
use block_plp\core\forms\settings_form;
use block_plp\models\mis_connection;
use block_plp\models\plugin;
use block_plp\plp;
use block_plp\template;
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
     * Controller method to run on the settings config page.
     * @return bool
     */
    public function page_settings() : bool {

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

    /**
     * Controller method to run on the MIS config page.
     * @return true
     */
    public function page_mis() : bool {
        return true;
    }

    /**
     * Delete an MIS connection
     * @return bool
     */
    public function action_mis_delete() : bool {

        $params = $this->get_required_parameters([
            ['name' => 'id', 'type' => PARAM_INT],
            ['name' => 'confirmed', 'type' => PARAM_INT, 'default' => 0]
        ]);

        $connection = mis_connection::load($params['id']);
        $this->get_template()->connection = $connection;

        // If it's confirmed, run the delete.
        if ($params['confirmed']) {

            // Delete the MIS connection and print the success notification.
            $connection->delete();
            notification::success(get_string('mis:deleted', 'block_plp', $connection->get('name')));

            // Then redirect. We set the action to NULL here so that the get_url() will return just the MIS connection list page.
            $this->set_action(null);
            redirect($this->get_url());

        }

        return true;

    }

    /**
     * Change the enabled status of an MIS connection
     * @return bool
     */
    public function action_mis_enabledisable() : bool {

        $params = $this->get_required_parameters([
            ['name' => 'id', 'type' => PARAM_INT]
        ]);

        $connection = mis_connection::load($params['id']);
        if ($connection->exists() && confirm_sesskey()) {
            $connection->toggle_enabled();
            return true;
        } else {
            return false;
        }

    }

    /**
     * Edit an MIS connection or create a new one.
     * @return bool
     */
    public function action_mis_edit() : bool {

        // Load the MIS connection record into an object.
        $id = required_param('id', PARAM_INT);
        $connection = mis_connection::load($id);

        // Load the form to display.
        $form = new mis_connection_form(null, $connection);

        // If the form is submitted.
        if ($data = $form->get_data()) {

            $connection->set('name', $data->name);
            $connection->set('type', $data->type);
            $connection->set('host', $data->host);
            $connection->set('dbname', $data->database);
            $connection->set('username', $data->user);
            $connection->set('userpassword', $data->pass);
            $connection->set('enabled', $data->enabled);
            $connection->save();

            notification::success(get_string('mis:saved', 'block_plp'));
            redirect($this->get_url(['id' => $connection->get('id')]));

        } else {
            $form->set_data([
                'id' => $connection->get('id'),
                'name' => $connection->get('name'),
                'type' => $connection->get('type'),
                'host' => $connection->get('host'),
                'database' => $connection->get('dbname'),
                'user' => $connection->get('username'),
                'pass' => $connection->get('userpassword'),
                'enabled' => $connection->get('enabled'),
            ]);
        }

        $this->get_template()->connection = $connection;
        $this->get_template()->set_form($form);
        $this->get_template()->add_var('connection', $connection->to_array());
        return true;

    }

    /**
     * Run the MIS Connection test, using the supplied details.
     * Returns JSON response to the template.
     * @return bool
     */
    public function action_mis_test() : bool {

        $this->get_template()->set_response_type(template::RESPONSE_TYPE_JSON);

        // This is a JSON response, so we need to wrap our parameter checks and return an error in the JSON if they do not pass.
        $response = [];

        // Try and get these parameters from the AJAX request.
        list('type' => $type, 'host' => $host, 'database' => $database, 'user' => $user, 'pass' => $pass) =
            $this->get_required_parameters([
                ['name' => 'type', 'type' => PARAM_TEXT],
                ['name' => 'host', 'type' => PARAM_TEXT],
                ['name' => 'database', 'type' => PARAM_TEXT],
                ['name' => 'user', 'type' => PARAM_TEXT],
                ['name' => 'pass', 'type' => PARAM_TEXT],
            ], $this->get_template());

        // Build the connection.
        $connection = new mis_connection();
        $connection->set('type', $type);
        $connection->set('host', $host);
        $connection->set('dbname', $database);
        $connection->set('username', $user);
        $connection->set('userpassword', $pass);

        // Try and connect using these details.
        $db = $connection->connect();
        if (!$db) {
            $response['result'] = false;
            $response['error'] = $connection->get_error();
        } else {
            $response['result'] = true;
            $response['success'] = 'yeah boi';
        }

        $this->get_template()->set_content($response);
        return true;

    }

    /**
     * Plugins page can be used in this controller.
     * @return true
     */
    public function page_plugins() : bool {

        $form = new install_plugin_form();

        // TODO: Process form.

        // Pass the form object through to the template for rendering.
        $this->get_template()->set_form($form);

        return true;
    }

    /**
     * Change the enabled status of a plugin
     * @return bool
     */
    public function action_plugins_enabledisable() : bool {

        $params = $this->get_required_parameters([
            ['name' => 'id', 'type' => PARAM_INT]
        ]);

        $plugin = plugin::load($params['id']);
        if ($plugin->exists() && confirm_sesskey()) {
            $plugin->toggle_enabled();
            return true;
        } else {
            return false;
        }

    }

    /**
     * Delete a plugin
     * @return bool
     */
    public function action_plugins_delete() : bool {

        $params = $this->get_required_parameters([
            ['name' => 'id', 'type' => PARAM_INT],
            ['name' => 'confirmed', 'type' => PARAM_INT, 'default' => 0]
        ]);

        $plugin = plugin::load($params['id']);
        $this->get_template()->plugin = $plugin;

        // If it's confirmed, run the delete.
        if ($params['confirmed']) {

            // Delete the MIS connection and print the success notification.
            $plugin->delete();
            notification::success(get_string('plugin:deleted', 'block_plp', $plugin->get('name')));

            // Then redirect. We set the action to NULL here so that the get_url() will return just the MIS connection list page.
            $this->set_action(null);
            redirect($this->get_url());

        }

        return true;

    }

}