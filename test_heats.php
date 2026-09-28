<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=set_system_db", "root", "");
// Check what heats exist for race 310, 311, 312, 313
$stmt = $db->query("
    SELECT c.race_number, p.heat_name, COUNT(p.skater_id) as count 
    FROM roll_pelotons p 
    JOIN roll_event_details c ON p.race_class_id = c.id 
    WHERE c.race_number IN ('310', '311', '312', '313') 
    GROUP BY c.race_number, p.heat_name
");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
