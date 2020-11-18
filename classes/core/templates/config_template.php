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

use block_plp\plp;
use block_plp\template as base_template;

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
     * Overridden default template constructor.
     */
    public function __construct() {

        global $CFG;

        // Call parent constructor first.
        parent::__construct();

        // Set template variables which must be present on all config templates.
        $version = plp::get_version_info();
        $this->add_var('plugin_version', $version->release . ' (' . $version->version . ')');
        $this->add_var('wwwroot', $CFG->wwwroot);

    }

    /**
     * Build up the template to be displayed for the config overview page.
     * @return bool
     */
    public function call_overview() {

        // Get data to be passed into template.
        $moodle = plp::get_system_info();
        $datarootwriteable = (is_writable(plp::get_dataroot()));

        // Add to the template variables.
        $this->add_var('system_version', $moodle->release . ' (' . $moodle->version . ')');
        $this->add_var('dataroot', plp::get_dataroot());
        $this->add_var('dataroot_writeable', ($datarootwriteable) ? get_string('writeable', 'block_plp') :
            get_string('notwriteable', 'block_plp'));
        $this->add_var('dataroot_writeable_badge', ($datarootwriteable) ? 'success' : 'danger');
        $this->add_var('overview_selected', true);

        return true;

    }

}