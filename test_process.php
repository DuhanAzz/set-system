<?php
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['roll_admin_active_event_id'] = 1; // Assuming event_id is 1
$_GET['class_id'] = 106; // R101 DTT 200m
$_GET['round'] = 'Kualifikasi';
$_GET['algorithm'] = 'distributed';
$_GET['max_lanes'] = 0;
$_GET['override_mechanism'] = 'heat';

require 'index.php'; // Wait, index.php might boot the whole app. Better to just instantiate the controller.
