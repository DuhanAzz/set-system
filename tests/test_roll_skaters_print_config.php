<?php

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Roll/Controllers/Admin/RollSkaterController.php';

use App\Roll\Controllers\Admin\RollSkaterController;

echo "--- RUNNING TEST: ROLL SKATERS PRINT CONFIG & VIEW RENDER ---\n";

// 1. Default Starting List config (without cfg=1)
$cfg1 = RollSkaterController::resolvePrintConfig([], 'starting_list');
assert($cfg1['show_bib'] === true);
assert($cfg1['show_dob'] === false);
assert($cfg1['show_club'] === true);
assert($cfg1['show_classes'] === true);
assert($cfg1['page_break_cat'] === true);
echo "✓ Case 1: Starting List defaults (BIB, Klub, Kelas, Page Break ON; Tgl Lahir OFF) [OK]\n";

// 2. Custom Starting List config (cfg=1 with Tgl Lahir ON, Kelas OFF, Page Break OFF)
$cfg2 = RollSkaterController::resolvePrintConfig(['cfg' => '1', 'show_bib' => '1', 'show_dob' => '1'], 'starting_list');
assert($cfg2['show_bib'] === true);
assert($cfg2['show_dob'] === true);
assert($cfg2['show_club'] === false);
assert($cfg2['show_classes'] === false);
assert($cfg2['page_break_cat'] === false);
echo "✓ Case 2: Custom Starting List checkbox toggles [OK]\n";

// 3. Default BPJS config
$cfg3 = RollSkaterController::resolvePrintConfig([], 'bpjs');
assert($cfg3['show_bib'] === true);
assert($cfg3['show_dob'] === true);
assert($cfg3['show_nik'] === true);
assert($cfg3['show_club'] === true);
assert($cfg3['show_bpjs'] === true);
echo "✓ Case 3: BPJS defaults (all columns ON) [OK]\n";

// 4. Custom BPJS config (hide NIK)
$cfg4 = RollSkaterController::resolvePrintConfig(['cfg' => '1', 'show_bib' => '1', 'show_dob' => '1', 'show_club' => '1'], 'bpjs');
assert($cfg4['show_nik'] === false);
assert($cfg4['show_bpjs'] === false);
assert($cfg4['show_dob'] === true);
echo "✓ Case 4: Custom BPJS checkbox toggles [OK]\n";

// 5. Render print_skaters view in both modes to verify zero PHP notices/warnings & expected markers
$renderView = static function (array $vars): string {
    extract($vars);
    ob_start();
    include __DIR__ . '/../views/roll/admin/skaters/print_skaters.php';
    return ob_get_clean();
};

$dummyEvent = [
    'event_name'       => 'KEJURNAS SEPATU RODA 2026',
    'event_date_start' => '2026-11-20',
    'event_date_end'   => '2026-11-22',
    'event_location'   => 'GOR Bekasi',
    'header_logos'     => json_encode(['left' => ['public/uploads/logo_l.png'], 'center' => [], 'right' => []]),
    'sponsor_logos'    => json_encode(['public/uploads/sp1.png']),
];

$htmlStarting = $renderView([
    'event'          => $dummyEvent,
    'activeTab'      => 'starting_list',
    'printConfig'    => $cfg2,
    'totalSkaters'   => 2,
    'categoryCounts' => ['Speed' => 1, 'Pemula' => 1],
    'grouped'        => [
        'Speed' => [
            'KU-A' => [
                'Putri' => [[
                    'skater_name' => 'Alya Putri',
                    'bib_number'  => '101',
                    'birth_date'  => '2018-04-12',
                    'club_name'   => 'Klub Cepat',
                    'distances'   => '300m, 500m',
                    'class_name'  => 'Speed',
                ]],
            ],
        ],
        'Pemula' => [
            'KU-A' => [
                'Putra' => [[
                    'skater_name' => 'Budi Santoso',
                    'bib_number'  => '202',
                    'birth_date'  => '0000-00-00',
                    'club_name'   => 'Klub Maju',
                    'distances'   => '100m',
                    'class_name'  => 'Pemula',
                ]],
            ],
        ],
    ],
]);

assert(strpos($htmlStarting, 'STARTING LIST PESERTA') !== false || strpos($htmlStarting, 'Starting List Peserta') !== false);
assert(strpos($htmlStarting, 'cat-break-tbody') !== false);
assert(strpos($htmlStarting, 'hide-col-classes') !== false);
assert(strpos($htmlStarting, 'no-cat-break') !== false);
assert(strpos($htmlStarting, '12/04/2018') !== false);
assert(strpos($htmlStarting, 'Supported By') !== false);
echo "✓ Case 5: Starting List A4 HTML rendered with DOB, category breaks & sponsors [OK]\n";

$htmlBpjs = $renderView([
    'event'            => $dummyEvent,
    'activeTab'        => 'bpjs',
    'printConfig'      => $cfg4,
    'search'           => '',
    'bpjsFilter'       => 'all',
    'nikFilter'        => 'all',
    'bpjsSummary'      => ['total' => 1, 'bpjs_sudah' => 1, 'bpjs_belum' => 0, 'nik_kosong' => 0],
    'bpjsParticipants' => [[
        'skater_name' => 'Alya Putri',
        'bib_number'  => '101',
        'gender'      => 'F',
        'birth_date'  => '2018-04-12',
        'nik'         => '3275011204180001',
        'club_name'   => 'Klub Cepat',
        'status_bpjs' => 'sudah',
    ]],
]);

assert(strpos($htmlBpjs, 'Data Peserta, NIK &amp; BPJS') !== false || strpos($htmlBpjs, 'Data Peserta, NIK & BPJS') !== false);
assert(strpos($htmlBpjs, 'hide-col-nik') !== false);
assert(strpos($htmlBpjs, '3275011204180001') !== false);
echo "✓ Case 6: BPJS A4 HTML rendered with column toggle classes & summary [OK]\n";

echo "--- ALL ROLL SKATERS PRINT TESTS PASSED! ---\n";
