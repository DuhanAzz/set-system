<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name)."=".trim($value, '"\''));
    }
}
require 'app/Core/Database.php';
try {
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmtTokens = $db->prepare("
        SELECT t.*, c.club_name, 
               (SELECT u.phone FROM roll_users u WHERE u.club_id = c.id LIMIT 1) as club_phone,
               (SELECT COUNT(*) FROM roll_entries e WHERE e.manual_invoice_code = t.manual_invoice_code) as entry_count
        FROM roll_event_tokens t
        JOIN roll_clubs c ON t.club_id = c.id
        WHERE t.event_id = 1
        ORDER BY t.created_at DESC
    ");
    $stmtTokens->execute();
    print_r($stmtTokens->fetchAll(PDO::FETCH_ASSOC));
} catch (\PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
