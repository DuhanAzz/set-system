<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$sql = "
    SELECT 
        e.id as entry_id,
        s.skater_name,
        e.race_class_id,
        ed.id as ed_id,
        ed.skate_class_id,
        sc.class_name,
        ed.age_group_id,
        a.group_name
    FROM roll_entries e
    JOIN roll_skaters s ON e.skater_id = s.id
    LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
    LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
    WHERE e.event_id = 1 AND sc.class_name IS NULL
";
$stmt = $db->query($sql);
echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
