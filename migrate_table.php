<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $sql = "CREATE TABLE IF NOT EXISTS `swimmer_transfers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `swimmer_id` int(11) NOT NULL,
      `old_club_id` int(11) DEFAULT NULL,
      `new_club_id` int(11) DEFAULT NULL,
      `processed_by` int(11) DEFAULT NULL,
      `transfer_date` date DEFAULT NULL,
      `notes` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "Table swimmer_transfers created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
