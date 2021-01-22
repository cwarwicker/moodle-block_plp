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
 * This file holds the main class for the Select form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\helper;
use block_plp\models\form_field;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for the Select form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class select extends form_field {

    /**
     * Check if this select menu is a multiple select or single.
     * @return bool
     */
    protected function is_multi() : bool {
        $options = $this->get_options();
        return (isset($options->multi) && $options->multi === true);
    }

    /**
     * Add any extra data to the array passed into the mustache template.
     * @return void
     */
    protected function apply_extra_data(&$data) : void {

        // We need to alter the options array to work better with the mustache template.
        // Use this new array variable to store the new version of the options array.
        $array = [];

        $multi = (isset($data['options']->multi) && $data['options']->multi === true);

        // If it's a multiselect, then explode the user values by delimiter.
        if ($multi) {
            $value = explode(static::DELIM, $data['value']);
        } else {
            $value = $data['value'];
        }

        foreach ($data['options']->options as $key => $option) {
            $array[] = [
                'value' => $key,
                'name' => $option,
                'selected' => (($multi && in_array($key, $value)) || !$multi && $key == $value)
            ];
        }

        $data['options']->options = $array;

    }

    /**
     * Get the submitted data for this field.
     * @return mixed
     */
    public function get_submitted_value() {

        // If multi-select, the data will be in an array.
        if ($this->is_multi()) {
            return helper::optional_param_array_recursive($this->get('elementname'), null, PARAM_RAW);
        } else {
            return parent::get_submitted_value();
        }

    }

}