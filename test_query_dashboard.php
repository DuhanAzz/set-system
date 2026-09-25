<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $stmtClubs = $db->prepare("
        SELECT 
            c.id, 
            c.club_name, 
            MAX(u.phone) as phone,
            MAX(u.fullname) as pic_name,
            COUNT(DISTINCT s.id) as total_athletes,
            COUNT(e.id) as total_entries,
            SUM(CASE WHEN pay_club.status = 'Paid' OR pay_man.status = 'Paid' THEN 1 ELSE 0 END) as verified_entries
        FROM roll_entries e
        JOIN roll_clubs c ON e.club_id = c.id
        JOIN roll_skaters s ON e.skater_id = s.id
        LEFT JOIN roll_users u ON u.club_id = c.id
        LEFT JOIN roll_payments pay_club ON pay_club.club_id = e.club_id AND pay_club.event_id = e.event_id
        LEFT JOIN roll_manual_payments pay_man ON pay_man.invoice_code = e.manual_invoice_code
        WHERE e.event_id = 1
        GROUP BY c.id, c.club_name
        ORDER BY c.club_name ASC
    ");
    $stmtClubs->execute();
    echo "SQL SUCCESS\n";
} catch (\Exception $e) {
    echo "SQL ERROR: " . $e->getMessage() . "\n";
}
