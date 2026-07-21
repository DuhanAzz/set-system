<?php
require_once __DIR__ . '/app/Core/Database.php';
putenv("DB_HOST=localhost");
putenv("DB_NAME=set_system_db");
putenv("DB_USER=root");
putenv("DB_PASS=");
$db = \App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("SELECT * FROM roll_users WHERE id = 2");
print_r($stmt->fetch(PDO::FETCH_ASSOC));

try {
    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE roll_users SET username=?, role=?, nama_lengkap=?, email=?, phone=? WHERE id=?");
    $stmt->execute(['asepknalpot_test', 'admin', 'Asep Knalpot 2', 'asep_test@example.com', '123', 2]);
    echo "Rows affected: " . $stmt->rowCount() . "\n";
    $db->commit();
    echo "Committed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $db->rollBack();
}

$stmt = $db->query("SELECT * FROM roll_users WHERE id = 2");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
