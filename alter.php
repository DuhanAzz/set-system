<?php
$lines = file(__DIR__ . '/.env');
foreach ($lines as $line) {
    if (trim($line) === '' || strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $_ENV[trim($name)] = trim($value, "\"' \r\n");
}
$_ENV['DB_HOST'] = '127.0.0.1';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $db->exec("ALTER TABLE roll_skaters ADD COLUMN athlete_level ENUM('pemula', 'standar', 'speed') DEFAULT 'pemula'");
    echo "Added athlete_level\n";
} catch(Exception $e) { echo $e->getMessage() . "\n"; }
