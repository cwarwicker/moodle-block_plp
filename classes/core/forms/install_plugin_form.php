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

use block_plp\models\plugin;
use block_plp\moodle;
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
class install_plugin_form extends moodleform {


    /**
     * Define the fields for the plugin installation form
     */
    protected function definition() {

        $moodle = new moodle();

        $this->_form->addElement('hidden', 'page');
        $this->_form->setType('page', PARAM_TEXT);

        // Type of install.
        $this->_form->addElement('select', 'install_type', get_string('plugin:install:type', 'block_plp'), [
            '' => '',
            'core' => get_string('plugin:core', 'block_plp'),
            'external' => get_string('plugin:external', 'block_plp'),
            'import' => get_string('plugin:import', 'block_plp'),
            'custom' => get_string('plugin:custom', 'block_plp'),
        ]);

        // Core plugin - This gets a list of the available core plugins you can install.
        // Add an empty option to the front.
        $coreplugins = [''] + plugin::get_core_plugins();
        $this->_form->addElement('select', 'core_plugin', get_string('plugin', 'block_plp'), $coreplugins);

        // External plugin - This gives you a list of all the plugins installed in Moodle to try and install a plp plugin from.
        $externalplugins = [['']] + $moodle->get_plugins();
        $this->_form->addElement('selectgroups', 'external_plugin', get_string('plugin', 'block_plp'), $externalplugins);
        $this->_form->addElement('text', 'external_file', get_string('plugin:file', 'block_plp'));
        $this->_form->setType('external_file', PARAM_TEXT);

        // Import - This gives you a file upload to import an XML file.
        $this->_form->addElement('filepicker', 'import_plugin', get_string('plugin', 'block_plp'), null, [
            'accepted_types' => 'xml'
        ]);

        // Custom - This takes you to a new plugin form.
        // This doesn't need any extra fields, as it will just redirect you to the new plugin form.

        // Hide fields when not applicable.
        $this->_form->hideIf('core_plugin', 'install_type', 'neq', 'core');
        $this->_form->hideIf('external_plugin', 'install_type', 'neq', 'external');
        $this->_form->hideIf('external_file', 'install_type', 'neq', 'external');
        $this->_form->hideIf('import_plugin', 'install_type', 'neq', 'import');

        $this->_form->addElement('submit', 'install', get_string('install', 'block_plp'));
        $this->_form->hideIf('install', 'install_type', 'eq', '');

    }
}