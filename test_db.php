<?php
try {
    $db = new PDO("mysql:host=localhost;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=set_system_db", "root", "");
    $stmt = $db->query("SHOW COLUMNS FROM roll_skaters");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
