<?php
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['swim_role'] = 'admin';
$_SESSION['user_id'] = 1;
$_SESSION['swim_user_id'] = 1;
require_once __DIR__ . '/../vendor/autoload.php';
// We will just run the DashboardController manually
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Swim/Controllers/DashboardController.php';
$c = new \App\Swim\Controllers\DashboardController();
$c->admin();
