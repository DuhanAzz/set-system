<?php
$_ENV['DB_HOST'] = '127.0.0.1';
require_once __DIR__ . '/../app/Core/Database.php';
$db = App\Core\Database::getInstance()->getConnection();

echo "=== ANALISIS DUPLIKAT SKATER (NAMA SAMA, TANGGAL LAHIR SAMA) ===\n";
$stmt = $db->query("
    SELECT skater_name, birth_date, gender, COUNT(*) as total_entries, GROUP_CONCAT(c.club_name SEPARATOR ' | ') as clubs
    FROM roll_skaters s
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    GROUP BY skater_name, birth_date, gender
    HAVING total_entries > 1
    ORDER BY total_entries DESC
");
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "TIDAK ADA DUPLIKAT.\n";
} else {
    foreach ($duplicates as $d) {
        echo "- {$d['skater_name']} (Lahir: {$d['birth_date']}, JK: {$d['gender']}) -> Ada {$d['total_entries']} entri. Terdaftar di Klub: {$d['clubs']}\n";
    }
}

echo "\n=== ANALISIS NAMA MIRIP TAPI BEDA KLUB/TGL LAHIR (POTENSI DOUBLE TYPO) ===\n";
// This is harder in raw SQL, but we can look for identical names with slightly different DOBs.
$stmt = $db->query("
    SELECT skater_name, COUNT(*) as total_entries, GROUP_CONCAT(birth_date SEPARATOR ' | ') as dobs, GROUP_CONCAT(c.club_name SEPARATOR ' | ') as clubs
    FROM roll_skaters s
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    GROUP BY skater_name
    HAVING total_entries > 1
");
$nameDupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$found_typo = false;
foreach ($nameDupes as $n) {
    // If the dates of birth are different, it might be a typo or two different people with the exact same name.
    $dobs = explode(' | ', $n['dobs']);
    if (count(array_unique($dobs)) > 1) {
        echo "- {$n['skater_name']} terdaftar {$n['total_entries']} kali, TAPI DENGAN TGL LAHIR BERBEDA: {$n['dobs']} (Klub: {$n['clubs']})\n";
        $found_typo = true;
    }
}
if (!$found_typo) {
    echo "Tidak ditemukan nama identik dengan tanggal lahir berbeda.\n";
}

echo "\n=== ANALISIS ANOMALI TANGGAL LAHIR (KOSONG / TIDAK MASUK AKAL) ===\n";
$stmt = $db->query("
    SELECT id, skater_name, birth_date, gender, c.club_name
    FROM roll_skaters s
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    WHERE birth_date IS NULL OR birth_date = '0000-00-00' OR YEAR(birth_date) < 1950 OR YEAR(birth_date) > YEAR(CURDATE())
");
$anomalies = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($anomalies)) {
    echo "Tidak ada anomali tanggal lahir.\n";
} else {
    foreach ($anomalies as $a) {
        echo "- {$a['skater_name']} (Klub: {$a['club_name']}) -> Tgl Lahir Anomali: {$a['birth_date']}\n";
    }
}
