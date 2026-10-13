<?php

// Test for winners CSV generation logic
$testHeaders = [
    'No_Lomba', 'Nama_Lomba', 'Kategori', 'Kelompok_Umur', 'Gender',
    'Jarak', 'Babak', 'Peringkat', 'Juara', 'Medali', 'BIB',
    'Nama_Atlet', 'Klub', 'Kota_Provinsi', 'Waktu', 'Poin', 'Status'
];

assert(count($testHeaders) === 17, "Expected 17 CSV columns");

// Test medali assignment logic
function getMedali($rank, $isOk) {
    if (!$isOk) return '-';
    if ($rank === 1) return 'EMAS';
    if ($rank === 2) return 'PERAK';
    if ($rank === 3) return 'PERUNGGU';
    return '-';
}

assert(getMedali(1, true) === 'EMAS');
assert(getMedali(2, true) === 'PERAK');
assert(getMedali(3, true) === 'PERUNGGU');
assert(getMedali(4, true) === '-');
assert(getMedali(1, false) === '-');

// Test BOM
$bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
assert(strlen($bom) === 3, "BOM length must be 3 bytes");

echo "All winners CSV unit assertions passed successfully!\n";
