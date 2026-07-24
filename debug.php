<?php
require 'vendor/autoload.php';
$db = App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE roll_payments");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
