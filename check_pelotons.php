<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

$db = App\Core\Database::getInstance()->getConnection(); 

$stmt = $db->query("SELECT p.skater_id, p.heat_name, p.start_grid, e.team_name, e.bib_number FROM roll_pelotons p JOIN roll_entries e ON p.skater_id = e.skater_id AND p.race_class_id = e.race_class_id WHERE p.race_class_id=197 ORDER BY p.start_grid");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
