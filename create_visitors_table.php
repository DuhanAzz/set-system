<?php
$host = '127.0.0.1';
$db_name = 'set_system_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create site_visitors table
    $sql = "CREATE TABLE IF NOT EXISTS `site_visitors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `module` varchar(50) NOT NULL,
        `visit_date` date NOT NULL,
        `views_count` int(11) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_module_date` (`module`, `visit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);
    
    echo "Table site_visitors created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
