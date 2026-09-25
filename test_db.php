<?php
require_once 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, class_name FROM roll_ref_skate_classes");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
