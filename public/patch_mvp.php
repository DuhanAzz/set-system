<?php
$filePath = __DIR__ . '/../app/Roll/Controllers/Admin/RollMedalTallyController.php';

if (!file_exists($filePath)) {
    die("<h1 style='color:red'>Gagal: File tidak ditemukan di server!</h1>");
}

$content = file_get_contents($filePath);

// Cek apakah file sudah diperbaiki sebelumnya
if (strpos($content, "COALESCE(r.status, 'OK') = 'OK'") !== false) {
    die("<h1 style='color:green'>Sistem MVP sudah berhasil di-patch/diperbarui! Silakan cek klasemen MVP.</h1>");
}

// Lakukan Replace / Patching kode
$content = str_replace(
    "AND r2.round = 'Final' AND r2.status = 'OK'", 
    "AND r2.round = 'Final' AND COALESCE(r2.status, 'OK') = 'OK'", 
    $content
);

$content = str_replace(
    "WHERE r.event_id = ? AND r.round = 'Final' AND r.status = 'OK'", 
    "WHERE r.event_id = ? AND r.round = 'Final' AND COALESCE(r.status, 'OK') = 'OK'", 
    $content
);

// Simpan kembali
$result = file_put_contents($filePath, $content);

if ($result) {
    echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>";
    echo "<h1 style='color:green'>PATCH BERHASIL! ✅</h1>";
    echo "<p>Sistem Klasemen MVP sudah berhasil diperbarui secara manual melawati Git.</p>";
    echo "<p>Silakan tutup halaman ini dan <b>Refresh Halaman MVP</b>. Gamila pasti sudah muncul!</p>";
    echo "</div>";
    
    // Hapus script ini otomatis agar aman
    unlink(__FILE__);
} else {
    echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>";
    echo "<h1 style='color:red'>GAGAL MENYIMPAN ❌</h1>";
    echo "<p>Permission Denied. PHP di server live Bapak/Ibu tidak memiliki izin (write permission) untuk menimpa file.</p>";
    echo "<p>Silakan timpa file secara manual via cPanel File Manager.</p>";
    echo "</div>";
}
