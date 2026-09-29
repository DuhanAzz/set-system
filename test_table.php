<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SHOW TABLES LIKE '%payment%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
