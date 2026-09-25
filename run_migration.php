<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$db->exec("
CREATE TABLE IF NOT EXISTS `roll_event_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `club_id` INT NOT NULL,
    `token_code` VARCHAR(20) NOT NULL,
    `manual_invoice_code` VARCHAR(50) NOT NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `used_at` DATETIME NULL
);
");
echo "Table created successfully.";
