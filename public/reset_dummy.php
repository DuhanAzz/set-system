<?php
// FILE: public/reset_dummy.php
// URL UNTUK EKSEKUSI: http://DOMAIN_ANDA/reset_dummy.php?key=rahasiaroll2026
// PENTING: Hapus file ini setelah selesai digunakan di hosting!

$secret_key = 'rahasiaroll2026';

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die("Akses ditolak. Gunakan parameter key yang benar.");
}

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();

    // Menonaktifkan pengecekan foreign key sementara
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Hapus semua akun user klub (Jangan hapus role = admin/master)
    $stmt = $db->prepare("DELETE FROM roll_users WHERE role = 'user'");
    $stmt->execute();
    $userCount = $stmt->rowCount();

    // 2. Kosongkan tabel klub, atlet, dan riwayat pendaftaran
    $db->exec("TRUNCATE TABLE roll_clubs");
    $db->exec("TRUNCATE TABLE roll_skaters");
    $db->exec("TRUNCATE TABLE roll_entries");
    $db->exec("TRUNCATE TABLE roll_payments");
    $db->exec("TRUNCATE TABLE roll_skater_transfers");

    // 3. Kosongkan tabel hasil dan peloton (karena peserta sudah kosong)
    $db->exec("TRUNCATE TABLE roll_pelotons");
    $db->exec("TRUNCATE TABLE roll_event_results");
    
    // Note: roll_events dan roll_event_details TIDAK dihapus 
    // agar pengaturan event (matriks, harga, dll) yang sudah di-set admin tidak hilang.

    // Menyalakan kembali pengecekan foreign key
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "<h2 style='color:green'>BERHASIL!</h2>";
    echo "<p>Data dummy berhasil dibersihkan.</p>";
    echo "<ul>
            <li>Akun Klub Dihapus: $userCount akun</li>
            <li>Tabel Klub, Atlet, Pendaftaran, Pembayaran, Peloton, dan Hasil telah dikosongkan.</li>
            <li>Pengaturan Event & Matriks Admin TETAP AMAN.</li>
          </ul>";
    echo "<p style='color:red; font-weight:bold;'>PENTING: Segera hapus file <code>reset_dummy.php</code> ini dari server hosting Anda demi keamanan!</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>GAGAL!</h2>";
    echo "<p>Terjadi kesalahan: " . $e->getMessage() . "</p>";
}
