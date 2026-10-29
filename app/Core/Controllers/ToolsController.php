<?php

namespace App\Core\Controllers;

use App\Core\Controller;

class ToolsController extends Controller {
    
    public function pace_calculator() {
        $this->trackVisitor("core"); // Track visitor if needed

        // Tampilkan view tools
        return $this->view('core/tools/pace_calculator', [
            'pageTitle' => 'Kalkulator Pace Training'
        ]);
    }

    public function race_analysis() {
        $this->trackVisitor("core");

        return $this->view('core/tools/race_analysis', [
            'pageTitle' => 'Analisis Performa Lomba (Race Analyzer)',
            'poolOptions' => \App\Core\Models\RaceAnalysisModel::getPoolOptions(),
            'strokeOptions' => \App\Core\Models\RaceAnalysisModel::getStrokeOptions(),
            'distanceOptions' => \App\Core\Models\RaceAnalysisModel::getDistanceOptions()
        ]);
    }
}
