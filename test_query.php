<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
try {
    $stmtAthletes = $db->prepare("
            SELECT DISTINCT
                s.id as skater_id,
                s.skater_name,
                s.gender,
                s.birth_date,
                e.bib_number,
                c.club_name,
                c.id as club_id,
                a.group_name as ku
            FROM roll_entries e
            JOIN roll_skaters s ON e.skater_id = s.id
            JOIN roll_clubs c ON e.club_id = c.id
            JOIN roll_event_details ed ON e.race_class_id = ed.id
            JOIN roll_ref_distances d ON ed.distance_id = d.id
            JOIN roll_ref_age_groups a ON ed.age_group_id = a.id
            LEFT JOIN roll_payments pay_club ON pay_club.club_id = e.club_id AND pay_club.event_id = e.event_id
            LEFT JOIN roll_manual_payments pay_man ON pay_man.invoice_code = e.manual_invoice_code
            WHERE e.event_id = 1
              AND (pay_club.status = 'Paid' OR pay_man.status = 'Paid')
              AND LOWER(d.distance_name) NOT LIKE '%relay%'
              AND LOWER(d.distance_name) NOT LIKE '%team%'
              AND LOWER(d.distance_name) NOT LIKE '%pair%'
            ORDER BY c.club_name ASC, a.group_name ASC, s.skater_name ASC
        ");
    $stmtAthletes->execute();
    echo "OK";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
