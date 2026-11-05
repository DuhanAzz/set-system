<?php
// Test Suite: Same-Club Separation Algorithm for Relay Seeding

function separateRelayClubs(array $chunks, int $totalHeats, bool $isRelay): array {
    if ($totalHeats > 1 && $isRelay) {
        $maxPasses = 50;
        for ($pass = 0; $pass < $maxPasses; $pass++) {
            $foundConflict = false;
            
            for ($h1 = 0; $h1 < count($chunks); $h1++) {
                $clubCounts = [];
                foreach ($chunks[$h1] as $idx => $swimmer) {
                    $cid = (int)($swimmer['club_id'] ?? 0);
                    if ($cid > 0) {
                        $clubCounts[$cid][] = $idx;
                    }
                }
                
                foreach ($clubCounts as $cid => $indexes) {
                    if (count($indexes) > 1) {
                        $idxToMove = $indexes[count($indexes) - 1];
                        $teamToMove = $chunks[$h1][$idxToMove];
                        
                        $bestSwapH2 = -1;
                        $bestSwapIdx = -1;
                        $minTimeDiff = PHP_INT_MAX;
                        
                        for ($h2 = 0; $h2 < count($chunks); $h2++) {
                            if ($h1 === $h2) continue;
                            
                            $h2Clubs = [];
                            foreach ($chunks[$h2] as $other) {
                                $otherCid = (int)($other['club_id'] ?? 0);
                                if ($otherCid > 0) {
                                    $h2Clubs[$otherCid] = ($h2Clubs[$otherCid] ?? 0) + 1;
                                }
                            }
                            
                            if (($h2Clubs[$cid] ?? 0) < count($indexes) - 1) {
                                foreach ($chunks[$h2] as $idx2 => $cand) {
                                    $candCid = (int)($cand['club_id'] ?? 0);
                                    if ($candCid === 0 || empty($clubCounts[$candCid])) {
                                        $timeDiff = abs(($teamToMove['ms'] ?? 0) - ($cand['ms'] ?? 0));
                                        if ($timeDiff < $minTimeDiff) {
                                            $minTimeDiff = $timeDiff;
                                            $bestSwapH2 = $h2;
                                            $bestSwapIdx = $idx2;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ($bestSwapH2 !== -1 && $bestSwapIdx !== -1) {
                            $temp = $chunks[$h1][$idxToMove];
                            $chunks[$h1][$idxToMove] = $chunks[$bestSwapH2][$bestSwapIdx];
                            $chunks[$bestSwapH2][$bestSwapIdx] = $temp;
                            $foundConflict = true;
                            break 2;
                        }
                    }
                }
            }
            
            if (!$foundConflict) break;
        }
        
        foreach ($chunks as &$batchSwimmers) {
            usort($batchSwimmers, function($a, $b) {
                if ($a['ms'] != $b['ms']) {
                    return ($a['ms'] < $b['ms']) ? -1 : 1;
                }
                return 0;
            });
        }
        unset($batchSwimmers);
    }
    return $chunks;
}

echo "Running Relay Club Separation Seeding Tests...\n";

// Test 1: Two Clubs, Two Teams Each, Two Heats
$initialChunks1 = [
    [
        ['id' => 1, 'team' => 'Klub A Team 1', 'club_id' => 10, 'ms' => 60000],
        ['id' => 2, 'team' => 'Klub A Team 2', 'club_id' => 10, 'ms' => 60000],
    ],
    [
        ['id' => 3, 'team' => 'Klub B Team 1', 'club_id' => 20, 'ms' => 70000],
        ['id' => 4, 'team' => 'Klub B Team 2', 'club_id' => 20, 'ms' => 70000],
    ]
];

$res1 = separateRelayClubs($initialChunks1, 2, true);

// Verify heat 0 has no duplicate clubs
$heat0Clubs = array_column($res1[0], 'club_id');
assert(count($heat0Clubs) === count(array_unique($heat0Clubs)), "Heat 0 must have unique clubs");

// Verify heat 1 has no duplicate clubs
$heat1Clubs = array_column($res1[1], 'club_id');
assert(count($heat1Clubs) === count(array_unique($heat1Clubs)), "Heat 1 must have unique clubs");

echo "✓ Test 1: Two clubs with two teams each separated into different heats PASSED!\n";

// Test 2: One club with 2 teams, other clubs with 1 team each across 2 heats
$initialChunks2 = [
    [
        ['id' => 1, 'team' => 'Klub X Team 1', 'club_id' => 99, 'ms' => 50000],
        ['id' => 2, 'team' => 'Klub X Team 2', 'club_id' => 99, 'ms' => 52000],
    ],
    [
        ['id' => 3, 'team' => 'Klub Y Team 1', 'club_id' => 100, 'ms' => 65000],
        ['id' => 4, 'team' => 'Klub Z Team 1', 'club_id' => 101, 'ms' => 68000],
    ]
];

$res2 = separateRelayClubs($initialChunks2, 2, true);

$heat0Clubs2 = array_column($res2[0], 'club_id');
assert(count($heat0Clubs2) === count(array_unique($heat0Clubs2)), "Heat 0 must have unique clubs for Test 2");

$heat1Clubs2 = array_column($res2[1], 'club_id');
assert(count($heat1Clubs2) === count(array_unique($heat1Clubs2)), "Heat 1 must have unique clubs for Test 2");

// Verify Klub 99 has exactly 1 team in heat 0 and 1 team in heat 1
$countKlubXInHeat0 = count(array_filter($res2[0], fn($t) => $t['club_id'] === 99));
$countKlubXInHeat1 = count(array_filter($res2[1], fn($t) => $t['club_id'] === 99));
assert($countKlubXInHeat0 === 1 && $countKlubXInHeat1 === 1, "Klub X must have exactly 1 team per heat");
echo "✓ Test 2: Single club with 2 teams distributed across heats PASSED!\n";

echo "ALL RELAY SEEDING CLUB SEPARATION TESTS PASSED!\n";

