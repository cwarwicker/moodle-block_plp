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
 * This file holds the main class for the Text Editor form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\models\form_field;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Text Editor form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class editor extends form_field {

    /**
     * Add any extra data to the array passed into the mustache template.
     * @return void
     */
    protected function apply_extra_data(&$data) : void {

        global $PAGE;

        // Calling the editor function here will cause a debugging warning about page url not being set. However, this is not set
        // until later on in the rendering process. So, annoyingly, going to have to set it and then overwrite it later.
        $PAGE->set_url($_SERVER['REQUEST_URI']);

        // Get the preferred text editor.
        $editor = editors_get_preferred_editor();
        $editor->use_editor($this->elementid, [
            'autosave' => false
        ]);

    }

}