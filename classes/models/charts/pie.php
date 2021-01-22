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
 * This file contains the code to generate a pie chart.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace block_plp\models\charts;

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
use block_plp\models\chart;

/**
 * This class generates a pie chart.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class pie extends chart {

    /**
     * Array of pie slices, containing elements: string 'title', string 'colour', mixed 'value'.
     * @var string
     */
    protected $slices;

    /**
     * Construct bar chart object.
     * @param array $data
     */
    public function __construct(array $data) {

        parent::__construct($data);
        $this->slices = ($data['slices']) ?? null;

    }

    /**
     * Generate the pie chart and return its contents in base64.
     * @return string base64 encoded image contents.
     */
    public function generate() : string {

        $data = ['titles' => [], 'colours' => [], 'values' => []];

        foreach ($this->slices as $slice) {
            $data['titles'][] = $slice['title'];
            $data['colours'][] = $slice['colour'];
            $data['values'][] = $slice['value'];
        }

        // Create the graph object.
        $graph = new Graph\PieGraph($this->width, $this->height);

        // Set the title, font and margin.
        $graph->title->Set($this->title);
        $graph->title->SetFont(FF_FONT1, FS_BOLD);

        // Set the chart legend.
        $graph->legend->Pos(0.02, 0.075);

        // Add slices.
        $plot = new Plot\PiePlot($data['values']);
        $plot->ShowBorder();
        $plot->SetSliceColors($data['colours']);
        $plot->SetLegends($data['titles']);
        $plot->value->SetFormat('%d%%');
        $graph->Add($plot);

        // Generate image.
        $image = $graph->Stroke(_IMG_HANDLER);
        ob_start();
        imagepng($image);
        $data = ob_get_contents();
        ob_end_clean();

        return base64_encode($data);

    }
}