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

use block_plp\model;
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
            $record = $DB->get_record(static::TABLE, ['id' => $id]);
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
     * @param stdClass|array $record
     * @return void
     */
    public function map_orm($record) : void {

        if (is_object($record)) {
            $record = (array)$record;
        }

        foreach ($record as $key => $value) {

            if (property_exists($this, $key)) {

                // Is there a method to overwrite this simple property set?
                $method = 'map_' . $key;
                if (method_exists($this, $method)) {
                    $this->$method($value);
                } else {
                    $this->$key = $value;
                }

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
        foreach (array_keys(get_object_vars($this)) as $key) {
            $obj->$key = $this->get($key);
        }

        // If it already exists, we can update it using the dbkeys.
        if ($this->exists()) {
            // If the update is successful, return the object id, otherwise false.
            return ($DB->update_record(static::TABLE, $obj)) ? $this->id : false;
        } else {
            // If it's new, we will need to pass the values through into this method.
            unset($obj->id);
            $this->id = $DB->insert_record(static::TABLE, $obj);
            return $this->id;
        }

    }

    /**
     * Delete this record from the database.
     * @return bool
     */
    public function delete() : bool {

        global $DB;
        return $DB->delete_records(static::TABLE, ['id' => $this->get('id')]);

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
     * @return model|null Returns an new instance of the class this is called on, or null.
     */
    public static function load(int $id = null) : ?model {
        $class = get_called_class();
        return new $class($id);
    }

    /**
     * Find a specific record of this type and return the object
     * @param array $filters Conditions to be passed into the get_record() method.
     * @return model|null Either the object instance, or null if not found.
     */
    public static function find(array $filters) : ?model {

        global $DB;

        $class = get_called_class();
        $record = $DB->get_record(static::TABLE, $filters, 'id');
        if ($record) {
            $obj = new $class($record->id);
            if ($obj->exists()) {
                return $obj;
            }
        }

        return null;

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
        $records = $DB->get_records(static::TABLE, $filters, '', 'id');
        if ($records) {
            foreach ($records as $record) {
                $obj = $class::load($record->id);
                if ($obj && $obj->exists()) {
                    $return[$record->id] = $obj;
                }
            }
        }

        return $return;

    }

    /**
     * Get a specific field from a database record of this type.
     * @param int $id
     * @param string $field
     * @return string|null
     */
    public static function field(int $id, string $field) : ?string {

        global $DB;
        $field = $DB->get_field(static::TABLE, $field, ['id' => $id]);
        return ($field) ? $field : null;

    }

    /**
     * Given an array of data, load an object of this instance, passing that data in.
     * @param array $data Array of data to map onto the new object.
     * @param string|null $class (Optional) Class to use to load objects. If left null, will use calling class.
     * @return model
     */
    public static function from_array(array $data, string $class = null) : model {

        // If we did not specify the class in the method call, use the current model's class.
        $class = ($class) ?? get_called_class();

        // If we passed through an id, load the database record. Otherwise set it to null.
        $id = null;
        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        // Load the new instance of this object.
        $object = new $class($id);

        // Now map the rest of the data to the object.
        $object->map_orm($data);

        return $object;

    }

}