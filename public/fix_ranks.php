<?php
// ============================================================
// 🛡️ BULLETPROOF ENV PARSER
// ============================================================
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, '"\' ');
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    // Cari event ID yang aktif
    session_start();
    $eventId = $_SESSION['roll_admin_active_event_id'] ?? 0;
    
    if ($eventId == 0) {
        // Ambil event terbaru jika tidak ada session
        $stmt = $db->query("SELECT id FROM roll_events ORDER BY id DESC LIMIT 1");
        $eventId = $stmt->fetchColumn();
    }

    // Cari race class ID untuk Race 111
    $stmt = $db->prepare("SELECT id FROM roll_event_details WHERE event_id = ? AND race_number = 111");
    $stmt->execute([$eventId]);
    $raceClassId = $stmt->fetchColumn();

    if ($raceClassId) {
        // Cari Jazzie dan Sekar Ayu di race tersebut
        $stmtSkater = $db->prepare("
            SELECT r.id, s.skater_name, r.rank 
            FROM roll_event_results r
            JOIN roll_skaters s ON r.skater_id = s.id
            WHERE r.event_id = ? AND r.race_class_id = ? AND r.round = 'Final'
            AND (s.skater_name LIKE '%JAZZIE%' OR s.skater_name LIKE '%SEKAR%')
        ");
        $stmtSkater->execute([$eventId, $raceClassId]);
        $skaters = $stmtSkater->fetchAll(PDO::FETCH_ASSOC);

        $jazzieId = null;
        $sekarId = null;

        foreach ($skaters as $skater) {
            if (stripos($skater['skater_name'], 'JAZZIE') !== false) {
                $jazzieId = $skater['id'];
            }
            if (stripos($skater['skater_name'], 'SEKAR') !== false) {
                $sekarId = $skater['id'];
            }
        }

        if ($jazzieId && $sekarId) {
            // Tukar rank: Jazzie jadi 5, Sekar jadi 4
            $updateStmt = $db->prepare("UPDATE roll_event_results SET rank = ? WHERE id = ?");
            $updateStmt->execute([5, $jazzieId]);
            $updateStmt->execute([4, $sekarId]);
            echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
            echo "<h1 style='color: green;'>BERHASIL!</h1><p>Rank untuk Jazzie dan Sekar Ayu pada Race 111 sudah berhasil ditukar (Jazzie juara 5, Sekar juara 4).</p>";
            echo "<p>Silakan tutup halaman ini dan cek kembali halaman Klasemen MVP dan Cetak PDF-nya.</p>";
            echo "</div>";
        } else {
            echo "<h1>Gagal</h1><p>Data Jazzie atau Sekar tidak ditemukan di Race 111.</p>";
        }
    } else {
        echo "<h1>Gagal</h1><p>Race 111 tidak ditemukan pada event ini.</p>";
    }

} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
}

// Hapus file ini secara otomatis setelah dijalankan sekali
unlink(__FILE__);
