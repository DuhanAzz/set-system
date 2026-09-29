<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SHOW COLUMNS FROM roll_entries LIKE 'club_id'");
print_r($stmt->fetchAll());
