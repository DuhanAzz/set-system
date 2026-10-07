<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("
    SELECT r.skater_id, s.skater_name, c.club_name, ed.category_name, ag.age_group, r.rank, r.event_id 
    FROM roll_event_results r
    JOIN roll_skaters s ON r.skater_id = s.id
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    JOIN roll_event_details ed ON r.race_class_id = ed.id
    JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
    WHERE s.skater_name LIKE '%Rafael Armadi%'
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
