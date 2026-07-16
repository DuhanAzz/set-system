<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterFinanceController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        // Asumsi struktur finance: semua paid/pending dibaca
        $entries = [];
        try {
            $stmt = $db->query("
                SELECT e.*, c.club_name, ev.event_name, ev.registration_fee
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_clubs c ON s.club_id = c.id
                JOIN roll_events ev ON e.event_id = ev.id
                WHERE e.payment_status IN ('Paid', 'Pending')
                ORDER BY e.id DESC
            ");
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // fallback
        }

        return $this->view('roll/master/finance/index', [
            'entries' => $entries
        ]);
    }
}
