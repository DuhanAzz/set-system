<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;

class RollMaintenanceController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        // Implementasi Garbage Collection, dll
        return $this->view('roll/master/maintenance/index');
    }
}
