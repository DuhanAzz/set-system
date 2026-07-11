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
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && strtolower($_SESSION['role']) !== 'master')) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/login' : '/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance()->getConnection();
        
        $stats = [
            'total_admin' => 0,
            'total_user'  => 0,
            'total_lomba' => 0
        ];

        try {
            // Contoh penarikan data statistik (Sesuaikan nama tabel dengan DB Anda nanti)
            // Menghitung Total Admin
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            if ($stmt) $stats['total_admin'] = $stmt->fetchColumn();

            // Menghitung Total User
            $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
            if ($stmt) $stats['total_user'] = $stmt->fetchColumn();

            // Menghitung Total Lomba (contoh menggunakan tabel swim_events)
            $stmt = $db->query("SELECT COUNT(*) FROM swim_events");
            if ($stmt) $stats['total_lomba'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            // Tangkap error jika tabel 'users' belum ada atau namanya berbeda
            // agar halaman tidak crash saat baru diinisialisasi
        }

        // Render view dan kirimkan array $stats
        return $this->view('master/dashboard', ['stats' => $stats]);
    }
}
