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
 * This file contains the code to generate a bar chart.
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

defined('MOODLE_INTERNAL') or die();

/**
 * This class is to generate a bar chart.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class bar extends chart {

    /**
     * Array of axes, containing elements: string 'title', array 'columns'.
     * @var array
     */
    protected $axes;

    /**
     * Array of plot groups, containing elements: string 'title', string 'colour', array 'values'.
     * @var string
     */
    protected $groups;

    /**
     * Construct bar chart object.
     * @param array $data
     */
    public function __construct(array $data) {

        parent::__construct($data);
        $this->axes = ($data['axes']) ?? null;
        $this->groups = ($data['groups']) ?? null;

    }

    /**
     * Generate the bar chart and return its contents in base64.
     * @return string base64 encoded image contents.
     */
    public function generate() : string {

        $graph = new Graph\Graph($this->width, $this->height, 'auto');
        $graph->SetScale('textlin');

        // Set the title, font and margin.
        $graph->title->Set($this->title);
        $graph->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->img->SetMargin(40, 20, 80, 20);

        // Set the chart legend.
        $graph->legend->Pos(0.02, 0.075);

        // X-Axis - Set labels and title.
        $graph->xaxis->SetTickLabels($this->axes['x']['columns']);
        $graph->xaxis->title->Set($this->axes['x']['title']);

        // Y-Axis - Set title.
        $graph->yaxis->title->Set($this->axes['y']['title']);

        // Loop through plot points and add them to the chart.
        $plots = [];
        foreach ($this->groups as $group) {
            $plot = new Plot\BarPlot($group['values']);
            $plot->SetColor('white');
            $plot->SetFillColor($group['colour']);
            $plot->SetLegend($group['title']);
            $plot->value->Show();
            $plot->value->SetFormat('%d');
            $plots[] = $plot;
        }

        // Add group plot to the chart.
        $groupplot = new Plot\GroupBarPlot($plots);
        $groupplot->SetWidth(0.8);
        $graph->Add($groupplot);

        // Generate image.
        $image = $graph->Stroke(_IMG_HANDLER);
        ob_start();
        imagepng($image);
        $data = ob_get_contents();
        ob_end_clean();

        return base64_encode($data);

    }
}