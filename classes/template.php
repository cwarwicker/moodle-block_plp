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
 * This file holds the base template class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp;

use context;
use context_system;
use moodle_exception;
use moodle_url;
use renderer_base;

defined('MOODLE_INTERNAL') or die();

/**
 * The base template class is an abstract class which is extended by other, more specific, templates.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class template {

    /**
     * Array of named variables we will be passing into the template.
     * @var array
     */
    protected $vars = [];

    /**
     * The method we are calling on the template.
     * @var string
     */
    protected $action;

    /**
     * Page context to use.
     * @var context
     */
    protected $context;

    /**
     * Moodle renderer to be used to render mustache templates.
     * @var renderer_base
     */
    protected $renderer;

    /**
     * Page title to display.
     * @var string
     */
    protected $pagetitle;

    /**
     * If specified, the template will try to load this mustache file instead of working out which one it should use.
     * @var string
     */
    protected $specificmustachefile;

    /**
     * Construct the template object.
     */
    public function __construct() {

        global $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);

        // Set default property values.
        $this->set_context($context);
        $this->set_renderer($PAGE->get_renderer('block_plp'));

    }

    /**
     * Get all of the variables we want to inject into the template.
     * @return array
     */
    public function get_vars() : array {
        return $this->vars;
    }

    /**
     * Set the array of variables to pass into the template.
     * @param array $vars
     * @return $this
     */
    public function set_vars(array $vars) {
        $this->vars = $vars;
        return $this;
    }

    /**
     * Add an element to the array of template variables.
     * @param string $key Array key to use.
     * @param mixed $val The value to set.
     * @return $this
     */
    public function add_var(string $key, $val) {
        $this->vars[$key] = $val;
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
     * Check if this template actually has the specified action as a method.
     * @return bool
     */
    protected function has_action() : bool {
        return method_exists($this, 'call_' . $this->get_action());
    }

    /**
     * Get the context of the page.
     * @return context
     */
    public function get_context() : context {
        return $this->context;
    }

    /**
     * Set the context of the page.
     * @param context $context
     * @return $this
     */
    public function set_context(context $context) {
        $this->context = $context;
        return $this;
    }

    /**
     * Get the renderer class we are using to render the mustache templates.
     * @return renderer_base
     */
    public function get_renderer() : renderer_base {
        return $this->renderer;
    }

    /**
     * Set the renderer object.
     * @param renderer_base $renderer
     * @return $this
     */
    public function set_renderer(renderer_base $renderer) {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Get the page title we have set for this template and action.
     * @return string|null
     */
    public function get_page_title() : ?string {
        return $this->pagetitle;
    }

    /**
     * Set the page title to use.
     * @param string $title
     * @return $this
     */
    public function set_page_title(string $title) {
        $this->pagetitle = $title;
        return $this;
    }

    /**
     * Get the page title to display, either from one which has been set, or a default using the component and action.
     * @return string
     */
    protected function get_default_page_title() : string {

        $default = 'title:' . $this->get_component() . ':' . $this->get_component_name() . ':' . $this->get_action();
        $key = ($this->pagetitle) ?? $default;
        return get_string('pluginname', 'block_plp') . ' - ' . get_string($key, 'block_plp');

    }

    /**
     * Get the specific mustache file we want to load.
     * @return string
     */
    public function get_specific_mustache_file() : ?string {
        return $this->specificmustachefile;
    }

    /**
     * Set a specific mustache file to load, instead of working it out based on component and action.
     * @param string $file Path to the mustache file. E.g. "block_plp/myfile"
     * @return $this
     */
    public function set_specific_mustache_file(string $file) {
        $this->specificmustachefile = $file;
        return $this;
    }

    /**
     * Call the action's method.
     * @return bool The result of calling the method. If return is not TRUE, will be assumed something went wrong.
     */
    public function call_action() : bool {
        if ($this->has_action()) {
            return call_user_func([$this, 'call_' . $this->get_action()]);
        } else {
            return false;
        }
    }

    /**
     * Get the component from the namespace.
     *
     * For example, if the namespace is: block_plp\core\templates\config_template then the component is "core".
     * @return string|string[]
     * @throws \ReflectionException
     */
    protected function get_component() : string {
        $reflect = new \ReflectionClass($this);
        return str_replace(['block_plp\\', '\\templates'], '', $reflect->getNamespaceName());
    }

    /**
     * Get the component name from the namespace.
     *
     * For example, if the namespace is block_plp\core\templates\config_template then the component name is: "config".
     * @return string
     * @throws \ReflectionException
     */
    protected function get_component_name() : string {
        $reflect = new \ReflectionClass($this);
        return str_replace('_template', '', $reflect->getShortName());
    }

    /**
     * Work out which mustache file needs loading by the renderer, based on this class name and the action.
     * @return array Array of possible template files to try.
     */
    protected function get_possible_template_files() {

        $files = array();

        // If we specified a file to load, use that instead.
        if ($this->get_specific_mustache_file() !== null) {
            $files[] = $this->get_specific_mustache_file();
            return $files;
        }

        // Mustache files must be within the top level plugin directory 'templates'. These will be broken down into
        // sub-directories based on their components. E.g. templates/core/config, templates/plugins/attendance/
        // Therefore we need to work out the path in the templates directory, based on this class and action.
        $component = $this->get_component();
        $name = $this->get_component_name();

        // First try using the action template. E.g. "/templates/core/config/action.mustache".
        $files[] = 'block_plp/' . $component . '/' . $name . '/' . $this->get_action();

        // If that doesn't exist, try looking for an 'index' one. E.g. "/templates/core/config/index.mustache".
        $files[] = 'block_plp/' . $component . '/' . $name . '/index';

        return $files;

    }

    /**
     * Render the template.
     * @throws moodle_exception
     */
    public function render() {

        global $OUTPUT, $PAGE;

        $plp = new plp();

        // Start by setting up the Moodle page.
        $PAGE->set_url(new moodle_url($_SERVER['REQUEST_URI']));
        $PAGE->set_title( $this->get_default_page_title() );
        $PAGE->set_cacheable(true);
        $PAGE->set_pagelayout($plp->get_setting('layout'));

        echo $OUTPUT->header();

        // There may not be a mustache file for the action we ran, in which case we will want to fall back to a default.
        // So, we need to work out which mustache files to try loading and see if any of them can be loaded.
        $files = $this->get_possible_template_files();
        foreach ($files as $index => $file) {
            try {
                // If we can successfully load this musctache file, then break out of the loop and don't try the others.
                echo $this->get_renderer()->render_from_template($file, $this->get_vars());
                break;
            } catch (moodle_exception $ex) {
                // If we catch the exception, do nothing and try the next file.
                // However, if we reach the last file and that won't load either, then we can throw the exception back.
                if ($index == count($files) - 1) {
                    throw $ex;
                }
            }
        }

        echo $OUTPUT->footer();

    }

    /**
     * Instantiate a template object.
     * @param string $action The action we are performing
     * @param array|null $options Array of controller options to apply before running anything
     * @param array|null $data Array of extra data to pass through with the action
     * @return controller
     */
    public static function load(string $action, array $options = null, array $data = null) : controller {

        // Get the class this method was actually called from, as this class is abstract so we can't use self.
        $class = get_called_class();

        // Instantiate instance of this class and set action and other params.
        $controller = new $class();
        $controller->set_action($action)->set_options($options)->set_data($data);
        return $controller;

    }

}