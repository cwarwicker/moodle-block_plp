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
 * This file holds the main class for the Single plugin section.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models\sections;

use block_plp\exceptions\coding_exception;
use block_plp\helper;
use block_plp\models\plugin_section;
use block_plp\models\user;
use block_plp\traits\editable_section;
use block_plp\traits\multiple_section;

/**
 * This is the class for the Multi plugin section.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class multi extends plugin_section {

    // This plugin section is editable and supports multiple items.
    use editable_section, multiple_section {
        multiple_section::save_user_data insteadof editable_section;
    }

    /**
     * Get the data to be passed into the mustache template for the display method.
     * @param string|null $form
     * @return array
     * @throws coding_exception
     */
    protected function get_display_data(?string $form = null) : array {

        if (!is_null($form)) {
            $method = 'get_display_data_' . $form;
            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                throw new coding_exception('exception:code:missingmethod', ['class' => get_class($this), 'method' => $method]);
            }
        }

        $data = [];
        $data['id'] = $this->id;

        // Now get any saved items for this section.
        $data['items'] = [];
        foreach ($this->get_items() as $item) {

            $row = $item->to_array();

            $row['title'] = helper::format_date($row['settime']); // TODO: Set different titles based on plugin.
            $row['setbyuser'] = $item->get_set_by_user_name();
            $row['settime'] = helper::format_date($row['settime']);

            foreach ($this->get_fields() as $field) {

                // As we need to get new data each time depending on the item, we need to reset the data when we set the item.
                $field->set_item($item);

                $row['fields'][] = ['fieldid' => $field->get('id'),
                    'edit' => $field->display(),
                    'value' => $field->display_value()
                ];
            }

            // Permissions to see links.
            $user = user::load_by_session();
            $row['can_edit'] = $user->can_edit_plugin_item($item);
            $row['can_delete'] = $user->can_delete_plugin_item($item);

            $data['items'][] = $row;

        }

        return $data;

    }

    /**
     * Get the form field HTML for displaying in the New Item form.
     * @return array
     */
    protected function get_display_data_new() : array {

        $data = [];
        $data['id'] = $this->id;

        foreach ($this->get_fields() as $field) {
            $data['fields'][] = ['fieldid' => $field->get('id'), 'edit' => $field->display(false)];
        }

        // Apply sesskey and any other core hidden fields we need.
        helper::apply_hidden_form_fields($data);

        return $data;

    }

    /**
     * Return the display HTML for this section.
     * @param string|null $form If specified, this is a different form to display, instead of the default one. E.g. 'new' item form.
     * @return string
     * @throws coding_exception
     */
    public function display(?string $form = null) : string {

        global $PAGE;

        $template = (!is_null($form)) ? 'block_plp/core/plugins/section_multi_' . $form : 'block_plp/core/plugins/section_multi';
        $data = $this->get_display_data($form);

        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

}