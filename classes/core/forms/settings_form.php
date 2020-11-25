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
 * This file holds the settings_form class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\core\forms;

use block_plp\moodle;
use block_plp\plp;
use core_course_category;
use moodleform;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Settings form class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class settings_form extends moodleform {

    /**
     * Define the fields of the settings form.
     * @return void
     */
    protected function definition() {

        $moodle = new moodle();
        $plp = new plp();

        // Hidden elements.
        // Seems that Moodle doesn't keep the parameters in the querystring for the form action, so we'll need to set the page here.
        $this->_form->addElement('hidden', 'page');
        $this->_form->setType('page', PARAM_TEXT);

        // Appearance section.
        $this->_form->addElement('header', 'appearance', get_string('appearance'));
        $this->_form->setExpanded('appearance', true);

        // Page layout.
        $this->_form->addElement('select', 'layout', get_string('settings:layout:info', 'block_plp'), $moodle->get_layouts());

        // Dock position.
        $dockpositions = ['left' => get_string('left', 'block_plp'),
            'bottom' => get_string('bottom', 'block_plp')];
        $this->_form->addElement('select', 'dock', get_string('settings:dock:info', 'block_plp'), $dockpositions);

        // Logo.
        $this->_form->addElement('filepicker', 'logo', get_string('settings:logo:info', 'block_plp'), null,
            ['accepted_types' => 'image']);

        // Course categories section.
        $this->_form->addElement('header', 'courses', get_string('courses'));
        $this->_form->setExpanded('courses', true);

        // Use category inclusion, exclusion or neither.
        $this->_form->addGroup([
            $this->_form->createElement('radio', 'category_usage', '', get_string('settings:categoryuse:neither', 'block_plp'),
                'no'),
            $this->_form->createElement('radio', 'category_usage', '', get_string('settings:categoryuse:inc', 'block_plp'), 'inc'),
            $this->_form->createElement('radio', 'category_usage', '', get_string('settings:categoryuse:exc', 'block_plp'), 'exc'),
        ], 'category_usage_radio', get_string('settings:categoryuse:info', 'block_plp'), [' '], false);

        // Category selection for the Inclusion/Exclusion above.
        $categories = core_course_category::make_categories_list();
        $this->_form->addElement('select', 'categories', get_string('settings:categories:info', 'block_plp'), $categories)
            ->setMultiple(true);
        $this->_form->disabledIf( 'categories', 'category_usage', 'eq', 'no');

        // General settings section.
        $this->_form->addElement('header', 'general', get_string('settings:general', 'block_plp'));
        $this->_form->setExpanded('general', true);

        // Academic year.
        $this->_form->addGroup([
            $this->_form->createElement('advcheckbox', 'academic_year_enabled', '', get_string('enable')),
            $this->_form->createElement('date_selector', 'academic_year')
        ], 'academic_year_group', get_string('settings:academicyear:info', 'block_plp'), [' '], false);
        // TODO: This isn't working. Seems to be a moodle bug if date_selector is in a group.
        $this->_form->disabledIf( 'academic_year_group[academic_year]', 'academic_year_group[academic_year_enabled]', 'neq', '1');

        // Email alerts.
        $this->_form->addElement('advcheckbox', 'email_alerts_enabled', get_string('settings:alerts:info', 'block_plp'),
            get_string('enable'));

        // System roles section.
        $this->_form->addElement('header', 'roles', get_string('roles'));
        $this->_form->setExpanded('roles', true);

        $roles = $moodle->get_roles();

        // Student role(s).
        $this->_form->addElement('select', 'role_student', get_string('settings:role:student:info', 'block_plp'), $roles)
            ->setMultiple(true);

        // Teacher role(s).
        $this->_form->addElement('select', 'role_teacher', get_string('settings:role:teacher:info', 'block_plp'), $roles)
            ->setMultiple(true);

        // Tutor role(s).
        $this->_form->addElement('select', 'role_tutor', get_string('settings:role:tutor:info', 'block_plp'), $roles)
            ->setMultiple(true);

        // PLP Manager role(s).
        $this->_form->addElement('select', 'role_manager', get_string('settings:role:manager:info', 'block_plp'), $roles)
            ->setMultiple(true);

        // Submit/Cancel buttons.
        $this->add_action_buttons();

        // Set current settings values into the fields as default values.
        $this->_form->setDefault('page', 'settings');
        $this->_form->setDefault('layout', $plp->get_setting('layout'));
        $this->_form->setDefault('dock', $plp->get_setting('dock'));
        $this->_form->setDefault('logo', $plp->get_setting('logo'));
        $this->_form->setDefault('category_usage', $plp->get_setting('category_usage'));
        $this->_form->setDefault('categories', $plp->get_setting('categories'));
        $this->_form->setDefault('academic_year_enabled', $plp->get_setting('academic_year_enabled'));
        $this->_form->setDefault('academic_year', $plp->get_setting('academic_year'));
        $this->_form->setDefault('email_alerts_enabled', $plp->get_setting('email_alerts_enabled'));
        $this->_form->setDefault('role_student', $plp->get_setting('role_student'));
        $this->_form->setDefault('role_teacher', $plp->get_setting('role_teacher'));
        $this->_form->setDefault('role_tutor', $plp->get_setting('role_tutor'));
        $this->_form->setDefault('role_manager', $plp->get_setting('role_manager'));

    }
}