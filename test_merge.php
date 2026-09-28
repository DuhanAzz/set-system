<?php
// Just mimic the DB connection
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(trim($parts[0]) . '=' . trim(trim($parts[1]), "\"'"));
        }
    }
}
$db = new PDO("mysql:host=".getenv('DB_HOST').";dbname=".getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->beginTransaction();
try { $db->exec("ALTER TABLE roll_event_details ADD COLUMN custom_name VARCHAR(255) NULL"); } catch (\Exception $e) { echo "Caught ALTER error\n"; }
try {
    $stmt = $db->prepare("SELECT 1");
    $stmt->execute();
    $db->commit();
    echo "Success";
} catch (\Exception $e) {
    echo "Fatal: " . $e->getMessage();
}
