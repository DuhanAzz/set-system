<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT team_size FROM roll_event_details LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
