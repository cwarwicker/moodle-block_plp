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
 * This file holds the mis_connection class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models;

use block_plp\extdb\connection;
use block_plp\model;
use block_plp\traits\orm;
use coding_exception;
use html_writer;
use moodle_url;

/**
 * This class represents an MIS connection to an external database.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mis_connection extends model {

    // Use the ORM trait.
    use orm;

    /**
     * Array of supported databases for the external connection
     */
    const SUPPORTED_TYPES = ['mariadb', 'mysqli', 'pgsql', 'sqlsrv', 'oci'];

    /**
     * This table will be used to find records of this object type.
     * @var string
     */
    protected static $table = 'block_plp_mis_connections';

    /**
     * Connection name
     * @var string
     */
    protected $name;

    /**
     * Connection type
     * @var string
     */
    protected $type;

    /**
     * Connection host
     * @var string
     */
    protected $host;

    /**
     * Connection username
     * @var string
     */
    protected $username;

    /**
     * Connection password for this username
     * @var string
     */
    protected $userpassword;

    /**
     * Name of the database to connect to
     * @var string
     */
    protected $dbname;

    /**
     * Flag to see if the connection is enabled or not (Default: 1)
     * @var int
     */
    protected $enabled = 1;

    /**
     * Stores the latest connection error.
     * @var
     */
    protected $error;

    /**
     * Check if the connection is enabled
     * @return bool
     */
    public function is_enabled() : bool {
        return ($this->enabled == 1);
    }

    /**
     * Returns the latest connection error.
     * @return mixed
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Try and connect to the external database.
     * @return connection|bool
     */
    public function connect() {

        // Load the database driver object.
        try {
            return connection::load($this->type, $this->host, $this->username, $this->userpassword, $this->dbname);
        } catch (coding_exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    /**
     * Toggle the value of the enabled setting to enable/disable.
     */
    public function toggle_enabled() {
        $this->set('enabled', !$this->get('enabled'));
        return $this->save();
    }

    /**
     * Get the array of supported database types.
     * @return array
     */
    public static function get_supported_types() : array {
        $array = [];
        foreach (static::SUPPORTED_TYPES as $type) {
            $array[$type] = get_string('mis:type:' . $type, 'block_plp');
        }
        return $array;
    }

    /**
     * Display the actions for this row in the connections table
     * @return string
     */
    public function get_actions() : string {

        global $PAGE;

        $renderer = $PAGE->get_renderer('block_plp');

        return $renderer->render_from_template('block_plp/actions', [
            'enabledisable_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'mis',
                'action' => 'enabledisable',
                'id' => $this->get('id'),
                'sesskey' => sesskey()
            ]),
            'enabledisable_icon' => ($this->is_enabled()) ? 'eye' : 'eye-slash',
            'enabledisable_title' => ($this->is_enabled()) ? get_string('disable', 'block_plp') : get_string('enable', 'block_plp'),
            'edit_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'mis',
                'action' => 'edit',
                'id' => $this->get('id'),
                'sesskey' => sesskey()
            ]),
            'delete_url' => new moodle_url('/blocks/plp/config.php', [
                'page' => 'mis',
                'action' => 'delete',
                'id' => $this->get('id'),
                'sesskey' => sesskey()
            ]),
        ]);

    }

}