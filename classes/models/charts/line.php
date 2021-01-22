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

/**
 * This class generates a pie chart.
 *
 * @package     block_plp
 * @copyright   2020-onwards Conn Warwicker <conn@cmrwarwicker.com>
 * @link        https://github.com/cwarwicker/moodle-block_plp
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class line extends bar {

    /**
     * Generate the pie chart and return its contents in base64.
     * @return string base64 encoded image contents.
     */
    public function generate() : string {

        $graph = new Graph\Graph($this->width, $this->height, 'auto');
        $graph->SetScale('textlin');

        // Set the title, font and margin.
        $graph->title->Set($this->title);
        $graph->title->SetFont(FF_FONT1, FS_BOLD);
        $graph->img->SetMargin(40, 20, 100, 10);
        $graph->img->SetAntiAliasing();

        // Set the chart legend.
        $graph->legend->Pos(0.02, 0.075);

        // X-Axis - Set labels and title.
        $graph->xaxis->SetTickLabels($this->axes['x']['columns']);
        $graph->xaxis->title->Set($this->axes['x']['title']);

        // Y-Axis - Set title and settings.
        $graph->yaxis->title->Set($this->axes['y']['title']);

        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);

        // Loop through plot points and add them to the chart.
        foreach ($this->groups as $group) {
            $plot = new Plot\LinePlot($group['values']);
            $plot->SetColor($group['colour']);
            $plot->SetLegend($group['title']);
            $graph->Add($plot);
        }

        // Generate image.
        $image = $graph->Stroke(_IMG_HANDLER);
        ob_start();
        imagepng($image);
        $data = ob_get_contents();
        ob_end_clean();

        return base64_encode($data);

    }
}