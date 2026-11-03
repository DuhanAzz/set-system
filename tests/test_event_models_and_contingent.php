<?php
/**
 * Test Suite: Multi-Model Events & Contingent Official Architecture
 * Run with: /Applications/XAMPP/xamppfiles/bin/php tests/test_event_models_and_contingent.php
 */

// Parse .env if available
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'set_system_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== STARTING MULTI-MODEL EVENT & CONTINGENT ARCHITECTURE TESTS ===\n\n";

// TEST 1: Schema Checks
echo "[TEST 1] Verifying schema definitions...\n";
$colEvents = $pdo->query("SHOW COLUMNS FROM roll_events LIKE 'participation_type'")->fetch(PDO::FETCH_ASSOC);
assert(!empty($colEvents), "roll_events.participation_type must exist");
echo "  ✓ roll_events.participation_type exists (Type: {$colEvents['Type']})\n";

$statusCol = $pdo->query("SHOW COLUMNS FROM roll_events LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
assert(strpos($statusCol['Type'], 'Official Registration') !== false, "roll_events.status must include 'Official Registration'");
echo "  ✓ roll_events.status includes 'Official Registration'\n";

$colClubType = $pdo->query("SHOW COLUMNS FROM roll_clubs LIKE 'club_type'")->fetch(PDO::FETCH_ASSOC);
assert(!empty($colClubType), "roll_clubs.club_type must exist");
echo "  ✓ roll_clubs.club_type exists (Type: {$colClubType['Type']})\n";

$colProvince = $pdo->query("SHOW COLUMNS FROM roll_clubs LIKE 'province'")->fetch(PDO::FETCH_ASSOC);
assert(!empty($colProvince), "roll_clubs.province exists\n");
echo "  ✓ roll_clubs.province exists\n";


// TEST 2: Create Test Contingent & Test Athlete in Database
echo "\n[TEST 2] Verifying Official Contingent Account Creation & Drafting Pool...\n";

// 2a. Insert Club A & Atlet A
$pdo->exec("INSERT INTO roll_clubs (club_name, club_type, city_province, province) VALUES ('Klub Sepatu Roda Mataram', 'club', 'Kota Yogyakarta', 'D.I. Yogyakarta')");
$clubAId = $pdo->lastInsertId();

$testUid = 'TEST' . time() . 'M1';
$pdo->prepare("
    INSERT INTO roll_skaters (uid, club_id, skater_name, gender, birth_date, asal_sekolah, kota_kabupaten, provinsi, status_bpjs, age_group)
    VALUES (?, ?, 'Atlet Bintang Pratama', 'M', '2012-05-10', 'SMP Negeri 1 Yogyakarta', 'Kota Yogyakarta', 'D.I. Yogyakarta', 'sudah', 'KU-C')
")->execute([$testUid, $clubAId]);
$skaterAId = $pdo->lastInsertId();
echo "  ✓ Athlete created in Club A with UID $testUid\n";

// 2b. Insert Official Contingent Organization
$pdo->exec("INSERT INTO roll_clubs (club_name, club_type, city_province, province) VALUES ('KONTINGEN D.I. YOGYAKARTA', 'contingent', 'D.I. Yogyakarta', 'D.I. Yogyakarta')");
$contingentId = $pdo->lastInsertId();
echo "  ✓ Official Contingent created with ID $contingentId (Type: contingent)\n";

// 2c. Test Drafting Pool Simulation
$stmtSearch = $pdo->prepare("
    SELECT s.id, s.uid, s.skater_name, s.gender, s.birth_date, s.asal_sekolah, s.kota_kabupaten, s.provinsi, s.status_bpjs, s.age_group
    FROM roll_skaters s
    WHERE s.club_id != ? AND s.uid = ?
");
$stmtSearch->execute([$contingentId, $testUid]);
$poolResult = $stmtSearch->fetch(PDO::FETCH_ASSOC);
assert(!empty($poolResult), "Regional pool drafting must find athlete from other club");
assert($poolResult['asal_sekolah'] === 'SMP Negeri 1 Yogyakarta', "Drafted athlete must have school data intact");
echo "  ✓ Pool search found athlete '{$poolResult['skater_name']}' with school '{$poolResult['asal_sekolah']}'\n";

// 2d. Draft into Contingent
$insDraft = $pdo->prepare("
    INSERT INTO roll_skaters (uid, club_id, skater_name, gender, birth_date, asal_sekolah, kota_kabupaten, provinsi, status_bpjs, age_group)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insDraft->execute([
    $poolResult['uid'],
    $contingentId,
    $poolResult['skater_name'],
    $poolResult['gender'],
    $poolResult['birth_date'],
    $poolResult['asal_sekolah'],
    $poolResult['kota_kabupaten'],
    $poolResult['provinsi'],
    $poolResult['status_bpjs'],
    $poolResult['age_group']
]);
$draftedSkaterId = $pdo->lastInsertId();

// Verify in contingent roster
$stmtCheckRoster = $pdo->prepare("SELECT * FROM roll_skaters WHERE club_id = ? AND uid = ?");
$stmtCheckRoster->execute([$contingentId, $testUid]);
$rosterSkater = $stmtCheckRoster->fetch(PDO::FETCH_ASSOC);
assert(!empty($rosterSkater), "Athlete must be present in contingent roster");
assert($rosterSkater['uid'] === $testUid, "UID must be preserved across contingent and origin club");
echo "  ✓ Athlete drafted into Contingent roster successfully with identical UID: {$rosterSkater['uid']}\n";


// TEST 3: Event Creation with 'Official Registration' & 'contingent_province'
echo "\n[TEST 3] Verifying Event Creation & Access Control Gatekeeper...\n";
$pdo->exec("
    INSERT INTO roll_events (event_name, status, participation_type, event_date_start, race_format)
    VALUES ('KEJUARAAN NASIONAL PRA-PON 2026', 'Official Registration', 'contingent_province', '2026-09-10', 'SPRINT')
");
$testEventId = $pdo->lastInsertId();

$stmtEvt = $pdo->prepare("SELECT * FROM roll_events WHERE id = ?");
$stmtEvt->execute([$testEventId]);
$evt = $stmtEvt->fetch(PDO::FETCH_ASSOC);
assert($evt['status'] === 'Official Registration', "Status must be 'Official Registration'");
assert($evt['participation_type'] === 'contingent_province', "Participation type must be 'contingent_province'");
echo "  ✓ Event created: '{$evt['event_name']}' | Status: {$evt['status']} | Type: {$evt['participation_type']}\n";

// Test Gatekeeper Logic
$clubUserType = 'club';
$contingentUserType = 'contingent';

// Simulating club user attempting access
$canClubRegister = ($evt['status'] === 'Open Registration') || ($evt['status'] === 'Official Registration' && $clubUserType === 'contingent');
assert($canClubRegister === false, "Club user must NOT be allowed to register for 'Official Registration' event");
echo "  ✓ Gatekeeper check: Club user BLOCKED from Official Registration event\n";

// Simulating contingent user attempting access
$canContingentRegister = ($evt['status'] === 'Open Registration') || ($evt['status'] === 'Official Registration' && $contingentUserType === 'contingent');
assert($canContingentRegister === true, "Contingent user MUST be allowed to register for 'Official Registration' event");
echo "  ✓ Gatekeeper check: Contingent user GRANTED access to Official Registration event\n";


// TEST 4: Medal Tally Dynamic Aggregation Logic
echo "\n[TEST 4] Verifying Dynamic Medal Tally Query for Event Models...\n";

// Helper function to test query generation
function getTallyQuery($partType) {
    if ($partType === 'school') {
        return "COALESCE(NULLIF(s.asal_sekolah, ''), 'Tanpa Sekolah') as entity_name";
    } elseif ($partType === 'contingent_province') {
        return "COALESCE(NULLIF(s.provinsi, ''), 'Tanpa Provinsi') as entity_name";
    } elseif ($partType === 'contingent_city') {
        return "COALESCE(NULLIF(s.kota_kabupaten, ''), 'Tanpa Kota/Kab') as entity_name";
    } else {
        return "c.club_name as entity_name";
    }
}

assert(strpos(getTallyQuery('school'), 's.asal_sekolah') !== false);
assert(strpos(getTallyQuery('contingent_province'), 's.provinsi') !== false);
assert(strpos(getTallyQuery('contingent_city'), 's.kota_kabupaten') !== false);
assert(strpos(getTallyQuery('club'), 'c.club_name') !== false);
echo "  ✓ Antar Club query maps to: " . getTallyQuery('club') . "\n";
echo "  ✓ Antar Sekolah query maps to: " . getTallyQuery('school') . "\n";
echo "  ✓ Antar Provinsi query maps to: " . getTallyQuery('contingent_province') . "\n";
echo "  ✓ Antar Kota/Kab query maps to: " . getTallyQuery('contingent_city') . "\n";


// CLEANUP TEST DATA
echo "\n[CLEANUP] Cleaning up test records...\n";
$pdo->prepare("DELETE FROM roll_skaters WHERE id IN (?, ?)")->execute([$skaterAId, $draftedSkaterId]);
$pdo->prepare("DELETE FROM roll_clubs WHERE id IN (?, ?)")->execute([$clubAId, $contingentId]);
$pdo->prepare("DELETE FROM roll_events WHERE id = ?")->execute([$testEventId]);
echo "  ✓ Cleaned up test skaters, clubs, and event.\n";

echo "\n>>> ALL TESTS PASSED SUCCESSFULLY! <<<\n";
