<?php
ob_start();

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Roll/Controllers/User/RollUserAthleteController.php';

use App\Roll\Controllers\User\RollUserAthleteController;

echo "--- RUNNING TEST: ROLL ATHLETE MASS CSV EXPORT/IMPORT PARSER ---\n";

// 1. Date parser formats (Indonesian DD/MM/YYYY, DD-MM-YYYY, ISO YYYY-MM-DD, strict rejection of US MM/DD/YYYY & invalid)
assert(RollUserAthleteController::parseBirthDate('12/05/2015') === '2015-05-12');
assert(RollUserAthleteController::parseBirthDate('05-11-2016') === '2016-11-05');
assert(RollUserAthleteController::parseBirthDate('2015-05-12') === '2015-05-12');
assert(RollUserAthleteController::parseBirthDate('05/28/2015') === null, 'US MM/DD/YYYY with month>12 must fail');
assert(RollUserAthleteController::parseBirthDate('0000-00-00') === null);
assert(RollUserAthleteController::parseBirthDate('31/02/2015') === null);
echo "✓ Case 1: parseBirthDate enforces DD/MM/YYYY (Indo) + ISO & rejects invalid/US dates [OK]\n";

// 2. Comma-delimited CSV with UTF-8 BOM, sample row skip, formula NIK ="...", apostrophe NIK, Putra/Putri aliases
$csvComma = "\xEF\xBB\xBF" . implode("\n", [
    "ID (Kosongkan Jika Baru),UID (Otomatis),NIK (16 Digit),Nama Lengkap Atlet,Jenis Kelamin (M/F),Tanggal Lahir (DD/MM/YYYY),Asal Sekolah,Kota / Kabupaten,Provinsi,Status BPJS (sudah/belum)",
    ",,'3304012345670001,[CONTOH - HAPUS BARIS INI] BAGUS KURNIAWAN,M,12/05/2015,SMPN 1,YOGYAKARTA,DIY,sudah",
    ",,\"=\"\"3275011204180001\"\"\",Alya Putri,Putri,12/04/2018,\"SDN 1\nBekasi\",Kota Bekasi,Jawa Barat,sudah",
    "15,BU151,'3275011204160002,Budi Santoso,Putra,20/08/2016,SMPN 2 Bekasi,Kota Bekasi,Jawa Barat,belum",
]);
$res1 = RollUserAthleteController::parseAthleteCsvRows($csvComma);
assert(count($res1['errors']) === 0, 'Expected 0 errors on valid comma CSV');
assert(count($res1['nik_warnings']) === 0, 'Expected 0 NIK warnings on valid NIKs');
assert(count($res1['valid']) === 2, 'Expected 2 rows (example row skipped, multiline school preserved)');
assert($res1['valid'][0]['nik'] === '3275011204180001');
assert($res1['valid'][0]['skater_name'] === 'ALYA PUTRI');
assert($res1['valid'][0]['gender'] === 'F');
assert($res1['valid'][0]['birth_date'] === '2018-04-12');
assert($res1['valid'][0]['asal_sekolah'] === "SDN 1\nBEKASI");
assert($res1['valid'][0]['status_bpjs'] === 'sudah');
assert($res1['valid'][1]['id'] === 15);
assert($res1['valid'][1]['gender'] === 'M');
assert($res1['valid'][1]['birth_date'] === '2016-08-20');
assert($res1['valid'][1]['status_bpjs'] === 'belum');
echo "✓ Case 2: Comma CSV, BOM, =\"NIK\" anti-E+15 wrapper, multiline cell & DD/MM/YYYY [OK]\n";

