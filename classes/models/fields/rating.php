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
 * This file holds the main class for the Rating form field.
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
 * This file holds the main class for the Rating form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class rating extends form_field {

    /**
     * Return the simple HTML for displaying the value of the field, in non-editing mode.
     * This should be overridden by any form fields which do not use the simple template, of just display a text value.
     * @return string
     */
    protected function get_value_html() : string {

        global $PAGE;

        $template = 'block_plp/fields/value/rating';

        $data = [];

        // Get the actual user value to display (in whatever format suits this field type) or just '-' to denote no data.
        $data['value'] = $this->get_user_data() ?? '0';

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

}