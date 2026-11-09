<?php

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

echo "Running tests for Gaya prefix naming standarisasi...\n\n";

$testCases = [
    // 1. Standar 4 Gaya Renang (Upper & Title case)
    ['BEBAS', 'GAYA BEBAS'],
    ['Bebas', 'Gaya Bebas'],
    ['DADA', 'GAYA DADA'],
    ['Dada', 'Gaya Dada'],
    ['PUNGGUNG', 'GAYA PUNGGUNG'],
    ['Punggung', 'Gaya Punggung'],
    ['KUPU-KUPU', 'GAYA KUPU-KUPU'],
    ['KUPU - KUPU', 'GAYA KUPU-KUPU'],
    ['Kupu-kupu', 'Gaya Kupu-kupu'],
    ['Kupu - kupu', 'Gaya Kupu-kupu'],

    // 2. Idempotent check (tidak boleh menghasilkan duplikasi seperti GAYA GAYA)
    ['GAYA BEBAS', 'GAYA BEBAS'],
    ['Gaya Bebas', 'Gaya Bebas'],
    ['GAYA DADA', 'GAYA DADA'],
    ['Gaya Dada', 'Gaya Dada'],
    ['GAYA PUNGGUNG', 'GAYA PUNGGUNG'],
    ['Gaya Punggung', 'Gaya Punggung'],
    ['GAYA KUPU-KUPU', 'GAYA KUPU-KUPU'],
    ['Gaya Kupu-kupu', 'Gaya Kupu-kupu'],

    // 3. Papan Kaki
    ['25 M PAPAN KAKI BEBAS PUTRA - LCM', '25 M PAPAN KAKI GAYA BEBAS PUTRA - LCM'],
    ['25 M PAPAN KAKI DADA PUTRI - LCM', '25 M PAPAN KAKI GAYA DADA PUTRI - LCM'],
    ['25 M PAPAN KAKI PUNGGUNG PUTRA - LCM', '25 M PAPAN KAKI GAYA PUNGGUNG PUTRA - LCM'],
    ['25 M PAPAN KAKI KUPU-KUPU PUTRA - LCM', '25 M PAPAN KAKI GAYA KUPU-KUPU PUTRA - LCM'],
    ['25 M PAPAN KAKI KUPU - KUPU PUTRA - LCM', '25 M PAPAN KAKI GAYA KUPU-KUPU PUTRA - LCM'],
    ['25 M PAPAN KAKI GAYA KUPU-KUPU PUTRA - LCM', '25 M PAPAN KAKI GAYA KUPU-KUPU PUTRA - LCM'],

    // 4. Nomor Lomba Biasa
    ['25 M BEBAS PUTRA - LCM', '25 M GAYA BEBAS PUTRA - LCM'],
    ['50 M DADA PUTRI - LCM', '50 M GAYA DADA PUTRI - LCM'],
    ['100 M PUNGGUNG PUTRA - LCM', '100 M GAYA PUNGGUNG PUTRA - LCM'],
    ['50 M KUPU-KUPU PUTRI - LCM', '50 M GAYA KUPU-KUPU PUTRI - LCM'],
    ['25 M GAYA BEBAS PUTRA - LCM', '25 M GAYA BEBAS PUTRA - LCM'],

    // 5. Estafet
    ['4 x 25 M ESTAFET BEBAS PUTRA', '4 x 25 M ESTAFET GAYA BEBAS PUTRA'],
    ['4 x 25 M ESTAFET GAYA BEBAS PUTRA', '4 x 25 M ESTAFET GAYA BEBAS PUTRA'],
    ['4 x 50 M ESTAFET DADA PUTRI', '4 x 50 M ESTAFET GAYA DADA PUTRI']
];

$passCount = 0;
$failCount = 0;

foreach ($testCases as $idx => [$input, $expected]) {
    $actual = SwimEventHelper::ensureGayaPrefix($input);
    if ($actual === $expected) {
        $passCount++;
        echo " [PASS] #{$idx}: '{$input}' => '{$actual}'\n";
    } else {
        $failCount++;
        echo " [FAIL] #{$idx}: '{$input}'\n        Expected: '{$expected}'\n        Actual:   '{$actual}'\n";
    }
}

// Test formatStrokeName
$strokeTests = [
    ['Bebas', 'Gaya Bebas'],
    ['BEBAS', 'GAYA BEBAS'],
    ['Papan Bebas', 'Papan Kaki Gaya Bebas'],
    ['PAPAN BEBAS', 'PAPAN KAKI GAYA BEBAS'],
    ['Papan Kaki Bebas', 'Papan Kaki Gaya Bebas'],
    ['Papan Kaki Kupu-kupu', 'Papan Kaki Gaya Kupu-kupu'],
    ['PAPAN KAKI KUPU-KUPU', 'PAPAN KAKI GAYA KUPU-KUPU'],
    ['Estafet Bebas', 'Estafet Gaya Bebas'],
];

echo "\nTesting formatStrokeName...\n";
foreach ($strokeTests as $idx => [$input, $expected]) {
    $actual = SwimEventHelper::formatStrokeName($input);
    if ($actual === $expected) {
        $passCount++;
        echo " [PASS] stroke #{$idx}: '{$input}' => '{$actual}'\n";
    } else {
        $failCount++;
        echo " [FAIL] stroke #{$idx}: '{$input}'\n        Expected: '{$expected}'\n        Actual:   '{$actual}'\n";
    }
}

// Test formatEventName
$eventNameTests = [
    ['25 M PAPAN KUPU-KUPU PUTRA - LCM', false, 25, '25 M PAPAN KAKI GAYA KUPU-KUPU PUTRA - LCM'],
    ['25 M BEBAS PUTRA - LCM', false, 25, '25 M GAYA BEBAS PUTRA - LCM'],
    ['100 M ESTAFET BEBAS PUTRA', true, 100, '4 x 25 M ESTAFET GAYA BEBAS PUTRA'],
    ['4 x 25 M ESTAFET GAYA BEBAS PUTRA', true, 100, '4 x 25 M ESTAFET GAYA BEBAS PUTRA'],
];

echo "\nTesting formatEventName...\n";
foreach ($eventNameTests as $idx => [$input, $isRelay, $dist, $expected]) {
    $actual = SwimEventHelper::formatEventName($input, $isRelay, $dist);
    if ($actual === $expected) {
        $passCount++;
        echo " [PASS] event #{$idx}: '{$input}' => '{$actual}'\n";
    } else {
        $failCount++;
        echo " [FAIL] event #{$idx}: '{$input}'\n        Expected: '{$expected}'\n        Actual:   '{$actual}'\n";
    }
}

echo "\n----------------------------------------\n";
echo "Total passed: {$passCount}, Failed: {$failCount}\n";

if ($failCount > 0) {
    exit(1);
}

echo "All tests passed successfully!\n";
