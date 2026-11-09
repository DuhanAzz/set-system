<?php
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';

use App\Helpers\RollFinanceHelper;

echo "--- RUNNING TEST: ROLL EXTRA RACE FEES & SUMMARY ---\n";

$eventFees = [
    'fee_pemula' => 250000,
    'fee_pemula_extra' => 100000,
    'fee_standart' => 350000,
    'fee_standart_extra' => 0,
    'fee_speed' => 400000,
    'fee_speed_extra' => 200000,
    'fee_eksebisi' => 150000,
    'allow_pemula_standart_mix' => 0
];

// 1. Skater 1: Pemula Basic (2 nomor: 100m, 200m)
// 2. Skater 2: Pemula Tambahan (3 nomor: 100m, 200m, 300m)
// 3. Skater 3: Standard Basic (2 nomor individual + 1 relay)
// 4. Skater 4: Speed Basic (2 nomor individual + 1 relay)
// 5. Skater 5: Speed Tambahan (3 nomor individual + 1 relay)
$testEntries = [
    // Skater 1: Pemula 2 nomor
    ['skater_id' => 1, 'skate_class_name' => 'Pemula A', 'distance_name' => '100m', 'team_name' => null],
    ['skater_id' => 1, 'skate_class_name' => 'Pemula A', 'distance_name' => '200m', 'team_name' => null],

    // Skater 2: Pemula 3 nomor
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '100m', 'team_name' => null],
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '200m', 'team_name' => null],
    ['skater_id' => 2, 'skate_class_name' => 'Pemula B', 'distance_name' => '300m', 'team_name' => null],

    // Skater 3: Standard 2 nomor + 1 relay
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => '500m', 'team_name' => null],
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 3, 'skate_class_name' => 'Standar KU A', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team 1', 'race_class_id' => 99],

    // Skater 4: Speed 2 nomor + 1 relay
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => '500m+D', 'team_name' => null],
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 4, 'skate_class_name' => 'Speed KU B', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team Speed', 'race_class_id' => 98],

    // Skater 5: Speed 3 nomor + 1 relay
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => '500m+D', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => '1000m', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => 'Eliminasi 5000m', 'team_name' => null],
    ['skater_id' => 5, 'skate_class_name' => 'Speed KU C', 'distance_name' => 'Relay 3000m', 'team_name' => 'Klub A Team Speed', 'race_class_id' => 98],
];

$res = RollFinanceHelper::calculateTotalTagihan($testEntries, $eventFees);

// Assertions
// Skater 1: Rp 250.000
assert($res['skater_fees'][1] === 250000.0, "Skater 1 fee must be 250.000, got " . $res['skater_fees'][1]);
echo "✓ Skater 1 (Pemula 2 nomor): Rp " . number_format($res['skater_fees'][1], 0, ',', '.') . " [OK]\n";

// Skater 2: Rp 350.000
assert($res['skater_fees'][2] === 350000.0, "Skater 2 fee must be 350.000 (250k + 100k extra), got " . $res['skater_fees'][2]);
echo "✓ Skater 2 (Pemula 3 nomor): Rp " . number_format($res['skater_fees'][2], 0, ',', '.') . " [OK]\n";

// Skater 3: Rp 350.000
assert($res['skater_fees'][3] === 350000.0, "Skater 3 fee must be 350.000, got " . $res['skater_fees'][3]);
echo "✓ Skater 3 (Standar 2 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][3], 0, ',', '.') . " [OK]\n";

// Skater 4: Rp 400.000
assert($res['skater_fees'][4] === 400000.0, "Skater 4 fee must be 400.000, got " . $res['skater_fees'][4]);
echo "✓ Skater 4 (Speed 2 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][4], 0, ',', '.') . " [OK]\n";

// Skater 5: Rp 600.000
assert($res['skater_fees'][5] === 600000.0, "Skater 5 fee must be 600.000 (400k + 200k extra), got " . $res['skater_fees'][5]);
echo "✓ Skater 5 (Speed 3 nomor + 1 relay gratis): Rp " . number_format($res['skater_fees'][5], 0, ',', '.') . " [OK]\n";

// Total: 250k + 350k + 350k + 400k + 600k = 1.950.000
$expectedTotal = 250000 + 350000 + 350000 + 400000 + 600000;
assert($res['total_amount'] === (float)$expectedTotal, "Total amount must be $expectedTotal, got " . $res['total_amount']);
echo "✓ Total Amount: Rp " . number_format($res['total_amount'], 0, ',', '.') . " [OK]\n";

// Summary Breakdown Check
assert($res['summary']['Pemula']['skaters'] === 2, "Pemula skaters count must be 2");
assert($res['summary']['Pemula']['extra_count'] === 1, "Pemula extra count must be 1");
assert($res['summary']['Pemula']['extra_amount'] === 100000.0, "Pemula extra amount must be 100.000");

assert($res['summary']['Speed']['skaters'] === 2, "Speed skaters count must be 2");
assert($res['summary']['Speed']['extra_count'] === 1, "Speed extra count must be 1");
assert($res['summary']['Speed']['extra_amount'] === 200000.0, "Speed extra amount must be 200.000");

assert($res['summary']['Standart']['skaters'] === 1, "Standart skaters count must be 1");
assert($res['summary']['Standart']['extra_count'] === 0, "Standart extra count must be 0");

echo "✓ Summary Breakdown verification: ALL CHECKS PASSED!\n";
echo "--- ALL TESTS PASSED SUCCESSFULLY! ---\n";

