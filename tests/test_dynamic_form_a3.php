<?php

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "[FAIL] $message\n");
        exit(1);
    }
}

function assertSame($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        fwrite(STDERR, "[FAIL] $message\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n");
        exit(1);
    }
}

// 1. Custom Database Nomor Lomba (tanpa Papan, ada 100M & 200M Gaya Ganti)
$customEvents = [
    ['id' => 10, 'event_name' => '100 M GAYA BEBAS PUTRA - LCM', 'stroke' => 'BEBAS', 'distance' => 100, 'is_relay' => 0],
    ['id' => 11, 'event_name' => '50 M GAYA BEBAS PUTRI - LCM', 'stroke' => 'BEBAS', 'distance' => 50, 'is_relay' => 0],
    ['id' => 12, 'event_name' => '100 M GAYA DADA PUTRA - LCM', 'stroke' => 'DADA', 'distance' => 100, 'is_relay' => 0],
    ['id' => 13, 'event_name' => '200 M GAYA GANTI PERORANGAN PUTRA - LCM', 'stroke' => 'GAYA GANTI', 'distance' => 200, 'is_relay' => 0],
    ['id' => 14, 'event_name' => '4 x 50 M ESTAFET BEBAS PUTRA', 'stroke' => 'BEBAS', 'distance' => 200, 'is_relay' => 1],
];

$structCustom = SwimEventHelper::buildFormA3Structure($customEvents);
assertSame(false, $structCustom['is_fallback'], 'Custom events should not trigger fallback');
assertSame(4, count($structCustom['columns']), 'Relay excluded, exactly 4 unique columns');
assertSame(
    ['Nama', 'Sekolah', 'Tgl Lahir', 'Pa/Pi', 'Gaya Bebas', '', 'Gaya Dada', 'Gaya Ganti'],
    $structCustom['header_row_1'],
    'Header row 1 matches custom Database Nomor Lomba strokes'
);
assertSame(
    ['', '', '', '', '50M', '100M', '100M', '200M'],
    $structCustom['header_row_2'],
    'Header row 2 matches sorted distances without Papan Kaki'
);
assertSame(
    ['stroke' => 'GAYA GANTI', 'distance' => 200, 'is_kick' => false],
    [
        'stroke'   => $structCustom['columns'][7]['stroke'],
        'distance' => $structCustom['columns'][7]['distance'],
        'is_kick'  => $structCustom['columns'][7]['is_kick'],
    ],
    'Column 7 parses as 200M GAYA GANTI'
);

// 2. Mixed Papan Kaki (25M & 50M Papan Kaki) + Jarak Reguler (25M, 50M, 100M)
$mixedEvents = [
    ['id' => 20, 'event_name' => '50 M GAYA DADA PUTRA', 'stroke' => 'DADA', 'distance' => 50, 'is_relay' => 0],
    ['id' => 21, 'event_name' => '25 M PAPAN KAKI GAYA DADA PUTRA', 'stroke' => 'PAPAN DADA', 'distance' => 25, 'is_relay' => 0],
    ['id' => 22, 'event_name' => '50 M PAPAN KAKI GAYA DADA PUTRA', 'stroke' => 'PAPAN DADA', 'distance' => 50, 'is_relay' => 0],
    ['id' => 23, 'event_name' => '100 M GAYA KUPU-KUPU PUTRI', 'stroke' => 'KUPU-KUPU', 'distance' => 100, 'is_relay' => 0],
];

$structMixed = SwimEventHelper::buildFormA3Structure($mixedEvents);
assertSame(
    ['25M Papan Kaki', '50M Papan Kaki', '50M'],
    $structMixed['summary']['Gaya Dada'],
    'Papan Kaki columns sort ahead of regular distances'
);
assertSame(
    [0, 1, 50],
    array_keys($structMixed['table_structure']['GAYA DADA']),
    'Matrix table_structure keys place Papan Kaki (0, 1) before regular distance (50)'
);
assertSame(['100M'], $structMixed['summary']['Gaya Kupu-kupu'], 'Butterfly 100M mapped cleanly');

// 3. Empty DB / Relay-only DB -> fallback ke 12 kolom standar Form A3
$structEmpty = SwimEventHelper::buildFormA3Structure([]);
assertSame(true, $structEmpty['is_fallback'], 'Empty event list falls back to default Form A3 columns');
assertSame(12, count($structEmpty['columns']), 'Fallback generates 12 standard columns');

