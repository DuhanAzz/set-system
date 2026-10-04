<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/Database.php';

$envFile = '/Applications/XAMPP/xamppfiles/htdocs/set-system/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}
putenv('DB_HOST=localhost');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check Standart U7 Putri Final results
    $stmt = $db->query("
        SELECT r.race_class_id, d.distance_name, r.heat_name, s.skater_name, r.rank, r.time
        FROM roll_event_results r
        JOIN roll_skaters s ON r.skater_id = s.id
        JOIN roll_event_details ed ON r.race_class_id = ed.id
        JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
        JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
        LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
        WHERE r.round = 'Final' AND sc.class_name = 'Standart' AND ag.group_name = 'U7' AND s.gender IN ('P','F','Putri')
        ORDER BY r.race_class_id, r.rank ASC
    ");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($res as $r) {
        echo "Class {$r['race_class_id']} ({$r['distance_name']}) | {$r['heat_name']} | Rank: {$r['rank']} | Time: {$r['time']} | Name: {$r['skater_name']}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
