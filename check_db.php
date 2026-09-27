<?php
$db = new PDO('mysql:host=localhost;dbname=setsystem', 'root', '');
$stmt = $db->query("DESCRIBE roll_skaters");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $columns);
