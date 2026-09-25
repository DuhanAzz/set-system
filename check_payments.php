<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SHOW CREATE TABLE roll_payments");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
