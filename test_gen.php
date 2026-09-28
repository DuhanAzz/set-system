<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['roll_admin_active_event_id'] = 1; // Assuming event 1
$_POST['class_id'] = 265;
$_GET['round'] = 'Kualifikasi';
$_GET['algorithm'] = 'distributed';
$_GET['max_lanes'] = 6;
$_GET['override_mechanism'] = '';

require 'vendor/autoload.php'; // If exists
require 'app/Core/Database.php';
require 'app/Roll/Controllers/Admin/RollPelotonController.php';

$controller = new \App\Roll\Controllers\Admin\RollPelotonController();
$controller->generateAll();
