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
    
    // Cari Skater bernama Gamila Fadia Arismansyah
    $stmt = $db->prepare("SELECT id, skater_name, gender FROM roll_skaters WHERE skater_name LIKE '%Gamila%' LIMIT 1");
    $stmt->execute();
    $skater = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($skater) {
        $oldGender = $skater['gender'];
        
        // Update gender menjadi P (Perempuan/Putri)
        $updateStmt = $db->prepare("UPDATE roll_skaters SET gender = 'P' WHERE id = ?");
        $updateStmt->execute([$skater['id']]);
        
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
        echo "<h1 style='color: green;'>BERHASIL! ✅</h1>";
        echo "<p>Data Jenis Kelamin atlet atas nama <b>{$skater['skater_name']}</b> sudah diperbaiki dari <b>'{$oldGender}'</b> menjadi <b>'P' (Putri)</b>.</p>";
        echo "<p>Silakan tutup halaman ini dan <b>refresh halaman Klasemen MVP</b> Bapak/Ibu. Nama Gamila sekarang pasti sudah muncul!</p>";
        echo "</div>";
    } else {
        echo "<h1 style='color: red; text-align: center;'>Gagal ❌</h1><p style='text-align: center;'>Atlet bernama Gamila tidak ditemukan di database.</p>";
    }

} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
}

// Hapus file ini secara otomatis setelah dijalankan sekali
unlink(__FILE__);
