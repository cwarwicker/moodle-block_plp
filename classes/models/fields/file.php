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
 * This file holds the main class for the File form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models\fields;

use block_plp\helper;
use block_plp\models\form_field;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/lib/form/filemanager.php');

/**
 * This file holds the main class for the File form field.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class file extends form_field {

    /**
     * Array of default options to use in filemanager fields.
     */
    const FILEMANAGER_OPTIONS = ['subdirs' => 0];

    /**
     * String to use for the 'filearea' of uploaded files.
     */
    const FILEMANAGER_AREA = 'field';

    /**
     * Overwrite constructor.
     * @param int|null $id
     */
    public function __construct(int $id = null) {

        parent::__construct($id);

        // Element name must end in _filemanager for Moodle functions to work correctly.
        $this->elementname = $this->elementname . '_filemanager';

    }

    /**
     * Strip the _filemanager suffix from the element name.
     * @return string
     */
    protected function get_stripped_element_name() : string {
        return str_replace('_filemanager', '', $this->get('elementname'));
    }

    /**
     * Apply field options to the filemanager, overwriting any defaults.
     * @return array
     */
    protected function get_filemanager_options() : array {

        $defaults = static::FILEMANAGER_OPTIONS;
        $options = $this->get_options();

        if (isset($options->accepted_types)) {
            $defaults['accepted_types'] = $options->accepted_types;
        }

        if (isset($fieldoptions->maxfiles)) {
            $defaults['maxfiles'] = $options->maxfiles;
        }

        return $defaults;

    }

    /**
     * Add any extra data to the array passed into the mustache template.
     * @param array $data
     * @return void
     */
    protected function apply_extra_data(array &$data) : void {

        $options = $this->get_filemanager_options();

        // Build a filemanager form element and pass it through in the mustache template.
        $input = new \MoodleQuickForm_filemanager($this->get('elementname'), $this->get('title'), [
            'id' => $this->get('elementid')
        ], $options);

        // Load previously saved files into the filemanager field.
        if ($this->get_user_data()) {

            $context = \context_user::instance($this->get('user')->get('id'));

            $fielddata = new \stdClass();
            $fielddata = file_prepare_standard_filemanager($fielddata, $this->get_stripped_element_name(), $options,
                $context, 'block_plp', static::FILEMANAGER_AREA, $this->get('id'));

            // Apply prepared filemanagers to form.
            $input->setValue($fielddata->{$this->get('elementname')});

        }

        $data['fieldhtml'] = $input->toHtml();

    }

    /**
     * Process the file uploads.
     * @param mixed $value
     * @return void
     */
    protected function pre_save_user_data(&$value) : void {

        $context = \context_user::instance($this->get('user')->get('id'));

        $data = new \stdClass();
        $data->{$this->get('elementname')} = $value;

        // Save the uploaded file(s).
        file_postupdate_standard_filemanager($data, $this->get_stripped_element_name(), $this->get_filemanager_options(), $context,
            'block_plp', static::FILEMANAGER_AREA, $this->get('id'));

        // Get the mdl_files record ID, based on where we attempted to save the file.
        $value = helper::get_uploaded_file($context->id, 'block_plp', static::FILEMANAGER_AREA, $this->get('id'));

    }

    /**
     * Display the uploaded files.
     * @return string
     */
    protected function get_value_html() : string {

        global $PAGE;

        $template = 'block_plp/fields/value/file';
        $files = false;

        $data = $this->to_array();
        $data['files'] = [];

        // Load the uploaded files into an array of links.
        if ($this->get_user_data()) {
            $context = \context_user::instance($this->get('user')->get('id'));
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'block_plp', static::FILEMANAGER_AREA,
                $this->get('id'), 'itemid, filepath, filename', false);
        }

        // If we have found any files, loop through them and generate download links.
        if ($files) {
            foreach ($files as $file) {
                $data['files'][] = \html_writer::link(\moodle_url::make_pluginfile_url(
                    $file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), $file->get_filename(), false),
                    $file->get_filename());
            }
        } else {
            $data['files'][] = '-';
        }

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

}