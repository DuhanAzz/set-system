<?php

namespace App\Roll\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Proteksi Akses (Hanya yang sudah login via Roll)
        if (!isset($_SESSION['roll_user_id']) || !isset($_SESSION['roll_role'])) {
            $loginUrl = getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . '/roll/login' : '/roll/login';
            header("Location: " . $loginUrl);
            exit;
        }
    }

    public function master() {
        // Cek Role Master
        if ($_SESSION['roll_role'] !== 'master') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        return $this->view('roll/master/dashboard');
    }

    public function admin() {
        // Cek Role Admin
        if ($_SESSION['roll_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        return $this->view('roll/admin/dashboard');
    }

    public function user() {
        // Cek Role User/Club
        if ($_SESSION['roll_role'] !== 'user' && $_SESSION['roll_role'] !== 'club') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
        return $this->view('roll/user/dashboard');
    }

    // Menambahkan method index() sebagai fallback
    public function index() {
        $role = strtolower($_SESSION['roll_role'] ?? '');
        switch ($role) {
            case 'master':
                header('Location: ' . getenv('APP_URL') . '/roll/dashboard/master');
                break;
            case 'admin':
                header('Location: ' . getenv('APP_URL') . '/roll/dashboard/admin');
                break;
            case 'user':
            case 'club':
                header('Location: ' . getenv('APP_URL') . '/roll/dashboard/user');
                break;
            default:
                header('Location: ' . getenv('APP_URL') . '/roll/login');
                break;
        }
        exit;
    }
}
