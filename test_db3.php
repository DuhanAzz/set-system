<?php
try {
    $db = new PDO("mysql:host=localhost;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=set_system_db", "root", "");
    $stmt = $db->query("SHOW COLUMNS FROM roll_entries");
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) echo $row['Field'] . "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
