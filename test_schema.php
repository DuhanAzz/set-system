<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/Database.php';

putenv('DB_HOST=127.0.0.1');
putenv('DB_USER=root');
putenv('DB_PASS=');
putenv('DB_NAME=set_system_db');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    $stmt = $db->query("DESCRIBE roll_event_details");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
