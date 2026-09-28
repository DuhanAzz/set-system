<?php
// PHP Script to test connection
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
try {
    $db = new PDO("mysql:host=127.0.0.1;dbname=".getenv_fallback('DB_NAME'), getenv_fallback('DB_USER'), getenv_fallback('DB_PASS'));
    $stmt = $db->query("SELECT e.skater_id, e.is_manual, e.manual_invoice_code, pay.status as pay_status, mpay.status as mpay_status FROM roll_entries e LEFT JOIN roll_skaters s ON e.skater_id = s.id LEFT JOIN roll_payments pay ON pay.club_id = s.club_id AND pay.event_id = e.event_id AND (e.is_manual = 0 OR e.is_manual IS NULL) LEFT JOIN roll_manual_payments mpay ON mpay.invoice_code = e.manual_invoice_code AND e.is_manual = 1 WHERE e.race_class_id = 265");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} catch (\Exception $e) {
    echo $e->getMessage();
}
