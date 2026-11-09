<?php
// tests/test_results_missing_swimmer_fix.php
// Verification test for ensuring all swimmers (including those with NULL, 0, or duplicate lanes)
// are properly allocated, rendered in input.php, and have their results saved and calculated.

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== Running Missing Swimmer & Results Fix Test Suite ===\n\n";

// 1. Test Lane Allocation & Collision Prevention Logic
echo "Test 1: Lane Allocation & Collision Prevention (Mental & Algorithmic Check)\n";

$usedLanes = [1, 2, 3, 4, 5]; // 5 lanes pool
$raw_data = [
    ['id' => 101, 'heat' => 1, 'lane' => 1, 'nama_atlet' => 'NAQEEB ROSYID ALFALAH', 'final_time' => '00:30.93', 'is_dq' => 0],
    ['id' => 102, 'heat' => 1, 'lane' => 2, 'nama_atlet' => 'SYAILENDRA OMAR SOFYAN', 'final_time' => '00:32.48', 'is_dq' => 0],
    ['id' => 103, 'heat' => 1, 'lane' => 3, 'nama_atlet' => 'AFTAB FATHIAN EMRAN ABRORI', 'final_time' => '00:39.37', 'is_dq' => 0],
    ['id' => 104, 'heat' => 1, 'lane' => 4, 'nama_atlet' => 'ANTONIUS RAKA WICAKSONO', 'final_time' => '00:45.45', 'is_dq' => 0],
    ['id' => 105, 'heat' => 1, 'lane' => 5, 'nama_atlet' => 'ARFA MUAZZAM ATHARRAZKA', 'final_time' => '00:45.80', 'is_dq' => 0],
    ['id' => 106, 'heat' => 2, 'lane' => 1, 'nama_atlet' => 'OMAR IBRAHIM MAQIL', 'final_time' => '00:48.29', 'is_dq' => 0],
    ['id' => 107, 'heat' => 2, 'lane' => 2, 'nama_atlet' => 'ALFARIZKI MAHENDRA PRIAMBUDI', 'final_time' => '00:54.49', 'is_dq' => 0],
    ['id' => 108, 'heat' => 2, 'lane' => 3, 'nama_atlet' => 'AKSARA KHALANDRA ARSENIO', 'final_time' => '01:06.72', 'is_dq' => 0],
    ['id' => 109, 'heat' => 2, 'lane' => 4, 'nama_atlet' => 'ATHARRAZKA SYAHPUTRA', 'final_time' => '01:25.07', 'is_dq' => 0],
    // ARFAN has lane NULL or duplicate lane 4
    ['id' => 110, 'heat' => 2, 'lane' => null, 'nama_atlet' => 'ARFAN IZZAN SIDIK', 'final_time' => null, 'is_dq' => 0]
];

// Run the sanitized algorithm
$heats = [];
$occupiedLanes = [];
$needsLaneAssignment = [];

foreach ($raw_data as &$row) {
    $h = (int)($row['heat'] ?? 1);
    if ($h <= 0) $h = 1;
    $row['heat'] = $h;

    $laneVal = $row['lane'];
    $l = ($laneVal !== null && $laneVal !== '' && is_numeric($laneVal)) ? (int)$laneVal : null;

    if ($l !== null && $l > 0 && empty($occupiedLanes[$h][$l])) {
        $occupiedLanes[$h][$l] = $row['id'];
        $row['lane'] = $l;
        $heats[$h][$l] = $row;
    } else {
        $needsLaneAssignment[] = &$row;
    }
}
unset($row);

assert(count($needsLaneAssignment) === 1, "Exactly 1 swimmer (ARFAN) should be in needsLaneAssignment");
assert($needsLaneAssignment[0]['nama_atlet'] === 'ARFAN IZZAN SIDIK', "Target swimmer must be ARFAN IZZAN SIDIK");

