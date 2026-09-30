<?php
$env = parse_ini_file('../.env');
$dsn = "mysql:host=" . $env['DB_HOST'] . ";dbname=" . $env['DB_NAME'];
$pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);

$stmt = $pdo->query("
    SELECT DISTINCT s.skater_name, cl.club_name, sc.class_name
    FROM roll_entries e
    JOIN roll_skaters s ON e.skater_id = s.id
    LEFT JOIN roll_clubs cl ON s.club_id = cl.id
    LEFT JOIN roll_event_details ed ON e.race_class_id = ed.id
    LEFT JOIN roll_ref_skate_classes sc ON ed.skate_class_id = sc.id
    WHERE e.event_id = 1 
      AND (sc.class_name IS NULL 
           OR (LOWER(sc.class_name) NOT LIKE '%speed%' 
               AND LOWER(sc.class_name) NOT LIKE '%standar%' 
               AND LOWER(sc.class_name) NOT LIKE '%pemula%'))
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    echo $r['skater_name'] . " - " . ($r['club_name'] ?: 'Tidak ada klub') . " (Kategori: " . ($r['class_name'] ?: 'KOSONG / DIHAPUS') . ")\n";
}
