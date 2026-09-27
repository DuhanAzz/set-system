<?php
$lines = file(__DIR__ . '/.env');
foreach ($lines as $line) {
    if (trim($line) === '' || strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $_ENV[trim($name)] = trim($value, "\"' \r\n");
}
$_ENV['DB_HOST'] = 'localhost;unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
require 'app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE roll_skaters");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
$found = false;
foreach($cols as $c) {
    if ($c['Field'] == 'athlete_level') { $found = true; }
}
if ($found) {
    echo "Found athlete_level!\n";
} else {
    $db->exec("ALTER TABLE roll_skaters ADD COLUMN athlete_level ENUM('pemula', 'standar', 'speed') DEFAULT 'pemula'");
    echo "Added athlete_level\n";
}
