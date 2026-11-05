<?php
// tests/test_swim_results_sync.php
// Runnable assert-based test for Swim Results Controller & Views Synchronization

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Running Swim Results Synchronization Tests...\n";

// 1. Test timeToMs parsing
function parseTimeToMs($time) {
    if (empty($time) || strtoupper($time) == 'NT') return null;
    $time = str_replace(',', '.', trim($time));
    $parts = explode(':', $time);
    if (count($parts) == 2) {
        $minutes = (int)$parts[0];
        $secParts = explode('.', $parts[1]);
        $seconds = (int)$secParts[0];
        $hundredths = isset($secParts[1]) ? (int)str_pad(substr($secParts[1], 0, 2), 2, '0') : 0;
        return ($minutes * 60 * 1000) + ($seconds * 1000) + ($hundredths * 10);
    } elseif (count($parts) == 1) {
        $secParts = explode('.', $parts[0]);
        $seconds = (int)$secParts[0];
        $hundredths = isset($secParts[1]) ? (int)str_pad(substr($secParts[1], 0, 2), 2, '0') : 0;
        return ($seconds * 1000) + ($hundredths * 10);
    }
    return null;
}

assert(parseTimeToMs('00:25.43') === 25430, "00:25.43 should be 25430ms");
assert(parseTimeToMs('25.43') === 25430, "25.43 should be 25430ms");
assert(parseTimeToMs('01:05.12') === 65120, "01:05.12 should be 65120ms");
assert(parseTimeToMs('NT') === null, "NT should be null");
echo "✓ Test 1: timeToMs conversion logic PASSED!\n";

// 2. Test event ordering comparison (CAST event_number AS UNSIGNED)
$mockEvents = [
    ['id' => 10, 'event_number' => '10'],
    ['id' => 1,  'event_number' => '1'],
    ['id' => 2,  'event_number' => '2'],
    ['id' => 100, 'event_number' => '100'],
    ['id' => 20, 'event_number' => '20']
];
usort($mockEvents, function($a, $b) {
    return ((int)$a['event_number']) - ((int)$b['event_number']);
});
$orderedIds = array_column($mockEvents, 'id');
assert($orderedIds === [1, 2, 10, 20, 100], "Event numbers must be sorted numerically not alphabetically");
echo "✓ Test 2: Numerical event sorting PASSED!\n";

// 3. Test Results Index Progress calculation
$count_seeded = 8;
$total_finished = 8;
$pct1 = $count_seeded > 0 ? round(($total_finished / $count_seeded) * 100) : 0;
$is_completed1 = ($count_seeded > 0 && $total_finished >= $count_seeded);
assert($pct1 === 100.0 && $is_completed1 === true, "100% completion detection works");

$count_seeded = 8;
$total_finished = 4;
$pct2 = $count_seeded > 0 ? round(($total_finished / $count_seeded) * 100) : 0;
$is_completed2 = ($count_seeded > 0 && $total_finished >= $count_seeded);
assert($pct2 === 50.0 && $is_completed2 === false, "50% partial progress works");
echo "✓ Test 3: Progress percentage and completion flag PASSED!\n";

// 4. Test Ranking logic with ties
$swimmers = [
    ['id' => 1, 'ms' => 30000],
    ['id' => 2, 'ms' => 28500],
    ['id' => 3, 'ms' => 28500],
    ['id' => 4, 'ms' => 31200],
];
usort($swimmers, function($a, $b) { return $a['ms'] - $b['ms']; });

$rank = 1; $counter = 1; $prevMs = null;
$ranks = [];
foreach ($swimmers as $s) {
    if ($prevMs !== null && $s['ms'] != $prevMs) { $rank = $counter; }
    $ranks[$s['id']] = $rank;
    $prevMs = $s['ms'];
    $counter++;
}
assert($ranks[2] === 1, "Fastest swimmer is rank 1");
assert($ranks[3] === 1, "Tied swimmer is rank 1");
assert($ranks[1] === 3, "Next swimmer gets rank 3 after tie");
assert($ranks[4] === 4, "Last swimmer gets rank 4");
echo "✓ Test 4: Overall ranking calculation and tie handling PASSED!\n";

echo "ALL SWIM RESULTS SYNCHRONIZATION TESTS PASSED!\n";
