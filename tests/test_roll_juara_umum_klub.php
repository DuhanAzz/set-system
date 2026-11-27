<?php

require_once __DIR__ . '/../app/Helpers/RollMedalHelper.php';

use App\Helpers\RollMedalHelper;

echo "--- RUNNING TEST: ROLL JUARA UMUM KLUB (THB) ---\n";

// Helper to build a skater row
$skaterRow = fn($sid, $cid, $cname, $rank = 0, $time = '', $point = 0, $status = 'OK', $heat = 'Heat 1', $team = '') => [
    'skater_id'     => $sid,
    'club_id'       => $cid,
    'club_name'     => $cname,
    'city_province' => '',
    'rank'          => $rank,
    'time'          => $time,
    'point'         => $point,
    'status'        => $status,
    'heat_name'     => $heat,
    'team_name'     => $team,
    'bib_number'    => (string)$sid,
];

$classes = [
    // 101: Speed 500m (5 peserta di Kualifikasi, 3 di Final) -> E, P, P semua dihitung
    ['id' => 101, 'race_number' => '101', 'class_name' => 'Speed', 'distance_name' => '500m +D', 'group_name' => 'Junior', 'gender' => 'Putra', 'category_name' => null],
    // 102: Speed 200m DTT (3 peserta di Kualifikasi/starting list) -> Hanya Emas
    ['id' => 102, 'race_number' => '102', 'class_name' => 'Speed', 'distance_name' => 'DTT 200m', 'group_name' => 'Junior', 'gender' => 'Putri', 'category_name' => null],
    // 103: Speed 1000m (4 peserta) -> Hanya Emas & Perak
    ['id' => 103, 'race_number' => '103', 'class_name' => 'Speed', 'distance_name' => '1000m', 'group_name' => 'Senior', 'gender' => 'Putra', 'category_name' => null],
    // 104: Speed Eliminasi (2 peserta) -> Tidak dihitung sama sekali
    ['id' => 104, 'race_number' => '104', 'class_name' => 'Speed', 'distance_name' => '3000m Eliminasi', 'group_name' => 'Senior', 'gender' => 'Putri', 'category_name' => null],
    // 105: Standart Relay 1800m (5 tim: Rank 1 Mix Club, Rank 2 Reguler Klub C, Rank 3 Reguler Klub D)
    ['id' => 105, 'race_number' => '105', 'class_name' => 'Standart', 'distance_name' => 'Relay 1800m', 'group_name' => 'KU C-D', 'gender' => 'Putra', 'category_name' => null],
    // 106: Pemula 100m (5 peserta) -> Dikecualikan (bukan Speed/Standard)
    ['id' => 106, 'race_number' => '106', 'class_name' => 'Pemula', 'distance_name' => '100m', 'group_name' => 'KU A', 'gender' => 'Putra', 'category_name' => null],
    // 107: Speed Eksebisi (5 peserta) -> Dikecualikan
    ['id' => 107, 'race_number' => '107', 'class_name' => 'Speed', 'distance_name' => '100m', 'group_name' => 'Senior', 'gender' => 'Putra', 'category_name' => 'EKSEBISI'],
    // 108: Speed 300m untuk uji seri Rank 1 (Klub B dapat 1 Emas juga -> Klub A & B sama-sama 1E 1P 0Pr? cek!)
    ['id' => 108, 'race_number' => '108', 'class_name' => 'Speed', 'distance_name' => '300m', 'group_name' => 'Senior', 'gender' => 'Putri', 'category_name' => null],
];

