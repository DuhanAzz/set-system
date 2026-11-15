<?php
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';

use App\Helpers\RollFinanceHelper;

echo "--- RUNNING TEST: ROLL CENTRALIZED EXTRA RACE FEES & LIMITS ---\n";

$eventFees = [
    'fee_pemula' => 250000,
    'fee_pemula_extra' => 100000,
    'quota_pemula_basic' => 2,

    'fee_standart' => 350000,
    'fee_standart_extra' => 0,
    'quota_std_basic' => 2,

    'fee_speed' => 400000,
    'fee_speed_extra' => 200000,
    'quota_speed_basic' => 2,

    'fee_eksebisi' => 150000,
    'allow_pemula_standart_mix' => 0
];

// Test 1: Standard Event Scenarios (Basic Quota = 2)
$testEntries = [
    // Skater 1: Pemula 2 nomor -> 250.000
    ['skater_id' => 1, 'skate_class_name' => 'Pemula A', 'distance_name' => '100m', 'team_name' => null],
    ['skater_id' => 1, 'skate_class_name' => 'Pemula A', 'distance_name' => '200m', 'team_name' => null],

    // Skater 2: Pemula 3 nomor -> 250.000 + 100.000 = 350.000
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '100m', 'team_name' => null],
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '200m', 'team_name' => null],
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '300m', 'team_name' => null],

    // Skater 3: Standard 2 nomor + 1 relay -> 350.000
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => '500m', 'team_name' => null],
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team 1', 'race_class_id' => 99],

    // Skater 4: Speed 2 nomor + 1 relay -> 400.000
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => '500m+D', 'team_name' => null],
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team Speed', 'race_class_id' => 98],

    // Skater 5: Speed 3 nomor + 1 relay -> 400.000 + 200.000 = 600.000
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => '500m+D', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => 'Eliminasi 5000m', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team Speed', 'race_class_id' => 98],
];

$res = RollFinanceHelper::calculateTotalTagihan($testEntries, $eventFees);

assert($res['skater_fees'][1] === 250000.0, "Skater 1 must be 250.000");
echo "✓ Skater 1 (Pemula 2 nomor): Rp " . number_format($res['skater_fees'][1], 0, ',', '.') . " [OK]\n";

assert($res['skater_fees'][2] === 350000.0, "Skater 2 must be 350.000");
echo "✓ Skater 2 (Pemula 3 nomor): Rp " . number_format($res['skater_fees'][2], 0, ',', '.') . " [OK]\n";

assert($res['skater_fees'][3] === 350000.0, "Skater 3 must be 350.000");
echo "✓ Skater 3 (Standar 2 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][3], 0, ',', '.') . " [OK]\n";

assert($res['skater_fees'][4] === 400000.0, "Skater 4 must be 400.000");
echo "✓ Skater 4 (Speed 2 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][4], 0, ',', '.') . " [OK]\n";

assert($res['skater_fees'][5] === 600000.0, "Skater 5 must be 600.000");
echo "✓ Skater 5 (Speed 3 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][5], 0, ',', '.') . " [OK]\n";

$expectedTotal = 250000 + 350000 + 350000 + 400000 + 600000;
assert($res['total_amount'] === (float)$expectedTotal, "Total amount mismatch");
echo "✓ Total Amount: Rp " . number_format($res['total_amount'], 0, ',', '.') . " [OK]\n";

// Test 2: Custom Basic Quota (e.g. Basic Quota = 1 for Speed)
$customFees = $eventFees;
$customFees['quota_speed_basic'] = 1;
// Skater with 2 races under 1-basic quota: 400.000 + 1 * 200.000 = 600.000
$customEntries = [
    ['skater_id' => 10, 'skate_class_name' => 'Speed KU B', 'distance_name' => '500m+D', 'team_name' => null],
    ['skater_id' => 10, 'skate_class_name' => 'Speed KU B', 'distance_name' => '1000m', 'team_name' => null],
];
$resCustom = RollFinanceHelper::calculateTotalTagihan($customEntries, $customFees);
assert($resCustom['skater_fees'][10] === 600000.0, "Custom 1-basic quota must charge 600.000 for 2 races");
echo "✓ Custom Quota Flexibility (Speed Basic = 1, takes 2 races): Rp " . number_format($resCustom['skater_fees'][10], 0, ',', '.') . " [OK]\n";

echo "--- ALL TESTS PASSED SUCCESSFULLY! ---\n";

