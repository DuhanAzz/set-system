<?php
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, "\"'\t\n\r ");
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
require_once __DIR__ . '/../app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $stmtClubs = $db->prepare("
        SELECT 
            c.id, 
            c.club_name, 
            MAX(u.phone) as phone,
            MAX(u.nama_lengkap) as pic_name,
            COUNT(DISTINCT s.id) as total_athletes,
            COUNT(e.id) as total_entries,
            SUM(CASE WHEN pay_club.status = 'Paid' OR pay_man.status = 'Paid' THEN 1 ELSE 0 END) as verified_entries
        FROM roll_entries e
        JOIN roll_clubs c ON e.club_id = c.id
        JOIN roll_skaters s ON e.skater_id = s.id
        LEFT JOIN roll_users u ON u.club_id = c.id
        LEFT JOIN roll_payments pay_club ON pay_club.club_id = e.club_id AND pay_club.event_id = e.event_id
        LEFT JOIN roll_manual_payments pay_man ON pay_man.invoice_code COLLATE utf8mb4_unicode_ci = e.manual_invoice_code COLLATE utf8mb4_unicode_ci
        WHERE e.event_id = 1
        GROUP BY c.id, c.club_name
        ORDER BY c.club_name ASC
    ");
    $stmtClubs->execute();
    echo "SUCCESS: " . count($stmtClubs->fetchAll()) . " clubs found.";
} catch (\Exception $e) {
    echo "PDO EXCEPTION: " . $e->getMessage();
}
