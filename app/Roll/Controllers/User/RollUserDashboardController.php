<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollUserDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $club_id = $_SESSION['roll_club_id'] ?? 0;

        $totalSkaters = $db->prepare("SELECT COUNT(*) FROM roll_skaters WHERE club_id = ?");
        $totalSkaters->execute([$club_id]);
        $totalSkaters = $totalSkaters->fetchColumn();

        $activeEvents = $db->query("SELECT * FROM roll_events WHERE event_status != 'Draft' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/user/dashboard/index', [
            'totalSkaters' => $totalSkaters,
            'activeEvents' => $activeEvents
        ]);
    }
}
