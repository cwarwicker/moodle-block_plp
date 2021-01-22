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
use ReflectionClass;
use stdClass;

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
    const DELIM = ', ';

    /**
     * ID of the plugin this field resides in
     * @var int
     */
    protected $pluginid;

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
     *  If it hasn't been loaded yet: NULL
     *  If we tried to load it but there was no data: FALSE
     *  Data: STRING
     * @var string|null|false
     */
    protected $userdata;

    /**
     * ID of the user data record for ease of updating later.
     *  If it hasn't been loaded yet: NULL
     *  If we tried to load it but there was no data: FALSE
     *  Data: INT
     * @var int|null|false
     */
    protected $userdataid;

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
     * Should the title be shown alongside the form field?
     * @var bool
     */
    protected $showtitle = true;

    /**
     * Plugin item to save the user's data to, if applicable.
     * @var plugin_item|null
     */
    protected $item;

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
        $this->regenerate_id();

        // Set the element name to use for the form input.
        $this->elementname = 'field-' . (int)$this->id;

    }

    /**
     * (Re)generate a random ID for the DOM element.
     * @param string $append Any extra string to add to the end, if we need to make it further unique.
     */
    public function regenerate_id(string $append = '') : void {
        $this->elementid = (int)$this->id . '-' . helper::random_string(20) . $append;
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
     * Check if any user data exists for the user in this field.
     * @return bool
     */
    public function has_user_data() : bool {

        if (!$this->userdata) {
            $this->load_user_data();
        }

        return (!is_null($this->userdataid) && $this->userdataid !== false);

    }

    /**
     * Can this field type be edited and saved through the PLP?
     * @return bool
     */
    public function is_editable() : bool {
        return true;
    }

    /**
     * Get the json decoded array of options.
     * @return stdClass|null
     */
    public function get_options() : ?stdClass {
        return json_decode($this->options);
    }

    /**
     * Get the ID of the related item, if applicable.
     * @return int|null
     */
    public function get_item_id() : ?int {

        $item = $this->item;
        return ($item) ? $item->get('id') : null;

    }

    /**
     * Set an item into the field, so that we can get the data for this field in that specific item.
     * This also resets any userdata currently set, as changing the item means we will need to change the user data.
     * @param plugin_item $item
     */
    public function set_item(plugin_item $item) : void {

        $this->item = $item;
        $this->reset_user_data();
        $this->regenerate_id('-item-' . $item->get('id'));

    }

    /**
     * Return the HTML of the form field to display in the editing mode.
     * @param bool $showuserdata Whether or not to include the user data in the field
     * @return string
     */
    public function display($showuserdata = true) : string {

        global $PAGE;

        $template = 'block_plp/fields/' . $this->type;
        $data = $this->to_array();

        // Decode JSON values.
        $data['options'] = json_decode($data['options']);

        // Set default empty strings for value, ahead of checking if we need the value or not.
        $data['value'] = '';
        $data['valueuntouched'] = '';

        // Is there a user value to set?
        if ($showuserdata) {

            // Get the formatted user data and include it as the value, if set.
            $userdata = $this->get_user_data();

            $data['value'] = (!is_null($userdata) && $userdata !== false) ? $userdata : $this->defaultvalue;

            // Set the unformatted user data and include it as the untouched value, if set.
            $userdataunformatted = $this->get_user_data(false);
            $data['valueuntouched'] = (!is_null($userdataunformatted) && $userdataunformatted !== false) ?
                $userdataunformatted : $this->defaultvalue;

        }

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

        // When viewing the value of the field, we don't need the instruction text.
        $data['hasinstructions'] = false;

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
        $userdata = $this->get_user_data();
        $data['value'] = (!is_null($userdata) && $userdata !== false) ? $userdata : '-';

        // Load the generic field mustache template, passing through the specific field's HTML to display.
        return $PAGE->get_renderer('block_plp')->render_from_template($template, $data);

    }

    /**
     * Add any extra data to the array passed into the mustache template.
     * @param array $data
     * @return void
     */
    protected function apply_extra_data(array &$data) : void {
        // To be overwritten in sub classes.
    }

    /**
     * Get the user's data for this field.
     * @param bool $format Whether ot not to format the value.
     * @return mixed This can take different forms, depending on the field type.
     */
    public function get_user_data(bool $format = true) {

        if (is_null($this->userdata)) {
            $this->load_user_data();
        }

        return ($format) ? $this->format_user_data($this->userdata) : $this->userdata;

    }

    /**
     * Load the user's saved data for this field from the database
     * @return void
     */
    protected function load_user_data() : void {

        global $DB;

        // If we have a user loaded, load their data for this particular field.
        if ($this->user) {

            $params = ['fieldid' => $this->get('id'), 'userid' => $this->user->get('id')];

            // If we are using a plugin section type which supports multiple items, check the itemid as well.
            $item = $this->item;
            if ($item && $item->exists()) {
                $params['itemid'] = $item->get('id');
            }

            $record = $DB->get_record('block_plp_plugin_field_data', $params, 'id, data');

            // Save the data and the record ID for later use.
            $this->userdata = ($record) ? $record->data : false;
            $this->userdataid = ($record) ? $record->id : false;

        }

    }

    /**
     * Reset the userdata to null.
     * @return void
     */
    protected function reset_user_data() : void {
        $this->userdata = null;
        $this->userdataid = null;
    }

    /**
     * This method should be overwritten by specific field type classes.
     * It should take the data from the plugin_field_data table for this field and user, and return it in the relevant format.
     * @param mixed $value
     * @return mixed
     */
    protected function format_user_data($value) {
        return $value;
    }

    /**
     * Run any extra functions which are required, prior to saving user data.
     * @param mixed $value
     * @return void
     */
    protected function pre_save_user_data(&$value) : void {
        // To be overwritten in sub classes.
    }

    /**
     * Save the user's data for this field.
     * @return bool
     */
    public function save_user_data() : bool {

        global $DB;

        // Find the submitted data for this field and convert it to a database-friendly format.
        $value = helper::convert_value_for_db($this->get_submitted_value());

        // If the value is empty, set it to null.
        if ($value === '') {
            $value = null;
        }

        // Run any pre-saving functions which are required by the field type.
        $this->pre_save_user_data($value);

        // If the user already has some data saved for this field, update it.
        if ($this->has_user_data()) {

            $data = new \stdClass();
            $data->id = $this->userdataid;
            $data->data = $value;
            $DB->update_record('block_plp_plugin_field_data', $data);

            // Update the user data value on the object.
            $this->userdata = $data->data;

        } else if (!is_null($value)) {

            // If the user does not have any data for this yet, but the submitted value is null, we don't want to bother saving it.
            $data = new \stdClass();
            $data->fieldid = $this->id;
            $data->userid = $this->user->get('id');
            $data->itemid = $this->get_item_id();
            $data->data = $value;

            $data->id = $DB->insert_record('block_plp_plugin_field_data', $data);

            // Load the data into the field object now to save us having to retrieve it again.
            $this->userdata = $data->data;
            $this->userdataid = $data->id;

        }

        return (!is_null($this->get('userdata')));
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