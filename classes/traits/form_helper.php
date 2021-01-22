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
 * Form Helper trait.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use MoodleQuickForm;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait includes some helpful methods to add to forms, which are not available in the standard Moodle forms library.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait form_helper {

    /**
     * Change the value of an element on the form.
     *
     * After you run get_data(), it becomes a bit of a hassle trying to change a value, as set_data() no longer has any effect.
     * This method can be used after you've already run get_data(), if you want to then change the value which is displayed back in
     * the form. For example, changing the id field if you just created a record and want the 'new' form to become an 'edit' form.
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function change_value(string $field, $value) {

        $element = $this->_form->getElement($field);
        if ($element) {
            $element->setValue($value);
        }

    }

    /**
     * Register an array of custom form elements for use.
     * @param array $elements Must contain keys: 'name', 'path' and 'class'. (Path does NOT need to include $CFG->dirroot/)
     */
    public function register(array $elements) {

        global $CFG;

        // Loop through any elements we want to register and try to register them for use.
        foreach ($elements as $element) {

            MoodleQuickForm::registerElementType($element['name'], $CFG->dirroot . DIRECTORY_SEPARATOR . $element['path'],
                $element['class']);

        }

    }

}