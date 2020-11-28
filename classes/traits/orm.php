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
 * This file holds the orm_mapping trait.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use coding_exception;
use stdClass;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait can be added to a class and used to automatically map values from a database record to the class properties.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait orm {

    /**
     * ID of database record
     * @var int
     */
    protected $id;

    /**
     * Construct the object.
     * @param int $id ID of record in Moodle database table.
     * @return void
     */
    protected function load_from_db(int $id = null) {

        global $DB;

        if (!is_null($id)) {
            $record = $DB->get_record(static::$table, ['id' => $id]);
            if ($record) {
                $this->map_orm($record);
            }
        }

    }

    /**
     * Check if the record which was loaded into this object actually exists and was mapped successfully.
     * @return bool
     */
    public function exists() : bool {
        return ($this->id !== null);
    }

    /**
     * Map values from a database record onto the class properties.
     * @param stdClass $record
     * @return void
     */
    protected function map_orm(stdClass $record) {

        $record = (array)$record;

        foreach ($record as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

    }

    /**
     * Get a property from the class.
     * @param string $property
     * @return mixed
     * @throws coding_exception
     */
    public function get(string $property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            throw new coding_exception(get_string('error:invalidclassproperty', 'block_plp', $property));
        }
    }

    /**
     * Set the value of a property on the object
     * @param string $property
     * @param mixed $value
     * @return mixed Returns the object instance so set() methods can be chained.
     * @throws coding_exception
     */
    public function set(string $property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
            return $this;
        } else {
            throw new coding_exception(get_string('error:invalidclassproperty', 'block_plp', $property));
        }
    }

    /**
     * Save a record of this object into its database table.
     * @return bool|int
     */
    public function save() {

        global $DB;

        // Get all the properties on the object to find out what keys will need inserting/updating.
        // Any which exist on the object but not in the database table will be ignored by Moodle's database API.
        $obj = new stdClass();
        foreach (get_object_vars($this) as $key => $value) {
            $obj->$key = $this->get($key);
        }

        // If it already exists, we can update it using the dbkeys.
        if ($this->exists()) {
            return $DB->update_record(static::$table, $obj);
        } else {
            // If it's new, we will need to pass the values through into this method.
            unset($obj->id);
            return $DB->insert_record(static::$table, $obj);
        }

    }

    /**
     * Convert the record to an array
     * @return array
     */
    public function to_array() : array {

        $array = [];

        foreach (get_object_vars($this) as $key => $value) {
            $array[$key] = $value;
        }

        return $array;

    }

    /**
     * Helper static function to return an instance of an object.
     * @param int|null $id
     * @return mixed Returns an new instance of the class this is called on.
     */
    public static function load(int $id = null) {
        $class = get_called_class();
        return new $class($id);
    }

    /**
     * Search for records of this type and return an array of objects.
     * @param array $filters Conditions to be passed into the get_records() method.
     * @return array
     */
    public static function all(array $filters = []) : array {

        global $DB;

        $class = get_called_class();
        $return = [];
        $records = $DB->get_records(static::$table, $filters, '', 'id');
        if ($records) {
            foreach ($records as $record) {
                $obj = new $class($record->id);
                if ($obj->exists()) {
                    $return[$record->id] = $obj;
                }
            }
        }

        return $return;

    }

}