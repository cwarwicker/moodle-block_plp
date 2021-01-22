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

use coding_exception;

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
     * Default date format to use, if one not configured.
     */
    const DATE_FORMAT = 'd-m-Y H:i';

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
     * @param string $parname The name of the page parameter we want
     * @param mixed $default The default value to return if nothing is found
     * @param string $type Expected type of parameter
     * @param array|null $parent The parent element of the multidimensional array. Or null if we are at the top level.
     * @return mixed Either a filtered array, or the default value, if one is specified and the parameter is not found.
     * @throws coding_exception
     */
    public static function optional_param_array_recursive(string $parname, $default, string $type, array $parent = null) {

        // The majority of this function is core code taken from optional_param_array.
        // If no parent is passed through, because we have not yet hit a multidimensional array,
        // use the core code from optional_param_array and go through the normal process.
        // Otherwise, just use the array we passed through and clean that instead.
        if (is_null($parent)) {

            if (func_num_args() != 3 or empty($parname) or empty($type)) {
                throw new coding_exception('optional_param_array_recursive() requires $parname, $default + $type
                    to be specified (parameter: ' . $parname . ')');
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

    /**
     * Convert a value so that it can be inserted into the database (scalar or json).
     * @param mixed $value The value to convert
     * @return mixed Either the value itself, if it is already scalar, or a json-encoded string.
     */
    public static function convert_value_for_db($value) {

        // If it is a scalar value, the only check we want to do is see if it's a string, in which case trim it.
        if (is_scalar($value)) {
            return (is_string($value)) ? trim($value) : $value;
        } else {
            // Otherwise, if it is null, simply return it. If it's not null, json_encode it, as it's probably an array.
            return (!is_null($value)) ? json_encode($value) : $value;
        }
    }

    /**
     * Given a context, component,filearea and itemid, get the ID of the mdl_files record which matches.
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @param false $itemid
     * @return int|null
     */
    public static function get_uploaded_file(int $contextid, string $component, string $filearea, $itemid = false) : ?int {

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, $itemid, 'itemid, filepath, filename', false);

        // Get the ID of the saved file, or null.
        if ($files) {
            $file = reset($files);
            return $file->get_id();
        } else {
            return null;
        }
    }

    /**
     * Format a date.
     * @param int $timestamp
     * @return string
     */
    public static function format_date(int $timestamp) : string {

        // TODO: Look for config date format setting.
        return date(static::DATE_FORMAT, $timestamp);

    }

    /**
     * Check if the given string is JSON and attempt to decode it. If it's not JSON, just return it back.
     * @param string|null $string
     * @return mixed
     */
    public static function decode_json(?string $string) {

        $decode = json_decode($string);
        return (!is_null($decode)) ? $decode : $string;

    }

    /**
     * Convert a numeric string to its correct type. E.g. int or float.
     * @param string $number
     * @return mixed
     */
    public static function cast_numeric_string(string $number) {
        return $number + 0;
    }

    /**
     * Apply core hidden form fields to be used across most forms.
     * @param array $data
     * @return void
     */
    public static function apply_hidden_form_fields(array &$data) : void {
        $data['sesskey'] = sesskey();
    }

}