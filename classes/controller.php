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
 * This file holds the base controller class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp;

use coding_exception;

defined('MOODLE_INTERNAL') or die();

/**
 * The base controller class is an abstract class which is extended by other, more specific, controllers.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class controller {

    /**
     * Constant to be used when specifying a controller requires certain capabilities.
     * Used in the format: ['capability' => $context]
     */
    const OPTION_REQUIRE_CAPABILITIES = 'require_capabilities';

    /**
     * Template file we are going to load.
     * @var local_plp\template
     */
    protected $template;

    /**
     * The method we are calling on the controller.
     * @var string
     */
    protected $action;

    /**
     * Array of options used in the instantiation of the object.
     * @var array
     */
    protected $options;

    /**
     * The data we are passing into the action method.
     * @var array
     */
    protected $data;

    /**
     * Construct controller object.
     * @param string $action The action we want the controller to perform.
     * @param array|null $options Array of controller options to check before running the action.
     * @param array|null $data Array of extra data to be used in the action.
     */
    public function __construct(string $action, array $options = null, array $data = null) {

        // Load the action, controller options and action data into the object.
        $this->set_action($action);
        $this->set_options($options);
        $this->set_data($data);

        // Work out which template we should be using. (This can be overridden with set_template()).
        $this->set_default_template();

    }

    /**
     * Get the component from the namespace.
     *
     * For example, if the namespace is: block_plp\core\controllers\config_controller then the component is "core".
     * @return string|string[]
     * @throws \ReflectionException
     */
    protected function get_component() : string {
        $reflect = new \ReflectionClass($this);
        return str_replace('\\controllers', '\\templates', $reflect->getNamespaceName());
    }

    /**
     * Get the component name from the namespace.
     *
     * For example, if the namespace is block_plp\core\controllers\config_controller then the component name is: "config".
     * @return string
     * @throws \ReflectionException
     */
    protected function get_component_name() : string {
        $reflect = new \ReflectionClass($this);
        return str_replace('_controller', '_template', $reflect->getShortName());
    }

    /**
     * Set the default template object to be used, unless overridden by set_template().
     * @throws \coding_exception
     * @return void
     */
    protected function set_default_template() {

        // By default, the template we are wanting to use is likely in the same directory as the controller, but
        // inside the /templates/ sub directory and namespace. So we can work out its path from the controllers path.
        $namespace = $this->get_component();
        $name = $this->get_component_name();

        $class = $namespace . '\\' . $name;

        // All controllers should have a matching template, even if it's just a blank shell.
        if (!class_exists($class)) {
            throw new coding_exception(get_string('error:class', 'block_plp', $class));
        }

        $template = new $class();
        $template->set_action($this->get_action());
        $this->set_template($template);

    }

    /**
     * Get the template we are using.
     * @return template|null
     */
    public function get_template() : ?template {
        return $this->template;
    }

    /**
     * Set the template we are going to be loading.
     * @param template $template
     * @return $this
     */
    public function set_template(template $template) {
        $this->template = $template;
        return $this;
    }

    /**
     * Get the named action we are performing.
     * @return string
     */
    public function get_action() : string {
        return $this->action;
    }

    /**
     * Set the action we are going to perform.
     * @param string $action
     * @return $this
     */
    public function set_action(string $action) {
        $this->action = $action;
        return $this;
    }

    /**
     * Get all of the controller options.
     * @return array
     */
    public function get_options() : ?array {
        return $this->options;
    }

    /**
     * Set the array of options.
     * @param array $options
     * @return $this
     */
    public function set_options(array $options = null) {
        $this->options = $options;
        return $this;
    }

    /**
     * Get the array of data we are passing through with the action.
     * @return array
     */
    public function get_data() : ?array {
        return $this->data;
    }

    /**
     * Set the array of data to submit with the action.
     * @param array $data Array of action data.
     * @return $this
     */
    public function set_data(array $data = null) {
        $this->data = $data;
        return $this;
    }

    /**
     * Check if this controller actually has the specified action as a method.
     * @return bool
     */
    protected function has_action() : bool {
        return method_exists($this, 'call_' . $this->get_action());
    }

    /**
     * Call the action's method.
     * @return bool The result of calling the method. If return is not TRUE, will be assumed something went wrong.
     */
    protected function call_action() : bool {
        if ($this->has_action()) {
            return call_user_func([$this, 'call_' . $this->get_action()], $this->get_data());
        } else {
            // We return true, as it's not necessarily a failure, the controller may simply not need a method for this action.
            return true;
        }
    }

    /**
     * Go through pre-run checks to do things like authentication and permission checks for this controller.
     * @return void
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    protected function pre_run_checks() {

        $options = $this->get_options();

        // TODO: Auth adapters, so we can do authentication via moodle or external service, e.g. Parent Portal.
        require_login();

        // If we need specific capabilities to perform any actions on this controller, check those.
        if (array_key_exists(static::OPTION_REQUIRE_CAPABILITIES, $options)) {

            foreach ($options[static::OPTION_REQUIRE_CAPABILITIES] as $capability => $context) {
                require_capability($capability, $context);
            }

        }

        // Make sure that the template we have loaded in is actually a valid template class.
        if (!$this->get_template() instanceof template) {
            print_error('error:template:invalid', 'block_plp');
        }

    }

    /**
     * Run the controller action from start to finish, rendering the page at the end.
     * @return void
     * @throws \moodle_exception
     */
    public function run() {

        // Before we run anything, go through the pre-run checks.
        $this->pre_run_checks();

        // If we haven't exited the process so far, checks must have passed. So now call the action.
        if ($this->call_action() !== true ) {
            print_error('error:unknown', 'block_plp');
        }

        // Now call the action on the template to load the correct display.
        if ($this->get_template()->call_action() !== true) {
            print_error('error:unknown', 'block_plp');
        };

        // Assuming everything went okay up to this point, we can now render the template.
        $this->get_template()->render();

    }

    /**
     * Instantiate a controller object
     * @param string $action The action we are performing
     * @param array|null $options Array of controller options to apply before running anything
     * @param array|null $data Array of extra data to pass through with the action
     * @return controller
     */
    public static function load(string $action, array $options = null, array $data = null) : controller {

        // Get the class this method was actually called from, as this class is abstract so we can't use self.
        $class = get_called_class();

        // Instantiate instance of this class and set action and other params.
        return new $class($action, $options, $data);

    }

}