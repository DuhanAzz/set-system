<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SHOW CREATE TABLE roll_entries");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $db->query("SHOW CREATE TABLE roll_event_details");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
