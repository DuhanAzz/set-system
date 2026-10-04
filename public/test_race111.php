<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance();
$stmt = $db->query("
    SELECT r.id, s.skater_name, r.rank, r.time, r.status, p.heat_name 
    FROM roll_event_results r
    JOIN roll_skaters s ON r.skater_id = s.id
    JOIN roll_event_details ed ON r.race_class_id = ed.id
    JOIN roll_pelotons p ON r.event_id = p.event_id AND r.race_class_id = p.race_class_id AND r.skater_id = p.skater_id AND r.round = p.round
    WHERE ed.race_number = 111 AND r.round = 'Final'
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
