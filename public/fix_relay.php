<?php
// Load dotenv
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

$eventId = 1;

// 1. Cari race_class_id untuk SPEED - RELAY 3000M - JUNIOR (putra)
$sql = "
    SELECT ed.id as race_class_id, sc.class_name, a.group_name 
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

// Now find the skaters
$skaterNames = [
    "Calvine Maynanda Dwi I'zaz",
    "Ibnu Syahri Romadhon",
    "Sebastian Fajar Fahrurrozi",
    "Muhammad Abyan Mawlana Ghaisani",
    "Rendhyata Arkha dena Atmadja",
    "Yudhistira putra hutama"
];

$skatersData = [];
foreach($skaterNames as $name) {
    $st = $db->prepare("SELECT e.id as entry_id, s.id as skater_id, s.skater_name FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id WHERE e.event_id = ? AND s.skater_name LIKE ?");
    $st->execute([$eventId, "%$name%"]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if($rows) {
        $skatersData[] = $rows[0];
    }
}
echo "\nFound Skaters:\n";
print_r($skatersData);

// Set them to the first found class (assuming it's SPEED - RELAY 3000M - JUNIOR)
if (!empty($classes) && !empty($skatersData)) {
    $targetRaceClassId = $classes[0]['race_class_id'];
    foreach($skatersData as $sd) {
        $up = $db->prepare("UPDATE roll_entries SET race_class_id = ? WHERE id = ?");
        $up->execute([$targetRaceClassId, $sd['entry_id']]);
        echo "Updated entry ID {$sd['entry_id']} for {$sd['skater_name']} to race_class_id {$targetRaceClassId}\n";
    }
} else {
    echo "Could not find target class or skaters.\n";
}
