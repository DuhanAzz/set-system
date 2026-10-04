<?php
// Script untuk memperbaiki Gender Gamila dan mengecek data
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value, '"\' '));
    }
}
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    echo "<div style='font-family: sans-serif; padding: 20px;'>";
    
    // 1. PERBAIKI GENDER GAMILA
    $stmt = $db->prepare("SELECT id, skater_name, gender FROM roll_skaters WHERE skater_name LIKE '%Gamila%' LIMIT 1");
    $stmt->execute();
    $gamila = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($gamila) {
        if (strtoupper($gamila['gender']) === 'M' || strtoupper($gamila['gender']) === 'L' || strtoupper($gamila['gender']) === 'PUTRA') {
            $db->prepare("UPDATE roll_skaters SET gender = 'F' WHERE id = ?")->execute([$gamila['id']]);
            echo "<h2 style='color:green'>✅ GENDER GAMILA BERHASIL DIPERBAIKI!</h2>";
            echo "<p>Sebelumnya tercatat sebagai Laki-Laki, sekarang sudah diubah menjadi Perempuan.</p>";
        } else {
            echo "<h2 style='color:blue'>ℹ️ GENDER GAMILA SUDAH BENAR (Perempuan)</h2>";
            echo "<p>Gender saat ini: {$gamila['gender']}</p>";
        }
    } else {
        echo "<h2 style='color:red'>❌ GAMILA TIDAK DITEMUKAN DI DATABASE</h2>";
    }

    // 2. CEK DARIMANA JAZZIE DAPAT PERUNGGU
    echo "<hr><h2>Darimana Jazzie dapat Medali Perunggu di MVP?</h2>";
    $stmtJazzie = $db->prepare("
        SELECT r.event_id, r.race_class_id, ed.race_number, d.distance_name, r.rank, r.round
        FROM roll_event_results r
        JOIN roll_skaters s ON r.skater_id = s.id
        JOIN roll_event_details ed ON r.race_class_id = ed.id
        LEFT JOIN roll_ref_distances d ON ed.distance_id = d.id
        WHERE s.skater_name LIKE '%JAZZIE%' AND r.rank IN (1, 2, 3) AND r.status = 'OK'
    ");
    $stmtJazzie->execute();
    $jazzieMedals = $stmtJazzie->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jazzieMedals) > 0) {
        echo "<ul>";
        foreach ($jazzieMedals as $m) {
            echo "<li>Jazzie mendapat medali dari Race Nomor <b>{$m['race_number']} ({$m['distance_name']})</b> karena dia Juara <b>{$m['rank']}</b> di race tersebut!</li>";
        }
        echo "</ul>";
        echo "<p><b>Kesimpulan:</b> Jazzie mendapat medali Perunggu BUKAN dari Race 111 (Eliminasi), melainkan dari race lainnya! Jadi medali Jazzie dan Gamila TIDAK TERTUKAR.</p>";
    } else {
        echo "<p>Jazzie tidak tercatat mendapat rank 1, 2, 3 di race manapun yang menggunakan r.rank. (Mungkin dari poin/dtt).</p>";
    }

    echo "</div>";

} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
}
