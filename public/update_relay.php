<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$eventId = 1;

// 1. Cari race_class_id untuk SPEED - RELAY 3000M - JUNIOR (mungkin khusus putra atau digabung)
$sql = "
    SELECT ed.id, sc.class_name, a.group_name 
    FROM roll_event_details ed 
    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id 
    JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
    WHERE ed.event_id = ? 
    AND sc.class_name LIKE '%Relay%' 
    AND a.group_name LIKE '%Junior%'
";
$stmt = $db->prepare($sql);
$stmt->execute([$eventId]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Available Relay Junior Classes:\n";
print_r($classes);

// 2. Jika ketemu, update
// Nanti akan kita update e.race_class_id
