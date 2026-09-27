<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT s.id, s.skater_name, s.club_id as s_club_id, e.club_id as e_club_id, c.club_name FROM roll_entries e JOIN roll_skaters s ON e.skater_id = s.id LEFT JOIN roll_clubs c ON s.club_id = c.id WHERE e.manual_invoice_code = 'MAN-TOK-T-6FE551'");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('dump_db.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped to dump_db.json";
