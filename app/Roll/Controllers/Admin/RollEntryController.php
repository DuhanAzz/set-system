<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollEntryController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT e.*, s.skater_name, s.age_group, c.club_name, ev.event_name 
                FROM roll_entries e 
                JOIN roll_skaters s ON e.skater_id = s.id 
                LEFT JOIN roll_clubs c ON s.club_id = c.id 
                JOIN roll_events ev ON e.event_id = ev.id
                ORDER BY e.id DESC";
        $entries = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/entries/index', [
            'entries' => $entries
        ]);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM roll_entries WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash_message'] = "Pendaftaran berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
            exit;
        }
    }

    public function approve($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE roll_entries SET status = 'Paid' WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash_message'] = "Pembayaran berhasil diverifikasi!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
            exit;
        }
    }
}
