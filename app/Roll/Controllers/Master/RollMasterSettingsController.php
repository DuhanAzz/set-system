<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;

class RollMasterSettingsController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        // Implementasi Web Config (Global limit, Landing page content, etc)
        return $this->view('roll/master/settings/index');
    }
}
