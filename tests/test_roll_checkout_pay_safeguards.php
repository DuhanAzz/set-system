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

echo "--- ALL CHECKOUT PAY SAFEGUARD TESTS PASSED! ---\n";

