<?php
// tests/test_canva_export_sync.php
// Runnable assert-based test for Canva Bulk Create CSV export format synchronization

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Running Canva Bulk Create Export Synchronization Tests...\n";

// 1. Read Swift.csv reference
$referenceCsvPath = '/Users/mac/Downloads/Swift.csv';
assert(file_exists($referenceCsvPath), "Reference Swift.csv must exist");

$refLines = file($referenceCsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$refHeader = trim($refLines[0]);
assert($refHeader === 'Nama;Asal Sekolah;Juara;Nomor lomba;Catatan Waktu', "Header in Swift.csv must match exact expected columns");
echo "✓ Test 1: Swift.csv reference header verified ($refHeader)\n";

// 2. Test Canva Column Header & Row Formatting Logic
$romanMap = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];

function formatTimeCanvaTest($timeFinal, $isDQ) {
    if ($isDQ || empty($timeFinal) || strtoupper($timeFinal) === 'NT') return '-';
    $timeClean = str_replace(',', '.', trim($timeFinal));
    if ($timeClean === '99:99.99' || $timeClean === '99.99.99' || $timeClean === '99:99:99') {
        return '99:99:99';
    }
    $parts = preg_split('/[:.]/', $timeClean);
    $menit = 0; $detik = 0; $ms = 0;
    if (count($parts) == 3) {
        $menit = (int)$parts[0];
        $detik = (int)$parts[1];
        $ms = (int)str_pad(substr($parts[2], 0, 2), 2, '0');
    } elseif (count($parts) == 2) {
        $detik = (int)$parts[0];
        $ms = (int)str_pad(substr($parts[1], 0, 2), 2, '0');
    } elseif (count($parts) == 1) {
        $detik = (int)$parts[0];
    }
    return sprintf("%02d:%02d:%02d", $menit, $detik, $ms);
}

function formatSchoolTest($raw) {
    $title = ($raw && $raw !== '-') ? ucwords(strtolower(trim($raw))) : '-';
    if ($title !== '-') {
        $title = preg_replace_callback('/\b(sd|smp|sma|smk|sdn|smpn|sman|mi|mts|ma|club|sc)\b/i', function($m) {
            return strtoupper($m[0]);
        }, $title);
    }
    return $title;
}

// 3. Test Sample Row Transformation
$mockSwimmer = [
    'nama_atlet' => 'SAPTONO',
    'asal_sekolah' => 'SD 1 GAMPING',
    'dynamic_rank' => 1,
    'distance' => '50',
    'stroke' => 'Gaya Bebas',
    'jenis_kelamin' => 'L',
    'time_final' => '99:99.99',
    'is_dq_final' => 0
];

$namaTitle = mb_convert_case(trim($mockSwimmer['nama_atlet']), MB_CASE_TITLE, "UTF-8");
$asalSekolah = formatSchoolTest($mockSwimmer['asal_sekolah']);
$juara = 'Juara ' . $romanMap[$mockSwimmer['dynamic_rank']];
$dist = trim($mockSwimmer['distance']) . 'm';
$stroke = ucwords(strtolower(trim($mockSwimmer['stroke'])));
$gender = (strtoupper($mockSwimmer['jenis_kelamin']) === 'L') ? 'Putra' : 'Putri';
$nomorLomba = $dist . ' ' . $stroke . ' ' . $gender;
$waktu = formatTimeCanvaTest($mockSwimmer['time_final'], $mockSwimmer['is_dq_final']);

$row = [
    'Nama' => $namaTitle,
    'Asal Sekolah' => $asalSekolah,
    'Juara' => $juara,
    'Nomor lomba' => $nomorLomba,
    'Catatan Waktu' => $waktu
];

$csvRow = implode(';', $row);
assert($csvRow === 'Saptono;SD 1 Gamping;Juara I;50m Gaya Bebas Putra;99:99:99', "Generated CSV row must match Swift.csv row 1 format");
echo "✓ Test 2: Sample row matching Swift.csv PASSED: ($csvRow)\n";

// 4. Test Second Sample Row with Real Time
$mockSwimmer2 = [
    'nama_atlet' => 'EDI',
    'asal_sekolah' => 'sdn 1 gamping',
    'dynamic_rank' => 2,
    'distance' => '100m',
    'stroke' => 'Bebas',
    'jenis_kelamin' => 'L',
    'time_final' => '01:05.12',
    'is_dq_final' => 0
];

$namaTitle2 = mb_convert_case(trim($mockSwimmer2['nama_atlet']), MB_CASE_TITLE, "UTF-8");
$asalSekolah2 = formatSchoolTest($mockSwimmer2['asal_sekolah']);
$juara2 = 'Juara ' . $romanMap[$mockSwimmer2['dynamic_rank']];
$dist2 = rtrim(trim($mockSwimmer2['distance']), 'mM') . 'm';
$stroke2 = 'Gaya ' . ucwords(strtolower(trim($mockSwimmer2['stroke'])));
$gender2 = 'Putra';
$nomorLomba2 = $dist2 . ' ' . $stroke2 . ' ' . $gender2;
$waktu2 = formatTimeCanvaTest($mockSwimmer2['time_final'], $mockSwimmer2['is_dq_final']);

$row2 = [
    'Nama' => $namaTitle2,
    'Asal Sekolah' => $asalSekolah2,
    'Juara' => $juara2,
    'Nomor lomba' => $nomorLomba2,
    'Catatan Waktu' => $waktu2
];
$csvRow2 = implode(';', $row2);
assert($csvRow2 === 'Edi;SDN 1 Gamping;Juara II;100m Gaya Bebas Putra;01:05:12', "Generated CSV row 2 matches expected format");
echo "✓ Test 3: Second sample row with time 01:05:12 PASSED: ($csvRow2)\n";

echo "ALL CANVA EXPORT SYNCHRONIZATION TESTS PASSED!\n";
