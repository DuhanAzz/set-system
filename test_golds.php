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
putenv('DB_HOST=127.0.0.1');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check global_rank distribution for Standart U7 Putri
    $stmt = $db->query("
        SELECT r.event_id, r.race_class_id, r.skater_id, s.skater_name, r.rank, r.time, r.point, r.heat_name, d.distance_name,
            (
                SELECT COUNT(*) 
                FROM roll_event_results r2 
                WHERE r2.event_id = r.event_id AND r2.race_class_id = r.race_class_id 
                  AND r2.round = 'Final' AND r2.status = 'OK'
                  AND (
                      (LOWER(d.distance_name) LIKE '%eliminasi%' AND r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank))
                      OR (LOWER(d.distance_name) LIKE '%dtt%' AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                      OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point > r.point)
                      OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point = r.point AND r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
                      OR (LOWER(d.distance_name) NOT LIKE '%eliminasi%' AND LOWER(d.distance_name) NOT LIKE '%dtt%' AND r2.point = r.point AND (r2.time = r.time OR ((r2.time IS NULL OR r2.time = '' OR r2.time = '00.00.000') AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000'))) AND r2.rank > 0 AND (r.rank IS NULL OR r.rank = 0 OR r2.rank < r.rank))
                  )
            ) + 1 as global_rank
        FROM roll_event_results r
        JOIN roll_skaters s ON r.skater_id = s.id
        JOIN roll_event_details ed ON r.race_class_id = ed.id
        JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
        JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
        LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
        WHERE r.round = 'Final' AND r.status = 'OK' 
          AND sc.class_name = 'Standart' AND ag.group_name = 'U7' AND s.gender IN ('P','F','Putri')
        ORDER BY r.race_class_id, global_rank ASC
    ");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($res as $r) {
        if ($r['global_rank'] == 1) {
            echo "Gold for {$r['skater_name']} (Class: {$r['race_class_id']} - {$r['distance_name']}, Heat: {$r['heat_name']}) - Rank: {$r['rank']}, Time: {$r['time']}, Point: {$r['point']}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
