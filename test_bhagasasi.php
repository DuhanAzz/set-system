<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM roll_clubs WHERE club_name LIKE '%bhaga%'");
print_r($stmt->fetchAll());