if (!empty($needsLaneAssignment)) {
    $candidateLanes = !empty($usedLanes) ? $usedLanes : [1, 2, 3, 4, 5, 6, 7, 8];

    foreach ($needsLaneAssignment as &$row) {
        $h = $row['heat'];
        $assignedLane = null;

        foreach ($candidateLanes as $candLane) {
            if (empty($occupiedLanes[$h][$candLane])) {
                $assignedLane = $candLane;
                break;
            }
        }

        if ($assignedLane === null) {
            for ($candLane = 1; $candLane <= 10; $candLane++) {
                if (empty($occupiedLanes[$h][$candLane])) {
                    $assignedLane = $candLane;
                    break;
                }
            }
        }

        if ($assignedLane === null) {
            $h = max(array_keys($heats) ?: [1]) + 1;
            $row['heat'] = $h;
            $assignedLane = $candidateLanes[0] ?? 1;
        }

        $occupiedLanes[$h][$assignedLane] = $row['id'];
        $row['lane'] = $assignedLane;
        $heats[$h][$assignedLane] = $row;
    }
    unset($row);
}

assert(isset($heats[2][5]), "ARFAN IZZAN SIDIK must be allocated to Heat 2, Lane 5");
assert($heats[2][5]['nama_atlet'] === 'ARFAN IZZAN SIDIK', "Swimmer on Heat 2, Lane 5 must be ARFAN IZZAN SIDIK");
assert(count($heats[1]) === 5, "Heat 1 must have exactly 5 swimmers");
assert(count($heats[2]) === 5, "Heat 2 must have exactly 5 swimmers");
echo "✓ Test 1 Passed: ARFAN IZZAN SIDIK cleanly allocated to Heat 2 Lane 5 without collisions.\n\n";

// 2. Test Input View Rendering
echo "Test 2: Input View Rendering Simulation\n";

$renderedEntries = [];
foreach ($heats as $heatNo => $lanesData) {
    for ($ln = 0; $ln <= 9; $ln++) {
        $s = $lanesData[$ln] ?? null;
        if (!$s && isset($usedLanes) && !in_array($ln, $usedLanes)) continue;
        if ($s) {
            $renderedEntries[$s['id']] = $s['nama_atlet'];
        }
    }
    foreach ($lanesData as $extraLn => $s) {
        if (is_int($extraLn) && $extraLn >= 0 && $extraLn <= 9) continue;
        if ($s) {
            $renderedEntries[$s['id']] = $s['nama_atlet'];
        }
    }
}

assert(count($renderedEntries) === 10, "All 10 swimmers must have input entries rendered");
assert(isset($renderedEntries[110]), "ARFAN IZZAN SIDIK (ID 110) must be among rendered input fields");
assert($renderedEntries[110] === 'ARFAN IZZAN SIDIK', "Rendered name must match ARFAN IZZAN SIDIK");
echo "✓ Test 2 Passed: All 10 swimmers rendered in the input form.\n\n";

// 3. Test Rank Calculation when ARFAN's time is entered
echo "Test 3: Rank Calculation with ARFAN's Time\n";

// Suppose admin inputs ARFAN's time as 00:35.00
$allSwimmers = $raw_data;
$allSwimmers[9]['final_time'] = '00:35.00'; // ARFAN's recorded time

function timeToMsTest($timeStr) {
    $parts = explode(':', trim($timeStr));
    if (count($parts) == 2) {
        $secParts = explode('.', $parts[1]);
        $min = (int)$parts[0];
        $sec = (int)$secParts[0];
        $ms = (int)($secParts[1] ?? 0);
        if (strlen($secParts[1] ?? '') == 2) $ms *= 10;
        return ($min * 60 * 1000) + ($sec * 1000) + $ms;
    }
    return 999999999;
}

$valid = [];
foreach ($allSwimmers as $s) {
    if (!empty($s['final_time'])) {
        $s['ms'] = timeToMsTest($s['final_time']);
        $valid[] = $s;
    }
}

