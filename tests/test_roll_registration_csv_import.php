<?php
ob_start();

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/DateHelper.php';
require_once __DIR__ . '/../app/Helpers/RollTeamHelper.php';
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';
require_once __DIR__ . '/../app/Roll/Controllers/User/RollRegistrationController.php';

use App\Roll\Controllers\User\RollRegistrationController;

echo "--- RUNNING TEST: ROLL REGISTRATION MASS CSV IMPORT & VALIDATION ---\n";

$event = [
    'id' => 5,
    'event_date_start' => '2026-11-20',
    'limit_speed_ind' => 2,
    'limit_std_ind' => 2,
    'limit_pemula_ind' => 2,
    'allow_pemula_standart_mix' => 0,
    'is_speed_open' => 1,
    'is_std_open' => 1,
    'is_pemula_open' => 1,
];

$eventFees = [
    'fee_speed' => 400000, 'fee_speed_extra' => 200000, 'quota_speed_basic' => 2,
    'fee_standart' => 350000, 'fee_standart_extra' => 100000, 'quota_std_basic' => 2,
    'fee_pemula' => 250000, 'fee_pemula_extra' => 100000, 'quota_pemula_basic' => 2,
    'fee_eksebisi' => 150000, 'allow_pemula_standart_mix' => 0,
];

$classes = [
    // Pemula KU C (10-11 thn) Putri: 100m, 200m
    ['id' => 10, 'class_cat_id' => 3, 'class_name' => 'Pemula', 'distance_id' => 1, 'dist_ref_id' => 1, 'distance_name' => '100m', 'group_name' => 'KU C', 'min_year' => 10, 'max_year' => 11, 'gender' => 'Putri', 'race_number' => '101'],
    ['id' => 11, 'class_cat_id' => 3, 'class_name' => 'Pemula', 'distance_id' => 2, 'dist_ref_id' => 2, 'distance_name' => '200m', 'group_name' => 'KU C', 'min_year' => 10, 'max_year' => 11, 'gender' => 'Putri', 'race_number' => '102'],
    // Standart KU C (10-11 thn) Putra: 300m, 500m
    ['id' => 20, 'class_cat_id' => 2, 'class_name' => 'Standart', 'distance_id' => 3, 'dist_ref_id' => 3, 'distance_name' => '300m', 'group_name' => 'KU C', 'min_year' => 10, 'max_year' => 11, 'gender' => 'Putra', 'race_number' => '201'],
    ['id' => 21, 'class_cat_id' => 2, 'class_name' => 'Standart', 'distance_id' => 4, 'dist_ref_id' => 4, 'distance_name' => '500m', 'group_name' => 'KU C', 'min_year' => 10, 'max_year' => 11, 'gender' => 'Putra', 'race_number' => '202'],
    // Speed Senior (16-99 thn) Putra: 500m, 1000m (and mandatory * 200m)
    ['id' => 30, 'class_cat_id' => 1, 'class_name' => 'Speed', 'distance_id' => 2, 'dist_ref_id' => 2, 'distance_name' => '200m', 'group_name' => 'Senior', 'min_year' => 16, 'max_year' => 99, 'gender' => 'Putra', 'race_number' => '300*'],
    ['id' => 31, 'class_cat_id' => 1, 'class_name' => 'Speed', 'distance_id' => 4, 'dist_ref_id' => 4, 'distance_name' => '500m', 'group_name' => 'Senior', 'min_year' => 16, 'max_year' => 99, 'gender' => 'Putra', 'race_number' => '301'],
    ['id' => 32, 'class_cat_id' => 1, 'class_name' => 'Speed', 'distance_id' => 5, 'dist_ref_id' => 5, 'distance_name' => '1000m', 'group_name' => 'Senior', 'min_year' => 16, 'max_year' => 99, 'gender' => 'Putra', 'race_number' => '302'],
    // Relay should be ignored from individual matrix
    ['id' => 40, 'class_cat_id' => 1, 'class_name' => 'Speed', 'distance_id' => 6, 'dist_ref_id' => 6, 'distance_name' => 'Relay 3000m', 'group_name' => 'Senior', 'min_year' => 16, 'max_year' => 99, 'gender' => 'Putra', 'race_number' => '401'],
];

$athletes = [
    ['id' => 1, 'uid' => 'AL169', 'nik' => '3275011204160001', 'skater_name' => 'ALYA PUTRI', 'gender' => 'F', 'birth_date' => '2016-04-12', 'is_pon_veteran' => 0], // Age 10 in 2026 (KU C Putri)
    ['id' => 2, 'uid' => 'BU151', 'nik' => '3275012008150002', 'skater_name' => 'BUDI SANTOSO', 'gender' => 'M', 'birth_date' => '2015-08-20', 'is_pon_veteran' => 0], // Age 11 in 2026 (KU C Putra)
    ['id' => 3, 'uid' => 'PO151', 'nik' => '3275010101150003', 'skater_name' => 'PON VETERAN', 'gender' => 'M', 'birth_date' => '2015-01-01', 'is_pon_veteran' => 1], // Age 11, but PON -> Senior!
    ['id' => 4, 'uid' => 'SK169', 'nik' => '3275010101160004', 'skater_name' => 'SKIPPED ATHLETE', 'gender' => 'F', 'birth_date' => '2016-01-01', 'is_pon_veteran' => 0],
];

