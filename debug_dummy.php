<?php
$host = '127.0.0.1';
$dbname = 'set_system_db';
$user = 'root';
$pass = '';
$db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$eventId = 1;

// Ambil Skater
$skaters = $db->query("SELECT * FROM roll_skaters WHERE club_id = 1")->fetchAll(PDO::FETCH_ASSOC);

// Ambil Classes
$classes = $db->query("
    SELECT ed.*, d.distance_name, sc.class_name, ag.min_year, ag.max_year 
    FROM roll_event_details ed 
    JOIN roll_ref_distances d ON ed.distance_id = d.id 
    JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id 
    JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id 
    WHERE ed.event_id = $eventId AND ed.race_number IS NOT NULL AND ed.race_number != ''
")->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($skaters) . " skaters and " . count($classes) . " classes.\n";

$eligibility = [];
foreach ($skaters as $s) {
    // BUG MIGHT BE HERE:
    $age = 2026 - date('Y', strtotime($s['birth_date']));
    foreach ($classes as $c) {
        if ($s['gender'] == $c['gender'] && $age >= $c['min_year'] && $age <= $c['max_year']) {
            $eligibility[$c['id']][] = $s;
        }
    }
}
$countEligible = 0;
foreach($eligibility as $k => $v) $countEligible += count($v);
echo "Total eligible combinations: $countEligible\n";