$starting = [
    101 => [
        $skaterRow(1, 1, 'Klub Alpha'), $skaterRow(2, 2, 'Klub Beta'),
        $skaterRow(3, 3, 'Klub Gamma'), $skaterRow(4, 4, 'Klub Delta'),
        $skaterRow(5, 5, 'Klub Epsilon'),
    ],
    102 => [
        $skaterRow(11, 2, 'Klub Beta'),
        $skaterRow(12, 1, 'Klub Alpha'),
        $skaterRow(13, 3, 'Klub Gamma'),
    ],
    103 => [
        $skaterRow(21, 1, 'Klub Alpha'),
        $skaterRow(22, 2, 'Klub Beta'),
        $skaterRow(23, 3, 'Klub Gamma'),
        $skaterRow(24, 4, 'Klub Delta'),
    ],
    104 => [
        $skaterRow(31, 3, 'Klub Gamma'),
        $skaterRow(32, 4, 'Klub Delta'),
    ],
    105 => [
        // 5 Tim (Mix Team 3 anggota, Tim C 3 anggota, Tim D 3 anggota, Tim E 3 anggota, Tim F 3 anggota)
        $skaterRow(41, 1, 'Klub Alpha', 0, '', 0, 'OK', 'Heat 1', 'Tim Mix'),
        $skaterRow(42, 2, 'Klub Beta',  0, '', 0, 'OK', 'Heat 1', 'Tim Mix'),
        $skaterRow(43, 1, 'Klub Alpha', 0, '', 0, 'OK', 'Heat 1', 'Tim Mix'),
        $skaterRow(44, 3, 'Klub Gamma', 0, '', 0, 'OK', 'Heat 1', 'Tim Gamma'),
        $skaterRow(45, 3, 'Klub Gamma', 0, '', 0, 'OK', 'Heat 1', 'Tim Gamma'),
        $skaterRow(46, 3, 'Klub Gamma', 0, '', 0, 'OK', 'Heat 1', 'Tim Gamma'),
        $skaterRow(47, 4, 'Klub Delta', 0, '', 0, 'OK', 'Heat 1', 'Tim Delta'),
        $skaterRow(48, 4, 'Klub Delta', 0, '', 0, 'OK', 'Heat 1', 'Tim Delta'),
        $skaterRow(49, 4, 'Klub Delta', 0, '', 0, 'OK', 'Heat 1', 'Tim Delta'),
        $skaterRow(50, 5, 'Klub Epsilon', 0, '', 0, 'OK', 'Heat 2', 'Tim Eps'),
        $skaterRow(51, 5, 'Klub Epsilon', 0, '', 0, 'OK', 'Heat 2', 'Tim Eps'),
        $skaterRow(52, 5, 'Klub Epsilon', 0, '', 0, 'OK', 'Heat 2', 'Tim Eps'),
        $skaterRow(53, 6, 'Klub Zeta', 0, '', 0, 'OK', 'Heat 2', 'Tim Zeta'),
        $skaterRow(54, 6, 'Klub Zeta', 0, '', 0, 'OK', 'Heat 2', 'Tim Zeta'),
        $skaterRow(55, 6, 'Klub Zeta', 0, '', 0, 'OK', 'Heat 2', 'Tim Zeta'),
    ],
    106 => [
        $skaterRow(61, 5, 'Klub Epsilon'), $skaterRow(62, 5, 'Klub Epsilon'),
        $skaterRow(63, 5, 'Klub Epsilon'), $skaterRow(64, 5, 'Klub Epsilon'),
        $skaterRow(65, 5, 'Klub Epsilon'),
    ],
    107 => [
        $skaterRow(71, 5, 'Klub Epsilon'), $skaterRow(72, 5, 'Klub Epsilon'),
        $skaterRow(73, 5, 'Klub Epsilon'), $skaterRow(74, 5, 'Klub Epsilon'),
        $skaterRow(75, 5, 'Klub Epsilon'),
    ],
    108 => [
        $skaterRow(81, 2, 'Klub Beta'), $skaterRow(82, 1, 'Klub Alpha'),
        $skaterRow(83, 3, 'Klub Gamma'), $skaterRow(84, 4, 'Klub Delta'),
        $skaterRow(85, 5, 'Klub Epsilon'),
    ],
];

