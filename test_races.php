<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=set_system_db", "root", "");
$stmt = $db->query("SELECT id, race_number, custom_name, (SELECT distance_name FROM roll_ref_distances WHERE id = distance_id) as dn, (SELECT group_name FROM roll_ref_age_groups WHERE id = age_group_id) as ag FROM roll_event_details WHERE race_number IN ('310', '311', '312', '313')");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
