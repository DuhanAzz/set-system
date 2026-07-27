<?php
$host = '127.0.0.1'; // Use IP instead of localhost to force TCP instead of socket on Mac
$db_name = 'set_system_db';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$name = 'Athar Setiawan';

echo "=== CHECKING SKATERS ===\n";
$stmt = $db->prepare("SELECT * FROM roll_skaters WHERE skater_name LIKE ?");
$stmt->execute(["%$name%"]);
$skaters = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($skaters);

if (!empty($skaters)) {
    echo "\n=== CHECKING ENTRIES ===\n";
    $skaterIds = array_column($skaters, 'id');
    $inClause = implode(',', array_fill(0, count($skaterIds), '?'));
    $stmt2 = $db->prepare("SELECT e.*, c.category_name, d.distance_name, sc.class_name 
                           FROM roll_entries e 
                           LEFT JOIN roll_event_details c ON e.race_class_id = c.id
                           LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
                           LEFT JOIN roll_ref_skate_classes sc ON c.skate_class_id = sc.id
                           WHERE e.skater_id IN ($inClause)");
    $stmt2->execute($skaterIds);
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n=== CHECKING PELOTONS ===\n";
    $stmt3 = $db->prepare("SELECT * FROM roll_pelotons WHERE skater_id IN ($inClause)");
    $stmt3->execute($skaterIds);
    print_r($stmt3->fetchAll(PDO::FETCH_ASSOC));
}

echo "\n=== FINDING OTHER DUPLICATES IN SKATERS ===\n";
$stmt4 = $db->query("SELECT skater_name, gender, club_id, COUNT(*) as count 
                     FROM roll_skaters 
                     GROUP BY skater_name, gender, club_id 
                     HAVING count > 1");
print_r($stmt4->fetchAll(PDO::FETCH_ASSOC));