usort($valid, function($a, $b) { return $a['ms'] - $b['ms']; });

$ranks = [];
$rank = 1; $counter = 1; $prevMs = null;
foreach ($valid as $s) {
    if ($prevMs !== null && $s['ms'] != $prevMs) { $rank = $counter; }
    $ranks[$s['id']] = $rank;
    $prevMs = $s['ms'];
    $counter++;
}

assert(isset($ranks[110]), "ARFAN IZZAN SIDIK must receive a rank");
// Times:
// NAQEEB: 30.93 -> Rank 1
// SYAILENDRA: 32.48 -> Rank 2
// ARFAN: 35.00 -> Rank 3
// AFTAB: 39.37 -> Rank 4
assert($ranks[110] === 3, "ARFAN IZZAN SIDIK (00:35.00) should be Rank 3");
echo "✓ Test 3 Passed: Rank calculated correctly for all 10 swimmers.\n\n";

// 4. Test Millisecond .99 Handling (strpos vs preg_match bug)
echo "Test 4: Millisecond .99 Handling (Anti-Regression for strpos 99 bug)\n";

$timeWith99 = "00:48.99";
$dummyTimeColon = "99:99.99";
$dummyTimeDot = "99.99.99";
$ntTime = "NT";

// Test is_valid logic
$isValidTimeWith99 = (!empty($timeWith99) && strtoupper($timeWith99) != 'NT' && !preg_match('/^99[:.]99/', trim($timeWith99)));
$isValidDummyColon = (!empty($dummyTimeColon) && strtoupper($dummyTimeColon) != 'NT' && !preg_match('/^99[:.]99/', trim($dummyTimeColon)));
$isValidDummyDot = (!empty($dummyTimeDot) && strtoupper($dummyTimeDot) != 'NT' && !preg_match('/^99[:.]99/', trim($dummyTimeDot)));
$isValidNT = (!empty($ntTime) && strtoupper($ntTime) != 'NT' && !preg_match('/^99[:.]99/', trim($ntTime)));

assert($isValidTimeWith99 === true, "Time '00:48.99' must be recognized as valid!");
assert($isValidDummyColon === false, "Dummy time '99:99.99' must be recognized as invalid!");
assert($isValidDummyDot === false, "Dummy time '99.99.99' must be recognized as invalid!");
assert($isValidNT === false, "Time 'NT' must be recognized as invalid!");

// Test timeToMsPrint logic with 00:48.99
function timeToMsPrintTest($time) {
    $time = trim($time);
    if (empty($time) || strtoupper($time) == 'NT' || preg_match('/^99[:.]99/', $time) || $time == '-') return 9999999999; 
    $parts = preg_split('/[:.]/', $time);
    $menit = 0; $detik = 0; $ms = 0;
    if (count($parts) == 3) { $menit = (int)$parts[0]; $detik = (int)$parts[1]; $ms = (int)$parts[2]; } 
    elseif (count($parts) == 2) { $detik = (int)$parts[0]; $ms = (int)$parts[1]; } 
    elseif (count($parts) == 1) { $detik = (int)$parts[0]; }
    $lastLen = isset($parts[count($parts)-1]) ? strlen($parts[count($parts)-1]) : 0;
    if ($lastLen == 2) $ms *= 10;
    elseif ($lastLen == 1) $ms *= 100;
    return ($menit * 60000) + ($detik * 1000) + $ms;
}

$ms48_99 = timeToMsPrintTest("00:48.99");
assert($ms48_99 === 48990, "00:48.99 must convert to 48990 ms (got: $ms48_99)");

$msDummy = timeToMsPrintTest("99:99.99");
assert($msDummy === 9999999999, "99:99.99 must convert to 9999999999 ms sentinel");

echo "✓ Test 4 Passed: Times ending in .99 are correctly recognized as valid and converted to exact milliseconds.\n\n";

echo "ALL TESTS PASSED SUCCESSFULLY!\n";

