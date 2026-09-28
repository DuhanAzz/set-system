<?php
function getenv_fallback($key) {
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2 && trim($parts[0]) === $key) return trim(trim($parts[1]), "\"'");
        }
    }
    return '';
}
$db = new PDO("mysql:host=localhost;dbname=set_system_db", "root", "");
$stmt = $db->query("SHOW COLUMNS FROM roll_entries");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
