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

}