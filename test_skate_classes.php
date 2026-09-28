<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=set_system_db", "root", "");
$stmt = $db->query("SELECT * FROM roll_ref_skate_classes");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
