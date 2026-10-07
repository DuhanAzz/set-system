<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("SELECT published_ku_standings FROM roll_series WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "DB published_ku_standings: " . $row['published_ku_standings'] . "\n";
$pubKu = json_decode($row['published_ku_standings'], true);
echo "Decoded is array: " . (is_array($pubKu) ? 'Yes' : 'No') . "\n";
print_r($pubKu);
