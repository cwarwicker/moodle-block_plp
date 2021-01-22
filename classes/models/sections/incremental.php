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
 * This file holds the main class for the Inremental plugin section type.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models\sections;

use block_plp\models\plugin_section;
use block_plp\models\user;
use block_plp\traits\editable_section;
use block_plp\traits\multiple_section;

/**
 * This class is for the Incremental plugin section type
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class incremental extends plugin_section {

    // This plugin section is editable and supports multiple items.
    use editable_section, multiple_section {
        multiple_section::save_user_data insteadof editable_section;
    }

    /**
     * Return the display HTML for this section.
     * @return string
     */
    public function display() : string {

        global $PAGE;
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/core/plugins/section_incremental',
            $this->get_display_data());

    }

    /**
     * Get the data to be passed into the mustache template for the display method.
     * @return array
     */
    protected function get_display_data() : array {

        $user = user::load_by_session();

        $data = [];
        $data['id'] = $this->get('id');
        $data['show_edit_header'] = false;
        $data['show_delete_header'] = false;

        // Populate the field headers for the table.
        $data['headers'] = [];
        foreach ($this->get_fields() as $field) {
            $data['headers'][] = ['title' => $field->get('title')];
        }

        // Now get any saved items for this section.
        $data['items'] = [];
        foreach ($this->get_items() as $item) {

            $row = $item->to_array();

            foreach ($this->get_fields() as $field) {

                // As we need to get new data each time depending on the item, we need to reset the data when we set the item.
                $field->set_item($item);

                // We don't want to show the field titles.
                $field->set('showtitle', false);

                $row['fields'][] = ['fieldid' => $field->get('id'),
                    'edit' => $field->display(),
                    'value' => $field->display_value()
                ];

            }

            // Permissions to see links.
            $row['can_edit'] = $user->can_edit_plugin_item($item);
            $row['can_delete'] = $user->can_delete_plugin_item($item);

            // We need to check if any of the items can be edited/deleted, in order to know whether to show the table headers.
            if ($row['can_edit']) {
                $data['show_edit_header'] = true;
            }

            if ($row['can_delete']) {
                $data['show_delete_header'] = true;
            }

            $data['items'][] = $row;

        }

        return $data;

    }

}