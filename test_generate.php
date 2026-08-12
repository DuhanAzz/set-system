<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['race_class_id'] = 95; // Wait, what is the class ID in the screenshot? 123!
$_POST['advancement_count'] = 8;
$_POST['next_round'] = 'Semi Final';
$_POST['original_round_name'] = 'Kualifikasi';

require 'app/Core/Database.php';
// Need to mock session
session_start();
$_SESSION['roll_admin_active_event_id'] = 2; // Assuming event id 2
// Let's find the correct event_id for class 123
$db = \App\Core\Database::getInstance()->getConnection();
$event_id = $db->query("SELECT event_id FROM roll_event_details WHERE id = 123")->fetchColumn();
$_SESSION['roll_admin_active_event_id'] = $event_id;
$_POST['race_class_id'] = 123;

require 'app/Roll/Controllers/Admin/RollResultController.php';
$c = new \App\Roll\Controllers\Admin\RollResultController();
try {
    $c->generate_next_round();
} catch (\Exception $e) {
    echo $e->getMessage();
}
