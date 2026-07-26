<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT ed.id, ed.race_number, ed.gender, sc.class_name as roller_name
    FROM roll_event_details ed 
    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
    WHERE sc.class_name = 'Pemula'
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