$results = [
    // 101 (5 peserta): Rank 1 Alpha (E), Rank 2 Beta (P), Rank 3 Gamma (Pr)
    101 => ['Final' => [
        $skaterRow(1, 1, 'Klub Alpha', 1, '00.45.100'),
        $skaterRow(2, 2, 'Klub Beta',  2, '00.45.500'),
        $skaterRow(3, 3, 'Klub Gamma', 3, '00.46.000'),
    ]],
    // 102 (3 peserta, starting_list di Kualifikasi): Rank 1 Beta (E), Rank 2 Alpha (diabaikan), Rank 3 Gamma (diabaikan)
    102 => ['Kualifikasi' => [
        $skaterRow(11, 2, 'Klub Beta',  0, '00.18.200', 0, 'OK', 'Final'),
        $skaterRow(12, 1, 'Klub Alpha', 0, '00.18.900', 0, 'OK', 'Final'),
        $skaterRow(13, 3, 'Klub Gamma', 0, '00.19.100', 0, 'OK', 'Final'),
    ]],
    // 103 (4 peserta): Rank 1 Alpha (E), Rank 2 Beta (P), Rank 3 Gamma (diabaikan!)
    103 => ['Final' => [
        $skaterRow(21, 1, 'Klub Alpha', 1, '01.30.000'),
        $skaterRow(22, 2, 'Klub Beta',  2, '01.31.000'),
        $skaterRow(23, 3, 'Klub Gamma', 3, '01.32.000'),
    ]],
    // 104 (2 peserta): 0 medali dihitung
    104 => ['Final' => [
        $skaterRow(31, 3, 'Klub Gamma', 1, '05.00.000'),
        $skaterRow(32, 4, 'Klub Delta', 2, '05.05.000'),
    ]],
    // 105 (Standart Relay 5 tim): Rank 1 Tim Mix (Alpha+Beta -> diabaikan!), Rank 2 Tim Gamma (1 Perak), Rank 3 Tim Delta (1 Perunggu)
    105 => ['Final' => [
        $skaterRow(41, 1, 'Klub Alpha', 1, '03.00.000', 0, 'OK', 'Final', 'Tim Mix'),
        $skaterRow(42, 2, 'Klub Beta',  1, '03.00.000', 0, 'OK', 'Final', 'Tim Mix'),
        $skaterRow(43, 1, 'Klub Alpha', 1, '03.00.000', 0, 'OK', 'Final', 'Tim Mix'),
        $skaterRow(44, 3, 'Klub Gamma', 2, '03.05.000', 0, 'OK', 'Final', 'Tim Gamma'),
        $skaterRow(45, 3, 'Klub Gamma', 2, '03.05.000', 0, 'OK', 'Final', 'Tim Gamma'),
        $skaterRow(46, 3, 'Klub Gamma', 2, '03.05.000', 0, 'OK', 'Final', 'Tim Gamma'),
        $skaterRow(47, 4, 'Klub Delta', 3, '03.10.000', 0, 'OK', 'Final', 'Tim Delta'),
        $skaterRow(48, 4, 'Klub Delta', 3, '03.10.000', 0, 'OK', 'Final', 'Tim Delta'),
        $skaterRow(49, 4, 'Klub Delta', 3, '03.10.000', 0, 'OK', 'Final', 'Tim Delta'),
    ]],
    // 106 (Pemula) & 107 (Eksebisi) -> diabaikan
    106 => ['Final' => [$skaterRow(61, 5, 'Klub Epsilon', 1, '00.12.000')]],
    107 => ['Final' => [$skaterRow(71, 5, 'Klub Epsilon', 1, '00.11.000')]],
    // 108 (5 peserta): Rank 1 Beta (E), Rank 2 Alpha (P), Rank 3 Gamma (Pr)
    // Total Speed:
    //   Klub Alpha: 101(E) + 103(E) + 108(P) = 2E, 1P, 0Pr
    //   Klub Beta : 101(P) + 102(E) + 103(P) + 108(E) = 2E, 2P -> wait, adjust so tie at #1!
    // Let's make 108 Rank 1 Beta (E), Rank 2 Gamma (P), Rank 3 Delta (Pr):
    //   Alpha: 101(E) + 103(E) = 2E, 0P, 0Pr -> let's give Alpha 1P in 108? Wait:
    //   If 108 Rank 1 Beta(E), Rank 2 Alpha(P), Rank 3 Delta(Pr): Alpha = 2E, 1P, 0Pr; Beta = 2E, 2P, 0Pr.
    //   To test exact tie 1, 1, 3: let's have 103 Rank 1 Alpha(E), Rank 2 Delta(P) instead!
    //   Then Alpha = 2E 1P 0Pr, Beta = 2E 1P 0Pr -> TIE #1! Gamma = 0E 0P 1Pr -> Rank 3!
    108 => ['Final' => [
        $skaterRow(81, 2, 'Klub Beta',  1, '00.28.000'),
        $skaterRow(82, 1, 'Klub Alpha', 2, '00.28.500'),
        $skaterRow(84, 4, 'Klub Delta', 3, '00.29.000'),
    ]],
];
// Adjust 103 Rank 2 to Delta so Alpha & Beta tie identically at 2E 1P 0Pr
$results[103]['Final'][1] = $skaterRow(24, 4, 'Klub Delta', 2, '01.31.000');

