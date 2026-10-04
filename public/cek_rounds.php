<?php
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance()->getConnection();

echo "Checking round distribution in roll_event_results...\n";
$stmt = $db->query("SELECT round, COUNT(*) as count FROM roll_event_results GROUP BY round");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['round'] . ": " . $row['count'] . " records\n";
}

echo "\nChecking if anyone not in Final has rank > 0...\n";
$stmt = $db->query("SELECT round, rank, COUNT(*) as count FROM roll_event_results WHERE round != 'Final' AND rank > 0 GROUP BY round, rank LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['round'] . " Rank " . $row['rank'] . ": " . $row['count'] . " records\n";
}

echo "\nDone.\n";
