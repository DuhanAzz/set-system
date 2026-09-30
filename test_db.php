<?php
try {
    $db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=setsystem", "root", "");
    $stmt = $db->query("
        SELECT e.skater_id, s.skater_name, sc.class_name, e.race_class_id, ed.id as ed_id
        FROM roll_entries e
        LEFT JOIN roll_skaters s ON e.skater_id = s.id
        LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
        LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
        WHERE e.event_id = 1 AND (sc.class_name IS NULL OR (LOWER(sc.class_name) NOT LIKE '%speed%' AND LOWER(sc.class_name) NOT LIKE '%standar%' AND LOWER(sc.class_name) NOT LIKE '%pemula%'))
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
