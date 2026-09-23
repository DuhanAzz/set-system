<?php
require_once __DIR__ . '/app/Core/Database.php';
use App\Core\Database;
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE roll_clubs");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
