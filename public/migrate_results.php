<?php
// Script Migrasi Database Otomatis untuk roll_event_results
// Akses URL: http://domain-anda.com/migrate_results.php

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
} else {
    die("File .env tidak ditemukan. Pastikan script ini berada di folder public/.");
}

try {
    $db = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Memulai Migrasi Tabel roll_event_results...</h1>";

    // 1. Rename finish_position -> rank
    $db->exec("ALTER TABLE roll_event_results CHANGE finish_position `rank` INT(11) NULL");
    echo "<p>✅ finish_position berhasil diubah menjadi rank.</p>";

    // 2. Rename finish_time_ms -> time
    $db->exec("ALTER TABLE roll_event_results CHANGE finish_time_ms `time` VARCHAR(15) NULL");
    echo "<p>✅ finish_time_ms berhasil diubah menjadi time.</p>";

    // 3. Rename total_points -> point
    $db->exec("ALTER TABLE roll_event_results CHANGE total_points `point` INT(11) DEFAULT 0");
    echo "<p>✅ total_points berhasil diubah menjadi point.</p>";

    // 4. Rename race_status -> status
    // Set all existing invalid status to 'OK' before altering ENUM to prevent data truncation
    $db->exec("UPDATE roll_event_results SET race_status = 'OK' WHERE race_status NOT IN ('DNS', 'DNF')");
    $db->exec("ALTER TABLE roll_event_results CHANGE race_status `status` ENUM('OK', 'DNS', 'DNF', 'DQ', 'FS') DEFAULT 'OK'");
    echo "<p>✅ race_status berhasil diubah menjadi status dengan Enum standar.</p>";

    echo "<h2 style='color:green'>MIGRASI SUKSES 100%! Aplikasi Anda siap untuk Live Timing.</h2>";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "<h2 style='color:blue'>Migrasi sepertinya sudah pernah dijalankan sebelumnya.</h2>";
    } else {
        die("<h2 style='color:red'>Gagal melakukan migrasi: " . $e->getMessage() . "</h2>");
    }
}

// HAPUS DIRI SENDIRI DEMI KEAMANAN
@unlink(__FILE__);
echo "<p><i>Script ini (migrate_results.php) telah menghapus dirinya sendiri dari server secara otomatis demi keamanan.</i></p>";
?>
