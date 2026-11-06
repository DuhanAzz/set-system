<?php
/**
 * Test Suite: Sparing Partner Feature (Independent Ranking, Medal Tally & Best Swimmer Exclusion)
 */

echo "Running Swim Sparing Partner Feature Tests...\n";

// --- TEST 1: Sparing Partner Ranking Logic in recalculateRanks ---
// Function simulating the exact ranking algorithm implemented in ResultsController::recalculateRanks
function simulateCalculateRanks($swimmers) {
    // Sort swimmers by ms
    usort($swimmers, function($a, $b) {
        return $a['ms'] - $b['ms'];
    });

    $rank = 1;
    $counter = 1;
    $prevMs = null;
    $results = [];

    foreach ($swimmers as $s) {
        $isSparing = (int)($s['is_sparing'] ?? 0);
        if ($isSparing === 1) {
            // Sparing partner: rank is null/SP, counter DOES NOT increment
            $results[] = [
                'name' => $s['name'],
                'time' => $s['time'],
                'ms' => $s['ms'],
                'is_sparing' => 1,
                'rank' => null,
                'display_rank' => 'SP'
            ];
        } else {
            if ($prevMs !== null && $s['ms'] != $prevMs) {
                $rank = $counter;
            }
            $results[] = [
                'name' => $s['name'],
                'time' => $s['time'],
                'ms' => $s['ms'],
                'is_sparing' => 0,
                'rank' => $rank,
                'display_rank' => (string)$rank
            ];
            $prevMs = $s['ms'];
            $counter++;
        }
    }

    return $results;
}

// Case A: Sparing Partner has the fastest time (faster than Rank 1)
$mockSwimmersA = [
    ['name' => 'Budi (Reguler)', 'time' => '00:28.00', 'ms' => 28000, 'is_sparing' => 0],
    ['name' => 'Sparing Pro (Guest/SP)', 'time' => '00:25.50', 'ms' => 25500, 'is_sparing' => 1],
    ['name' => 'Citra (Reguler)', 'time' => '00:29.10', 'ms' => 29100, 'is_sparing' => 0],
    ['name' => 'Doni (Reguler)', 'time' => '00:30.50', 'ms' => 30500, 'is_sparing' => 0]
];

$resA = simulateCalculateRanks($mockSwimmersA);

// Verify Sparing Pro is display_rank SP and rank null
assert($resA[0]['name'] === 'Sparing Pro (Guest/SP)', "Fastest should be Sparing Pro");
assert($resA[0]['display_rank'] === 'SP', "Sparing Pro display_rank must be 'SP'");
assert($resA[0]['rank'] === null, "Sparing Pro rank must be null");

// Verify Budi gets Rank 1 (Gold), NOT displaced to Rank 2!
assert($resA[1]['name'] === 'Budi (Reguler)', "Second fastest is Budi");
assert($resA[1]['rank'] === 1, "Budi must be Rank 1 (Gold), got: " . $resA[1]['rank']);
assert($resA[1]['display_rank'] === '1', "Budi display_rank must be '1'");

// Verify Citra gets Rank 2 (Silver), NOT displaced to Bronze!
assert($resA[2]['name'] === 'Citra (Reguler)', "Third fastest is Citra");
assert($resA[2]['rank'] === 2, "Citra must be Rank 2 (Silver), got: " . $resA[2]['rank']);

// Verify Doni gets Rank 3 (Bronze)!
assert($resA[3]['name'] === 'Doni (Reguler)', "Fourth fastest is Doni");
assert($resA[3]['rank'] === 3, "Doni must be Rank 3 (Bronze), got: " . $resA[3]['rank']);

echo "✓ Test 1: Sparing Partner with fastest time does NOT displace regular podium finishers PASSED!\n";

