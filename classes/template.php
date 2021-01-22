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
use ReflectionClass;
use renderer_base;
use SimpleXMLElement;

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
     * Response type for standard HTML page.
     */
    const RESPONSE_TYPE_HTML = 'html';

    /**
     * Response type for JSON response.
     */
    const RESPONSE_TYPE_JSON = 'json';

    /**
     * Response type for XML response.
     */
    const RESPONSE_TYPE_XML = 'xml';

    /**
     * Array of named variables we will be passing into the template.
     * @var array
     */
    protected $vars = [];

    /**
     * The page we are loading through the template
     * @var string
     */
    protected $page;

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
     * The response type of the template.
     * @var string
     */
    protected $responsetype;

    /**
     * If set, this specific content will be rendered and nothing else.
     * @var mixed
     */
    protected $content;

    /**
     * Construct the template object.
     */
    public function __construct() {

        global $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);

        // Set default property values.
        $this->set_response_type(static::RESPONSE_TYPE_HTML);
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
     * Try and get a specific variable that has been loaded into the template.
     * @param string $key
     * @return mixed|null
     */
    public function get_var(string $key) {
        return (array_key_exists($key, $this->vars)) ? $this->vars[$key] : null;
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
     * @return string|null
     */
    public function get_action() : ?string {
        return $this->action;
    }

    /**
     * Set the action we are going to perform.
     * @param string|null $action
     * @return $this
     */
    public function set_action(?string $action) {
        $this->action = $action;
        return $this;
    }

    /**
     * Get the page we are wanting to load.
     * @return string
     */
    public function get_page() : string {
        return $this->page;
    }

    /**
     * Set the page we are wanting to load.
     * @param string $page
     * @return $this
     */
    public function set_page(string $page) {
        $this->page = $page;
        return $this;
    }

    /**
     * Check if this template actually has the specified page.
     * @return bool
     */
    protected function has_page() : bool {
        return method_exists($this, 'page_' . $this->get_page());
    }

    /**
     * Check if this template actually has the specified action.
     * @return bool
     */
    protected function has_action() : bool {
        return method_exists($this, 'action_' . $this->get_page() . '_' . $this->get_action());
    }

    /**
     * Get the context of the $PAGE.
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

        $default = 'title:' . $this->get_component() . ':' . $this->get_component_name() . ':' . $this->get_page();
        if (!is_null($this->get_action())) {
            $default .= ':' . $this->get_action();
        }
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
     * Get the template's response type.
     * @return string
     */
    public function get_response_type() : string {
        return $this->responsetype;
    }

    /**
     * Set the template's response type.
     * @param string $type
     * @return $this
     */
    public function set_response_type(string $type) {
        $this->responsetype = $type;
        return $this;
    }

    /**
     * Get the template's specific content.
     * @return mixed
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * Set the template's specific content.
     * @param mixed $content
     * @return $this
     */
    public function set_content($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * Call the template's page/action method.
     * @return bool The result of calling the method. If return is not TRUE, will be assumed something went wrong.
     */
    public function call_page_action() : bool {

        if ($this->has_page()) {

            if ($this->has_action()) {
                // Are we running a specific action? If so, call that.
                return call_user_func([$this, 'action_' . $this->get_page() . '_' . $this->get_action()]);
            } else {
                // If not, just call the page loading method.
                return call_user_func([$this, 'page_' . $this->get_page()]);
            }

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
        $reflect = new ReflectionClass($this);
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
        $reflect = new ReflectionClass($this);
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

        // If we have an action, try that first.
        if (!is_null($this->get_action())) {
            $files[] = 'block_plp/' . $component . '/' . $name . '/' . $this->get_page() . '_' . $this->get_action();
        }

        // First try using the page we are trying to load. E.g. "/templates/core/config/page.mustache".
        $files[] = 'block_plp/' . $component . '/' . $name . '/' . $this->get_page();

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

        // Send the HTTP headers for the response type we are using.
        $this->send_headers();

        // If we send through content direct to this method, we are rendering just that and not the rest of the page.
        if ($this->get_content() !== null) {
            $this->render_content();
        } else {

            // Start by setting up the Moodle page.
            $PAGE->set_url(new moodle_url($_SERVER['REQUEST_URI']));
            $PAGE->set_title( $this->get_default_page_title() );
            $PAGE->set_cacheable(true);
            $PAGE->set_pagelayout($plp->get_setting('layout'));

            // Load any scripts required by this page.
            $this->load_scripts();

            // Load any extra styles required.
            $this->load_css();

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

    }

    /**
     * Get the URL of the template page.
     * @param array|null $extraparams
     * @return moodle_url
     */
    public function get_url(array $extraparams = null) : moodle_url {

        $params = [];
        $params['page'] = $this->get_page();
        if ($this->has_action()) {
            $params['action'] = $this->get_action();
        }

        // Add any extra params to the url.
        if (!is_null($extraparams)) {
            $params = $params + $extraparams;
        }

        return new moodle_url('/blocks/plp/' . $this->get_component_name() . '.php', $params);

    }

    /**
     * See if there are any scripts to load for this page.
     * @return void
     */
    protected function load_scripts() : void {

        global $CFG, $PAGE;

        // Always load the core 'main' scripts.
        $PAGE->requires->js_call_amd('block_plp/main', 'init');

        $path = $this->get_component() . '_' . $this->get_component_name() . '_' . $this->get_page();
        if (file_exists($CFG->dirroot . '/blocks/plp/amd/src/' . $path . '.js')) {
            $PAGE->requires->js_call_amd('block_plp/' . $path, 'init');
        }

    }

    /**
     * Load any stylesheets in the css directory
     * @return void
     */
    protected function load_css() {

        global $CFG, $PAGE;

        foreach (glob($CFG->dirroot . '/blocks/plp/css/*.css') as $file) {
            $PAGE->requires->css('/blocks/plp/css/' . basename($file));
        }

    }

    /**
     * Render specific content, depending on the response type.
     * @return void
     */
    protected function render_content() {

        $content = $this->get_content();

        switch ($this->responsetype) {

            case static::RESPONSE_TYPE_JSON:

                // If the content is an object, cast it to an array.
                if (is_object($content) && $content instanceof model) {
                    $content = $content->to_array();
                } else if (is_object($content)) {
                    $content = (array)$content;
                }

                echo json_encode($content);

            break;

            case static::RESPONSE_TYPE_XML:

                $xml = new SimpleXMLElement('<root/>');

                // To output as XML, the content must be an array.
                if (!is_array($content)) {
                     $xml->addChild('error', get_string('error:xml:content', 'block_plp', gettype($content)));
                } else {
                    array_walk_recursive($content, array($xml, 'addChild'));
                }
                echo $xml->asXML();

            break;

            // HTML or anything else, just render as-is.
            case static::RESPONSE_TYPE_HTML:
            default:
                echo $content;

        }

    }

    /**
     * Send relevant HTTP headers, depending on response type of template.
     * @return void
     */
    protected function send_headers() {

        switch ($this->responsetype) {

            // Send JSON headers.
            case static::RESPONSE_TYPE_JSON:
                header('Content-type: application/json');
            break;

            // Send XML headers.
            case static::RESPONSE_TYPE_XML:
                header('Content-Type: application/xml; charset=utf-8');
            break;

            // For HTML or anything else, we can just leave Moodle to deal with the headers.
            case static::RESPONSE_TYPE_HTML:
            default:
                return;

        }

    }

}