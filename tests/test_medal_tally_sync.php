<?php
// tests/test_medal_tally_sync.php
// Runnable assert-based test for Medal Tally and Best Swimmer Synchronization

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Running Medal Tally & Best Swimmer Synchronization Tests...\n";

// 1. Test timeToMs parsing
function parseTimeToMsMedal($time) {
    if (empty($time) || strtoupper($time) == 'NT') return 9999999999;
    $time = str_replace(',', '.', trim($time));
    if ($time == '99:99.99' || $time == '99.99.99' || $time == '-') return 9999999999;
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
    return 9999999999;
}

assert(parseTimeToMsMedal('00:25.43') === 25430, "00:25.43 should be 25430ms");
assert(parseTimeToMsMedal('25.43') === 25430, "25.43 should be 25430ms");
assert(parseTimeToMsMedal('01:05.12') === 65120, "01:05.12 should be 65120ms");
assert(parseTimeToMsMedal('NT') === 9999999999, "NT should be penalty ms");
assert(parseTimeToMsMedal('-') === 9999999999, "- should be penalty ms");
echo "✓ Test 1: timeToMs logic PASSED!\n";

// 2. Test Olympic Medal Tally sorting
$teams = [
    ['entity_name' => 'Club Alpha', 'gold' => 2, 'silver' => 3, 'bronze' => 1],
    ['entity_name' => 'Club Beta',  'gold' => 3, 'silver' => 0, 'bronze' => 0],
    ['entity_name' => 'Club Gamma', 'gold' => 2, 'silver' => 3, 'bronze' => 2],
    ['entity_name' => 'Club Delta', 'gold' => 2, 'silver' => 3, 'bronze' => 1]
];

usort($teams, function($a, $b) {
    if ($a['gold'] != $b['gold']) return $b['gold'] - $a['gold'];
    if ($a['silver'] != $b['silver']) return $b['silver'] - $a['silver'];
    if ($a['bronze'] != $b['bronze']) return $b['bronze'] - $a['bronze'];
    return strcmp($a['entity_name'], $b['entity_name']);
});

assert($teams[0]['entity_name'] === 'Club Beta', "Club Beta with 3 Gold ranks 1st");
assert($teams[1]['entity_name'] === 'Club Gamma', "Club Gamma with 2G 3S 2B ranks 2nd");
assert($teams[2]['entity_name'] === 'Club Alpha', "Club Alpha with 2G 3S 1B ranks 3rd before Club Delta alphabetically");
assert($teams[3]['entity_name'] === 'Club Delta', "Club Delta with 2G 3S 1B ranks 4th");
echo "✓ Test 2: Olympic ranking order PASSED!\n";

// 3. Test KU Preservation Order in Best Swimmer
$available_kus = [
    ['group_name' => 'KU 1', 'min_age' => 15, 'max_age' => 17],
    ['group_name' => 'KU 2', 'min_age' => 13, 'max_age' => 14],
    ['group_name' => 'KU 3', 'min_age' => 11, 'max_age' => 12],
    ['group_name' => 'SENIOR', 'min_age' => 18, 'max_age' => 99]
];

$rawGrouped = [
    'SENIOR' => ['L' => [['name' => 'Senior Man']]],
    'KU 3'   => ['L' => [['name' => 'Kid 3']]],
    'KU 1'   => ['L' => [['name' => 'Teen 1']]],
    'KU 2'   => ['L' => [['name' => 'Kid 2']]],
    'UMUM'   => ['L' => [['name' => 'Open Man']]]
];

$ordered = [];
foreach ($available_kus as $ku) {
    $kName = strtoupper($ku['group_name']);
    if (isset($rawGrouped[$kName])) {
        $ordered[$kName] = $rawGrouped[$kName];
        unset($rawGrouped[$kName]);
    }
}
foreach ($rawGrouped as $kName => $val) {
    $ordered[$kName] = $val;
}

$keys = array_keys($ordered);
assert($keys === ['KU 1', 'KU 2', 'KU 3', 'SENIOR', 'UMUM'], "Official KU order must be preserved");
echo "✓ Test 3: KU Order Preservation PASSED!\n";

// 4. Test Relay Team Name Resolution
$relay1 = ['team_name' => 'Relay Team A', 'nama_klub' => 'Club Alpha'];
$relay2 = ['team_name' => '', 'nama_klub' => 'Club Alpha'];
$relay3 = ['team_name' => null, 'nama_klub' => null];

function resolveTeamName($r) {
    return (!empty($r['team_name']) ? $r['team_name'] : (!empty($r['nama_klub']) ? $r['nama_klub'] : 'RELAY TEAM'));
}

assert(resolveTeamName($relay1) === 'Relay Team A', "Uses specific relay team_name if present");
assert(resolveTeamName($relay2) === 'Club Alpha', "Falls back to nama_klub if team_name empty");
assert(resolveTeamName($relay3) === 'RELAY TEAM', "Falls back to default RELAY TEAM if both empty");
echo "✓ Test 4: Relay Team Name fallback PASSED!\n";

echo "ALL MEDAL TALLY SYNCHRONIZATION TESTS PASSED!\n";