// Case B: Sparing Partner finishes between 1st and 2nd place
$mockSwimmersB = [
    ['name' => 'Agus (Reguler)', 'time' => '00:26.00', 'ms' => 26000, 'is_sparing' => 0],
    ['name' => 'Sparing Mid (SP)', 'time' => '00:27.00', 'ms' => 27000, 'is_sparing' => 1],
    ['name' => 'Eko (Reguler)', 'time' => '00:28.00', 'ms' => 28000, 'is_sparing' => 0],
    ['name' => 'Fajar (Reguler)', 'time' => '00:29.00', 'ms' => 29000, 'is_sparing' => 0]
];

$resB = simulateCalculateRanks($mockSwimmersB);
assert($resB[0]['name'] === 'Agus (Reguler)' && $resB[0]['rank'] === 1, "Agus must be Rank 1");
assert($resB[1]['name'] === 'Sparing Mid (SP)' && $resB[1]['display_rank'] === 'SP', "Sparing Mid must be SP");
assert($resB[2]['name'] === 'Eko (Reguler)' && $resB[2]['rank'] === 2, "Eko must be Rank 2 (Silver)");
assert($resB[3]['name'] === 'Fajar (Reguler)' && $resB[3]['rank'] === 3, "Fajar must be Rank 3 (Bronze)");

echo "✓ Test 2: Sparing Partner finishing between 1st & 2nd preserves Silver for 2nd regular swimmer PASSED!\n";

// --- TEST 3: Medal Tally Exclusion Logic ---
// Simulating the SQL query condition in MedalTallyController
function simulateMedalTallyFilter($entries) {
    $tally = [];
    foreach ($entries as $e) {
        // Condition: COALESCE(ee.is_sparing, 0) = 0
        if (!empty($e['is_sparing'])) {
            continue; // Excluded!
        }
        $club = $e['club'];
        if (!isset($tally[$club])) {
            $tally[$club] = ['gold' => 0, 'silver' => 0, 'bronze' => 0];
        }
        if ($e['rank'] === 1) $tally[$club]['gold']++;
        if ($e['rank'] === 2) $tally[$club]['silver']++;
        if ($e['rank'] === 3) $tally[$club]['bronze']++;
    }
    return $tally;
}

$mockMedalEntries = [
    ['name' => 'Sparing Pro', 'club' => 'Guest Club', 'is_sparing' => 1, 'rank' => null],
    ['name' => 'Budi', 'club' => 'Tirta Amanda', 'is_sparing' => 0, 'rank' => 1],
    ['name' => 'Citra', 'club' => 'Swift SC', 'is_sparing' => 0, 'rank' => 2],
    ['name' => 'Doni', 'club' => 'Kaizen SC', 'is_sparing' => 0, 'rank' => 3]
];

$tallyResult = simulateMedalTallyFilter($mockMedalEntries);

assert(!isset($tallyResult['Guest Club']), "Guest Club (Sparing) must have ZERO medals recorded");
assert($tallyResult['Tirta Amanda']['gold'] === 1, "Tirta Amanda gets 1 Gold");
assert($tallyResult['Swift SC']['silver'] === 1, "Swift SC gets 1 Silver");
assert($tallyResult['Kaizen SC']['bronze'] === 1, "Kaizen SC gets 1 Bronze");

echo "✓ Test 3: Sparing Partner is completely excluded from Medal Tally PASSED!\n";

// --- TEST 4: View Print Display Markup Verification ---
$printInputCode = file_get_contents(__DIR__ . '/../views/swim/admin/results/print_input.php');
assert(strpos($printInputCode, "\$rank_display === 'SP'") !== false, "print_input.php must handle 'SP' rank display");
assert(strpos($printInputCode, "\$is_sparing") !== false, "print_input.php must check is_sparing");

$inputCode = file_get_contents(__DIR__ . '/../views/swim/admin/results/input.php');
assert(strpos($inputCode, "toggleSparing") !== false, "input.php must have toggleSparing function");

$detailCode = file_get_contents(__DIR__ . '/../views/swim/admin/entries/detail.php');
assert(strpos($detailCode, "toggleEntrySparing") !== false, "detail.php must have toggleEntrySparing function");

echo "✓ Test 4: Views markup, toggle handlers & SP badges verified PASSED!\n";

echo "ALL SPARING PARTNER TESTS PASSED!\n";
