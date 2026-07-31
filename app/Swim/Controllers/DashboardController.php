<?php

namespace App\Swim\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Proteksi Akses (Hanya yang sudah login via Swim)
        if (!isset($_SESSION['swim_user_id']) || !isset($_SESSION['swim_role'])) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/swim/login' : '/swim/login';
            header("Location: " . $loginUrl);
            exit;
        }
    }

    public function master() {
        // Cek Role Master
        if ($_SESSION['swim_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $stats = [
            'eo' => 0,
            'clubs' => 0,
            'athletes' => 0,
            'entries' => 0,
            'revenue' => 0,
            'pending_users' => 0,
            'pending_uids' => 0
        ];
        $liveEvents = [];
        $recentUsers = [];
        $systemStatus = 0; 
        $heroTitle = 'SwimMeet App'; 

        try {
            // A. Statistik Dasar User
            $stats['eo']       = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE role = 'admin'")->fetchColumn();
            $stats['clubs']    = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE role = 'user'")->fetchColumn();
            
            // Hitung User Pending
            try {
                $stats['pending_users'] = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE account_status = 'pending'")->fetchColumn();
            } catch (\Exception $e) {}

            // Cek tabel swimmers
            try {
                $stats['athletes'] = $pdo->query("SELECT COUNT(*) FROM swim_swimmers")->fetchColumn();
                $stats['pending_uids'] = $pdo->query("SELECT COUNT(*) FROM swim_swimmers WHERE uid IS NULL OR trim(uid) = '' OR uid = '-' OR uid LIKE 'SW%' OR uid = '0'")->fetchColumn();
            } catch (\Exception $e) {}
            
            // B. Hitung Entries
            $countActive = 0;
            try {
                $countActive = $pdo->query("SELECT COUNT(*) FROM swim_event_entries")->fetchColumn();
            } catch (\Exception $e) {}

            $countArchive = 0;
            try {
                $countArchive = $pdo->query("SELECT COUNT(*) FROM event_entries_archive")->fetchColumn();
            } catch (\Exception $e) {}
            
            $stats['entries'] = $countActive + $countArchive;

            // C. Statistik Keuangan
            try {
                $stats['revenue'] = $pdo->query("SELECT SUM(amount) FROM swim_payments WHERE status = 'Paid'")->fetchColumn() ?: 0;
            } catch (\Exception $e) {}

            // D. Cek Status Maintenance & Web Settings
            try {
                $settings = $pdo->query("SELECT * FROM swim_site_settings WHERE id=1")->fetch();
                if ($settings) {
                    $systemStatus = $settings['maintenance_mode'] ?? 0;
                    $heroTitle    = $settings['app_name'] ?? 'SwimMeet App';
                }
            } catch (\Exception $e) {}

                                    // Hitung Pengunjung (Chart Data)
            $visitorStats = [];
            try {
                $stmtChart = $pdo->query("SELECT DATE_FORMAT(visit_date, '%d %b') as visit_date, SUM(views_count) as total_views FROM site_visitors WHERE module IN ('swim', 'core') AND visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY visit_date ORDER BY visit_date ASC");
                $visitorStats = $stmtChart->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}

            // E. Event Live / Mendatang
            $sqlLive = "
                SELECT e.*, u.nama_lengkap as eo_name 
                FROM swim_events e 
                LEFT JOIN swim_users u ON e.user_id = u.id 
                WHERE e.event_status != 'Done' 
                AND e.event_date_start >= CURDATE()
                ORDER BY e.event_date_start ASC 
                LIMIT 5
            ";
            $liveEvents = $pdo->query($sqlLive)->fetchAll(\PDO::FETCH_ASSOC);

            // F. User Terbaru
            $sqlRecent = "
                SELECT id, username, role, created_at, nama_lengkap, email, account_status
                FROM swim_users 
                ORDER BY created_at DESC 
                LIMIT 5
            ";
            $recentUsers = $pdo->query($sqlRecent)->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            die("Database Error: " . $e->getMessage());
        }

        $visitorStats = [];
        try {
            $sqlVisitors = "SELECT visit_date, SUM(views_count) as total_views 
                            FROM site_visitors 
                            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                            GROUP BY visit_date ORDER BY visit_date ASC";
            $visitorStats = $pdo->query($sqlVisitors)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('swim/master/dashboard', [
            'stats' => $stats,
            'liveEvents' => $liveEvents,
            'recentUsers' => $recentUsers,
            'visitorStats' => $visitorStats,
            'visitorStats' => $visitorStats,
            'systemStatus' => $systemStatus,
            'heroTitle' => $heroTitle
        ]);
    }

    public function admin() {
        // Cek Role Admin
        if ($_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'] ?? $_SESSION['user_id'] ?? 0;

        // 2. AMBIL EVENT AKTIF 
        $stmtEvent = $pdo->prepare("SELECT * FROM swim_events WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtEvent->execute([$uid]);
        $event = $stmtEvent->fetch(\PDO::FETCH_ASSOC);

        // Variable Default
        $eventId   = $event['id'] ?? 0;
        $eventName = $event['event_name'] ?? 'Belum Ada Event Aktif';
        $eventDate = $event['event_date_start'] ?? date('Y-m-d'); 
        $eventLoc  = $event['event_location'] ?? '-';
        $eventStatus = $event['event_status'] ?? 'Draft';

        // 3. HITUNG STATISTIK 
        $stats = ['atlet' => 0, 'entries' => 0, 'clubs' => 0, 'revenue' => 0, 'pending_payments' => 0];
        $isSchool = false;

        if ($eventId > 0) {
            try {
                // A. Total Entries (Nomor Lomba yang diikuti)
                $stmtEntry = $pdo->prepare("SELECT COUNT(*) FROM swim_event_entries WHERE event_id = ?");
                $stmtEntry->execute([$eventId]);
                $stats['entries'] = $stmtEntry->fetchColumn();

                // B. Total Atlet (Unik)
                $stmtAtlet = $pdo->prepare("SELECT COUNT(DISTINCT swimmer_id) FROM swim_event_entries WHERE event_id = ?");
                $stmtAtlet->execute([$eventId]);
                $stats['atlet'] = $stmtAtlet->fetchColumn();

                // C. Total Klub/Sekolah (Unik)
                $partType = strtolower($event['participation_type'] ?? 'club');
                $isSchool = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
                
                if ($isSchool) {
                    $stmtClub = $pdo->prepare("SELECT COUNT(DISTINCT s.asal_sekolah) FROM swim_event_entries ee JOIN swim_swimmers s ON ee.swimmer_id = s.id WHERE ee.event_id = ? AND s.asal_sekolah != ''");
                } else {
                    $stmtClub = $pdo->prepare("SELECT COUNT(DISTINCT club_id) FROM swim_event_entries WHERE event_id = ?");
                }
                $stmtClub->execute([$eventId]);
                $stats['clubs'] = $stmtClub->fetchColumn();

                // D. PERBAIKAN: Total Pemasukan (Revenue) dari tabel payments yang sudah Lunas
                $stmtRev = $pdo->prepare("SELECT SUM(amount) FROM swim_payments WHERE event_id = ? AND status IN ('Paid', 'completed')");
                $stmtRev->execute([$eventId]);
                $stats['revenue'] = $stmtRev->fetchColumn() ?: 0;

                // E. Total Pembayaran/Pendaftaran yang masih Pending
                $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM swim_payments WHERE event_id = ? AND status IN ('Pending', 'Unpaid', 'pending')");
                $stmtPending->execute([$eventId]);
                $stats['pending_payments'] = $stmtPending->fetchColumn() ?: 0;

            } catch (\Exception $e) { /* Silent Error */ }
        }

        // 4. DATA CHART (Top 5 Klub / Sekolah)
        $chartLabels = [];
        $chartValues = [];

        if ($eventId > 0) {
            if ($isSchool) {
                $sqlChart = "
                    SELECT s.asal_sekolah as nama_klub, COUNT(DISTINCT ee.swimmer_id) as jumlah_atlet
                    FROM swim_event_entries ee
                    JOIN swim_swimmers s ON ee.swimmer_id = s.id
                    WHERE ee.event_id = ? AND s.asal_sekolah != ''
                    GROUP BY s.asal_sekolah
                    ORDER BY jumlah_atlet DESC
                    LIMIT 5
                ";
            } else {
                $sqlChart = "
                    SELECT c.nama_klub as nama_klub, COUNT(DISTINCT ee.swimmer_id) as jumlah_atlet
                    FROM swim_event_entries ee
                    JOIN swim_clubs c ON ee.club_id = c.id
                    WHERE ee.event_id = ?
                    GROUP BY c.id
                    ORDER BY jumlah_atlet DESC
                    LIMIT 5
                ";
            }
            
            try {
                $stmtChart = $pdo->prepare($sqlChart);
                $stmtChart->execute([$eventId]);
                $dataChart = $stmtChart->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($dataChart as $d) {
                    $chartLabels[] = $d['nama_klub'];
                    $chartValues[] = $d['jumlah_atlet'];
                }
            } catch(\Exception $e) {}
        }

        $jsLabels = json_encode($chartLabels);
        $jsValues = json_encode($chartValues);

        return $this->view('swim/admin/dashboard', [
            'eventId' => $eventId,
            'eventName' => $eventName,
            'eventDate' => $eventDate,
            'eventLoc' => $eventLoc,
            'eventStatus' => $eventStatus,
            'stats' => $stats,
            'jsLabels' => $jsLabels,
            'jsValues' => $jsValues,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'isSchool' => $isSchool
        ]);
    }

    public function user() {
        // Cek Role User/Club
        if ($_SESSION['swim_role'] !== 'user' && $_SESSION['swim_role'] !== 'club') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $uid = $_SESSION['swim_user_id'] ?? $_SESSION['user_id'] ?? 0;
        
        // 1. STATISTIK: TOTAL ATLET
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ?");
        $stmt->execute([$uid]);
        $totalSwimmers = $stmt->fetchColumn();

        // 2. STATISTIK: TOTAL EVENT YANG DIIKUTI
        $stmtEntries = $pdo->prepare("SELECT COUNT(*) FROM swim_event_entries WHERE club_id = ?");
        $stmtEntries->execute([$uid]);
        $totalEntries = $stmtEntries->fetchColumn();

        // 3. STATISTIK: STATUS PEMBAYARAN TERAKHIR
        $stmtPay = $pdo->prepare("SELECT status FROM swim_payments WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$uid]);
        $lastPaymentStatus = $stmtPay->fetchColumn(); 
        if(!$lastPaymentStatus) $lastPaymentStatus = 'Belum Ada';

        // 4. ACTION REQUIRED: UNPAID INVOICES
        $stmtUnpaid = $pdo->prepare("SELECT COUNT(*) FROM swim_payments WHERE user_id = ? AND status IN ('Pending', 'Unpaid', 'pending')");
        $stmtUnpaid->execute([$uid]);
        $unpaidInvoices = $stmtUnpaid->fetchColumn();

        // 5. ACTION REQUIRED: MISSING UID ATHLETES
        $stmtNoUid = $pdo->prepare("SELECT COUNT(*) FROM swim_swimmers WHERE user_id = ? AND (uid IS NULL OR trim(uid) = '' OR uid = '-' OR uid LIKE 'SW%' OR uid = '0')");
        $stmtNoUid->execute([$uid]);
        $missingUid = $stmtNoUid->fetchColumn();

        // 6. DETEKSI EVENT ESTAFET AKTIF
        $stmtRelayEvent = $pdo->query("SELECT e.id FROM swim_events e JOIN swim_event_numbers en ON e.id = en.event_id WHERE en.is_relay = 1 AND e.event_status IN ('Active', 'Open', 'Upcoming', 'Registration') ORDER BY e.event_date_start ASC LIMIT 1");
        $activeRelayEventId = $stmtRelayEvent->fetchColumn();

        return $this->view('swim/user/dashboard', [
            'totalSwimmers' => $totalSwimmers,
            'totalEntries' => $totalEntries,
            'lastPaymentStatus' => $lastPaymentStatus,
            'unpaidInvoices' => $unpaidInvoices,
            'missingUid' => $missingUid,
            'activeRelayEventId' => $activeRelayEventId
        ]);
    }

    // Menambahkan method index() sebagai fallback jika URL hanya /swim/dashboard
    public function index() {
        $role = strtolower($_SESSION['swim_role'] ?? '');
        switch ($role) {
            case 'master':
                header('Location: ' . getenv('APP_URL') . '/swim/dashboard/master');
                break;
            case 'admin':
                header('Location: ' . getenv('APP_URL') . '/swim/dashboard/admin');
                break;
            case 'user':
            case 'club':
                header('Location: ' . getenv('APP_URL') . '/swim/dashboard/user');
                break;
            default:
                header('Location: ' . getenv('APP_URL') . '/swim/login');
                break;
        }
        exit;
    }
}
