<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query('SELECT DISTINCT gender FROM roll_skaters');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
