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
 * This file holds the main class for confidentiality.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models;

use block_plp\model;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for confidentiality.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class confidentiality extends model {

    /**
     *This model uses the mdl_block_plp_confidentiality table.
     */
    const TABLE = 'block_plp_confidentiality';

    /**
     * If something is PUBLIC it can be viewed by anyone who passes the normal capability checks.
     */
    const CONFIDENTIALITY_PUBLIC = 1;

    /**
     * If something is RESTRICTED it can only be viewed by a user who is attached to the user in some way. So the student
     * themselves, the course tutors, personal tutors and any attached users from external systems, such as Parent/Guardian Portal.
     * But not people like site administrators or PLP administrators, who can normally see everything within the PLP.
     */
    const CONFIDENTIALITY_RESTRICTED = 2;

    /**
     * If something is PRIVATE it can only be viewed by a user who is attached to the user in some way, excluding external systems.
     * So the student themselves, the course tutors and personal tutors. But not external users (e.g. Parent/Guardian Portal) or
     * people like site administrators or PLP administrators, who can normally see everything within the PLP.
     */
    const CONFIDENTIALITY_PRIVATE = 3;

    /**
     * If something is PERSONAL it can only be viewed by the student themselves, no-one else.
     */
    const CONFIDENTIALITY_PERSONAL = 4;

    /**
     * The context in which this confidentiality setting is applied.
     * @var string
     */
    protected $context;

    /**
     * The ID of the relevant item in the database, related to the context.
     * @var int
     */
    protected $itemid;

    /**
     * The confidentiality level.
     * @var int
     */
    protected $level;

    /**
     * Return an array of all the possible confidentiality levels.
     * @return array
     */
    public static function get_levels() : array {

        return [
            static::CONFIDENTIALITY_PUBLIC => get_string('confidentiality:public', 'block_plp'),
            static::CONFIDENTIALITY_RESTRICTED => get_string('confidentiality:restricted', 'block_plp'),
            static::CONFIDENTIALITY_PRIVATE => get_string('confidentiality:private', 'block_plp'),
            static::CONFIDENTIALITY_PERSONAL => get_string('confidentiality:personal', 'block_plp')
        ];

    }

}