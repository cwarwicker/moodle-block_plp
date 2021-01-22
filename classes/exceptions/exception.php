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
 * This is a general exception class to be extended by any specific exceptions in the PLP plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\exceptions;

/**
 * This is a general exception class to be extended by any specific exceptions in the PLP plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class exception extends \moodle_exception {

    /**
     * Error code number for this type of exception.
     * @var int
     */
    protected $code = 0;

    /**
     * Overridden constructor from parent.
     * @param string $errstring Language string identifier.
     * @param null $a Any extra data for the lang string.
     * @param null $debuginfo Any extra data to be printed if in debugging mode.
     * @param string $link Any link to include to extra reference material.
     * @param string $module Plugin to look for lang string in.
     */
    public function __construct(string $errstring, $a = null, $debuginfo = null, $link = '', $module = 'block_plp') {

        global $CFG;

        // This code is all copied from the moodle_exception. The only change being that it allows us to pass through an
        // actual error code number instead of "0".

        if (empty($module) || $module == 'moodle' || $module == 'core') {
            $module = 'error';
        }

        $this->errorcode = $errstring;
        $this->module    = $module;
        $this->link      = $link;
        $this->a         = $a;
        $this->debuginfo = is_null($debuginfo) ? null : (string)$debuginfo;

        if (get_string_manager()->string_exists($errstring, $module)) {
            $message = get_string($errstring, $module, $a);
            $haserrorstring = true;
        } else {
            $message = $module . '/' . $errstring;
            $haserrorstring = false;
        }

        $isinphpunittest = (defined('PHPUNIT_TEST') && PHPUNIT_TEST);
        $hasdebugdeveloper = (
            isset($CFG->debugdisplay) &&
            isset($CFG->debug) &&
            $CFG->debugdisplay &&
            $CFG->debug === DEBUG_DEVELOPER
        );

        if ($debuginfo) {
            if ($isinphpunittest || $hasdebugdeveloper) {
                $message = "$message ($debuginfo)";
            }
        }

        if (!$haserrorstring and $isinphpunittest) {
            // Append the contents of $a to $debuginfo so helpful information isn't lost.
            // @codingStandardsIgnoreStart
            $message .= PHP_EOL . '$a contents: ' . print_r($a, true);
            // @codingStandardsIgnoreEnd
        }

        \Exception::__construct($message, $this->code);

    }

}