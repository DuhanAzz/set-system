<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

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
        $uid = $_SESSION['user_id'] ?? 0;

        // Ambil Event Aktif Terakhir milik User
        $stmtEvent = $db->prepare("SELECT * FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEvent->execute([$uid]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        // Variable Default
        $eventId   = $event['id'] ?? 0;
        $eventName = $event['event_name'] ?? 'Belum Ada Event Aktif';
        $eventDate = $event['event_date_start'] ?? date('Y-m-d'); 
        $eventLoc  = $event['event_location'] ?? '-';
        $eventStatus = $event['event_status'] ?? 'Draft';

        $stats = ['atlet' => 0, 'entries' => 0, 'clubs' => 0, 'revenue' => 0, 'pending_payments' => 0];
        $chartLabels = [];
        $chartValues = [];
        $isSchool = false;

        if ($eventId > 0) {
            try {
                // A. Total Entries
                $stmtEntry = $db->prepare("SELECT COUNT(*) FROM swim_event_entries WHERE event_id = ?");
                $stmtEntry->execute([$eventId]);
                $stats['entries'] = $stmtEntry->fetchColumn();

                // B. Total Atlet
                $stmtAtlet = $db->prepare("SELECT COUNT(DISTINCT swimmer_id) FROM swim_event_entries WHERE event_id = ?");
                $stmtAtlet->execute([$eventId]);
                $stats['atlet'] = $stmtAtlet->fetchColumn();

                // C. Total Klub / Sekolah
                $partType = strtolower($event['participation_type'] ?? 'club');
                $isSchool = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
                
                if ($isSchool) {
                    $stmtClub = $db->prepare("SELECT COUNT(DISTINCT s.asal_sekolah) FROM swim_event_entries ee JOIN swim_swimmers s ON ee.swimmer_id = s.id WHERE ee.event_id = ? AND s.asal_sekolah != ''");
                } else {
                    $stmtClub = $db->prepare("SELECT COUNT(DISTINCT club_id) FROM swim_event_entries WHERE event_id = ?");
                }
                $stmtClub->execute([$eventId]);
                $stats['clubs'] = $stmtClub->fetchColumn();

                // D. Total Pemasukan
                $stmtRev = $db->prepare("SELECT SUM(amount) FROM swim_payments WHERE event_id = ? AND status IN ('Paid', 'completed')");
                $stmtRev->execute([$eventId]);
                $stats['revenue'] = $stmtRev->fetchColumn() ?: 0;

                // E. Total Pembayaran Pending
                $stmtPending = $db->prepare("SELECT COUNT(*) FROM swim_payments WHERE event_id = ? AND status IN ('Pending', 'Unpaid', 'pending')");
                $stmtPending->execute([$eventId]);
                $stats['pending_payments'] = $stmtPending->fetchColumn() ?: 0;

                // Data Chart
                if ($isSchool) {
                    $sqlChart = "
                        SELECT s.asal_sekolah as nama_klub, COUNT(DISTINCT ee.swimmer_id) as jumlah_atlet
                        FROM swim_event_entries ee
                        JOIN swim_swimmers s ON ee.swimmer_id = s.id
                        WHERE ee.event_id = ? AND s.asal_sekolah != ''
                        GROUP BY s.asal_sekolah
                        ORDER BY jumlah_atlet DESC LIMIT 5
                    ";
                } else {
                    $sqlChart = "
                        SELECT c.nama_klub as nama_klub, COUNT(DISTINCT ee.swimmer_id) as jumlah_atlet
                        FROM swim_event_entries ee
                        JOIN swim_clubs c ON ee.club_id = c.id
                        WHERE ee.event_id = ?
                        GROUP BY c.id
                        ORDER BY jumlah_atlet DESC LIMIT 5
                    ";
                }
                $stmtChart = $db->prepare($sqlChart);
                $stmtChart->execute([$eventId]);
                $dataChart = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
                foreach ($dataChart as $d) {
                    $chartLabels[] = $d['nama_klub'];
                    $chartValues[] = $d['jumlah_atlet'];
                }

            } catch (Exception $e) { 
                // Silent Error seperti aslinya
            }
        }

        // Kirim data ke view
        return $this->view('swim/dashboard', [
            'eventId' => $eventId,
            'eventName' => $eventName,
            'eventDate' => $eventDate,
            'eventLoc' => $eventLoc,
            'eventStatus' => $eventStatus,
            'stats' => $stats,
            'chartLabels' => json_encode($chartLabels),
            'chartValues' => json_encode($chartValues),
            'isSchool' => $isSchool
        ]);
    }
}
