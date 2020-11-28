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
 * This file contains the base model class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp;

use block_plp\traits\orm;

/**
 * Base model class for all models to extend.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class model {

    // Use the ORM trait.
    use orm;

    /**
     * Name of the database table this object is loaded from.
     * @var string
     */
    protected static $table = '';

    /**
     * Construct the model and try to load it from the database.
     * @param int|null $id
     */
    public function __construct(int $id = null) {

        // Load the record from the database.
        if (!is_null($id)) {
            $this->load_from_db($id);
            $this->post_load_actions();
        }

    }

    /**
     * Run any extra actions the model needs after construction, like loading more specific data.
     * @return void
     */
    protected function post_load_actions() {
        return;
    }

}