$existingEntries = [
    // Alya has 1 existing Unpaid entry to be replaced
    ['entry_id' => 901, 'skater_id' => 1, 'race_class_id' => 10, 'skate_class' => 'Pemula', 'distance_name' => '100m', 'team_name' => null, 'is_manual' => 0, 'manual_invoice_code' => null, 'payment_status' => 'Unpaid'],
    // Skipped athlete has an existing Unpaid entry that must stay untouched because skipped in CSV
    ['entry_id' => 902, 'skater_id' => 4, 'race_class_id' => 10, 'skate_class' => 'Pemula', 'distance_name' => '100m', 'team_name' => null, 'is_manual' => 0, 'manual_invoice_code' => null, 'payment_status' => 'Unpaid'],
];

// Test 1: Valid semicolon CSV + BOM + PON override + auto mandatory * + skipped blank athlete
$csv = "\xEF\xBB\xBF" . implode("\n", [
    "UID;NIK;Nama Atlet;Gender;Tgl Lahir;Umur (31 Des);Kategori (Pemula/Standart/Speed);100m;200m;300m;500m;1000m",
    "AL169;'3275011204160001;ALYA PUTRI;F;2016-04-12;10 Thn;Pemula;1;1;;;",
    "BU151;'3275012008150002;BUDI SANTOSO;M;2015-08-20;11 Thn;Standart;;;1;1;",
    "PO151;'3275010101150003;PON VETERAN;M;2015-01-01;11 Thn;Speed;;;;1;", // PON -> Speed Senior 200m* (auto) + 500m
    "SK169;'3275010101160004;SKIPPED ATHLETE;F;2016-01-01;10 Thn;;;;;;", // Completely blank -> skipped silently
]);

$res = RollRegistrationController::parseRegistrationCsv($csv, $athletes, $classes, $event, $existingEntries, $eventFees);
assert(empty($res['errors']), 'Expected 0 errors on valid CSV: ' . implode(' | ', $res['errors']));
assert($res['stats']['total_skaters'] === 3, 'Expected 3 skaters planned');
assert($res['stats']['replaced_entries'] === 1, 'Expected 1 Unpaid entry replaced (Alya)');
// Alya (2: 100m, 200m) + Budi (2: 300m, 500m) + PON Veteran (2: 200m*, 500m) = 6 entries!
assert($res['stats']['total_entries'] === 6, 'Expected 6 entries total including mandatory *, got ' . $res['stats']['total_entries']);
// Projected fee: Alya Pemula (250k) + Budi Standart (350k) + PON Speed (400k) + Skipped Pemula (250k) = 1,250,000
assert(abs($res['stats']['estimated_total_fee'] - 1250000) < 0.01, 'Expected Rp 1,250,000 projected fee, got ' . $res['stats']['estimated_total_fee']);
echo "✓ Case 1: Semicolon BOM CSV, KU/gender matching, PON->Senior, mandatory *, replace count & fee estimate [OK]\n";

// Test 2: Validation errors (category limit exceeded, unavailable distance, age mismatch, unknown athlete)
$csvErr = implode("\n", [
    "UID,NIK,Nama Atlet,Gender,Tgl Lahir,Umur,Kategori,100m,200m,300m,500m,1000m",
    "PO151,'3275010101150003,PON VETERAN,M,2015-01-01,25,Speed,,,,1,1", // 200m* + 500m + 1000m = 3 > limit 2!
    "AL169,'3275011204160001,ALYA PUTRI,F,2016-04-12,10,Standart,1,,,,", // 100m not in Standart
    "AL169,'3275011204160001,ALYA PUTRI,F,2016-04-12,10,Speed,,,,1,", // Alya age 10 cannot enter Senior Speed
    "XX999,'9999999999999999,UNKNOWN SKATER,M,2015-01-01,11,Pemula,1,,,,", // Not in club roster
]);
$resErr = RollRegistrationController::parseRegistrationCsv($csvErr, $athletes, $classes, $event, $existingEntries, $eventFees);
assert(count($resErr['errors']) === 4, 'Expected 4 validation errors, got ' . count($resErr['errors']));
assert($resErr['stats']['total_skaters'] === 0);
echo "✓ Case 2: Limit, invalid distance, age/gender mismatch & missing skater rejections verified [OK]\n";

ob_end_flush();
echo "--- ALL ROLL REGISTRATION CSV IMPORT TESTS PASSED! ---\n";
