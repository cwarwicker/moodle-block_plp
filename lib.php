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
 * Global library functions for the PLP block.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use block_plp\core\forms\settings_form;
use block_plp\models\fields\file;

defined('MOODLE_INTERNAL') or die();

/**
 * Serve a saved file, checking the relevant permissions to see if the user should be able to see it.
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anythin
 */
function block_plp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    // Any files loaded from the PLP will be either System context (for plugin settings) or User context (added onto user PLPs).
    if (!in_array($context->contextlevel, [CONTEXT_SYSTEM, CONTEXT_USER])) {
        return false;
    }

    // Check filearea is valid.
    if (!in_array($filearea, [settings_form::FILEMANAGER_AREA, file::FILEMANAGER_AREA])) {
        return false;
    }

    // TODO: Check that the user has permission to view this file, based on the user context.

    $itemid = array_shift($args);
    $filename = array_shift($args);
    $filepath = '/' . implode('/', $args) . '/';

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_plp', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);

}