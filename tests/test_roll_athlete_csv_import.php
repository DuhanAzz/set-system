<?php
ob_start();

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Roll/Controllers/User/RollUserAthleteController.php';

use App\Roll\Controllers\User\RollUserAthleteController;

echo "--- RUNNING TEST: ROLL ATHLETE MASS CSV EXPORT/IMPORT PARSER ---\n";

// 1. Date parser formats (YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY, invalid)
assert(RollUserAthleteController::parseBirthDate('2015-05-12') === '2015-05-12');
assert(RollUserAthleteController::parseBirthDate('12/05/2015') === '2015-05-12');
assert(RollUserAthleteController::parseBirthDate('05-11-2016') === '2016-11-05');
assert(RollUserAthleteController::parseBirthDate('0000-00-00') === null);
assert(RollUserAthleteController::parseBirthDate('31/02/2015') === null);
echo "✓ Case 1: parseBirthDate handles ISO, ID slash/dash & invalid dates [OK]\n";

// 2. Comma-delimited CSV with UTF-8 BOM, sample row skip, quoted NIK with leading apostrophe, Putra/Putri aliases
$csvComma = "\xEF\xBB\xBF" . implode("\n", [
    "ID (Kosongkan Jika Baru),UID (Otomatis),NIK (16 Digit),Nama Lengkap Atlet,Jenis Kelamin (M/F),Tanggal Lahir (YYYY-MM-DD),Asal Sekolah,Kota / Kabupaten,Provinsi,Status BPJS (sudah/belum)",
    ",,'3304012345670001,[CONTOH - HAPUS BARIS INI] BAGUS KURNIAWAN,M,2015-05-12,SMPN 1,YOGYAKARTA,DIY,sudah",
    ",,'3275011204180001,Alya Putri,Putri,12/04/2018,SDN 1 Bekasi,Kota Bekasi,Jawa Barat,sudah",
    "15,BU151,3275011204160002,Budi Santoso,Putra,2016-08-20,SMPN 2 Bekasi,Kota Bekasi,Jawa Barat,belum",
]);
$res1 = RollUserAthleteController::parseAthleteCsvRows($csvComma);
assert(count($res1['errors']) === 0, 'Expected 0 errors on valid comma CSV');
assert(count($res1['valid']) === 2, 'Expected 2 rows (example row skipped)');
assert($res1['valid'][0]['nik'] === '3275011204180001');
assert($res1['valid'][0]['skater_name'] === 'ALYA PUTRI');
assert($res1['valid'][0]['gender'] === 'F');
assert($res1['valid'][0]['birth_date'] === '2018-04-12');
assert($res1['valid'][0]['status_bpjs'] === 'sudah');
assert($res1['valid'][1]['id'] === 15);
assert($res1['valid'][1]['gender'] === 'M');
assert($res1['valid'][1]['status_bpjs'] === 'belum');
echo "✓ Case 2: Comma CSV, BOM, sample-row skip, leading apostrophe NIK & smart aliases [OK]\n";

// 3. Semicolon-delimited CSV (Indonesian Excel default) + error reporting (short NIK, Excel scientific notation, bad date)
$csvSemi = implode("\n", [
    "NIK;Nama Lengkap Atlet;Jenis Kelamin;Tanggal Lahir;Asal Sekolah;Kota;Provinsi;Status BPJS",
    "'3304012345670009;Citra Kirana;F;01/03/2017;SDN 3;Bandung;Jawa Barat;ya",
    "3.30401E+15;Excel Scientific Error;M;2015-01-01;SDN 1;Solo;Jateng;belum",
    "12345;Short NIK Error;F;2015-01-01;SDN 1;Solo;Jateng;belum",
    "'3304012345670010;Bad Date Error;M;99/99/2015;SDN 1;Solo;Jateng;belum",
]);
$res2 = RollUserAthleteController::parseAthleteCsvRows($csvSemi);
assert(count($res2['valid']) === 1, 'Expected 1 valid row from semicolon CSV');
assert($res2['valid'][0]['nik'] === '3304012345670009');
assert($res2['valid'][0]['status_bpjs'] === 'sudah');
assert(count($res2['errors']) === 3, 'Expected 3 validation errors flagged clearly');
echo "✓ Case 3: Semicolon CSV & scientific-notation/short-NIK/bad-date rejections [OK]\n";

// 4. Bulk 120-athlete CSV stress test
$bulkLines = [implode(',', RollUserAthleteController::getCsvHeaders())];
for ($i = 1; $i <= 120; $i++) {
    $nik = '327501' . str_pad((string)$i, 10, '0', STR_PAD_LEFT);
    $g = ($i % 2 === 0) ? 'M' : 'F';
    $bulkLines[] = ",,'{$nik},Atlet Massal {$i},{$g},2016-06-15,SDN {$i},Bekasi,Jawa Barat,sudah";
}
$resBulk = RollUserAthleteController::parseAthleteCsvRows(implode("\n", $bulkLines));
assert(count($resBulk['valid']) === 120 && empty($resBulk['errors']));
echo "✓ Case 4: Bulk 120 athletes parsed cleanly with 0 errors [OK]\n";

ob_end_flush();
echo "--- ALL ROLL ATHLETE CSV IMPORT TESTS PASSED! ---\n";
