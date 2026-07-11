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
            $stats['eo']       = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE role = 'admin'")->fetchColumn() ?: 0;
            $stats['clubs']    = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE role = 'user'")->fetchColumn() ?: 0;
            $stats['pending_users'] = $pdo->query("SELECT COUNT(*) FROM swim_users WHERE account_status = 'pending'")->fetchColumn() ?: 0;
            
            try {
                $stats['athletes'] = $pdo->query("SELECT COUNT(*) FROM swim_swimmers")->fetchColumn() ?: 0;
                $stats['pending_uids'] = $pdo->query("SELECT COUNT(*) FROM swim_swimmers WHERE uid IS NULL OR trim(uid) = '' OR uid = '-' OR uid LIKE 'SW%' OR uid = '0'")->fetchColumn() ?: 0;
            } catch (Exception $e) {}

            try { $countActive = $pdo->query("SELECT COUNT(*) FROM swim_event_entries")->fetchColumn() ?: 0; } catch (Exception $e) { $countActive = 0; }
            try { $countArchive = $pdo->query("SELECT COUNT(*) FROM event_entries_archive")->fetchColumn() ?: 0; } catch (Exception $e) { $countArchive = 0; }
            $stats['entries'] = $countActive + $countArchive;

            try { $stats['revenue'] = $pdo->query("SELECT SUM(amount) FROM swim_payments WHERE status = 'Paid'")->fetchColumn() ?: 0; } catch (Exception $e) { $stats['revenue'] = 0; }

            try {
                $settings = $pdo->query("SELECT * FROM universal_settings WHERE id=1")->fetch();
                if ($settings) {
                    $systemStatus = $settings['maintenance_mode'] ?? 0;
                    $heroTitle    = $settings['app_name'] ?? 'Universal SET System';
                }
            } catch (Exception $e) {}

            $sqlLive = "SELECT e.*, u.nama_lengkap as eo_name FROM swim_events e LEFT JOIN swim_users u ON e.user_id = u.id WHERE e.event_status != 'Done' AND e.event_date_start >= CURDATE() ORDER BY e.event_date_start ASC LIMIT 5";
            try { $liveEvents = $pdo->query($sqlLive)->fetchAll(); } catch (Exception $e) {}

            $sqlRecent = "SELECT id, username, role, created_at, nama_lengkap, email, account_status FROM swim_users ORDER BY created_at DESC LIMIT 5";
            try { $recentUsers = $pdo->query($sqlRecent)->fetchAll(); } catch (Exception $e) {}

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
