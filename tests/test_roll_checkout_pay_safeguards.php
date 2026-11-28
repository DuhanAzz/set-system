<?php

require_once __DIR__ . '/../app/Helpers/DateHelper.php';
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';
require_once __DIR__ . '/../app/Core/UploadService.php';

use App\Helpers\DateHelper;
use App\Helpers\RollFinanceHelper;
use App\Core\UploadService;

echo "--- RUNNING TEST: ROLL CHECKOUT PAY SAFEGUARDS ---\n";

// TEST 1: DateHelper::calculateAge edge cases
$testCases = [
    ['dob' => '2015-05-12', 'event' => '2026-11-20', 'expected' => 11],
    ['dob' => '2018-12-31', 'event' => '2026-06-15', 'expected' => 8],
    ['dob' => '0000-00-00', 'event' => '2026-06-15', 'expected' => 0], // MySQL invalid date
    ['dob' => '',           'event' => '2026-06-15', 'expected' => 0], // Empty string
    ['dob' => null,         'event' => '2026-06-15', 'expected' => 0], // Null
    ['dob' => 'not-a-date', 'event' => '2026-06-15', 'expected' => 0], // Corrupt string
    ['dob' => '2016-01-01', 'event' => null,         'expected' => (int)date('Y') - 2016], // Null event date
];

