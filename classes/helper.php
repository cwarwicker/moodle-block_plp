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
 * This file holds the general Helper class for the PLP plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp;

use Matrix\Exception;

/**
 * General Helper class for the PLP plugin.
 *
 * This class contains various helper methods which can be used throughout the plugin.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class helper {

    /**
     * Run some text through format_text() and strip_tags() for display.
     *
     * This firstly converts any filters to their content, then strips out any HTML tags, returning only plain text.
     * This should be used for any user-supplied data which we want to display on the page, but which they _may_ wish to use
     * filters in, such as plugin titles, for example.
     *
     * @param string $content
     * @return string
     */
    public static function out(string $content) : string {
        return strip_tags(format_text($content));
    }

    /**
     * Generate a random string
     * @param int $length String should be this many characters long
     * @param string $chars String should use these possible characters
     * @return string
     */
    public static function random_string(int $length = 12, string $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') : string {

        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[mt_rand(0, (strlen($chars) - 1))];
        }
        return $string;

    }

    /**
     * This method acts as a replacement for optional_param_array, but also allows for multidimensional arrays, instead of just
     * one level.
     * @param string $parname
     * @param $default
     * @param $type
     * @param null $parent
     * @return array
     * @throws \coding_exception
     */
    public static function optional_param_array_recursive(string $parname, $default, $type, $parent = null) {

        // The majority of this function is core code taken from optional_param_array.
        // If no parent is passed through, because we have not yet hit a multidimensional array, use the core code from optional_param_array and go through the normal process.
        // Otherwise, just use the array we passed through and clean that instead.
        if (is_null($parent)) {

            if (func_num_args() != 3 or empty($parname) or empty($type)) {
                throw new coding_exception('optional_param_array_recursive() requires $parname, $default + $type to be specified (parameter: ' . $parname . ')');
            }

            if (isset($_POST[$parname])) {
                $param = $_POST[$parname];
            } else if (isset($_GET[$parname])) {
                $param = $_GET[$parname];
            } else {
                return $default;
            }

            if (!is_array($param)) {
                debugging('optional_param_array_recursive() expects array parameters only: ' . $parname);
                return $default;
            }

        } else {
            $param = $parent;
        }

        $result = array();

        foreach ($param as $key => $value) {

            if (!preg_match('/^[a-z0-9_ \-]+$/i', $key)) {
                debugging('Invalid key name in optional_param_array_recursive() detected: ('.$key.'), parameter: '.$parname);
                continue;
            }

            // If the value is an array, recursively go down through the levels, cleaning them all and keep the array
            // in the return value.
            if (is_array($value)) {
                $result[$key] = static::optional_param_array_recursive($parname, $default, $type, $value);
            } else {
                $result[$key] = clean_param($value, $type);
            }

        }

        return $result;

    }

}