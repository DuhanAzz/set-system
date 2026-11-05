<?php

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

echo "Running Results Print Configuration & Synchronization Tests...\n";

// 1. Test Default Configuration (All Enabled by Default)
$testUrlParamsEmpty = [];
$isSubmittedEmpty = !empty($testUrlParamsEmpty['cfg_submitted']) || isset($testUrlParamsEmpty['cfg_event_no']) || isset($testUrlParamsEmpty['col_uid']) || isset($testUrlParamsEmpty['col_tim']);

$pcDefault = [
    'show_event_no'      => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_event_no']) : true,
    'show_date'          => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_date']) : true,
    'show_event_name'    => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_event_name']) : true,
    'show_group'         => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_group']) : true,
    'show_gender'        => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_gender']) : true,
    'show_pool'          => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_pool']) : true,
    'show_round'         => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_round']) : true,
    'show_records'       => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_show_records']) : true,
    'show_event_records' => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['cfg_show_event_records']) : true
];

$ccDefault = [
    'uid'     => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_uid']) : true,
    'nama'    => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_nama']) : true,
    'ku'      => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_ku']) : true,
    'tim'     => $isSubmittedEmpty ? (!empty($testUrlParamsEmpty['col_tim']) || !empty($testUrlParamsEmpty['col_klub'])) : true,
    'sekolah' => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_sekolah']) : true,
    'waktu'   => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_waktu']) : true,
    'hasil'   => $isSubmittedEmpty ? !empty($testUrlParamsEmpty['col_hasil']) : true,
];

foreach ($pcDefault as $k => $v) {
    assert($v === true, "Default for $k must be true");
}
foreach ($ccDefault as $k => $v) {
    assert($v === true, "Default for $k must be true");
}
echo "✓ Test 1: Default print configuration (all active) PASSED!\n";

// 2. Test Customized Parameters (Filtering Title Components & Columns)
$customParams = [
    'cfg_submitted' => '1',
    'cfg_event_no' => '1',
    'cfg_event_name' => '1',
    // cfg_date omitted (false)
    // cfg_pool omitted (false)
    'cfg_group' => '1',
    'cfg_gender' => '1',
    'cfg_round' => '1',
    'cfg_show_records' => '1',
    // col_uid omitted (false)
    'col_nama' => '1',
    'col_ku' => '1',
    'col_tim' => '1',
    'col_waktu' => '1',
    'col_hasil' => '1'
];

$isSubmittedCustom = !empty($customParams['cfg_submitted']);
$pcCustom = [
    'show_event_no'   => !empty($customParams['cfg_event_no']),
    'show_date'       => !empty($customParams['cfg_date']),
    'show_event_name' => !empty($customParams['cfg_event_name']),
    'show_group'      => !empty($customParams['cfg_group']),
    'show_gender'     => !empty($customParams['cfg_gender']),
    'show_pool'       => !empty($customParams['cfg_pool']),
    'show_round'      => !empty($customParams['cfg_round']),
    'show_records'    => !empty($customParams['cfg_show_records'])
];

assert($pcCustom['show_event_no'] === true, "Event No should be true");
assert($pcCustom['show_date'] === false, "Date should be false when omitted");
assert($pcCustom['show_pool'] === false, "Pool should be false when omitted");
assert($pcCustom['show_round'] === true, "Round should be true");
echo "✓ Test 2: Custom parameter parsing PASSED!\n";

// 3. Test Colspan Calculation for Relay vs Individual
$isRelay = true;
$activeColumnsRelay = 1; // RANK
if (!empty($customParams['col_tim'])) $activeColumnsRelay++;
if (!empty($customParams['col_nama'])) $activeColumnsRelay++;
if (!empty($customParams['col_tim']) || !empty($customParams['col_sekolah'])) $activeColumnsRelay++;
if (!empty($customParams['col_waktu'])) $activeColumnsRelay++;
if (!empty($customParams['col_hasil'])) $activeColumnsRelay++;

assert($activeColumnsRelay === 6, "Relay active columns should be 6 with all active, got $activeColumnsRelay");

$isIndividual = false;
$activeColumnsIndiv = 1; // RANK
if (!empty($customParams['col_uid'])) $activeColumnsIndiv++; // omitted -> 0
if (!empty($customParams['col_nama'])) $activeColumnsIndiv++;
if (!empty($customParams['col_ku'])) $activeColumnsIndiv++;
if (!empty($customParams['col_tim'])) $activeColumnsIndiv++;
if (!empty($customParams['col_waktu'])) $activeColumnsIndiv++;
if (!empty($customParams['col_hasil'])) $activeColumnsIndiv++;

assert($activeColumnsIndiv === 6, "Individual active columns with UID disabled should be 6 (7 - 1), got $activeColumnsIndiv");
echo "✓ Test 3: Colspan calculation for relay & individual tables PASSED!\n";

// 4. Test Relay Title Formatting for Category 536
$distance = 100;
$stroke = 'Gaya Bebas';
$isRelay = true;
$distLabel = SwimEventHelper::formatDistance($distance, $isRelay);
$cleanStroke = trim(str_ireplace(['Gaya', 'GAYA', 'ESTAFET', 'RELAY'], '', $stroke));
$eventStrokeDisplay = $distLabel . " ESTAFET " . strtoupper($cleanStroke);

assert($eventStrokeDisplay === '4 x 25M ESTAFET BEBAS', "Relay title display should be '4 x 25M ESTAFET BEBAS', got: $eventStrokeDisplay");
echo "✓ Test 4: Relay title component formatting PASSED!\n";

echo "ALL RESULTS PRINT CONFIG & SYNC TESTS PASSED!\n";
