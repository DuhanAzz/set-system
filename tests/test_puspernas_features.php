<?php

// Test for Puspernas Features: Format Institusi, Fitur Pintar Transisi Jenjang, & Export Sertifikat
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim(trim($value), '"\''));
        $_ENV[trim($name)] = trim(trim($value), '"\'');
    }
}

require_once __DIR__ . '/../app/Helpers/SchoolHelper.php';

use App\Helpers\SchoolHelper;

echo "Running Puspernas Features & Smart School Transition Tests...\n";

// 1. Test Format Institusi Satu Kesatuan
$fmt1 = SchoolHelper::formatInstitusi('SMA Negeri 1 Yogyakarta', 'Kota Yogyakarta', 'D.I. Yogyakarta');
assert($fmt1 === 'SMA NEGERI 1 YOGYAKARTA, KOTA YOGYAKARTA, PROV. D.I. YOGYAKARTA', "Formatted full institution must match");

$fmt2 = SchoolHelper::formatInstitusi('SMPN 2 Sleman', 'Sleman', 'DI Yogyakarta');
assert(strpos($fmt2, 'SMPN 2 SLEMAN') !== false, "Must contain school name");
assert(strpos($fmt2, 'KOTA/KAB. SLEMAN') !== false, "Must prepend KOTA/KAB if not present");

$fmt3 = SchoolHelper::formatInstitusi('SD Percobaan 2');
assert($fmt3 === 'SD PERCOBAAN 2', "Single school without city/province");

$fmt4 = SchoolHelper::formatInstitusi('', null, '');
assert($fmt4 === '-', "Empty inputs must return dash '-'");

echo "✓ Test 1 Passed: Format Institusi Satu Kesatuan.\n";

// 2. Test Fitur Pintar Transisi Jenjang Sekolah
// Skenario A: Umur 14 tahun tapi masih tercatat SD -> Saran SMP
$resA = SchoolHelper::checkTransition('2012-05-01', 'SD Negeri 1 Yogyakarta', '2026-11-02');
assert($resA['needs_update'] === true, "Age 14 with SD school must need update");
assert($resA['suggested_level'] === 'SMP/MTs', "Suggested level must be SMP/MTs");
assert($resA['current_level'] === 'SD/MI', "Current level detected as SD/MI");

// Skenario B: Umur 17 tahun tapi masih tercatat SMP -> Saran SMA/SMK
$resB = SchoolHelper::checkTransition('2009-08-15', 'SMP Negeri 5 Yogyakarta', '2026-11-02');
assert($resB['needs_update'] === true, "Age 17 with SMP school must need update");
assert($resB['suggested_level'] === 'SMA/SMK', "Suggested level must be SMA/SMK");
assert($resB['current_level'] === 'SMP/MTs', "Current level detected as SMP/MTs");

// Skenario C: Umur 20 tahun tapi masih tercatat SMA -> Saran Perguruan Tinggi
$resC = SchoolHelper::checkTransition('2006-03-20', 'SMA Negeri 1 Bantul', '2026-11-02');
assert($resC['needs_update'] === true, "Age 20 with SMA school must need update");
assert($resC['suggested_level'] === 'Perguruan Tinggi', "Suggested level must be Perguruan Tinggi");
assert($resC['current_level'] === 'SMA/SMK', "Current level detected as SMA/SMK");

// Skenario D: Umur 10 tahun tercatat SD -> Normal, tidak perlu update
$resD = SchoolHelper::checkTransition('2016-01-10', 'SD Negeri 2 Depok', '2026-11-02');
assert($resD['needs_update'] === false, "Age 10 with SD school must NOT need update");

// Skenario E: Umur 14 tahun sudah tercatat SMP -> Normal, tidak perlu update
$resE = SchoolHelper::checkTransition('2012-01-10', 'SMPN 1 Sleman', '2026-11-02');
assert($resE['needs_update'] === false, "Age 14 with SMP school must NOT need update");

echo "✓ Test 2 Passed: Fitur Pintar Transisi Jenjang Sekolah.\n";

// 3. Test Controller Method Exists
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Roll/Controllers/Admin/RollExportController.php';

use App\Roll\Controllers\Admin\RollExportController;

assert(method_exists(RollExportController::class, 'export_canva_puspernas'), "Method export_canva_puspernas must exist");
assert(method_exists(RollExportController::class, 'print_certificates'), "Method print_certificates must exist");

echo "✓ Test 3 Passed: RollExportController methods exist.\n";

// 4. Test View Files Exist
assert(file_exists(__DIR__ . '/../views/roll/admin/export/print_certificates.php'), "print_certificates view must exist");
assert(file_exists(__DIR__ . '/../views/roll/admin/export/index.php'), "admin export index view must exist");

echo "✓ Test 4 Passed: View template files exist.\n";

echo "\nAll Puspernas & Smart Features tests passed successfully! 🎉\n";

