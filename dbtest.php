<?php
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$event_id = 1;

$stmtEntries = $db->prepare("
    SELECT e.id as entry_id, e.team_name, e.manual_invoice_code, e.club_id as e_club, s.club_id as s_club
    FROM roll_entries e
    JOIN roll_skaters s ON e.skater_id = s.id
    WHERE e.event_id = ? AND e.is_manual = 1
    ORDER BY e.id DESC LIMIT 5
");
if (!$stmtEntries->execute([$event_id])) {
    print_r($stmtEntries->errorInfo());
} else {
    $rows = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
}