// 3. Soft NIK warning policy: broken NIKs (E+15, #N/A, short/long, text) still enter valid[] with nik=null + warning,
//    while invalid Date / empty Name / invalid Gender are still rejected into errors[].
$csvSoftNik = "sep=;\n" . implode("\n", [
    "NIK;Nama Lengkap Atlet;Jenis Kelamin;Tanggal Lahir;Asal Sekolah;Kota;Provinsi;Status BPJS",
    "'3304012345670009;Citra Kirana;F;01/03/2017;SDN 3;Bandung;Jawa Barat;ya",
    "3.30401E+15;Excel Scientific Warning;M;01/01/2015;SDN 1;Solo;Jateng;belum",
    "12345;Short NIK Warning;F;02/01/2015;SDN 1;Solo;Jateng;belum",
    "#N/A;Formula NA Warning;F;03/01/2015;SDN 1;Solo;Jateng;sudah",
    "Aqilla 12200007;Corrupted Text NIK Warning;F;04/01/2015;SDN 1;Solo;Jateng;belum",
    "'3304012345670010;Bad Date Rejected;M;99/99/2015;SDN 1;Solo;Jateng;belum",
    "'3304012345670011;;M;05/01/2015;SDN 1;Solo;Jateng;belum",
]);
$res2 = RollUserAthleteController::parseAthleteCsvRows($csvSoftNik);
assert(count($res2['valid']) === 5, 'Expected 5 valid rows (1 clean NIK + 4 soft NIK warnings)');
assert(count($res2['nik_warnings']) === 4, 'Expected 4 NIK warnings flagged for manual edit');
assert($res2['valid'][0]['nik'] === '3304012345670009' && $res2['valid'][0]['nik_warning'] === null);
assert($res2['valid'][1]['nik'] === null && strpos($res2['valid'][1]['nik_warning'], 'notasi ilmiah Excel') !== false);
assert($res2['valid'][2]['nik'] === null && strpos($res2['valid'][2]['nik_warning'], '16 digit') !== false);
assert($res2['valid'][3]['nik'] === null && strpos($res2['valid'][3]['nik_warning'], 'bukan angka valid') !== false);
assert($res2['valid'][4]['nik'] === null && strpos($res2['valid'][4]['nik_warning'], 'bukan angka valid') !== false);
assert(count($res2['errors']) === 2, 'Expected 2 fatal errors (bad date + empty name)');
echo "✓ Case 3: Soft NIK warning pass-through + sep=; directive + fatal Name/DOB rejections [OK]\n";

// 4. Bulk 120-athlete CSV stress test with DD/MM/YYYY and ="NIK" wrapper
$bulkLines = [implode(',', RollUserAthleteController::getCsvHeaders())];
for ($i = 1; $i <= 120; $i++) {
    $nik = '327501' . str_pad((string)$i, 10, '0', STR_PAD_LEFT);
    $g = ($i % 2 === 0) ? 'M' : 'F';
    $bulkLines[] = ",,\"=\"\"{$nik}\"\"\",Atlet Massal {$i},{$g},15/06/2016,SDN {$i},Bekasi,Jawa Barat,sudah";
}
$resBulk = RollUserAthleteController::parseAthleteCsvRows(implode("\n", $bulkLines));
assert(count($resBulk['valid']) === 120 && empty($resBulk['errors']) && empty($resBulk['nik_warnings']));
echo "✓ Case 4: Bulk 120 athletes parsed cleanly with 0 errors & 0 warnings [OK]\n";

// 5. US Excel M/D/YYYY export/re-save detection (e.g. 6/13/2013, 3/17/2012, 8/28/2013 + ambiguous 12/5/2015) + NIK disambiguation
$csvUsExcel = implode("\n", [
    implode(',', RollUserAthleteController::getCsvHeaders()),
    ",,3.27502E+15,Aqila Sahira Liliana,F,6/13/2013,,Bekasi,Jawa Barat,sudah",
    ",,3.27505E+15,Aqilla Nadya Salma,F,3/17/2012,,Bekasi,Jawa Barat,belum",
    ",,3.21608E+15,Roro Kani Ambarwati,F,8/28/2013,,Bekasi,Jawa Barat,sudah",
    ",,3275024512150005,Ainayya Shabriyya Zhafeera,F,12/5/2015,,Bekasi,Jawa Barat,belum",
]);
$resUs = RollUserAthleteController::parseAthleteCsvRows($csvUsExcel);
assert(count($resUs['errors']) === 0, 'US Excel dates like 6/13/2013 and 8/28/2013 must be auto-detected');
assert(count($resUs['valid']) === 4);
assert($resUs['valid'][0]['birth_date'] === '2013-06-13');
assert($resUs['valid'][1]['birth_date'] === '2012-03-17');
assert($resUs['valid'][2]['birth_date'] === '2013-08-28');
assert($resUs['valid'][3]['birth_date'] === '2015-12-05', 'Ambiguous 12/5/2015 in US Excel file must become 2015-12-05');
assert(count($resUs['nik_warnings']) === 3, '3 scientific-notation NIKs flagged as soft warnings');

