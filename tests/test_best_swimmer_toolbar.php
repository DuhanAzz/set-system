<?php

// Test draw grouping extraction and detail_best_swimmer logic
$sampleGroupedAthleteData = [
    'KU OPEN' => [
        'L' => [
            [
                'entity_name'      => 'FAIQ ARYA CETTA',
                'gold'             => 2,
                'silver'           => 0,
                'bronze'           => 0,
                'total'            => 2,
                'is_draw'          => false,
            ],
            [
                'entity_name'      => 'AGASTYA DAMAR PRAMOEDYA',
                'gold'             => 1,
                'silver'           => 1,
                'bronze'           => 0,
                'total'            => 2,
                'is_draw'          => true,
                'is_draw_head'     => true,
                'draw_count'       => 2,
                'draw_swimmer_ids' => '1783,1786',
            ],
            [
                'entity_name'      => 'ALDREAN FAJAR PUTRA HARYONO',
                'gold'             => 1,
                'silver'           => 1,
                'bronze'           => 0,
                'total'            => 2,
                'is_draw'          => true,
                'is_draw_head'     => false,
            ],
            [
                'entity_name'      => 'MOCHAMMAD AKSA PRADYPTA',
                'gold'             => 1,
                'silver'           => 0,
                'bronze'           => 0,
                'total'            => 1,
                'is_draw'          => true,
                'is_draw_head'     => true,
                'draw_count'       => 3,
                'draw_swimmer_ids' => '1790,1791,1792',
            ],
            [
                'entity_name'      => 'SATRIA FADHIL HISBULLAH',
                'gold'             => 1,
                'silver'           => 0,
                'bronze'           => 0,
                'total'            => 1,
                'is_draw'          => true,
                'is_draw_head'     => false,
            ],
            [
                'entity_name'      => 'ARGA RIZKY SYAPUTRA',
                'gold'             => 1,
                'silver'           => 0,
                'bronze'           => 0,
                'total'            => 1,
                'is_draw'          => true,
                'is_draw_head'     => false,
            ],
        ],
    ],
];

// Replicate toolbar draw groups collector
$allDrawGroups = [];
foreach ($sampleGroupedAthleteData as $kuKey => $gendersMap) {
    foreach ($gendersMap as $gKey => $rowsList) {
        foreach ($rowsList as $r) {
            if (!empty($r['is_draw_head']) && !empty($r['draw_swimmer_ids'])) {
                $allDrawGroups[] = [
                    'ku'          => $kuKey,
                    'gender'      => ($gKey === 'L' ? 'Putra' : ($gKey === 'P' ? 'Putri' : $gKey)),
                    'medals'      => $r['gold'] . 'E ' . $r['silver'] . 'P ' . $r['bronze'] . 'P',
                    'gold'        => (int)$r['gold'],
                    'silver'      => (int)$r['silver'],
                    'bronze'      => (int)$r['bronze'],
                    'total'       => (int)$r['total'],
                    'count'       => (int)($r['draw_count'] ?? 2),
                    'swimmer_ids' => $r['draw_swimmer_ids'],
                ];
            }
        }
    }
}

assert(count($allDrawGroups) === 2, "Expected 2 draw groups");
assert($allDrawGroups[0]['swimmer_ids'] === '1783,1786', "First draw group must be 1783,1786");
assert($allDrawGroups[0]['count'] === 2, "First draw group count must be 2");
assert($allDrawGroups[1]['swimmer_ids'] === '1790,1791,1792', "Second draw group must be 1790,1791,1792");

// Test query string generation
$primaryGroupUrl = '/swim/admin/medal_tally/detail_best_swimmer?event_id=41&team_source=school&swimmers=' . urlencode($allDrawGroups[0]['swimmer_ids']);
assert(strpos($primaryGroupUrl, 'swimmers=1783%2C1786') !== false, "URL must contain swimmers=1783%2C1786");

// Test detail_best_swimmer fallback when swimmers param is omitted
$rawSwimmers = [];
$swimmerIds = array_values(array_unique(array_filter(array_map('intval', $rawSwimmers), fn($v) => $v > 0)));
if (empty($swimmerIds) && !empty($allDrawGroups)) {
    $rawSwimmers = explode(',', $allDrawGroups[0]['swimmer_ids']);
    $swimmerIds = array_values(array_unique(array_filter(array_map('intval', $rawSwimmers), fn($v) => $v > 0)));
}
assert($swimmerIds === [1783, 1786], "Fallback swimmerIds must be [1783, 1786]");

echo "All tests passed successfully!\n";