$teamClubsByClass = [
    105 => [
        'team:tim mix'   => [1 => true, 2 => true], // Mix Club (2 klub)
        'team:tim gamma' => [3 => true],            // Reguler 1 klub
        'team:tim delta' => [4 => true],            // Reguler 1 klub
    ],
];

$out = RollMedalHelper::computeStandingsFromData($classes, $starting, $results, $teamClubsByClass, 'club');

// Verify Speed standings: Alpha & Beta tied at Rank 1 (2E 1P 0Pr), Delta is Rank 3 (0E 1P 1Pr), Gamma is Rank 4 (0E 0P 1Pr)
$speed = $out['standings']['Speed'];
assert(count($speed) === 4, 'Speed should have 4 clubs (Epsilon Pemula/Eksebisi excluded)');
assert($speed[0]['club_name'] === 'Klub Alpha' && $speed[0]['rank'] === 1);
assert($speed[1]['club_name'] === 'Klub Beta' && $speed[1]['rank'] === 1);
assert($speed[0]['gold'] === 2 && $speed[0]['silver'] === 1 && $speed[0]['bronze'] === 0);
assert($speed[1]['gold'] === 2 && $speed[1]['silver'] === 1 && $speed[1]['bronze'] === 0);
assert($speed[2]['club_name'] === 'Klub Delta' && $speed[2]['rank'] === 3); // 1, 1 -> jumps to 3!
assert($speed[3]['club_name'] === 'Klub Gamma' && $speed[3]['rank'] === 4);
assert(count($out['champions']['Speed']) === 2, 'Both tied clubs at Rank 1 are Speed champions');
echo "✓ Speed standings: cutoff (2/3/4/5), Pemula/Eksebisi exclusion, tie rank 1,1,3 [OK]\n";

// Verify Standart standings (Relay Mix Club excluded, regular teams get 1 medal per team)
$std = $out['standings']['Standart'];
assert(count($std) === 2, 'Only Gamma (1S) and Delta (1B) should score in Standart');
assert($std[0]['club_name'] === 'Klub Gamma' && $std[0]['silver'] === 1 && $std[0]['gold'] === 0 && $std[0]['rank'] === 1);
assert($std[1]['club_name'] === 'Klub Delta' && $std[1]['bronze'] === 1 && $std[1]['rank'] === 2);
assert(count($out['mix_exclusions']) === 1 && $out['mix_exclusions'][0]['team_name'] === 'Tim Mix');
echo "✓ Standart standings: Relay Mix Club excluded, regular relay 1 team = 1 medal [OK]\n";

// Verify audit entries for classes < 5
assert(count($out['class_audit']) === 3, 'Classes 102(3), 103(4), 104(2) should be recorded in audit');
echo "✓ THB < 5 participants audit log [OK]\n";

echo "--- ALL JUARA UMUM KLUB (THB) TESTS PASSED! ---\n";
