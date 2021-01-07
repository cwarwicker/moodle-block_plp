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
 * This file holds the config_template class. For handling everything related to the config.php page's display.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
namespace block_plp\core\templates;

use block_plp\moodle;
use block_plp\plp;
use block_plp\template as base_template;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') or die();

/**
 * This is the config_template class.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class config_template extends base_template {

    /**
     * Form to be displayed
     * @var moodleform
     */
    protected $form;

    /**
     * Get the form we have set into this template
     * @return moodleform
     */
    public function get_form() : moodleform {
        return $this->form;
    }

    /**
     * Set a moodle form for displaying
     * @param moodleform $form
     * @return $this
     */
    public function set_form(moodleform $form) {
        $this->form = $form;
        return $this;
    }

    /**
     * Overridden default template constructor.
     */
    public function __construct() {

        global $CFG;

        // Call parent constructor first.
        parent::__construct();

        // Set template variables which must be present on all config templates.
        $plp = new plp();
        $version = $plp->get_version_info();
        $this->add_var('plugin_version', $version->release . ' (' . $version->version . ')');
        $this->add_var('wwwroot', $CFG->wwwroot);

    }

    /**
     * Render the template for the overview page.
     * @return bool
     */
    public function page_overview() : bool {

        // Get data to be passed into template.
        $moodle = new moodle();
        $plp = new plp();
        $datarootwriteable = (is_writable($plp->get_dataroot()));

        // Add to the template variables.
        $this->add_var('system_version', $moodle->get_release() . ' (' . $moodle->get_version() . ')');
        $this->add_var('dataroot', $plp->get_dataroot());
        $this->add_var('dataroot_writeable', ($datarootwriteable) ? get_string('writeable', 'block_plp') :
            get_string('notwriteable', 'block_plp'));
        $this->add_var('dataroot_writeable_badge', ($datarootwriteable) ? 'success' : 'danger');
        $this->add_var('overview_selected', true);

        return true;

    }

    /**
     * Render the template for the settings page.
     * @return bool
     */
    public function page_settings() : bool {

        $this->add_var('form', $this->get_form()->render());
        $this->add_var('settings_selected', true);
        return true;

    }

    /**
     * Render the template for the MIS page.
     * @return bool
     */
    public function page_mis() : bool {

        global $PAGE;

        // Render the default template for the MIS action.
        $renderer = $PAGE->get_renderer('block_plp');
        $this->add_var('mis_selected', true);
        $this->add_var('url_new', new moodle_url('/blocks/plp/config.php', ['page' => 'mis', 'action' => 'edit', 'id' => 0]));
        $this->add_var('connections', $renderer->mis_connections());
        return true;

    }

    /**
     * Display the editing form for an MIS connection
     * @return bool
     */
    public function action_mis_edit() : bool {

        $title = ($this->connection->exists()) ? $this->connection->get('name') : get_string('mis:newconnection', 'block_plp');

        // Render the form in the mustache template.
        $this->add_var('mis_selected', true);
        $this->add_var('form', $this->get_form()->render());
        $this->add_var('connection_title', $title);

        return true;

    }

    /**
     * Display the deletion confirmation screen for an MIS connection.
     * @return bool
     */
    public function action_mis_delete() : bool {

        global $PAGE;

        $renderer = $PAGE->get_renderer('block_plp');

        $this->set_specific_mustache_file('block_plp/generic');
        $this->add_var('data', $renderer->render_confirm_delete($this->connection->get('name'), new moodle_url
        ('/blocks/plp/config.php', [
            'page' => 'mis',
            'action' => 'delete',
            'id' => $this->connection->get('id'),
            'sesskey' => sesskey(),
            'confirmed' => 1
        ]), new moodle_url('/blocks/plp/config.php', [
            'page' => 'mis'
        ])));

        return true;

    }

    /**
     * Method required in order for the plugins page to function.
     * @return true
     */
    public function page_plugins() : bool {

        global $PAGE;

        // Render the default template for the MIS action.
        $renderer = $PAGE->get_renderer('block_plp');
        $this->add_var('plugins_selected', true);
        $this->add_var('plugins', $renderer->plugins());
        $this->add_var('form', $this->get_form()->render());
        return true;

    }

    /**
     * Display the deletion confirmation screen for a plugin.
     * @return bool
     */
    public function action_plugins_delete() : bool {

        global $PAGE;

        $renderer = $PAGE->get_renderer('block_plp');

        $this->set_specific_mustache_file('block_plp/generic');
        $this->add_var('data', $renderer->render_confirm_delete($this->plugin->get('name'), new moodle_url
        ('/blocks/plp/config.php', [
            'page' => 'plugins',
            'action' => 'delete',
            'id' => $this->plugin->get('id'),
            'sesskey' => sesskey(),
            'confirmed' => 1
        ]), new moodle_url('/blocks/plp/config.php', [
            'page' => 'plugins'
        ])));

        return true;

    }

}