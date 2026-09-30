<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$eventId = 1;
$sql = "
    SELECT ed.id as race_class_id, sc.class_name, a.group_name 
    FROM roll_event_details ed 
    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id 
    LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
    WHERE ed.event_id = ?
";
$stmt = $db->prepare($sql);
$stmt->execute([$eventId]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/../dump.json', json_encode($classes, JSON_PRETTY_PRINT));
echo "OK";
