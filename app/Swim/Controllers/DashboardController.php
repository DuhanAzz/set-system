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
        return $this->view('swim/master/dashboard');
    }

    public function admin() {
        // Cek Role Admin
        if ($_SESSION['swim_role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        return $this->view('swim/admin/dashboard');
    }

    public function user() {
        // Cek Role User/Club
        if ($_SESSION['swim_role'] !== 'user' && $_SESSION['swim_role'] !== 'club') {
            header("Location: " . getenv('APP_URL') . "/swim/login");
            exit;
        }
        return $this->view('swim/user/dashboard');
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
