<?php
// FILE: public/logout.php
require_once __DIR__ . '/../src/config/database.php';

// Isolasi Session Khusus Roll System
session_name('SET_ROLL_SESS');
session_start();

// Kosongkan semua variabel sesi dalam namespace ini
$_SESSION = [];

// Hapus cookie sesi spesifik 'SET_ROLL_SESS'
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi spesifik ini saja (tidak menyentuh sesi default PHPSESSID milik sistem renang)
session_destroy();

// Redirect ke Landing Page
header("Location: index.php");
exit;
