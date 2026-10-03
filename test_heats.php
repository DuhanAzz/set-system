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
putenv('DB_HOST=127.0.0.1');

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Check if there are multiple rank=1 for the same race_class_id in Final round
    $stmt = $db->query("
        SELECT race_class_id, COUNT(*) as gold_count 
        FROM roll_event_results 
        WHERE rank = 1 AND round = 'Final' 
        GROUP BY race_class_id 
        HAVING gold_count > 1
    ");
    $dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($dupes) {
        echo "Duplicate Golds found in these classes:\n";
        print_r($dupes);
    } else {
        echo "No duplicate golds found based on rank=1.\n";
    }

    // Also check if roll_entries has duplicates
    $stmt2 = $db->query("
        SELECT skater_id, race_class_id, COUNT(*) as c
        FROM roll_entries
        GROUP BY skater_id, race_class_id
        HAVING c > 1
    ");
    $dupesEntries = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    if($dupesEntries) {
        echo "Duplicate roll_entries found!\n";
        print_r($dupesEntries);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
