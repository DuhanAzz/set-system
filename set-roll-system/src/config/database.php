<?php
// FILE: src/config/database.php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Asia/Jakarta');

$is_local = false;
if (isset($_SERVER['SERVER_NAME'])) {
    $sn = $_SERVER['SERVER_NAME'];
    $is_local = (
        $sn == 'localhost' ||
        $sn == '127.0.0.1' ||
        str_contains($sn, 'ngrok')
    );
}

// ==========================================
// KONEKSI DATABASE
// ==========================================
$host = '127.0.0.1';
$dbname = 'set_system_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if ($is_local) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    define('BASE_URL', '/set-system/set-roll-system');
} else {
    ini_set('display_errors', 1);          
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 
    define('BASE_URL', 'https://domainkamu.com'); // Sesuaikan dengan domain hosting
}