foreach ($testCases as $i => $tc) {
    try {
        $age = DateHelper::calculateAge($tc['dob'], $tc['event']);
        assert($age === $tc['expected'], "Case $i age ($age) did not match expected ({$tc['expected']})");
        echo "✓ DateHelper case $i: dob '{$tc['dob']}' -> age {$age} [OK]\n";
    } catch (\Throwable $e) {
        echo "✗ DateHelper case $i threw exception: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// TEST 2: RollFinanceHelper::getEventFees inside active transaction vs outside
class MockPdoWithTx {
    public $inTx = false;
    public $execCalled = 0;
    public function inTransaction() { return $this->inTx; }
    public function exec($sql) {
        $this->execCalled++;
        return 1;
    }
    public function prepare($sql) {
        return new class {
            public function execute($params) {}
            public function fetch($mode) { return false; }
        };
    }
}

$mockDb = new MockPdoWithTx();
$mockDb->inTx = true; // In transaction!
RollFinanceHelper::getEventFees($mockDb, 3);
assert($mockDb->execCalled === 0, "DDL migration should NOT be executed when inTransaction is true!");
echo "✓ RollFinanceHelper skips DDL inside active transaction [OK]\n";

$mockDb->inTx = false; // Outside transaction
RollFinanceHelper::getEventFees($mockDb, 3);
assert($mockDb->execCalled > 0, "DDL migration should run when NOT in transaction");
echo "✓ RollFinanceHelper runs DDL outside transaction [OK]\n";

// TEST 3: UploadService fallback check
$tmpTestFile = tempnam(sys_get_temp_dir(), 'pay_test_');
file_put_contents($tmpTestFile, 'FAKE_IMAGE_DATA_FOR_PAYMENT');
$dummyFile = [
    'name' => 'receipt.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $tmpTestFile,
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];

// Since GD will fail on non-image data, uploadPaymentProof should catch \Throwable and fallback cleanly
try {
    $uploaded = UploadService::uploadPaymentProof($dummyFile);
    assert(!empty($uploaded), "Fallback should have produced a saved payment file");
    echo "✓ UploadService fallback saved non-standard / GD-failing image: $uploaded [OK]\n";
    
    // Cleanup generated file
    UploadService::deleteFile('payments', $uploaded);
} catch (\Throwable $e) {
    echo "✗ UploadService failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (file_exists($tmpTestFile)) unlink($tmpTestFile);

// TEST 4: Shared invoice data builder, User print_invoice links & Admin WA link
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Roll/Controllers/Admin/RollEntryController.php';

putenv('APP_URL=https://setsystem.id');
ob_start();
$bills = [
    ['id' => 5, 'event_id' => 5, 'event_name' => 'INDONESIA OPEN', 'amount' => 4200000, 'status' => 'Paid', 'entries' => 10],
    ['id' => 6, 'event_id' => 6, 'event_name' => 'OTHER EVENT', 'amount' => 350000, 'status' => 'Unpaid', 'entries' => 1],
];
include __DIR__ . '/../views/roll/user/checkout/index.php';
$idxHtml = (string)ob_get_clean();
assert(strpos($idxHtml, '/roll/user/checkout/print_invoice/5') !== false);
assert(strpos($idxHtml, '/roll/user/checkout/print_invoice/6') !== false);

ob_start();
$event = ['id' => 5, 'event_name' => 'INDONESIA OPEN'];
$clubName = 'Grand Wisata Roller Club';
$unpaidEntries = []; $historyEntries = []; $totalFee = 4200000; $paymentStatus = 'Paid';
$summaryCounts = []; $financeSummary = []; $skaterBreakdowns = [];
include __DIR__ . '/../views/roll/user/checkout/detail.php';
$detHtml = (string)ob_get_clean();
assert(strpos($detHtml, '/roll/user/checkout/print_invoice/5') !== false);

ob_start();
$eventId = 5; $eventName = 'INDONESIA OPEN'; $targetUserId = 203; $phoneUser = '082211080364';
$emailUser = 'gwis@test.id'; $totalTagihan = 4200000; $groupedSkaters = [];
$payData = ['id' => 1, 'status' => 'Paid', 'total_amount' => 4200000];
$distancesByCat = [];
include __DIR__ . '/../views/roll/admin/entries/detail.php';
$admHtml = (string)ob_get_clean();
$expectedTok = \App\Roll\Controllers\Admin\RollEntryController::makePublicInvoiceToken(203, 5);
$expectedUrl = 'https://setsystem.id/roll/invoice?e=5&c=203&t=' . $expectedTok;
assert(\App\Roll\Controllers\Admin\RollEntryController::verifyPublicInvoiceToken(203, 5, $expectedTok) === true);
assert(\App\Roll\Controllers\Admin\RollEntryController::verifyPublicInvoiceToken(204, 5, $expectedTok) === false, 'Tampered club id must fail');
assert(\App\Roll\Controllers\Admin\RollEntryController::verifyPublicInvoiceToken(203, 6, $expectedTok) === false, 'Tampered event id must fail');
assert(\App\Roll\Controllers\Admin\RollEntryController::verifyPublicInvoiceToken(203, 5, 'invalidtoken') === false, 'Invalid token must fail');
assert(strpos($admHtml, urlencode($expectedUrl)) !== false);
assert(strpos($admHtml, urlencode('*Silakan cek rincian & cetak Invoice / Tanda Terima resmi melalui link berikut:*')) !== false);
assert(strpos($admHtml, urlencode('INDONESIA OPEN')) !== false);

echo "✓ User invoice buttons (all statuses) & Admin Balas via WA link verified [OK]\n";

// TEST 5: Relay team grouping splits by race_class_id + team_name (e.g. 'monastana' in KU B Putra vs KU C Putra vs KU C Putri)
$relayEntries = [
    ['race_class_id' => 101, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU B', 'class_gender' => 'Putra', 'team_name' => 'monastana', 'team_role' => '', 'skater_name' => 'ABDILLAH', 'gender' => 'M', 'club_name' => 'Monastana'],
    ['race_class_id' => 101, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU B', 'class_gender' => 'Putra', 'team_name' => 'monastana', 'team_role' => '', 'skater_name' => 'HANNAN', 'gender' => 'M', 'club_name' => 'Monastana'],
    ['race_class_id' => 101, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU B', 'class_gender' => 'Putra', 'team_name' => 'monastana', 'team_role' => '', 'skater_name' => 'KEEANO', 'gender' => 'M', 'club_name' => 'Monastana'],
    ['race_class_id' => 102, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU C', 'class_gender' => 'Putri', 'team_name' => 'monastana', 'team_role' => '', 'skater_name' => 'KEINARRA', 'gender' => 'F', 'club_name' => 'Monastana'],
    ['race_class_id' => 102, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU C', 'class_gender' => 'Putri', 'team_name' => 'monastana', 'team_role' => '', 'skater_name' => 'NAYSILA', 'gender' => 'F', 'club_name' => 'Monastana'],
    ['race_class_id' => 102, 'class_name' => 'Speed', 'distance_name' => 'Relay 3000m', 'group_name' => 'KU C', 'class_gender' => 'Putri', 'team_name' => 'monastana', 'team_role' => 'cadangan', 'skater_name' => 'STESHA', 'gender' => 'F', 'club_name' => 'Monastana'],
];
$teams = \App\Roll\Controllers\Admin\RollEntryController::collectRelayTeamGroups($relayEntries);
assert(count($teams) === 2, 'Two distinct race classes with same team name must stay separate teams');
assert(count($teams[0]['members']) === 3 && count($teams[1]['members']) === 3);
assert(strpos($teams[0]['race_name'], 'PUTRA') !== false && strpos($teams[1]['race_name'], 'PUTRI') !== false);

foreach (['print_invoice.php', 'print_manual_invoice.php'] as $invView) {
    ob_start();
    $event = ['id' => 5, 'event_name' => 'INDONESIA OPEN', 'event_date_start' => '2026-12-04', 'event_date_end' => '2026-12-06'];
    $clubName = 'Monastana'; $invoiceCode = 'MAN-TOK-1';
    $payData = ['status' => 'Paid', 'total_amount' => 1000000];
    $groupedSkaters = []; $totalTagihan = 1000000;
    include __DIR__ . '/../views/roll/admin/entries/' . $invView;
    $invHtml = (string)ob_get_clean();
    assert(substr_count($invHtml, 'rowspan="3"') === 4, "$invView must rowspan NO & NAMA TIM per 3-athlete team block (2 cols x 2 teams)");
    assert(strpos($invHtml, '3 Atlet') !== false);
    assert(strpos($invHtml, 'CADANGAN') !== false);
}
echo "✓ Relay team table groups properly per (race + team) with rowspan across team members [OK]\n";

// TEST 6: Admin entries bulk delete deletes club entries owned via roll_skaters.club_id even when roll_entries.club_id IS NULL
if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    $memDb = new PDO('sqlite::memory:');
    $memDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $memDb->exec("CREATE TABLE roll_skaters (id INTEGER PRIMARY KEY, club_id INTEGER)");
    $memDb->exec("CREATE TABLE roll_entries (id INTEGER PRIMARY KEY, event_id INTEGER, skater_id INTEGER, club_id INTEGER, manual_invoice_code TEXT)");
    $memDb->exec("CREATE TABLE roll_payments (id INTEGER PRIMARY KEY, club_id INTEGER, event_id INTEGER, status TEXT)");

    $memDb->exec("INSERT INTO roll_skaters (id, club_id) VALUES (10, 5), (11, 6)");
    $memDb->exec("INSERT INTO roll_entries (id, event_id, skater_id, club_id, manual_invoice_code) VALUES
        (1, 5, 10, NULL, NULL),
        (2, 5, 10, 5, NULL),
        (3, 5, 10, NULL, 'MAN-TOK-1'),
        (4, 5, 11, NULL, NULL),
        (5, 6, 10, NULL, NULL)
    ");
    $memDb->exec("INSERT INTO roll_payments (id, club_id, event_id, status) VALUES (1, 5, 5, 'Pending'), (2, 5, 6, 'Paid'), (3, 6, 5, 'Paid')");

    $deleted = \App\Roll\Controllers\Admin\RollEntryController::deleteClubEventEntries($memDb, 5, 5);
    assert($deleted === 2, "Expected 2 club=5 event=5 non-token entries deleted, got $deleted");
    $remainingIds = $memDb->query("SELECT id FROM roll_entries ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    assert(array_map('intval', $remainingIds) === [3, 4, 5], 'Only MAN-TOK, other club, and other event entries may remain');
    $remainingPayIds = $memDb->query("SELECT id FROM roll_payments ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    assert(array_map('intval', $remainingPayIds) === [2, 3], 'Only target club+event payment should be deleted');
    echo "✓ Admin deleteClubEventEntries deletes regular (club_id IS NULL via roll_skaters.club_id) & direct entries [OK]\n";
} else {
    echo "ℹ SQLite PDO driver unavailable; skipped in-memory deleteClubEventEntries SQL check\n";
}

echo "--- ALL CHECKOUT PAY SAFEGUARD TESTS PASSED! ---\n";

