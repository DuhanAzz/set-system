<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database::getInstance()->getConnection();

$stmt = $db->query("SELECT id, slug, published_ku_standings FROM roll_series");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    echo "ID: {$row['id']} | Slug: {$row['slug']} | PubKU: {$row['published_ku_standings']}\n";
}
