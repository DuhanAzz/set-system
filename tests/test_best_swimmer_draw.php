<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function assertEqualsFloat(float $expected, float $actual, string $message, float $eps = 0.0001): void
{
    if (abs($expected - $actual) > $eps) {
        fwrite(STDERR, "FAIL: {$message} (expected {$expected}, got {$actual})\n");
        exit(1);
    }
}

// 1. Time parsing tests
assertEqualsFloat(32.10, SwimEventHelper::parseSwimTimeToSeconds('00.32.10'), '00.32.10 -> 32.10s');
assertEqualsFloat(65.23, SwimEventHelper::parseSwimTimeToSeconds('01.05.23'), '01.05.23 -> 65.23s');
assertEqualsFloat(62.15, SwimEventHelper::parseSwimTimeToSeconds('01:02.15'), '01:02.15 -> 62.15s');
assertEqualsFloat(23.84, SwimEventHelper::parseSwimTimeToSeconds('23.84'), '23.84 -> 23.84s');
assertEqualsFloat(0.0, SwimEventHelper::parseSwimTimeToSeconds('99.99.99'), '99.99.99 -> 0.0s');
assertEqualsFloat(0.0, SwimEventHelper::parseSwimTimeToSeconds('NT'), 'NT -> 0.0s');
assertEqualsFloat(0.0, SwimEventHelper::parseSwimTimeToSeconds('DQ'), 'DQ -> 0.0s');
assertEqualsFloat(0.0, SwimEventHelper::parseSwimTimeToSeconds(''), 'empty -> 0.0s');

// 2. Stroke normalization & Papan exclusion
assertTrue(SwimEventHelper::normalizeRecordStrokeKey('GAYA BEBAS') === 'BEBAS', 'GAYA BEBAS -> BEBAS');
assertTrue(SwimEventHelper::normalizeRecordStrokeKey('Gaya Ganti Perorangan') === 'GAYA GANTI', 'Gaya Ganti Perorangan -> GAYA GANTI');
assertTrue(SwimEventHelper::normalizeRecordStrokeKey('Gaya Kupu-kupu') === 'KUPU-KUPU', 'Gaya Kupu-kupu -> KUPU-KUPU');
assertTrue(SwimEventHelper::normalizeRecordStrokeKey('GAYA BEBAS', '25 M PAPAN KAKI GAYA BEBAS') === null, 'Papan kaki must return null');

// 3. In-memory SQLite Rekornas loading + sharpness evaluation
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Graceful degradation when table does not exist
assertTrue(SwimEventHelper::loadRekornasIndex($pdo) === [], 'Missing swim_master_records should return empty array');

$pdo->exec("
    CREATE TABLE swim_master_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        record_type TEXT NOT NULL,
        distance INTEGER NOT NULL,
        stroke TEXT NOT NULL,
        jenis_kelamin TEXT NOT NULL,
        age_group TEXT NOT NULL,
        holder_name TEXT,
        record_time TEXT NOT NULL,
        record_time_ms INTEGER DEFAULT 0
    )
");

$stmt = $pdo->prepare("
    INSERT INTO swim_master_records (record_type, distance, stroke, jenis_kelamin, age_group, holder_name, record_time, record_time_ms)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
// 50m Bebas Pa SENIOR = 23.00s
$stmt->execute(['rekornas', 50, 'Gaya Bebas', 'Pa', 'SENIOR', 'Atlet Senior', '00.23.00', 23000]);
// 50m Dada Pa KU 3 = 31.45s (KU-specific record vs SENIOR)
$stmt->execute(['rekornas', 50, 'Gaya Dada', 'Pa', 'KU 3', 'Atlet KU3', '00.31.45', 31450]);
$stmt->execute(['rekornas', 50, 'Gaya Dada', 'Pa', 'SENIOR', 'Atlet Senior Dada', '00.27.00', 27000]);
// 100m Kupu Pa SENIOR = 54.00s
$stmt->execute(['rekornas', 100, 'Gaya Kupu-kupu', 'Pa', 'SENIOR', 'Atlet Kupu', '00.54.00', 54000]);
// Non-rekornas row should be ignored
$stmt->execute(['rekorda', 50, 'Gaya Bebas', 'Pa', 'SENIOR', 'Rekorda Dummy', '00.20.00', 20000]);

$index = SwimEventHelper::loadRekornasIndex($pdo);
assertTrue(!empty($index), 'Index should not be empty');

// Athlete A: 1 Gold in 50m Bebas (27.06s -> ~85.00% vs 23.00s), plus Papan 25m (ignored -> 0%)
$athARaces = [
    [
        'event_number'  => 101,
        'event_name'    => '50 M GAYA BEBAS PUTRA - LCM',
        'distance'      => 50,
        'stroke'        => 'GAYA BEBAS',
        'age_group'     => 'KU 3',
        'jenis_kelamin' => 'Pa',
        'time_final'    => '00.27.06',
        'rank_final'    => 1,
    ],
    [
        'event_number'  => 102,
        'event_name'    => '25 M PAPAN KAKI GAYA BEBAS PUTRA - LCM',
        'distance'      => 25,
        'stroke'        => 'GAYA BEBAS',
        'age_group'     => 'KU 3',
        'jenis_kelamin' => 'Pa',
        'time_final'    => '00.20.00',
        'rank_final'    => 1,
    ],
];
$evalA = SwimEventHelper::evaluateSwimmerSharpness($athARaces, $index);
assertEqualsFloat((23.0 / 27.06) * 100.0, $evalA['best_pct'], 'Athlete A sharpness vs SENIOR fallback', 0.01);
assertTrue($evalA['races'][1]['pct'] === '0%', 'Papan race must yield 0% sharpness');

// Athlete B: 1 Gold in 50m Dada KU 3 (34.00s vs KU 3 record 31.45s = 92.50%) -> beats Athlete A on draw!
$athBRaces = [
    [
        'event_number'  => 103,
        'event_name'    => '50 M GAYA DADA PUTRA - LCM',
        'distance'      => 50,
        'stroke'        => 'GAYA DADA',
        'age_group'     => 'KU 3',
        'jenis_kelamin' => 'Pa',
        'time_final'    => '00.34.00',
        'rank_final'    => 1,
    ],
];
$evalB = SwimEventHelper::evaluateSwimmerSharpness($athBRaces, $index);
assertEqualsFloat(92.50, $evalB['best_pct'], 'Athlete B exact KU 3 record match -> 92.50%', 0.01);
assertTrue(strpos($evalB['races'][0]['holder'], '(KU 3)') !== false, 'Must pick exact KU 3 record over SENIOR');
assertTrue($evalB['best_pct'] > $evalA['best_pct'], 'Athlete B should beat Athlete A on sharpness tie-breaker');

echo "PASS: test_best_swimmer_draw\n";
