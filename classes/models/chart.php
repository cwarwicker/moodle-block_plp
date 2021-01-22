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
 * This file contains the chart class to generate different types of chart images.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models;

use block_plp\exceptions\invalid_data_exception;
use block_plp\models\charts\bar;

defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot . '/blocks/plp/vendor/autoload.php');

/**
 * This is the chart class to generate different types of chart images.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
abstract class chart {

    /**
     * Title of the chart
     * @var string
     */
    protected $title;

    /**
     * Width of the chart in px
     * @var int
     */
    protected $width = 400;

    /**
     * Height of the chart in px
     * @var int
     */
    protected $height = 350;

    /**
     * Construct the chart object and set the data into relevant properties.
     * @param array $data
     */
    public function __construct(array $data) {

        $this->title = ($data['title']) ?? null;
        $this->width = ($data['width']) ?? $this->width;
        $this->height = ($data['height']) ?? $this->height;

    }

    /**
     * Generate the graph.
     * @return string
     */
    abstract public function generate() : string;

    /**
     * Load a chart object, depending on its type.
     * @param array $data
     * @return chart
     * @throws invalid_data_exception
     */
    public static function load(array $data) : chart {

        $class = __NAMESPACE__ . '\\charts\\' . $data['type'];
        if (class_exists($class)) {
            return new $class($data);
        } else {
            throw new invalid_data_exception('exception:data:chart:invalidtype');
        }

    }

}