<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT r.*, d.distance_name, s.skater_name FROM roll_event_results r JOIN roll_skaters s ON r.skater_id = s.id JOIN roll_event_details ed ON r.race_class_id = ed.id LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id WHERE s.skater_name LIKE '%JAZZIE%'");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($results);
