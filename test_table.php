<?php
require_once __DIR__ . '/app/Core/Database.php';
try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'roll_event_tokens'");
    if ($stmt->rowCount() > 0) {
        echo "Table exists";
    } else {
        echo "Table does NOT exist";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
