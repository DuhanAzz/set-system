<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT c.id, c.team_size, d.distance_name FROM roll_event_details c JOIN roll_ref_distances d ON c.distance_id = d.id WHERE d.distance_name LIKE '%relay%' LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
