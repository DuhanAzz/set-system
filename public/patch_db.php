<?php
// Let's just use the index.php env parser
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

try {
    $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create site_visitors table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS `site_visitors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `module` varchar(50) DEFAULT NULL,
        `visit_date` date DEFAULT NULL,
        `views_count` int(11) DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_visit` (`module`,`visit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Add dummy data for chart test
    $db->exec("INSERT IGNORE INTO site_visitors (module, visit_date, views_count) VALUES 
        ('roll', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 12),
        ('roll', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 25),
        ('roll', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 18),
        ('roll', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 40),
        ('roll', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 31),
        ('roll', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 55),
        ('roll', CURDATE(), 20)
    ");
    
    echo "<h1>AMAN!</h1><p>Table site_visitors is ready.</p>";

} catch (PDOException $e) {
    echo "<h1>ERROR!</h1><p>" . $e->getMessage() . "</p>";
}
