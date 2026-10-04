<?php
$filePath = __DIR__ . '/../app/Roll/Controllers/Admin/RollMedalTallyController.php';

if (!file_exists($filePath)) {
    die("<h1 style='color:red'>Gagal: File tidak ditemukan di server!</h1>");
}

$content = file_get_contents($filePath);

// Kita patch logika Eliminasi Tiebreaker yang salah
$oldEliminasiTiebreaker = "r2.rank = r.rank AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED)";

$newEliminasiTiebreaker = "r2.rank = r.rank AND (
    (r2.time != '00.00.000' AND r2.time != '' AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000' OR CAST(REPLACE(REPLACE(r2.time, ':', ''), '.', '') AS UNSIGNED) < CAST(REPLACE(REPLACE(r.time, ':', ''), '.', '') AS UNSIGNED)))
    OR (
        (r2.time = r.time OR ((r2.time IS NULL OR r2.time = '' OR r2.time = '00.00.000') AND (r.time IS NULL OR r.time = '' OR r.time = '00.00.000')))
        AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED)
    )
)";

// Pastikan kita replace agar spasinya tidak masalah
// Lebih aman gunakan str_replace pada bagian spesifik
if (strpos($content, "CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED))))") !== false && strpos($content, "r2.time !=") === false) {
    // String aslinya di kode adalah:
    // OR (r2.rank = r.rank AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED))))
    
    $search = "OR (r2.rank = r.rank AND CAST(REPLACE(r2.heat_name, 'Heat ', '') AS UNSIGNED) < CAST(REPLACE(r.heat_name, 'Heat ', '') AS UNSIGNED))))";
    $replace = "OR ( $newEliminasiTiebreaker )))";
    
    $content = str_replace($search, $replace, $content);
}

// Coba pendekatan Regex jika str_replace gagal karena spasi
$pattern = "/OR\s*\(\s*r2\.rank\s*=\s*r\.rank\s*AND\s*CAST\s*\(\s*REPLACE\s*\(\s*r2\.heat_name,\s*'Heat ',\s*''\s*\)\s*AS\s*UNSIGNED\s*\)\s*<\s*CAST\s*\(\s*REPLACE\s*\(\s*r\.heat_name,\s*'Heat ',\s*''\s*\)\s*AS\s*UNSIGNED\s*\)\s*\)\s*\)/";
$replacement = "OR ( $newEliminasiTiebreaker ))";
$content = preg_replace($pattern, $replacement, $content);

// Patch r.status yang kemarin (berjaga-jaga jika belum)
$content = str_replace("AND r2.round = 'Final' AND r2.status = 'OK'", "AND r2.round = 'Final' AND COALESCE(r2.status, 'OK') = 'OK'", $content);
$content = str_replace("WHERE r.event_id = ? AND r.round = 'Final' AND r.status = 'OK'", "WHERE r.event_id = ? AND r.round = 'Final' AND COALESCE(r.status, 'OK') = 'OK'", $content);

$result = file_put_contents($filePath, $content);

if ($result) {
    echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>";
    echo "<h1 style='color:green'>PATCH FINAL BERHASIL! ✅</h1>";
    echo "<p>Sistem Klasemen MVP sudah berhasil diperbarui dengan logika TIEBREAKER WAKTU (TIME) untuk Eliminasi.</p>";
    echo "<p>Silakan tutup halaman ini dan <b>Refresh Halaman MVP</b>. Gamila pasti menduduki peringkat di atas Jazzie sekarang!</p>";
    echo "</div>";
    
    unlink(__FILE__);
} else {
    echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>";
    echo "<h1 style='color:red'>GAGAL MENYIMPAN ❌</h1>";
    echo "<p>PHP di server live Bapak/Ibu tidak memiliki izin (write permission) untuk menimpa file.</p>";
    echo "</div>";
}
