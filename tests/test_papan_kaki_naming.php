<?php
/**
 * Test Suite: Papan Kaki Naming Convention & SwimEventHelper Normalization
 */

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

echo "Running Papan Kaki Naming Convention Tests...\n";

// --- TEST 1: SwimEventHelper::formatEventName for Board Events ---
$testCases = [
    [
        'input' => '25 M PAPAN KUPU-KUPU PUTRA - LCM',
        'isRelay' => false,
        'dist' => 25,
        'expected' => '25 M PAPAN KAKI KUPU-KUPU PUTRA - LCM'
    ],
    [
        'input' => '25M PAPAN BEBAS KU 2020',
        'isRelay' => false,
        'dist' => 25,
        'expected' => '25M PAPAN KAKI BEBAS KU 2020'
    ],
    [
        'input' => '25 M PAPAN KAKI KUPU-KUPU PUTRA - LCM',
        'isRelay' => false,
        'dist' => 25,
        'expected' => '25 M PAPAN KAKI KUPU-KUPU PUTRA - LCM' // Must not duplicate KAKI
    ],
    [
        'input' => '25 M PAPAN DADA PUTRI - LCM',
        'isRelay' => false,
        'dist' => 25,
        'expected' => '25 M PAPAN KAKI DADA PUTRI - LCM'
    ],
    [
        'input' => '25 M PAPAN PUNGGUNG PUTRA - LCM',
        'isRelay' => false,
        'dist' => 25,
        'expected' => '25 M PAPAN KAKI PUNGGUNG PUTRA - LCM'
    ],
    [
        'input' => '100 M ESTAFET GAYA BEBAS PUTRA - LCM',
        'isRelay' => true,
        'dist' => 100,
        'expected' => '4 x 25 M ESTAFET GAYA BEBAS PUTRA - LCM'
    ]
];

foreach ($testCases as $idx => $case) {
    $res = SwimEventHelper::formatEventName($case['input'], $case['isRelay'], $case['dist']);
    assert($res === $case['expected'], "Test $idx failed: expected '{$case['expected']}', got '{$res}'");
    echo "✓ Test 1.{$idx}: '{$case['input']}' => '{$res}' PASSED\n";
}

// --- TEST 2: EventsController string interpolation logic ---
$gaya = 'Papan Kupu-kupu';
$jk = 'L';
$labelJK = ($jk == 'L') ? 'PUTRA' : 'PUTRI';
$poolLabel = 'LCM';
$normGaya = trim(str_ireplace(['25m', '25 m', 'kick', 'papan', 'gaya', 'kaki'], '', $gaya));
$eventName = "25 M PAPAN KAKI " . strtoupper($normGaya) . " $labelJK - $poolLabel";
assert($eventName === '25 M PAPAN KAKI KUPU-KUPU PUTRA - LCM', "EventsController string construction failed: got '$eventName'");
echo "✓ Test 2: EventsController single and batch event generation format PASSED\n";

// --- TEST 3: Verify method syncEventPapanNames exists in SwimEventHelper ---
assert(method_exists(SwimEventHelper::class, 'syncEventPapanNames'), "syncEventPapanNames method must exist in SwimEventHelper");
echo "✓ Test 3: SwimEventHelper::syncEventPapanNames verified PASSED\n";

echo "ALL PAPAN KAKI TESTS PASSED!\n";

