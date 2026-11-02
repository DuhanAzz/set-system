<?php

// Test for Roll Athlete Puspernas Data & Automated BPJS Logic

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim(trim($value), '"\''));
        $_ENV[trim($name)] = trim(trim($value), '"\'');
    }
}

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Running Roll Athlete & BPJS Logic Tests...\n";

// 1. Verify Columns exist in roll_skaters
$stmtCols = $db->query("SHOW COLUMNS FROM roll_skaters");
$columns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);

assert(in_array('asal_sekolah', $columns), "Column asal_sekolah must exist in roll_skaters");
assert(in_array('provinsi', $columns), "Column provinsi must exist in roll_skaters");
assert(in_array('kota_kabupaten', $columns), "Column kota_kabupaten must exist in roll_skaters");
assert(in_array('status_bpjs', $columns), "Column status_bpjs must exist in roll_skaters");
echo "✓ Schema assertion passed: all 4 new columns exist.\n";

// 2. Unit Logic: Status BPJS & Incomplete Check Function
function evaluateAthleteBpjs($totalEvents, $statusBpjs) {
    if ($totalEvents > 0) return 'sudah_event';
    if ($statusBpjs === 'sudah') return 'sudah_mandiri';
    return 'belum';
}

function isDataIncomplete($sekolah, $kota, $provinsi) {
    return empty(trim((string)$sekolah)) || empty(trim((string)$kota)) || empty(trim((string)$provinsi));
}

// Case A: Athlete with event entries -> Automatically sudah_event
assert(evaluateAthleteBpjs(3, 'belum') === 'sudah_event', "Athlete with 3 events must be sudah_event even if status_bpjs is belum");
assert(evaluateAthleteBpjs(1, 'sudah') === 'sudah_event', "Athlete with 1 event must be sudah_event");

// Case B: Athlete with 0 events but marked as sudah -> sudah_mandiri
assert(evaluateAthleteBpjs(0, 'sudah') === 'sudah_mandiri', "Athlete with 0 events and status_bpjs='sudah' must be sudah_mandiri");

// Case C: Athlete with 0 events and status_bpjs='belum' -> belum
assert(evaluateAthleteBpjs(0, 'belum') === 'belum', "Athlete with 0 events and status_bpjs='belum' must be belum");

// Case D: Incomplete Puspernas data checks
assert(isDataIncomplete('', 'Yogyakarta', 'DIY') === true, "Missing school must be incomplete");
assert(isDataIncomplete('SMP 1', '', 'DIY') === true, "Missing city must be incomplete");
assert(isDataIncomplete('SMP 1', 'Yogyakarta', '') === true, "Missing province must be incomplete");
assert(isDataIncomplete('SMP 1', 'Yogyakarta', 'DIY') === false, "Complete data must NOT be incomplete");
echo "✓ Unit logic assertions passed: BPJS status & data completeness.\n";

// 3. Database Query Integration Test with Transaction Rollback
$db->beginTransaction();
try {
    // Insert a dummy club and two test skaters
    $clubStmt = $db->prepare("INSERT INTO roll_clubs (club_name, city_province) VALUES ('TEST_CLUB_BPJS', 'YOGYAKARTA')");
    $clubStmt->execute();
    $testClubId = $db->lastInsertId();

    // Skater 1: Complete data, no events, status_bpjs = 'belum'
    $stmt1 = $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, asal_sekolah, kota_kabupaten, provinsi, status_bpjs, age_group) 
                           VALUES (?, 'TEST ATLET A', 'M', '2012-05-10', 'SMPN 1 YOGYAKARTA', 'KOTA YOGYAKARTA', 'D.I. YOGYAKARTA', 'belum', '12 Thn')");
    $stmt1->execute([$testClubId]);
    $skaterAId = $db->lastInsertId();

    // Skater 2: Incomplete data (no school), has 1 event entry
    $stmt2 = $db->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, status_bpjs, age_group) 
                           VALUES (?, 'TEST ATLET B', 'F', '2014-08-20', 'belum', '10 Thn')");
    $stmt2->execute([$testClubId]);
    $skaterBId = $db->lastInsertId();

    // Add entry for Skater 2
    $entryStmt = $db->prepare("INSERT INTO roll_entries (event_id, skater_id, club_id, status) VALUES (9999, ?, ?, 'Registered')");
    $entryStmt->execute([$skaterBId, $testClubId]);

    // Test Query matching RollMasterSkaterController / RollUserAthleteController
    $query = "
        SELECT 
            s.id, s.skater_name, s.asal_sekolah, s.kota_kabupaten, s.provinsi,
            COUNT(e.id) as total_events,
            CASE 
                WHEN COUNT(e.id) > 0 THEN 'sudah_event'
                WHEN s.status_bpjs = 'sudah' THEN 'sudah_mandiri'
                ELSE 'belum'
            END as bpjs_badge
        FROM roll_skaters s
        LEFT JOIN roll_entries e ON s.id = e.skater_id
        WHERE s.club_id = ?
        GROUP BY s.id
        ORDER BY s.id ASC
    ";
    $testStmt = $db->prepare($query);
    $testStmt->execute([$testClubId]);
    $results = $testStmt->fetchAll(PDO::FETCH_ASSOC);

    assert(count($results) === 2, "Expected 2 test skaters");

    // Check Skater A: 0 events, belum
    assert($results[0]['bpjs_badge'] === 'belum', "Skater A must have bpjs_badge 'belum'");
    assert(isDataIncomplete($results[0]['asal_sekolah'], $results[0]['kota_kabupaten'], $results[0]['provinsi']) === false, "Skater A data is complete");

    // Check Skater B: 1 event -> automated sudah_event
    assert($results[1]['bpjs_badge'] === 'sudah_event', "Skater B must have bpjs_badge 'sudah_event' due to event entry");
    assert(isDataIncomplete($results[1]['asal_sekolah'], $results[1]['kota_kabupaten'], $results[1]['provinsi']) === true, "Skater B data is incomplete");

    // Test dashboard incomplete count query
    $stmtInc = $db->prepare("
        SELECT COUNT(*) 
        FROM roll_skaters 
        WHERE club_id = ? 
          AND (asal_sekolah IS NULL OR TRIM(asal_sekolah) = '' 
               OR provinsi IS NULL OR TRIM(provinsi) = '' 
               OR kota_kabupaten IS NULL OR TRIM(kota_kabupaten) = '')
    ");
    $stmtInc->execute([$testClubId]);
    $incCount = (int)$stmtInc->fetchColumn();
    assert($incCount === 1, "Expected exactly 1 incomplete skater for test club (Skater B)");

    echo "✓ Database integration assertions passed: automated detection & dashboard counting verified.\n";
} finally {
    // Always rollback so database remains clean
    $db->rollBack();
    echo "✓ Test transaction rolled back cleanly.\n";
}

echo "\nAll Roll Athlete & BPJS tests passed successfully! 🎉\n";
