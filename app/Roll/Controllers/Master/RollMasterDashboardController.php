<?php

namespace App\Roll\Controllers\Master;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class RollMasterDashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
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
        $pendingEntries = [];
        $systemStatus = 0; 
        $heroTitle = 'Roll Events App'; 

        try {
            $stats['eo']       = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE role = 'admin'")->fetchColumn();
            $stats['clubs']    = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE role = 'user'")->fetchColumn();
            
            try {
                $stats['pending_users'] = $pdo->query("SELECT COUNT(*) FROM roll_users WHERE account_status = 'pending'")->fetchColumn();
            } catch (\Exception $e) {}

            try {
                $stats['total_events'] = $pdo->query("SELECT COUNT(*) FROM roll_events")->fetchColumn();
            } catch (\Exception $e) {}

            try {
                $stats['athletes'] = $pdo->query("SELECT COUNT(*) FROM roll_skaters")->fetchColumn();
                $stats['pending_uids'] = $pdo->query("SELECT COUNT(*) FROM roll_skaters WHERE uid IS NULL OR trim(uid) = '' OR uid = '-' OR uid LIKE 'SW%' OR uid = '0'")->fetchColumn();
            } catch (\Exception $e) {}
            
            $countActive = 0;
            try {
                $countActive = $pdo->query("SELECT COUNT(*) FROM roll_entries")->fetchColumn();
            } catch (\Exception $e) {}

            $stats['entries'] = $countActive;

            try {
                $stats['revenue'] = $pdo->query("SELECT SUM(total_amount) FROM roll_payments WHERE status = 'Paid'")->fetchColumn() ?: 0;
            } catch (\Exception $e) {}

            try {
                $settings = $pdo->query("SELECT * FROM roll_site_settings WHERE id=1")->fetch();
                if ($settings) {
                    $systemStatus = $settings['maintenance_mode'] ?? 0;
                    $heroTitle    = $settings['app_name'] ?? 'Roll Events App';
                }
            } catch (\Exception $e) {}

            $sqlLive = "
                SELECT e.*, u.nama_lengkap as eo_name 
                FROM roll_events e 
                LEFT JOIN roll_users u ON e.user_id = u.id 
                WHERE e.event_status != 'Done' 
                AND e.event_date_start >= CURDATE()
                ORDER BY e.event_date_start ASC 
                LIMIT 5
            ";
            try {
                $liveEvents = $pdo->query($sqlLive)->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}

            $sqlRecent = "
                SELECT id, username, role, created_at, nama_lengkap, email, account_status
                FROM roll_users 
                ORDER BY created_at DESC 
                LIMIT 5
            ";
            try {
                $recentUsers = $pdo->query($sqlRecent)->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}

            $sqlPendingEntries = "
                SELECT re.id, re.created_at, rs.nama_lengkap as skater_name, re.status
                FROM roll_entries re
                LEFT JOIN roll_skaters rs ON re.skater_id = rs.id
                WHERE re.status = 'pending'
                ORDER BY re.created_at DESC
                LIMIT 5
            ";
            try {
                $pendingEntries = $pdo->query($sqlPendingEntries)->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {}

        } catch (\PDOException $e) {
            error_log("Master Dashboard DB Error: " . $e->getMessage());
            // Don't die, let it render with default zeros
        }

        $visitorStats = [];
        try {
            $sqlVisitors = "SELECT visit_date, SUM(views_count) as total_views 
                            FROM site_visitors 
                            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                            GROUP BY visit_date ORDER BY visit_date ASC";
            $visitorStats = $pdo->query($sqlVisitors)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        return $this->view('roll/master/dashboard/index', [
            'stats' => $stats,
            'liveEvents' => $liveEvents,
            'recentUsers' => $recentUsers,
            'visitorStats' => $visitorStats,
            'pendingEntries' => $pendingEntries,
            'visitorStats' => $visitorStats,
            'systemStatus' => $systemStatus,
            'heroTitle' => $heroTitle
        ]);
    }
}
