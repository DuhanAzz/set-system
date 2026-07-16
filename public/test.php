<?php
require __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, username, nama_lengkap, photo FROM roll_users");
print_r($stmt->fetchAll(\PDO::FETCH_ASSOC));
