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
 * This file holds the trait which can be used across different types of plugin_section which support multiple items.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use block_plp\models\plugin_item;
use block_plp\models\user;

/**
 * This is the trait which can be used across different types of plugin_section which support multiple items.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait multiple_section {

    /**
     * Array of Items in this multi section.
     * @var array
     */
    protected $items = [];

    /**
     * Load any plugin items into the items array.
     * @return void
     */
    protected function load_items() : void {

        $user = user::load_by_session();
        $this->items = [];

        $items = plugin_item::all(['sectionid' => $this->id, 'userid' => $this->user->get('id')]);
        if ($items) {
            foreach ($items as $item) {

                $item->set('pluginid', $this->get('pluginid'));
                $item->set('user', $this->get('user'));

                // Can the current user actually see this item?
                if ($user->can_view_plugin_item($item)) {
                    $this->items[$item->get('id')] = $item;
                }

            }
        }

    }

    /**
     * Get any items associated with this section.
     * @return array
     */
    protected function get_items() : array {

        if (!$this->items) {
            $this->load_items();
        }

        return $this->items;

    }

    /**
     * Save the submitted data for the current user.
     * @return void
     */
    public function save_user_data() : void {

        // If a user has not been loaded in, there is nothing for us to do.
        if (!$this->user) {
            return;
        }

        // First we need to check if this is an existing item, or if we need to create a new one.
        $itemid = optional_param('itemid', false, PARAM_INT);
        if ($itemid) {
            $item = plugin_item::load($itemid);
        } else {
            // Create a new item to store the data in.
            $item = plugin_item::create($this->get('id'), $this->user->get('id'));
        }

        // Loop through all the form fields in this section.
        foreach ($this->get_fields() as $field) {

            // If this field is editable.
            if ($field->is_editable()) {

                // Set the plugin item reference.
                $field->set('item', $item);

                // Save this value for the user.
                $field->save_user_data();

            }

        }

    }

}