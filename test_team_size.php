<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, team_size FROM roll_event_details WHERE id IN (SELECT ed.id FROM roll_event_details ed JOIN roll_ref_distances d ON ed.distance_id = d.id WHERE d.distance_name LIKE '%relay%') LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
