<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class UserDashboardController extends Controller {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private function checkAccess() {
        if (!isset($_SESSION['swim_role']) || $_SESSION['swim_role'] !== 'user') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
    }

    public function index() {
        $this->checkAccess();
        
        
// src/user/dashboard.php


$uid = $_SESSION['swim_user_id'];

// 1. STATISTIK: TOTAL ATLET
$stmt = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ?");
$stmt->execute([$uid]);
$totalSwimmers = $stmt->fetchColumn();

// 2. STATISTIK: TOTAL EVENT YANG DIIKUTI
// Menghitung berapa banyak baris di tabel event_entries milik klub ini
// Asumsi: club_id disimpan di event_entries
$stmtEntries = $this->db->prepare("SELECT COUNT(*) FROM swim_event_entries WHERE club_id = ?");
$stmtEntries->execute([$uid]);
$totalEntries = $stmtEntries->fetchColumn();

// 3. STATISTIK: STATUS PEMBAYARAN TERAKHIR
$stmtPay = $this->db->prepare("SELECT status FROM swim_payments WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmtPay->execute([$uid]);
$lastPaymentStatus = $stmtPay->fetchColumn(); 
if(!$lastPaymentStatus) $lastPaymentStatus = 'Belum Ada';

// 4. ACTION REQUIRED: UNPAID INVOICES
$stmtUnpaid = $this->db->prepare("SELECT COUNT(*) FROM swim_payments WHERE user_id = ? AND status IN ('Pending', 'Unpaid', 'pending')");
$stmtUnpaid->execute([$uid]);
$unpaidInvoices = $stmtUnpaid->fetchColumn();

// 5. ACTION REQUIRED: MISSING UID ATHLETES
$stmtNoUid = $this->db->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND (uid IS NULL OR trim(uid) = '' OR uid = '-' OR uid LIKE 'SW%' OR uid = '0')");
$stmtNoUid->execute([$uid]);
$missingUid = $stmtNoUid->fetchColumn();

// 6. DETEKSI EVENT ESTAFET AKTIF
$stmtRelayEvent = $this->db->query("SELECT e.id FROM swim_events e JOIN swim_event_numbers en ON e.id = en.event_id WHERE en.is_relay = 1 AND e.event_status IN ('Active', 'Open', 'Upcoming', 'Registration') ORDER BY e.event_date_start ASC LIMIT 1");
$activeRelayEventId = $stmtRelayEvent->fetchColumn();

// --- LOAD VIEWS ---

        
        $this->view('swim/user/dashboard/index', [
            'totalSwimmers' => $totalSwimmers,
            'totalEntries' => $totalEntries,
            'lastPaymentStatus' => $lastPaymentStatus,
            'unpaidInvoices' => $unpaidInvoices,
            'missingUid' => $missingUid,
            'activeRelayEventId' => $activeRelayEventId
        ]);
    }
}