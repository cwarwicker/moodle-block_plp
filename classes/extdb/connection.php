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
 * Core connection class for external database connections.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\extdb;

use coding_exception;
use moodle_database;

defined('MOODLE_INTERNAL') or die();

/**
 * This abstract class is used as a base for all external database connection objects.
 *
 * It defines some basic database query functionality, so that in the future if additional databases are supported, an extended
 * class can be easily defined.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class connection {

    /**
     * Database host
     * @var string
     */
    protected $host;

    /**
     * Database user
     * @var string
     */
    protected $user;

    /**
     * User's password
     * @var string
     */
    protected $password;

    /**
     * Database name
     * @var string
     */
    protected $database;

    /**
     * Database connection resource
     * @var
     */
    protected $conn;

    /**
     * Construct the connection object and try to connect.
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string|null $database
     * @throws coding_exception
     */
    public function __construct(string $host, string $user, string $pass, string $database = null) {

        if (!$this->connect($host, $user, $pass, $database)) {
            throw new coding_exception(get_string('error:mis:connection', 'block_plp'));
        }

    }

    /**
     * Destroy the object and disconnect from the database.
     */
    public function __destruct() {
        $this->disconnect();
    }

    /**
     * Get the connection resource or object.
     * @return mixed
     */
    public function get_connection() {
        return $this->conn;
    }

    /**
     * Connect to the database.
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string|null $database
     * @return bool
     */
    abstract protected function connect(string $host, string $user, string $pass, string $database = null) : bool;

    /**
     * Disconnect from the database.
     * @return void
     */
    abstract protected function disconnect();

    /**
     * Get records from the database
     * @param string $table Name of the table to select from
     * @param array $conditions Array of where clauses
     * @param string $sort Which fields to sort by
     * @param string $fields Which fields to return. (Default: *)
     * @param int $limit Number of rows to retrieve (Default: no limit)
     * @return array|null
     */
    abstract protected function get(string $table, array $conditions = null, string $sort = '', string $fields = '*', int $limit =
        0) : ?array;

    /**
     * Get one record from the database
     * @param string $table Name of the table to select from
     * @param array $conditions Array of where clauses
     * @param string $sort Which fields to sort by
     * @param string $fields Which fields to return. (Default: *)
     * @return array|null
     */
    public function get_one(string $table, array $conditions = null, string $sort='', string $fields = '*') : ?array {
        return $this->get($table, $conditions, $sort, $fields, 1);
    }

    /**
     * Get many records from the database
     * This is essentially just an alias for get()
     * @param string $table Name of the table to select from
     * @param array $conditions Array of where clauses
     * @param string $sort Which fields to sort by
     * @param string $fields Which fields to return. (Default: *)
     * @return array|null
     */
    public function get_many(string $table, array $conditions = null, string $sort='', string $fields = '*') : ?array {
        return $this->get($table, $conditions, $sort, $fields, 0);
    }

    /**
     * Get records from the external database using an SQL query and placeholder parameters.
     * @param string $sql SQL select query to run
     * @param array|null $params Array of placeholders
     * @param int $limit Number of rows to retrieve (Default: no limit)
     * @return array|null
     */
    abstract protected function get_sql(string $sql, array $params = null, int $limit = 0) : ?array;

    /**
     * Get one record from the external database using an SQL query and placeholder parameters.
     * @param string $sql SQL select query to run
     * @param array|null $params Array of placeholders
     * @return array|null
     */
    public function get_sql_one(string $sql, array $params = null) : ?array {
        return $this->get_sql($sql, $params, 1);
    }

    /**
     * Get multiple records from the external database using an SQL query and placeholder parameters.
     * This is essentially just an alias for get_sql()
     * @param string $sql SQL select query to run
     * @param array|null $params Array of placeholders
     * @return array|null
     */
    public function get_sql_many(string $sql, array $params = null) : ?array {
        return $this->get_sql($sql, $params, 0);
    }

    /**
     * Update a record in the external database
     * @param string $table Table name
     * @param array $conditions Where clauses to find the correct record to update
     * @param array $data Array of fields => values to change
     * @return bool
     */
    public abstract function update(string $table, array $conditions, array $data) : bool;

    /**
     * Insert a new record into the external database
     * @param string $table Table name
     * @param array $data Array of fields => values to insert
     * @return int ID of inserted record
     */
    public abstract function insert(string $table, array $data) : int;

    /**
     * Given a string 'type' of database, return an instantiated connection object, connected to the database.
     * @param string $type E.g. 'mysql', 'mariadb', 'pgsql', etc...
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string|null $database
     * @return connection
     * @throws coding_exception
     */
    public static function load(string $type, string $host, string $user, string $pass, string $database = null) : connection {

        switch ($type) {

            case 'mariadb':
                return new mariadb($host, $user, $pass, $database);

            case 'mysql':
            case 'mysqli':
                return new mysql($host, $user, $pass, $database);

            case 'pgsql':
                return new pgsql($host, $user, $pass, $database);

            case 'sqlsrv':
                return new sqlsrv($host, $user, $pass, $database);

            case 'oci':
                return new oci($host, $user, $pass, $database);

            default:
                throw new coding_exception(get_string('error:mis:type', 'block_plp', $type));
        }

    }

}