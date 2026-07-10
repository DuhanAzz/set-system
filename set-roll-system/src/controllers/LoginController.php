<?php
// FILE: src/controllers/LoginController.php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM roll_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['account_status'] === 'pending') {
                    $waNumber = $pdo->query("SELECT contact_wa FROM roll_site_settings WHERE id=1")->fetchColumn() ?: '628123456789';
                    $_SESSION['error'] = "Akun Anda masih PENDING. Silakan Hubungi Admin via WA ($waNumber).";
                    header("Location: " . BASE_URL . "/public/login.php");
                    exit;
                } elseif ($user['account_status'] === 'suspended') {
                    $_SESSION['error'] = "Akun Anda telah ditangguhkan oleh Sistem Pusat.";
                    header("Location: " . BASE_URL . "/public/login.php");
                    exit;
                }
                
                // Mencegah Session Hijacking
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['club_id'] = $user['club_id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

                // Pengalihan berdasarkan role
                if ($user['role'] === 'master') {
                    header("Location: " . BASE_URL . "/src/master/dashboard.php"); 
                } elseif ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "/src/admin/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "/src/user/dashboard.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Username/Email atau Password salah!";
                header("Location: " . BASE_URL . "/public/login.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi Kesalahan Sistem!";
            header("Location: " . BASE_URL . "/public/login.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Harap isi semua kolom!";
        header("Location: " . BASE_URL . "/public/login.php");
        exit;
    }
}
