<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Cek Keamanan: Pastikan user login dan memiliki role admin atau master
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'master'])) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/login' : '/login';
            header("Location: " . $loginUrl);
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        $stats = [
            'totalEvents' => 0,
            'totalClubs' => 0,
            'totalSkaters' => 0,
            'totalEntries' => 0
        ];
        $latestEvents = [];

        try {
            // 1. STATISTIK
            $stats['totalEvents'] = $db->query("SELECT COUNT(*) FROM roll_events")->fetchColumn();
            $stats['totalClubs'] = $db->query("SELECT COUNT(*) FROM roll_clubs")->fetchColumn();
            $stats['totalSkaters'] = $db->query("SELECT COUNT(*) FROM roll_skaters")->fetchColumn();
            $stats['totalEntries'] = $db->query("SELECT COUNT(*) FROM roll_entries")->fetchColumn();

            // 2. DATA TERBARU
            $latestEvents = $db->query("SELECT * FROM roll_events ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Silent error jika tabel belum siap
        }

        // Kirim data ke view
        return $this->view('roll/dashboard', [
            'stats' => $stats,
            'latestEvents' => $latestEvents
        ]);
    }
}
