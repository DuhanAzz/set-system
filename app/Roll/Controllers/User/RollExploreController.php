<?php

namespace App\Roll\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollExploreController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        // Ambil semua event aktif
        $stmt = $db->query("SELECT * FROM roll_events WHERE status IN ('Active', 'Published') ORDER BY event_date_start ASC");
        $competitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/user/explore/index', [
            'competitions' => $competitions
        ]);
    }

    public function detail($event_id = null) {
        if (!$event_id) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            header("Location: " . getenv('APP_URL') . "/roll/user/explore");
            exit;
        }

        $stmtClasses = $db->prepare("
            SELECT c.*, a.group_name, a.min_year, a.max_year, d.distance_name, c.category_name, sc.class_name as skate_class_name
            FROM roll_event_details c
            JOIN roll_ref_age_groups a ON c.age_group_id = a.id
            JOIN roll_ref_distances d ON c.distance_id = d.id
            JOIN roll_ref_skate_classes sc ON c.skate_class_id = sc.id
            WHERE c.event_id = ?
            ORDER BY CAST(c.race_number AS UNSIGNED) ASC, c.race_number ASC
        ");
        $stmtClasses->execute([$event_id]);
        $raceList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/user/explore/detail', [
            'event' => $event,
            'raceList' => $raceList
        ]);
    }
}