$structRelayOnly = SwimEventHelper::buildFormA3Structure([
    ['id' => 99, 'event_name' => '4 x 50 M ESTAFET BEBAS', 'stroke' => 'BEBAS', 'distance' => 200, 'is_relay' => 1],
]);
assertSame(true, $structRelayOnly['is_fallback'], 'Relay-only events trigger Form A3 fallback');

// 4. Round-trip CSV export dengan UTF-8 BOM + parse kembali (plus guard terhadap data waktu/Tgl Lahir)
$fp = fopen('php://temp', 'r+');
fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($fp, $structMixed['header_row_1']);
fputcsv($fp, $structMixed['header_row_2']);
fputcsv($fp, ['[CONTOH] Budi Santoso (Hapus baris ini)', 'SDN 01 Contoh', '15/05/2015', 'L', '00.28.50', '99.99.99', '', '']);
rewind($fp);

$parsedRow1 = fgetcsv($fp, 10000, ',');
$parsedRow1[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$parsedRow1[0]);
$parsedRow2 = fgetcsv($fp, 10000, ',');
$parsedDataRow = fgetcsv($fp, 10000, ',');
fclose($fp);

$roundTrip = SwimEventHelper::parseFormA3Headers($parsedRow1, $parsedRow2);
assertSame(true, $roundTrip['consume_row2_as_header'], 'Exported 2-row template header consumes row 2');
assertSame(count($structMixed['columns']), count($roundTrip['columns']), 'Round-trip column count preserved');
foreach ($structMixed['columns'] as $colIdx => $expectedSpec) {
    assertSame(
        [
            'stroke'   => $expectedSpec['stroke'],
            'distance' => $expectedSpec['distance'],
            'is_kick'  => $expectedSpec['is_kick'],
        ],
        $roundTrip['columns'][$colIdx],
        "Round-trip mismatch on column index $colIdx"
    );
}
assertTrue(stripos((string)$parsedDataRow[0], '[CONTOH]') === 0, 'Sample row starts with [CONTOH]');

// Single-row header diikuti langsung baris atlet dengan time 00.25.10 dan DOB 25/05/2015
$singleHeaderRow = ['Nama', 'Sekolah', 'Tgl Lahir', 'Pa/Pi', 'Gaya Bebas 25M', 'Gaya Dada 50M'];
$athleteRowAsRow2 = ['Arfan Izzan', 'SDN 02', '25/05/2015', 'L', '00.25.10', '00.50.99'];
$singleParsed = SwimEventHelper::parseFormA3Headers($singleHeaderRow, $athleteRowAsRow2);
assertSame(false, $singleParsed['consume_row2_as_header'], 'Athlete row with 00.25.10 & 25/05/2015 must NOT be consumed as header');
assertSame(
    [
        4 => ['stroke' => 'BEBAS', 'distance' => 25, 'is_kick' => false],
        5 => ['stroke' => 'DADA',  'distance' => 50, 'is_kick' => false],
    ],
    $singleParsed['columns'],
    'Single-row header parses stroke & distance cleanly'
);

// 5. Kompatibilitas template Form A3 lama (Papan, 25M, 50M)
$legacyRow1 = ['Nama', 'Sekolah', 'Tgl Lahir', 'Pa/Pi', 'Gaya Bebas', '', '', 'Gaya Dada', '', '', 'Gaya Punggung', '', '', 'Gaya Kupu-kupu', '', ''];
$legacyRow2 = ['', '', '', '', 'Papan', '25M', '50M', 'Papan', '25M', '50M', 'Papan', '25M', '50M', 'Papan', '25M', '50M'];
$legacyParsed = SwimEventHelper::parseFormA3Headers($legacyRow1, $legacyRow2);
assertSame(true, $legacyParsed['consume_row2_as_header'], 'Legacy 2-row Form A3 consumes row 2');
assertSame(12, count($legacyParsed['columns']), 'Legacy Form A3 produces 12 columns');
assertSame(
    ['stroke' => 'BEBAS', 'distance' => 25, 'is_kick' => true],
    $legacyParsed['columns'][4],
    'Legacy Papan defaults to 25M kick'
);
assertSame(
    ['stroke' => 'KUPU-KUPU', 'distance' => 50, 'is_kick' => false],
    $legacyParsed['columns'][15],
    'Legacy column 15 maps to 50M KUPU-KUPU'
);

echo "ALL DYNAMIC FORM A3 CHECKS PASSED\n";

