<?php
declare(strict_types=1);

function assertTrue(bool $cond, string $msg): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$msg}\n");
        exit(1);
    }
}

// 1. SQLite in-memory test for Prev / Next / All query logic
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE swim_event_numbers (
        id INTEGER PRIMARY KEY,
        event_id INTEGER NOT NULL,
        event_number TEXT NOT NULL,
        distance INTEGER NOT NULL,
        stroke TEXT NOT NULL,
        jenis_kelamin TEXT NOT NULL,
        age_group TEXT NOT NULL
    )
");

$stmtIns = $pdo->prepare("INSERT INTO swim_event_numbers (id, event_id, event_number, distance, stroke, jenis_kelamin, age_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
// Event 10 has 3 race categories: #1 (id: 101), #2 (id: 102), #3 (id: 103)
$stmtIns->execute([101, 10, '1', 400, 'Gaya Bebas', 'L', 'OPEN']);
$stmtIns->execute([102, 10, '2', 200, 'Gaya Dada', 'P', 'OPEN']);
$stmtIns->execute([103, 10, '3', 100, 'Gaya Kupu-kupu', 'L', 'OPEN']);

// Test case A: Race #2 (id: 102) -> should have prev = #1 (id 101) and next = #3 (id 103)
$currentEventId = 10;
$cat_id = 102;
$currentEvNo = 2;

$stmtPrev = $pdo->prepare("SELECT id, event_number, distance, stroke, jenis_kelamin, age_group FROM swim_event_numbers WHERE event_id = ? AND (CAST(event_number AS UNSIGNED) < ? OR (CAST(event_number AS UNSIGNED) = ? AND id < ?)) ORDER BY CAST(event_number AS UNSIGNED) DESC, id DESC LIMIT 1");
$stmtPrev->execute([$currentEventId, $currentEvNo, $currentEvNo, $cat_id]);
$rowPrev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

$stmtNext = $pdo->prepare("SELECT id, event_number, distance, stroke, jenis_kelamin, age_group FROM swim_event_numbers WHERE event_id = ? AND (CAST(event_number AS UNSIGNED) > ? OR (CAST(event_number AS UNSIGNED) = ? AND id > ?)) ORDER BY CAST(event_number AS UNSIGNED) ASC, id ASC LIMIT 1");
$stmtNext->execute([$currentEventId, $currentEvNo, $currentEvNo, $cat_id]);
$rowNext = $stmtNext->fetch(PDO::FETCH_ASSOC);

$stmtAllRaces = $pdo->prepare("SELECT id, event_number, distance, stroke, jenis_kelamin, age_group FROM swim_event_numbers WHERE event_id = ? ORDER BY CAST(event_number AS UNSIGNED) ASC, id ASC");
$stmtAllRaces->execute([$currentEventId]);
$allRaces = $stmtAllRaces->fetchAll(PDO::FETCH_ASSOC);

assertTrue($rowPrev !== false && (int)$rowPrev['id'] === 101 && $rowPrev['event_number'] === '1', 'Prev race of #2 should be #1 (id: 101)');
assertTrue($rowNext !== false && (int)$rowNext['id'] === 103 && $rowNext['event_number'] === '3', 'Next race of #2 should be #3 (id: 103)');
assertTrue(count($allRaces) === 3, 'All races count should be 3');

// Test case B: First race (#1) -> prev must be false/null
$stmtPrev1 = $pdo->prepare("SELECT id, event_number FROM swim_event_numbers WHERE event_id = ? AND (CAST(event_number AS UNSIGNED) < ? OR (CAST(event_number AS UNSIGNED) = ? AND id < ?)) ORDER BY CAST(event_number AS UNSIGNED) DESC, id DESC LIMIT 1");
$stmtPrev1->execute([10, 1, 1, 101]);
$rowPrev1 = $stmtPrev1->fetch(PDO::FETCH_ASSOC);
assertTrue($rowPrev1 === false, 'First race #1 should have no previous race');

// Test case C: Last race (#3) -> next must be false/null
$stmtNext3 = $pdo->prepare("SELECT id, event_number FROM swim_event_numbers WHERE event_id = ? AND (CAST(event_number AS UNSIGNED) > ? OR (CAST(event_number AS UNSIGNED) = ? AND id > ?)) ORDER BY CAST(event_number AS UNSIGNED) ASC, id ASC LIMIT 1");
$stmtNext3->execute([10, 3, 3, 103]);
$rowNext3 = $stmtNext3->fetch(PDO::FETCH_ASSOC);
assertTrue($rowNext3 === false, 'Last race #3 should have no next race');

// 2. Test URL construction with query parameter preservation
$mockGet = [
    'category_id' => '102',
    'print' => '1',
    'cfg_submitted' => '1',
    'cfg_event_no' => '1',
    'cfg_date' => '1',
    'col_uid' => '1',
    'col_nama' => '1',
    'col_hasil' => '1',
];

$appUrl = 'https://setsystem.id';

// Build prev URL
$prevParams = $mockGet;
$prevParams['category_id'] = $rowPrev['id'];
$prevPrintUrl = $appUrl . '/swim/admin/results/input?' . http_build_query($prevParams);

// Build next URL
$nextParams = $mockGet;
$nextParams['category_id'] = $rowNext['id'];
$nextPrintUrl = $appUrl . '/swim/admin/results/input?' . http_build_query($nextParams);

assertTrue(strpos($prevPrintUrl, 'category_id=101') !== false, 'Prev URL contains category_id=101');
assertTrue(strpos($prevPrintUrl, 'print=1') !== false, 'Prev URL preserves print=1');
assertTrue(strpos($prevPrintUrl, 'cfg_event_no=1') !== false, 'Prev URL preserves cfg_event_no=1');
assertTrue(strpos($prevPrintUrl, 'col_hasil=1') !== false, 'Prev URL preserves col_hasil=1');

assertTrue(strpos($nextPrintUrl, 'category_id=103') !== false, 'Next URL contains category_id=103');
assertTrue(strpos($nextPrintUrl, 'print=1') !== false, 'Next URL preserves print=1');
assertTrue(strpos($nextPrintUrl, 'col_uid=1') !== false, 'Next URL preserves col_uid=1');

echo "PASS: test_print_input_nav\n";

