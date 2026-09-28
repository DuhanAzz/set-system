<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock controller environment
require_once __DIR__ . '/.env'; // wait, no .env.php
function getenv_fallback($key) {
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) === $key) {
                return trim(trim($parts[1]), "\"'");
            }
        }
    }
    return '';
}
$db = new PDO("mysql:host=".getenv_fallback('DB_HOST').";dbname=".getenv_fallback('DB_NAME'), getenv_fallback('DB_USER'), getenv_fallback('DB_PASS'));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmtClasses = $db->prepare("SELECT ed.id, ed.race_number, d.distance_name, a.group_name, ed.custom_name, sc.class_name as skate_class_name, ed.gender, ed.advancement_count, ed.next_round, ed.auto_qualify_per_heat, ed.fastest_loser_count, ed.advancement_rule, ed.category_name
                                    FROM roll_event_details ed 
                                    LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id 
                                    LEFT JOIN roll_ref_age_groups a ON ed.age_group_id = a.id 
                                    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
                                    WHERE ed.event_id = 4 LIMIT 5");
    $stmtClasses->execute();
    echo "Query Classes OK.\n";
} catch (\Exception $e) {
    echo "Query Classes Error: " . $e->getMessage() . "\n";
}