// Mixed date file using valid NIK to disambiguate 12/05/2015 -> 2015-12-05
assert(RollUserAthleteController::parseBirthDate('12/05/2015', 'DMY', '3275024512150005') === '2015-12-05');
assert(RollUserAthleteController::parseBirthDate('13-Jun-2013') === '2013-06-13');
assert(RollUserAthleteController::parseBirthDate('17 Mei 2015') === '2015-05-17');
echo "✓ Case 5: US Excel M/D/YYYY auto-detection, NIK date disambiguation & month names [OK]\n";

// 6. Master Export CSV header compatibility (ID, Institusi Puspernas vs Nama Asal Sekolah, Klub Asal, BPJS columns) + UID > 10 + Master view render
$masterCsv = "\xEF\xBB\xBF" . implode("\n", [
    "ID (Kosongkan Jika Baru),No,UID,NIK,Nama Lengkap Atlet,Jenis Kelamin,Tanggal Lahir,Umur,Klub Asal,Institusi Puspernas (Satu Kesatuan),Nama Asal Sekolah,Kota / Kabupaten,Provinsi,Status Kepesertaan BPJS,Keterangan BPJS,Total Event Lomba Diikuti,Kelengkapan Data Puspernas",
    "761,1,119201591,\"=\"\"3275024512150005\"\"\",Ainayya Shabriyya Zhafeera,Putri,05/12/2015,11 Thn,GWIS SKATING CLUB,SDN 1 BEKASI - KABUPATEN BEKASI,SDN 1 BEKASI,KABUPATEN BEKASI,JAWA BARAT,belum,Belum Terdaftar,2,Lengkap",
]);
$resMaster = RollUserAthleteController::parseAthleteCsvRows($masterCsv);
assert(empty($resMaster['errors']) && count($resMaster['valid']) === 1);
assert($resMaster['valid'][0]['id'] === 761);
assert($resMaster['valid'][0]['nik'] === '3275024512150005');
assert($resMaster['valid'][0]['club_name'] === 'GWIS SKATING CLUB');
assert($resMaster['valid'][0]['asal_sekolah'] === 'SDN 1 BEKASI');
assert($resMaster['valid'][0]['status_bpjs'] === 'belum');

// UID > 10 collision test
$mockUidDb = new class {
    public function prepare($sql) {
        return new class {
            public function execute($params = []) { return true; }
            public function fetchColumn() {
                static $idx = 0;
                $existing = ['0101202010','0101202011','0101202012','0101202013','0101202014','0101202015','0101202016','0101202017','0101202018','0101202019'];
                return $existing[$idx++] ?? false;
            }
        };
    }
};
$used = [];
$uid11 = RollUserAthleteController::generateUniqueSkaterUid($mockUidDb, 'Adi Akbar', '2020-01-01', 'M', $used);
assert($uid11 === '01012020110', "Expected UID suffix 10 when 0..9 are taken, got $uid11");

ob_start();
$skaters = [];
$clubs = [['id' => 189, 'club_name' => 'GWIS SKATING CLUB']];
$search = 'GWIS'; $bpjsFilter = 'all'; $completenessFilter = 'all'; $summary = [];
include __DIR__ . '/../views/roll/master/skaters/index.php';
$masterHtml = (string)ob_get_clean();
assert(strpos($masterHtml, '/roll/master/skaters/import_csv') !== false);
assert(strpos($masterHtml, '/roll/master/skaters/export_excel?search=GWIS') !== false);
assert(strpos($masterHtml, 'value="189" selected') !== false, 'GWIS club must be auto-selected when search=GWIS');
echo "✓ Case 6: Master Export header compatibility, UID>10 suffix & Master Import modal render [OK]\n";

ob_end_flush();
echo "--- ALL ROLL ATHLETE CSV IMPORT TESTS PASSED! ---\n";
