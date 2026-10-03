<?php
require_once '/Applications/XAMPP/xamppfiles/htdocs/set-system/app/Core/Database.php';

$envFile = '/Applications/XAMPP/xamppfiles/htdocs/set-system/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}
$_ENV['DB_HOST'] = '127.0.0.1'; // Use TCP to avoid socket issues
putenv('DB_HOST=127.0.0.1');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check relay distances
    $stmt = $db->query("SELECT * FROM roll_ref_distances WHERE distance_name LIKE '%Relay%' OR distance_name LIKE '%Team%' OR is_relay = 1");
    if($stmt) {
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "Query failed\n";
    }
    
    // Check if `is_relay` exists
    $stmt2 = $db->query("DESCRIBE roll_ref_distances");
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
