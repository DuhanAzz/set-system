<?php
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, series_name, published_ku_standings FROM roll_series WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
