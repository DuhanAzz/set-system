<?php
require_once 'app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    $stmt = $db->query("SELECT * FROM site_visitors");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
