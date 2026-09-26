<?php
require_once __DIR__ . '/app/Core/Database.php';
try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE roll_manual_payments ADD COLUMN payment_proof VARCHAR(255) NULL");
    echo "Added payment_proof.\n";
} catch (\Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE roll_manual_payments ADD COLUMN club_id INT NULL");
    echo "Added club_id.\n";
} catch (\Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $db->exec("ALTER TABLE roll_manual_payments ADD COLUMN created_at DATETIME NULL");
    echo "Added created_at.\n";
} catch (\Exception $e) { echo $e->getMessage() . "\n"; }
