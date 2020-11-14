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

    /**
     * Name of the database table this object is loaded from.
     * @var string
     */
    protected static $table = '';

    /**
     * Array of columns and values on the object.
     * @var array
     */
    protected $columns = [];

    /**
     * Try to get one of the columns on this object.
     * @param string $col The column key.
     * @return mixed|null Either the value, or NULL is it doesn't exist.
     */
    public function get(string $col) {
        return $this->columns[$col] ?? null;
    }

    /**
     * Set the value of a column on this object.
     * @param string $col The column key.
     * @param mixed $val The value to set.
     * @return $this
     */
    public function set(string $col, $val) {
        $this->columns[$col] = $val;
        return $this;
    }

    /**
     * Run any extra actions the model needs after construction, like loading more specific data.
     * @return void
     */
    protected function post_load_actions() {
        return;
    }

    /**
     * Create an instance of this object based on a database record.
     * @param int $id ID of the database row.
     * @return model
     * @throws \dml_exception
     */
    public static function load(int $id) {

        global $DB;

        // Try and get the record from the database.
        $record = $DB->get_record(static::$table, ['id' => $id], '*', MUST_EXIST);

        $class = get_called_class();
        $object = new $class();

        // Set all of the database row values into the object.
        foreach ($record as $col => $val) {
            $object->set($col, $val);
        }

        // Call any more specific actions the model requires after construction.
        $object->post_load_actions();

        return $object;

    }

}