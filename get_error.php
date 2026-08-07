<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// bootstrap minimal
require_once __DIR__ . '/vendor/autoload.php';
$_ENV['DB_HOST'] = '127.0.0.1'; // Just mock enough for class loading
require_once __DIR__ . '/app/Core/Controller.php';
require_once __DIR__ . '/app/Roll/Controllers/Admin/RollEventController.php';

echo "Classes loaded.";
