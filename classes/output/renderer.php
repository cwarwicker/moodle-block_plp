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
 * This file holds the renderer class to be used in rendering all templates.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\output;

use block_plp\models\mis_connection;
use flexible_table;
use moodle_url;
use plugin_renderer_base;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Renderer class
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class renderer extends plugin_renderer_base {

    /**
     * Return a general confirmation box for deleting something.
     * @param string $name The name/title of the "thing" being deleted.
     * @param moodle_url $yesurl
     * @param moodle_url $nourl
     * @return mixed
     */
    public function render_confirm_delete(string $name, moodle_url $yesurl, moodle_url $nourl) {
        return $this->output->confirm(get_string('delete:sure', 'block_plp', $name), $yesurl, $nourl);
    }

    /**
     * Render the MIS connections table.
     * @return mixed
     */
    public function mis_connections() {

        ob_start();

        $table = new flexible_table('mis-connections');
        $table->define_columns(['name', 'type', 'host', 'username', 'password', 'database', 'actions']);
        $table->define_headers([
            get_string('name'),
            get_string('type', 'block_plp'),
            get_string('mis:host', 'block_plp'),
            get_string('username'),
            get_string('password'),
            get_string('mis:database', 'block_plp'),
            ''
        ]);
        $table->define_baseurl( new moodle_url('/blocks/plp/config.php', ['page' => 'mis']) );
        $table->setup();

        $connections = mis_connection::all();
        foreach ($connections as $connection) {
            $table->add_data([
                $connection->get('name'),
                $connection->get('type'),
                $connection->get('host'),
                $connection->get('username'),
                '********',
                $connection->get('dbname'),
                $connection->get_actions()
            ]);
        }

        $table->finish_output();
        $output = ob_get_contents();
        ob_end_clean();

        return $output;

    }

}