<?php
try {
    $db = new PDO('mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=set_system_db;charset=utf8', 'root', '');
    
    // Check Event Details
    $stmt1 = $db->query("
        SELECT ed.id, ed.event_id, c.class_name, ag.group_name, d.distance_name, ed.gender 
        FROM roll_event_details ed
        LEFT JOIN roll_ref_skate_classes c ON ed.skate_class_id = c.id
        LEFT JOIN roll_ref_age_groups ag ON ed.age_group_id = ag.id
        LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
        WHERE ed.id = 198
    ");
    $eventDetails = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    echo "--- EVENT DETAILS (race_class_id=198) ---\n";
    print_r($eventDetails);
    
    // Check Results
    $stmt2 = $db->query("
        SELECT r.id, r.heat_name, r.round, s.skater_name, s.gender, cl.club_name, r.time, r.rank, r.status
        FROM roll_event_results r
        LEFT JOIN roll_skaters s ON r.skater_id = s.id
        LEFT JOIN roll_clubs cl ON s.club_id = cl.id
        WHERE r.race_class_id = 198
        ORDER BY r.round, r.heat_name, r.rank, r.time
    ");
    $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n--- EVENT RESULTS ---\n";
    foreach ($results as $res) {
        echo "Round: {$res['round']} | Heat: {$res['heat_name']} | Rank: {$res['rank']} | Time: {$res['time']} | Name: {$res['skater_name']} ({$res['gender']}) | Club: {$res['club_name']} | Status: {$res['status']}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
