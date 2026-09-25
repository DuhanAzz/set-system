<?php

namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollAdminDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        $uid = $_SESSION['roll_user_id'] ?? 0;

        // 1. Get All Events for Switcher Dropdown
        $stmtAll = $db->prepare("SELECT id, event_name, status FROM roll_events WHERE user_id = ? ORDER BY id DESC");
        $stmtAll->execute([$uid]);
        $allEvents = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        // 2. Determine Active Event
        if (isset($_GET['switch_event_id'])) {
            $switchId = (int)$_GET['switch_event_id'];
            $isValid = false;
            foreach ($allEvents as $e) {
                if ($e['id'] == $switchId) {
                    $isValid = true;
                    break;
                }
            }
            if ($isValid) {
                $_SESSION['roll_admin_active_event_id'] = $switchId;
            } else {
                $_SESSION['flash_message'] = "Event tidak ditemukan atau Anda tidak memiliki akses.";
                $_SESSION['flash_type'] = "error";
            }
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        $isValidSession = false;
        if ($eventId > 0) {
            foreach ($allEvents as $e) {
                if ($e['id'] == $eventId) {
                    $isValidSession = true;
                    break;
                }
            }
        }

        if (!$isValidSession) {
            $eventId = $allEvents[0]['id'] ?? 0;
            if ($eventId > 0) {
                $_SESSION['roll_admin_active_event_id'] = $eventId;
            } else {
                unset($_SESSION['roll_admin_active_event_id']);
            }
        }

        // 3. Fetch Active Event Data
        $event = null;
        if ($eventId > 0) {
            $stmtEv = $db->prepare("SELECT * FROM roll_events WHERE id = ? AND user_id = ?");
            $stmtEv->execute([$eventId, $uid]);
            $event = $stmtEv->fetch(PDO::FETCH_ASSOC);
        }

        // Variables for View
        $eventName   = $event['event_name']       ?? 'Belum Ada Event Aktif';
        $eventLoc    = $event['event_location']   ?? '-';
        $eventDate   = $event['event_date_start'] ?? date('Y-m-d');
        $eventStatus = $event['status']           ?? 'Draft';

        // 4. Calculate Stats for this event
        $stats = ['atlet' => 0, 'entries' => 0, 'clubs' => 0, 'pending' => 0, 'paid' => 0, 'revenue' => 0];
        $chartLabels = [];
        $chartValues = [];
        
        if ($event) {
            // Entries
            $stats['entries'] = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ?")
                ->execute([$eventId]) ? $db->query("SELECT COUNT(*) FROM roll_entries WHERE event_id = $eventId")->fetchColumn() : 0;
            $stmtEntry = $db->prepare("SELECT COUNT(*) FROM roll_entries WHERE event_id = ?");
            $stmtEntry->execute([$eventId]);
            $stats['entries'] = $stmtEntry->fetchColumn();

            // Athletes (Unique Skaters)
            $stmtSkater = $db->prepare("SELECT COUNT(DISTINCT skater_id) FROM roll_entries WHERE event_id = ?");
            $stmtSkater->execute([$eventId]);
            $stats['atlet'] = $stmtSkater->fetchColumn();

            // Clubs (Unique Clubs)
            $stmtClub = $db->prepare("SELECT COUNT(DISTINCT s.club_id) FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE e.event_id = ?");
            $stmtClub->execute([$eventId]);
            $stats['clubs'] = $stmtClub->fetchColumn();

            // Pending Payments
            $stmtPending = $db->prepare("SELECT COUNT(*) FROM roll_payments WHERE event_id = ? AND status = 'Pending'");
            $stmtPending->execute([$eventId]);
            $stats['pending'] = $stmtPending->fetchColumn() ?: 0;

            // Paid
            $stmtPaid = $db->prepare("SELECT COUNT(*) FROM roll_payments WHERE event_id = ? AND status = 'Paid'");
            $stmtPaid->execute([$eventId]);
            $stats['paid'] = $stmtPaid->fetchColumn() ?: 0;

            // Revenue
            $stmtRevenue = $db->prepare("SELECT SUM(total_amount) FROM roll_payments WHERE event_id = ? AND status = 'Paid'");
            $stmtRevenue->execute([$eventId]);
            $stats['revenue'] = $stmtRevenue->fetchColumn() ?: 0;

            // Top 5 Clubs for Chart
            $stmtChart = $db->prepare("
                SELECT c.club_name, COUNT(e.id) as total
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                JOIN roll_clubs c ON s.club_id = c.id
                WHERE e.event_id = ?
                GROUP BY c.id, c.club_name
                ORDER BY total DESC
                LIMIT 5
            ");
            $stmtChart->execute([$eventId]);
            $chartRows = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
            foreach ($chartRows as $row) {
                $chartLabels[] = $row['club_name'];
                $chartValues[] = (int)$row['total'];
            }

            // --- PARTICIPANT BREAKDOWN LOGIC ---
            $breakdownData = [];
            
            $sqlBreakdown = "
                SELECT 
                    sc.class_name,
                    COALESCE(p.status, 'Unpaid') as pay_status,
                    a.group_name,
                    s.gender,
                    COUNT(DISTINCT s.id) as total_skaters
                FROM roll_entries e
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
                LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
                LEFT JOIN roll_payments p ON p.club_id = s.club_id AND p.event_id = e.event_id
                WHERE e.event_id = ?
                GROUP BY sc.class_name, pay_status, a.group_name, s.gender
            ";
            $stmtBreakdown = $db->prepare($sqlBreakdown);
            $stmtBreakdown->execute([$eventId]);
            $breakdownRows = $stmtBreakdown->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($breakdownRows as $r) {
                $rawClass = strtolower($r['class_name'] ?? '');
                $cat = 'Lainnya';
                if (strpos($rawClass, 'speed') !== false) $cat = 'Speed';
                elseif (strpos($rawClass, 'standar') !== false) $cat = 'Standart';
                elseif (strpos($rawClass, 'pemula') !== false) $cat = 'Pemula';
                
                // Terverifikasi jika Paid, selain itu Belum Terverifikasi
                $status = ($r['pay_status'] === 'Paid') ? 'Terverifikasi' : 'Belum Terverifikasi';
                $ku = $r['group_name'] ?: 'Tanpa KU';
                $gender = ($r['gender'] == 'M') ? 'Putra' : 'Putri';
                $count = (int)$r['total_skaters'];

                if (!isset($breakdownData[$cat])) {
                    $breakdownData[$cat] = [
                        'Terverifikasi' => ['total' => 0, 'details' => []],
                        'Belum Terverifikasi' => ['total' => 0, 'details' => []]
                    ];
                }
                
                $breakdownData[$cat][$status]['total'] += $count;
                
                if (!isset($breakdownData[$cat][$status]['details'][$ku])) {
                    $breakdownData[$cat][$status]['details'][$ku] = ['Putra' => 0, 'Putri' => 0];
                }
                $breakdownData[$cat][$status]['details'][$ku][$gender] += $count;
            }
            
            // Sort keys
            ksort($breakdownData);
            foreach ($breakdownData as $cat => &$stat) {
                foreach (['Terverifikasi', 'Belum Terverifikasi'] as $st) {
                    ksort($stat[$st]['details']);
                }
            }
            unset($stat);
        }

        return $this->view('roll/admin/dashboard/index', [
            'allEvents'    => $allEvents,
            'eventId'      => $eventId,
            'eventName'    => $eventName,
            'eventLoc'     => $eventLoc,
            'eventDate'    => $eventDate,
            'eventStatus'  => $eventStatus,
            'stats'        => $stats,
            'breakdownData'=> $breakdownData ?? []
        ]);
    }

    public function clubs() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        if ($eventId == 0) {
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            $stmtClubs = $db->prepare("
                SELECT 
                    c.id, 
                    c.club_name, 
                    MAX(u.phone) as phone,
                    MAX(u.fullname) as pic_name,
                    COUNT(DISTINCT s.id) as total_athletes,
                    COUNT(e.id) as total_entries,
                    SUM(CASE WHEN pay_club.status = 'Paid' OR pay_man.status = 'Paid' THEN 1 ELSE 0 END) as verified_entries
                FROM roll_entries e
                JOIN roll_clubs c ON e.club_id = c.id
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_users u ON u.club_id = c.id
                LEFT JOIN roll_payments pay_club ON pay_club.club_id = e.club_id AND pay_club.event_id = e.event_id
                LEFT JOIN roll_manual_payments pay_man ON pay_man.invoice_code COLLATE utf8mb4_unicode_ci = e.manual_invoice_code COLLATE utf8mb4_unicode_ci
                WHERE e.event_id = ?
                GROUP BY c.id, c.club_name
                ORDER BY c.club_name ASC
            ");
            $stmtClubs->execute([$eventId]);
            $clubs = $stmtClubs->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            die("SQL ERROR clubs: " . $e->getMessage());
        }

        $verifiedClubs = [];
        $unverifiedClubs = [];
        foreach ($clubs as $c) {
            if ($c['verified_entries'] > 0) {
                $verifiedClubs[] = $c;
            } else {
                $unverifiedClubs[] = $c;
            }
        }

        return $this->view('roll/admin/dashboard/clubs', [
            'event' => $event,
            'verifiedClubs' => $verifiedClubs,
            'unverifiedClubs' => $unverifiedClubs,
            'totalClubs' => count($clubs)
        ]);
    }

    public function printClubs() {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        if ($eventId == 0) {
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM roll_events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            $stmtClubs = $db->prepare("
                SELECT 
                    c.id, 
                    c.club_name, 
                    MAX(u.phone) as phone,
                    MAX(u.fullname) as pic_name,
                    COUNT(DISTINCT s.id) as total_athletes,
                    COUNT(e.id) as total_entries,
                    SUM(CASE WHEN pay_club.status = 'Paid' OR pay_man.status = 'Paid' THEN 1 ELSE 0 END) as verified_entries
                FROM roll_entries e
                JOIN roll_clubs c ON e.club_id = c.id
                JOIN roll_skaters s ON e.skater_id = s.id
                LEFT JOIN roll_users u ON u.club_id = c.id
                LEFT JOIN roll_payments pay_club ON pay_club.club_id = e.club_id AND pay_club.event_id = e.event_id
                LEFT JOIN roll_manual_payments pay_man ON pay_man.invoice_code COLLATE utf8mb4_unicode_ci = e.manual_invoice_code COLLATE utf8mb4_unicode_ci
                WHERE e.event_id = ?
                GROUP BY c.id, c.club_name
                ORDER BY c.club_name ASC
            ");
            $stmtClubs->execute([$eventId]);
            $clubs = $stmtClubs->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            die("SQL ERROR printClubs: " . $e->getMessage());
        }

        $verifiedClubs = [];
        $unverifiedClubs = [];
        foreach ($clubs as $c) {
            if ($c['verified_entries'] > 0) {
                $verifiedClubs[] = $c;
            } else {
                $unverifiedClubs[] = $c;
            }
        }

        return $this->view('roll/admin/dashboard/print_clubs', [
            'event' => $event,
            'verifiedClubs' => $verifiedClubs,
            'unverifiedClubs' => $unverifiedClubs
        ]);
    }
}
