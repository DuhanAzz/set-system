<?php
// Autoloader manual fallback
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

session_start();
$_SESSION['role'] = 'admin';
$_SESSION['roll_admin_active_event_id'] = 1;
$_GET['cat'] = 'Lainnya';
$_GET['status'] = 'Terverifikasi';
$_GET['ku'] = 'Tanpa KU';
$_GET['gender'] = 'Putra';

$controller = new \App\Roll\Controllers\Admin\RollAdminDashboardController();
$controller->api_breakdown_detail();
