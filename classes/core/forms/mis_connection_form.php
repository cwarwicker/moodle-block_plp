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
 * This file holds the new_mis_connection_form class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\core\forms;

use block_plp\models\mis_connection;
use html_writer;
use moodleform;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * This form allows the creation/editing of MIS connections to external databases.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mis_connection_form extends moodleform {

    /**
     * Define the fields of the mis connection form.
     * @return void
     */
    protected function definition() {

        // Hidden elements.
        // Seems that Moodle doesn't keep the parameters in the querystring for the form action, so we'll need to set the page here.
        $this->_form->addElement('hidden', 'page');
        $this->_form->addElement('hidden', 'action');
        $this->_form->addElement('hidden', 'id');
        $this->_form->setType('page', PARAM_TEXT);
        $this->_form->setType('action', PARAM_TEXT);
        $this->_form->setType('id', PARAM_INT);

        // Name of connection.
        $this->_form->addElement('text', 'name', get_string('name'));
        $this->_form->setType('name', PARAM_TEXT);

        // Type of connection.
        $options = mis_connection::get_supported_types();
        array_unshift($options, '');
        $this->_form->addElement('select', 'type', get_string('type', 'block_plp'), $options);

        // Database connection details.
        $this->_form->addElement('text', 'host', get_string('mis:host', 'block_plp'));
        $this->_form->addElement('text', 'database', get_string('mis:database', 'block_plp'));
        $this->_form->addElement('text', 'user', get_string('username'));
        $this->_form->addElement('passwordunmask', 'pass', get_string('password'));
        $this->_form->setType('database', PARAM_TEXT);
        $this->_form->setType('host', PARAM_TEXT);
        $this->_form->setType('user', PARAM_TEXT);
        $this->_form->addElement('static', 'test', '', html_writer::link(
            '#', html_writer::tag('i', '', ['class' => 'fa fa-plug']) . ' ' .
            get_string('mis:test', 'block_plp'), ['class' => 'btn btn-success block_plp-mis_connection_test']) .
            html_writer::tag('div', '', ['class' => 'block_plp-mis_connection_result']));

        $this->_form->addElement('advcheckbox', 'enabled', get_string('enabled', 'block_plp'));

        $this->add_action_buttons();

        // Set field rules.
        $this->_form->addRule('name', get_string('error:form:required', 'block_plp', 'name'), 'required', null, 'server');
        $this->_form->addRule('type', get_string('error:form:required', 'block_plp', 'type'), 'required', null, 'server');
        $this->_form->addRule('host', get_string('error:form:required', 'block_plp', 'host'), 'required', null, 'server');
        $this->_form->addRule('database', get_string('error:form:required', 'block_plp', 'database'), 'required', null, 'server');
        $this->_form->addRule('user', get_string('error:form:required', 'block_plp', 'user'), 'required', null, 'server');

        // Set current settings values into the fields as default values.
        $this->_form->setDefault('page', 'mis');
        $this->_form->setDefault('action', 'edit');

    }

}