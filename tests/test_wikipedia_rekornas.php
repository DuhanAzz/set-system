<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

function assertTrueW(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

$sampleWikitext = <<<'WIKI'
==Long Course (50 m)==
===Men===
{{Swimmingrecordlisttop|meet=yes|club=yes}}
{{Swimmingrecord
|eventsort = 01
|event = 50 m freestyle
|time = 22.66
|recordinfo = 
|firstname = Triady Fauzi
|lastname = Sidiq
|date = 24 August 2017
|city = Kuala Lumpur
|country = MAS
}}
{{Swimmingrecord
|eventsort = 03
|event = 200 m freestyle
|time = 1:50.35
|recordinfo = 
|firstname = Joe
|lastname = Kurniawan
|date = 17 September 2024
|city = Medan
|country = INA
}}
{{Swimmingrecord
|eventsort = 03
|event = 200 m freestyle
|time = 1:49.89
|recordinfo = h, #
|firstname = Harrison
|lastname = Andoko
|date = 23 September 2026
|city = Tokyo
|country = JPN
}}
{{Swimmingrecord
|eventsort = 12
|event = 100m breaststroke
|time = 1:00.88
|recordinfo = 
|firstname = Arya Adrean Putra
|lastname = Haryono
|date = 29 April 2026
|city = Jakarta
|country = INA
}}
{{Swimmingrecord
|eventsort = 18
|event = 4×100m freestyle relay
|time = 3:20.84
|recordinfo = 
|firstname = Team
|lastname = Indonesia
|date = 24 September 2026
|city = Tokyo
}}
{{Swimmingrecordlistbottom}}
===Women===
{{Swimmingrecord
|eventsort = 13
|event = 200m breaststroke
|time = 2:32.09{{efn|by official result source}}
|recordinfo = 
|firstname = 
|lastname = Adellia
|date = 19 March 2025{{efn|note}}
|city = Singapore
}}
{{Swimmingrecord
|eventsort = 17
|event = 200m individual medley
|time = 2:16.43
|recordinfo = 
|firstname = Azzahra
|lastname = Permatahani
|date = 27 September 2019
|city = Jakarta
}}
===Mixed relay===
{{Swimmingrecord
|eventsort = 22
|event = 4×100 m freestyle relay
|time = 3:39.13
|recordinfo = 
|firstname = Team
|lastname = INA
|date = 30 October 2025
|city = Isa Town
}}
{{Swimmingrecord
|eventsort = 99
|event = 50 m freestyle
|time = 21.00
|recordinfo = 
|firstname = Should Be
|lastname = Ignored
|date = 30 October 2025
|city = Isa Town
}}
WIKI;

// 1. Format helper check
assertTrueW(SwimEventHelper::formatMsToMMSScc(22660) === '00:22.66', '22660ms -> 00:22.66');
assertTrueW(SwimEventHelper::formatMsToMMSScc(60880) === '01:00.88', '60880ms -> 01:00.88');
assertTrueW(SwimEventHelper::formatMsToMMSScc(915770) === '15:15.77', '915770ms -> 15:15.77');

// 2. Include pending (#) = true -> 200m bebas Putra should pick Harrison Andoko (1:49.89)
$withPending = SwimEventHelper::parseWikipediaRecordsWikitext($sampleWikitext, true);
assertTrueW(count($withPending) === 5, 'Should parse exactly 5 individual non-relay records across Men & Women (got ' . count($withPending) . ')');

$mapPending = [];
foreach ($withPending as $r) {
    $mapPending[$r['jk'] . '|' . $r['distance'] . '|' . $r['stroke']] = $r;
}

assertTrueW(isset($mapPending['L|50|Gaya Bebas']) && $mapPending['L|50|Gaya Bebas']['time'] === '00:22.66', '50m Bebas Pa formatted as 00:22.66');
assertTrueW(isset($mapPending['L|200|Gaya Bebas']) && $mapPending['L|200|Gaya Bebas']['holder'] === 'HARRISON ANDOKO' && $mapPending['L|200|Gaya Bebas']['is_pending'] === true, '200m Bebas Pa picks faster pending record when includePending=true');
assertTrueW(isset($mapPending['L|100|Gaya Dada']) && $mapPending['L|100|Gaya Dada']['time'] === '01:00.88' && $mapPending['L|100|Gaya Dada']['year'] === 2026, '100m Dada Pa -> 01:00.88 (2026)');
assertTrueW(isset($mapPending['P|200|Gaya Dada']) && $mapPending['P|200|Gaya Dada']['time'] === '02:32.09' && $mapPending['P|200|Gaya Dada']['holder'] === 'ADELLIA' && $mapPending['P|200|Gaya Dada']['year'] === 2025, 'Strips {{efn|...}} from time and date on Adellia');
assertTrueW(isset($mapPending['P|200|Gaya Ganti Perorangan']) && $mapPending['P|200|Gaya Ganti Perorangan']['time'] === '02:16.43', '200m Individual Medley maps to Gaya Ganti Perorangan');

// 3. Include pending (#) = false -> 200m bebas Putra falls back to ratified Joe Kurniawan (1:50.35)
$ratifiedOnly = SwimEventHelper::parseWikipediaRecordsWikitext($sampleWikitext, false);
$mapRatified = [];
foreach ($ratifiedOnly as $r) {
    $mapRatified[$r['jk'] . '|' . $r['distance'] . '|' . $r['stroke']] = $r;
}
assertTrueW($mapRatified['L|200|Gaya Bebas']['holder'] === 'JOE KURNIAWAN' && $mapRatified['L|200|Gaya Bebas']['time'] === '01:50.35', '200m Bebas Pa falls back to Joe Kurniawan when includePending=false');

echo "PASS: test_wikipedia_rekornas\n";
