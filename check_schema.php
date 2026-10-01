<?php
require_once __DIR__ . '/app/Core/Database.php';
try {
    $conn = \App\Core\Database::getInstance()->getConnection();
    $stmt = $conn->query("DESCRIBE roll_skaters");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
