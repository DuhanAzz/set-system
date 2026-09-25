<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, category_name, skate_class_id FROM roll_event_details WHERE event_id = 1 AND category_name = 'EKSEBISI'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
