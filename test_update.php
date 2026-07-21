<?php
require_once __DIR__ . '/app/Core/Database.php';
putenv("DB_HOST=localhost");
putenv("DB_NAME=set_system_db");
putenv("DB_USER=root");
putenv("DB_PASS=");
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->prepare("UPDATE roll_users SET username=?, role=?, nama_lengkap=?, email=?, phone=? WHERE id=?");
$stmt->execute(['asepknalpot', 'admin', 'Asep Knalpot 2', 'asep@example.com', '', 2]);
echo "Rows affected: " . $stmt->rowCount() . "\n";
