<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        $totalEvents = $db->query("SELECT COUNT(*) FROM roll_events")->fetchColumn();
        $totalClubs = $db->query("SELECT COUNT(*) FROM roll_clubs")->fetchColumn();
        $totalSkaters = $db->query("SELECT COUNT(*) FROM roll_skaters")->fetchColumn();
        $totalEntries = $db->query("SELECT COUNT(*) FROM roll_entries")->fetchColumn();

        $latestEvents = $db->query("SELECT * FROM roll_events ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/dashboard/index', [
            'totalEvents' => $totalEvents,
            'totalClubs' => $totalClubs,
            'totalSkaters' => $totalSkaters,
            'totalEntries' => $totalEntries,
            'latestEvents' => $latestEvents
        ]);
    }
}
