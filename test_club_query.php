<?php
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $stmt = $db->query("SELECT DISTINCT c.id, c.club_name FROM roll_clubs c JOIN roll_entries e ON c.id = e.club_id LIMIT 1");
    print_r($stmt->fetchAll());
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
