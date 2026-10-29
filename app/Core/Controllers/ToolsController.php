<?php

namespace App\Core\Controllers;

use App\Core\Controller;

class ToolsController extends Controller {
    
    public function pace_calculator() {
        $this->trackVisitor("core");

        return $this->view('core/tools/pace_calculator', [
            'pageTitle' => 'Kalkulator Pace Training',
            'strokeOptions' => \App\Core\Models\PaceCalculatorModel::getStrokeOptions(),
            'distanceOptions' => \App\Core\Models\PaceCalculatorModel::getTestDistanceOptions(),
            'intensityZones' => \App\Core\Models\PaceCalculatorModel::getIntensityZones()
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

    public function race_stopwatch() {
        $this->trackVisitor("core");

        return $this->view('core/tools/race_stopwatch', [
            'pageTitle' => 'Stopwatch Analisis Lomba Renang',
            'poolOptions' => \App\Core\Models\RaceAnalysisModel::getPoolOptions(),
            'strokeOptions' => \App\Core\Models\RaceAnalysisModel::getStrokeOptions(),
            'distanceOptions' => \App\Core\Models\RaceAnalysisModel::getDistanceOptions()
        ]);
    }
}
