<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$event_id = 1;

$stmtEntries = $db->prepare("
    SELECT e.id as entry_id, e.race_class_id, e.team_name, e.skater_id,
           s.skater_name, s.gender,
           a.group_name, c.category_name, skc.class_name as skate_class,
           d.distance_name, c.race_number, c.gender as class_gender,
           COALESCE(p.status, 'Unpaid') as payment_status
    FROM roll_entries e
    JOIN roll_skaters s ON e.skater_id = s.id
    LEFT JOIN roll_event_details c ON e.race_class_id = c.id
    LEFT JOIN roll_ref_age_groups a ON c.age_group_id = a.id
    LEFT JOIN roll_ref_distances d ON c.distance_id = d.id
    LEFT JOIN roll_ref_skate_classes skc ON c.skate_class_id = skc.id
    LEFT JOIN roll_payments p ON p.club_id = e.club_id AND p.event_id = e.event_id
    WHERE e.event_id = ? AND e.is_manual = 1
    ORDER BY e.id DESC LIMIT 5
");
if (!$stmtEntries->execute([$event_id])) {
    echo "Error: ";
    print_r($stmtEntries->errorInfo());
} else {
    $rows = $stmtEntries->fetchAll(PDO::FETCH_ASSOC);
    echo "Success! Rows:\n";
    print_r($rows);
}
