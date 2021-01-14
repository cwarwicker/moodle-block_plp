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
 * This form allows the creation/editing of PLP plugins.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\core\forms;

use block_plp\traits\form_helper;
use moodleform;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * This form allows the creation/editing of PLP plugins.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class plugin_form extends moodleform {

    use form_helper;

    /**
     * Define the fields of the plugin form.
     * @return void
     */
    protected function definition() {

        global $CFG;

        // Hidden fields.
        $this->_form->addElement('hidden', 'id');
        $this->_form->setType('id', PARAM_INT);

        // General config.
        $this->_form->addElement('header', 'general', get_string('general'));
        $this->_form->setExpanded('general', true);

        // Enabled/Disabled checkbox.
        $this->_form->addElement('advcheckbox', 'enabled', get_string('enabled', 'block_plp'),
            get_string('enabled', 'block_plp'));

        // Title.
        $this->_form->addElement('text', 'title', get_string('title', 'block_plp'));
        $this->_form->setType('title', PARAM_TEXT);

        // Header background colour.
        $this->_form->addElement('text', 'background_colour', get_string('plugin:colour:background', 'block_plp'));
        $this->_form->setType('background_colour', PARAM_TEXT);

        // Header font colour.
        $this->_form->addElement('text', 'font_colour', get_string('plugin:colour:font', 'block_plp'));
        $this->_form->setType('font_colour', PARAM_TEXT);

        // Pages config - Each plugin can have multiple pages, which appear as tabs.
        $this->_form->addElement('header', 'pages', get_string('pages', 'block_plp'));
        $this->_form->setExpanded('pages', true);

        // TODO: Pages.

        // Permissions config.
        $this->_form->addElement('header', 'permissions', get_string('permissions', 'block_plp'));
        $this->_form->setExpanded('permissions', true);

        // TODO: permissions.

        // Scheduled task config.
        $this->_form->addElement('header', 'tasks', get_string('tasks', 'block_plp'));
        $this->_form->setExpanded('tasks', true);

        // TODO: permissions.



        // Submit/Cancel buttons.
        $this->add_action_buttons();

        // Default values for the plugin.
        $plugin = $this->_customdata;
        $this->_form->setDefault('id', $plugin->get('id'));
        $this->_form->setDefault('enabled', $plugin->get('enabled'));
        $this->_form->setDefault('title', $plugin->get('title'));
        $this->_form->setDefault('background_colour', $plugin->get_setting('background_colour'));
        $this->_form->setDefault('font_colour', $plugin->get_setting('font_colour'));

    }

    /**
     * Check the submitted form for validation errors.
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) : array {

        $errors = [];

        // Check background colour is valid (hex code).
        $regex = '/^#([A-F0-9]{6}|[A-F0-9]{3})$/i';
        if (!preg_match($regex, $data['background_colour'])) {
            $errors['background_colour'] = get_string('error:colour', 'block_plp');
        }

        // Check font colour is valid (hex code).
        if (!preg_match($regex, $data['font_colour'])) {
            $errors['font_colour'] = get_string('error:colour', 'block_plp');
        }

        return $errors;

    }

}