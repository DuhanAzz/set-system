<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=set_system_db", "root", "");
$stmt = $db->query("SELECT skater_name, gender, birth_date, athlete_level FROM roll_skaters WHERE skater_name LIKE '%test 3%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
