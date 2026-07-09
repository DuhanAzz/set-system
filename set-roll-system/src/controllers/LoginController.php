<?php
// FILE: src/controllers/LoginController.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM roll_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                
                // Mencegah Session Hijacking
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['club_id'] = $user['club_id'];

                // Pengalihan berdasarkan role
                if ($user['role'] === 'master') {
                    header("Location: " . BASE_URL . "/src/admin/dashboard.php"); 
                    // Asumsi: admin dan master berbagi dashboard admin untuk tahap ini
                } elseif ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "/src/admin/dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "/src/user/dashboard.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Username atau Password salah!";
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
