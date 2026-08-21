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
}
