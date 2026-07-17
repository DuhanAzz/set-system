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
        $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

        if ($eventId == 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }
        
        // Pintu Kasir: Ambil data entries untuk event ini
        $sql = "SELECT e.*, s.skater_name, s.gender, a.group_name as age_group, c.club_name, d.distance_name, ed.category_name 
                FROM roll_entries e 
                JOIN roll_skaters s ON e.skater_id = s.id 
                LEFT JOIN roll_clubs c ON s.club_id = c.id 
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                WHERE e.event_id = ?
                ORDER BY e.status ASC, e.id DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$eventId]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Data needed for manual insert by Admin
        $stmtSkaters = $db->prepare("SELECT id, skater_name, club_id FROM roll_skaters");
        $stmtSkaters->execute();
        $skaters = $stmtSkaters->fetchAll(PDO::FETCH_ASSOC);

        $stmtClasses = $db->prepare("SELECT ed.id, d.distance_name, a.group_name, ed.category_name 
                                     FROM roll_event_details ed 
                                     LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                     LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                     WHERE ed.event_id = ?");
        $stmtClasses->execute([$eventId]);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('roll/admin/entries/index', [
            'entries' => $entries,
            'skaters' => $skaters,
            'classes' => $classes,
            'eventId' => $eventId
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
            $skaterId = $_POST['skater_id'] ?? 0;
            $raceClassId = $_POST['race_class_id'] ?? 0; // Using race_class_id as requested

            if ($eventId == 0 || $skaterId == 0 || $raceClassId == 0) {
                $_SESSION['flash_message'] = "Data tidak lengkap!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
                exit;
            }

            // GEMBOK ANTI-DUPLIKASI (Double Entry Check)
            $stmtCek = $db->prepare("SELECT id FROM roll_entries WHERE event_id = ? AND skater_id = ? AND race_class_id = ?");
            $stmtCek->execute([$eventId, $skaterId, $raceClassId]);
            if ($stmtCek->rowCount() > 0) {
                $_SESSION['flash_message'] = "Atlet ini sudah terdaftar di kelas lomba tersebut!";
                $_SESSION['flash_type'] = "error";
                header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
                exit;
            }

            // Note: If roll_entries doesn't have race_class_id, we need to alter it.
            // But we will write the SQL assuming it exists per user requirements.
            $stmt = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, race_class_id, status) VALUES (?, ?, ?, 'Paid')");
            try {
                $stmt->execute([$eventId, $skaterId, $raceClassId]);
                $_SESSION['flash_message'] = "Atlet berhasil didaftarkan secara manual!";
                $_SESSION['flash_type'] = "success";
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Gagal mendaftar: " . $e->getMessage();
                $_SESSION['flash_type'] = "error";
            }
            
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

            $stmt = $db->prepare("DELETE FROM roll_entries WHERE id = ? AND event_id = ?");
            $stmt->execute([$id, $eventId]);

            $_SESSION['flash_message'] = "Pendaftaran berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
            exit;
        }
    }

    public function approvePayment($entry_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getInstance()->getConnection();
            $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;

            $stmt = $db->prepare("UPDATE roll_entries SET status = 'Paid' WHERE id = ? AND event_id = ? AND status IN ('Pending', 'Unpaid')");
            $stmt->execute([$entry_id, $eventId]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_message'] = "Pembayaran berhasil diverifikasi (Paid)!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Validasi gagal: Status tidak dapat diubah.";
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/entries");
            exit;
        }
    }
}
