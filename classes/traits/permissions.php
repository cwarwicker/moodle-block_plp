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
 * This file holds the permissions interface.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\traits;

use block_plp\models\user;

defined('MOODLE_INTERNAL') or die();

/**
 * This trait can be added to a class which and then used to check user permissions of an instance.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
trait permissions {

    /**
     * The Moodle user associated with this instance.
     * @var user
     */
    protected $user;

    /**
     * Return a user instance of whichever user is associated with this object instance.
     * @return user
     */
    public function get_user() : user {
        return $this->user;
    }

    /**
     * Given a capability and an array of contexts, see if the user has it on any of them.
     * @param string $capability Moodle capability
     * @param array $contexts Array of Moodle contexts to check against
     * @return bool
     */
    protected function check_permissions(string $capability, array $contexts) : bool {

        // Get the user id from the user object associated with this object.
        $userid = $this->get_user()->get('id');

        foreach ($contexts as $context) {
            if (has_capability($capability, $context, $userid)) {
                return true;
            }
        }

        return false;

    }

}