<?php

namespace App\Core\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use Exception;

class MasterController extends Controller {

    public function __construct() {
        // Proteksi Konstruktor (Middleware Darurat)
        // Memastikan sesi telah berjalan
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Pengecekan ketat: Jika bukan 'master' atau belum login, tendang ke halaman login!
        if (!isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'master')) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/core/login' : '/core/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    public function index() {
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
        $heroTitle = 'Universal SET System'; 

        try {
            // Revenue Aggregation
            $revenueSwim = 0;
            $revenueRoll = 0;
            try { $revenueSwim = (int)$pdo->query("SELECT SUM(amount) FROM swim_payments WHERE status = 'Paid'")->fetchColumn(); } catch(Exception $e) {}
            try { $revenueRoll = (int)$pdo->query("SELECT SUM(total_amount) FROM roll_payments WHERE status = 'Paid'")->fetchColumn(); } catch(Exception $e) {}
            $stats['revenue'] = $revenueSwim + $revenueRoll;
            $stats['revenue_swim'] = $revenueSwim;
            $stats['revenue_roll'] = $revenueRoll;

            // Events Count
            $eventsSwim = 0;
            $eventsRoll = 0;
            try { $eventsSwim = (int)$pdo->query("SELECT COUNT(*) FROM swim_events")->fetchColumn(); } catch(Exception $e) {}
            try { $eventsRoll = (int)$pdo->query("SELECT COUNT(*) FROM roll_events")->fetchColumn(); } catch(Exception $e) {}
            $stats['events'] = $eventsSwim + $eventsRoll;
            $stats['events_swim'] = $eventsSwim;
            $stats['events_roll'] = $eventsRoll;

            // Users Count (EO + Clubs)
            $usersSwim = 0;
            $usersRoll = 0;
            try { $usersSwim = (int)$pdo->query("SELECT COUNT(*) FROM swim_users")->fetchColumn(); } catch(Exception $e) {}
            try { $usersRoll = (int)$pdo->query("SELECT COUNT(*) FROM roll_users")->fetchColumn(); } catch(Exception $e) {}
            $stats['users'] = $usersSwim + $usersRoll;
            $stats['users_swim'] = $usersSwim;
            $stats['users_roll'] = $usersRoll;

            // Pending action placeholders (optional)
            $stats['pending_users'] = 0;
            try { $stats['pending_users'] = (int)$pdo->query("SELECT COUNT(*) FROM swim_users WHERE account_status = 'pending'")->fetchColumn(); } catch(Exception $e) {}
            $stats['pending_uids'] = 0;

            // Live Events (Combined)
            $eventsList = [];
            try {
                $sqlSwim = "SELECT e.id, e.event_name, e.event_date_start, e.event_location, e.event_status, u.nama_lengkap as eo_name, 'swim' as source 
                            FROM swim_events e LEFT JOIN swim_users u ON e.user_id = u.id 
                            WHERE e.event_status != 'Done' AND e.event_date_start >= CURDATE()";
                $stmtSwim = $pdo->query($sqlSwim);
                if ($stmtSwim) $eventsList = array_merge($eventsList, $stmtSwim->fetchAll(PDO::FETCH_ASSOC));
            } catch(Exception $e) {}

            try {
                $sqlRoll = "SELECT e.id, e.event_name, e.event_date_start, e.event_location, e.event_status, u.nama_lengkap as eo_name, 'roll' as source 
                            FROM roll_events e LEFT JOIN roll_users u ON e.user_id = u.id 
                            WHERE e.event_status != 'Done' AND e.event_date_start >= CURDATE()";
                $stmtRoll = $pdo->query($sqlRoll);
                if ($stmtRoll) $eventsList = array_merge($eventsList, $stmtRoll->fetchAll(PDO::FETCH_ASSOC));
            } catch(Exception $e) {}

            // Sort combined events by date ascending
            usort($eventsList, function($a, $b) {
                return strtotime($a['event_date_start']) - strtotime($b['event_date_start']);
            });
            $liveEvents = array_slice($eventsList, 0, 5);

            // Settings
            try {
                $settings = $pdo->query("SELECT * FROM universal_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
                if ($settings) {
                    $systemStatus = $settings['maintenance_mode'] ?? 0;
                    $heroTitle    = $settings['app_name'] ?? 'Universal SET System';
                }
            } catch (Exception $e) {}

            $sqlRecent = "SELECT id, username, role, created_at, nama_lengkap, email, account_status FROM swim_users ORDER BY created_at DESC LIMIT 5";
            try { $recentUsers = $pdo->query($sqlRecent)->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}

        } catch (Exception $e) { }

        return $this->view('master/dashboard', [
            'stats' => $stats,
            'liveEvents' => $liveEvents,
            'recentUsers' => $recentUsers,
            'systemStatus' => $systemStatus,
            'heroTitle' => $heroTitle
        ]);
    }
}
