<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $totalUsers = $db->query("SELECT COUNT(*) FROM roll_users")->fetchColumn();
        $totalClubs = $db->query("SELECT COUNT(*) FROM roll_clubs")->fetchColumn();
        $totalSkaters = $db->query("SELECT COUNT(*) FROM roll_skaters")->fetchColumn();
        $totalEvents = $db->query("SELECT COUNT(*) FROM roll_events")->fetchColumn();

        return $this->view('roll/master/dashboard/index', [
            'totalUsers' => $totalUsers,
            'totalClubs' => $totalClubs,
            'totalSkaters' => $totalSkaters,
            'totalEvents' => $totalEvents
        ]);
    }
}
