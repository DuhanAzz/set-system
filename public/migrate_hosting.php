<?php
// Script Migrasi Database Otomatis (Sekali Pakai)
// Akses URL: http://domain-anda.com/migrate_hosting.php

// 1. Load Environment Variables (seperti di index.php)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv("$name=$value");
    }
} else {
    die("File .env tidak ditemukan. Pastikan script ini berada di folder public/.");
}

// 2. Hubungkan ke Database Hosting
$host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    $db = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// 3. Jalankan Migrasi
try {
    echo "<h1>Memulai Migrasi Database...</h1>";
    
    // Cek apakah race_distance masih ada
    $check = $db->query("SHOW COLUMNS FROM roll_entries LIKE 'race_distance'")->fetch();
    
    if ($check) {
        // A. Tambah distance_id jika belum ada
        $checkDist = $db->query("SHOW COLUMNS FROM roll_entries LIKE 'distance_id'")->fetch();
        if (!$checkDist) {
            $db->exec("ALTER TABLE roll_entries ADD COLUMN distance_id INT(11) NULL AFTER race_class_id");
            echo "<p>✅ Kolom distance_id berhasil ditambahkan.</p>";
        }
        
        // B. Sinkronkan Data
        $updated = $db->exec("
            UPDATE roll_entries e
            JOIN roll_ref_distances d ON e.race_distance = d.distance_name
            SET e.distance_id = d.id
        ");
        echo "<p>✅ Berhasil mensinkronkan $updated baris data dari teks ke ID.</p>";
        
        // C. Hapus race_distance
        $db->exec("ALTER TABLE roll_entries DROP COLUMN race_distance");
        echo "<p>✅ Kolom race_distance lama berhasil dihapus.</p>";
        
        echo "<h2 style='color:green'>MIGRASI SUKSES 100%! Aplikasi Anda siap digunakan dengan struktur baru.</h2>";
    } else {
        echo "<h2 style='color:blue'>Migrasi sudah pernah dijalankan sebelumnya. (Kolom race_distance tidak ditemukan).</h2>";
    }

} catch (Exception $e) {
    die("<h2 style='color:red'>Gagal melakukan migrasi: " . $e->getMessage() . "</h2>");
}

// 4. HAPUS DIRI SENDIRI DEMI KEAMANAN
@unlink(__FILE__);
echo "<p><i>Script ini (migrate_hosting.php) telah menghapus dirinya sendiri dari server secara otomatis demi keamanan.</i></p>";

?>
