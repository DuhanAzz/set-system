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
assert(strpos($admHtml, urlencode('https://setsystem.id/roll/user/checkout/detail/5')) !== false);
assert(strpos($admHtml, urlencode('INDONESIA OPEN')) !== false);

echo "✓ User invoice buttons (all statuses) & Admin Balas via WA link verified [OK]\n";

// TEST 5: Relay rows in print_invoice & print_manual_invoice render per member (no rowspan) even with same team name
foreach (['print_invoice.php', 'print_manual_invoice.php'] as $invView) {
    ob_start();
    $event = ['id' => 5, 'event_name' => 'INDONESIA OPEN', 'event_date_start' => '2026-12-04', 'event_date_end' => '2026-12-06'];
    $clubName = 'Monastana'; $invoiceCode = 'MAN-TOK-1';
    $payData = ['status' => 'Paid', 'total_amount' => 1000000];
    $groupedSkaters = []; $totalTagihan = 1000000;
    $teams = [
        'Monastana' => [
            ['team_name' => 'Monastana', 'skater_name' => 'ABDILLAH ISMAIL PASA', 'gender' => 'Putra', 'team_role' => '', 'club_name' => 'Monastana', 'race_name' => 'SPEED - RELAY 3000M - KU B'],
            ['team_name' => 'Monastana', 'skater_name' => 'ABIGHAIL AINI AZIZ', 'gender' => 'Putri', 'team_role' => 'cadangan', 'club_name' => 'Monastana', 'race_name' => 'SPEED - RELAY 3000M - KU D'],
        ],
    ];
    include __DIR__ . '/../views/roll/admin/entries/' . $invView;
    $invHtml = (string)ob_get_clean();
    assert(strpos($invHtml, 'rowspan=') === false, "$invView must NOT use rowspan on relay team rows");
    assert(substr_count($invHtml, 'Monastana') >= 4, "$invView must repeat team name on each athlete row");
    assert(strpos($invHtml, 'CADANGAN') !== false);
}
echo "✓ Relay team table renders per member row without rowspan even for identical team names [OK]\n";
echo "--- ALL CHECKOUT PAY SAFEGUARD TESTS PASSED! ---\n";

