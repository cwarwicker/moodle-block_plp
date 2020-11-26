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
 * Moodle database trait.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use dml_exception;
use moodle_database;
use moodle_exception;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait handles the MIS connections when we are using a database type supported by Moodle core.
 *
 * Any methods in the Moodle database API which do not have a corresponding method here, can be called manually by doing:
 *  $obj->get_connection()->method_name(), e.g. $obj->get_connection()->record_exists($table, $conditions)
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait moodle_supported_database {

    /**
     * Connect to the database, using core Moodle's database API.
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string|null $database
     * @return bool
     * @throws dml_exception
     */
    protected function connect(string $host, string $user, string $pass, string $database = null) : bool {

        // Try and instantiate the object, using Moodle's database API.
        if (!$this->conn = moodle_database::get_driver_instance(static::TYPE, 'native')) {
            throw new dml_exception('dbdriverproblem', 'Unknown driver native/' . static::TYPE);
        }

        // Now try and connect to the database, using the provided details.
        try {
            $this->conn->connect($host, $user, $pass, $database, '');
            return true;
        } catch (moodle_exception $e) {
            return false;
        }

    }

    /**
     * Disconnect from the database.
     */
    protected function disconnect() {
        return $this->conn->dispose();
    }

    /**
     * Get records from the external database, using Moodle database API methods.
     * @param string $table Name of the table to select from
     * @param array $conditions Array of where clauses
     * @param string $sort Which fields to sort by
     * @param string $fields Which fields to return. (Default: *)
     * @param int $limit Number of rows to retrieve (Default: no limit)
     * @return array|null
     */
    protected function get(string $table, array $conditions = null, string $sort='', string $fields = '*', int $limit = 0): ?array {
        return $this->conn->get_records($table, $conditions, $sort, $fields, 0, $limit);
    }

    /**
     * Get records from the external database using an SQL query and placeholder parameters.
     * @param string $sql SQL select query to run
     * @param array|null $params Array of placeholders
     * @param int $limit Number of rows to retrieve (Default: no limit)
     * @return array|null
     */
    protected function get_sql(string $sql, array $params = null, int $limit = 0) : ?array {
        return $this->conn->get_records_sql($sql, $params, 0, $limit);
    }

    /**
     * Update a record in the external database.
     * @param string $table
     * @param array $conditions
     * @param array $data
     * @return bool
     */
    public function update(string $table, array $conditions, array $data) : bool {

        // TODO: Need a solution for escaping the table and column names. Can't use moodle database object's methods, as they are
        // protected. Also can't get dbh to manually run an escape method, as also protected. Can't check it exists in get_tables()
        // as that doesn't include views. May need to implement a get_views() and check both of those.

        $params = [];

        // Loop through the data we want to set and create an array of placeholders and parameters.
        $set = [];
        foreach ($data as $field => $value) {
            $set[] = "{$field} = ?";
            $params[] = $value;
        }
        $set = implode(', ', $set);

        // Loop through the where clauses and create an array of placeholders and parameters.
        $where = [];
        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }
        $where = implode(' AND ', $where);

        // Build the SQL query.
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        // Execute it.
        return $this->conn->execute($sql, $params);

    }

    /**
     * Insert a new record into the external database
     * @param string $table Table name
     * @param array $data Array of fields => values to insert
     * @return int ID of inserted record
     */
    public function insert(string $table, array $data) : int {
        return $this->conn->insert_record($table, $data, true, false, true);
    }

}