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
 * This file holds the main class for form fields used in plugins.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\models;

use block_plp\helper;
use block_plp\model;
use coding_exception;
use moodle_exception;
use ReflectionClass;

defined('MOODLE_INTERNAL') or die();

/**
 * This file holds the main class for form fields used in plugins.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class form_field extends model {

    /**
     * Plugin page model uses the mdl_block_plp_plugin_pages table.
     */
    const TABLE = 'block_plp_plugin_fields';

    /**
     * Where we need to separate values in 1 string, use this delimiter.
     */
    const DELIM = ',';

    /**
     * ID of the plugin_section this field is associated with.
     * @var int
     */
    protected $sectionid;

    /**
     * Title of the page
     * @var string
     */
    protected $title;

    /**
     * Type of form field
     * @var string
     */
    protected $type;

    /**
     * JSON decoded array of options on the field
     * @var array
     */
    protected $options;

    /**
     * Default value to user if the user does not specify one
     * @var string
     */
    protected $defaultvalue;

    /**
     * Placeholder value to use when there is no data in the field (where applicable).
     * @var string
     */
    protected $placeholder;

    /**
     * Array of validation types to apply (comma separated in the database)
     * @var array
     */
    protected $validation;

    /**
     * Instruction/help text to display along with the field.
     * @var string
     */
    protected $instructions;

    /**
     * User whose data we are loading
     * @var user
     */
    protected $user;

    /**
     * User data loaded into the field
     * @var string
     */
    protected $userdata;

    /**
     * A unique id to use as the html element id.
     * @var string
     */
    protected $elementid;

    /**
     * The name to use for the input, so we can find it when its submitted.
     * @var string
     */
    protected $elementname;

    /**
     * Does this field type use instructions data
     * @var bool
     */
    protected $hasinstructions = true;

    /**
     * Overwrite parent constructor to set the field type based on the class name.
     * @param int|null $id
     */
    public function __construct(int $id = null) {

        // Call the standard constructor.
        parent::__construct($id);

        // Set the type property from the class name.
        // It doesn't matter that this is in the abstract class, because this can't be constructed anyway.
        $reflect = new ReflectionClass($this);
        $this->type = $reflect->getShortName();

        // Create a unique element id for the element.
        $this->elementid = (int)$this->id . '-' . helper::random_string(20);

        // Set the element name to use for the form input.
        $this->elementname = 'field-' . (int)$this->id;

    }

    /**
     * Check if this field type uses the Instructions data
     * @return bool
     */
    protected function has_instructions() : bool {
        return $this->hasinstructions;
    }

    /**
     * Check if we have a valid user loaded into the field.
     * @return bool
     */
    protected function has_user() : bool {
        return ($this->user instanceof user && $this->user->exists());
    }

    /**
     * Get the json decoded array of options.
     * @return array
     */
    public function get_options() : \stdClass {
        return json_decode($this->options);
    }

    /**
     * Return the HTML of the form field to display in the editing mode.
     * @return string
     */
    public function display() : string {

        global $PAGE;

        $template = 'block_plp/fields/' . $this->type;
        $data = $this->to_array();

        // Decode JSON values.
        $data['options'] = json_decode($data['options']);

        // Is there a user value to set?
        $data['value'] = ($this->get_user_data()) ?? $this->defaultvalue;

        // Does this field type use instructions?
        $data['has_instructions'] = $this->has_instructions();

        // Any extra data that needs to be set from the specific field type.
        $this->apply_extra_data($data);

        // Load the mustache template HTML of the specific field type.
        $data['field'] = $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/fields/field', $data);

    }

    /**
     * Return the much simpler HTML to just display the non-editing value of the field.
     * @return string
     */
    public function display_value() : string {

        global $PAGE;

        $data = $this->to_array();

        // Load the value of the field for display.
        $data['field'] = $this->get_value_html();

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template('block_plp/fields/field', $data);

    }

    /**
     * Return the simple HTML for displaying the value of the field, in non-editing mode.
     * This should be overridden by any form fields which do not use the simple template, of just display a text value.
     * @return string
     */
    protected function get_value_html() : string {

        global $PAGE;

        $template = 'block_plp/fields/value/simple';

        $data = [];

        // Get the actual user value to display (in whatever format suits this field type) or just '-' to denote no data.
        $data['value'] = $this->get_user_data() ?? '-';

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

    /**
     * Add any extra data to the array passed into the mustache template.
     * @return void
     */
    protected function apply_extra_data(&$data) : void {
        // To be overwritten in sub classes.
    }

    /**
     * Get the user's data for this field.
     * @return mixed This can take different forms, depending on the field type.
     */
    public function get_user_data() {

        if (!$this->userdata) {
            $this->load_user_data();
        }

        return $this->format_user_data($this->userdata);

    }

    /**
     * Load the user's saved data for this field from the database
     * @return void
     */
    protected function load_user_data() : void {

        global $DB;

        // If we have a user loaded, load their data for this particular field.
        if ($this->user) {
            $record = $DB->get_field('block_plp_plugin_field_data', 'data', ['fieldid' => $this->get('id'), 'userid' =>
                $this->user->get('id')]);
            $this->userdata = ($record) ? $record : null;
        }

    }

    /**
     * This method should be overwritten by specific field type classes.
     * It should take the data from the plugin_field_data table for this field and user, and return it in the relevant format.
     * @param string $value
     * @return mixed
     */
    protected function format_user_data(?string $value) {
        return $value;
    }

    /**
     * Map the validation data to the property, depending on the format it comes in.
     * @param mixed $data
     * @return void
     */
    protected function map_validation($data) : void {

        // From the from_array() method, the data will be in an array and will require converting to json.
        if (is_array($data)) {
            $this->validation = (!empty($data)) ? json_encode($data) : null;
        } else {
            // Fallback to just setting it.
            $this->validation = $data;
        }

    }

    /**
     * Map the options data to the property, depending on the format it comes in.
     * @param mixed $data
     * @return void
     */
    protected function map_options($data) : void {

        // From the from_array() method, the data will be in an array and will require converting to json.
        if (is_array($data)) {
            $this->options = (!empty($data)) ? json_encode($data) : null;
        } else {
            // Fallback to just setting it.
            $this->options = $data;
        }

    }

    /**
     * Get the submitted data for this field.
     * @return mixed
     */
    public function get_submitted_value() {
        return optional_param($this->get('elementname'), null, PARAM_RAW);
    }

    /**
     * Overwrite the ORM from_array method, in case we call it on this abstract class.
     * @param array $data
     * @param string|null $class Not used in this implementation
     * @return model
     * @throws coding_exception
     */
    public static function from_array(array $data, string $class = null) : model {

        $class = get_called_class();

        // If we called this specifically on a form field sub class, we don't need to do anything different.
        if ($class !== 'block_plp\models\form_field') {
            return parent::from_array($data);
        }

        // However, if we called this method on this abstract form_field class, we need to work out which object to return.
        if (!isset($data['type'])) {
            throw new coding_exception(get_string('error:formfieldtype:missing', 'block_plp'));
        }

        // Make sure that the requested form field type has a valid class.
        $class = 'block_plp\models\fields\\' . strtolower($data['type']);
        if (!class_exists($class)) {
            throw new coding_exception(get_string('error:formfieldtype:invalid', 'block_plp', $data['type']));
        }

        // Call the form_array() method on that class, so it can return the correct object.
        unset($data['type']);
        return $class::from_array($data);

    }

    /**
     * Overridden load method, to take into account that this is an abstract class and we need to work out which actual
     * class to load dependant on the field type.
     * @param int|null $id
     * @return form_field|null
     */
    public static function load(int $id = null) : ?form_field {

        global $DB;

        // Get the record from the database to work out which type of field it is.
        $record = $DB->get_record(static::TABLE, ['id' => $id], 'id, type');
        if (!$record) {
            return null;
        }

        // Work out which class we need to try and load.
        $class = '\\block_plp\\models\\fields\\' . $record->type;
        if (!class_exists($class)) {
            return null;
        }

        // Instantiate that class.
        return new $class($id);

    }

}