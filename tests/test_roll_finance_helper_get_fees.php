<?php
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';

use App\Helpers\RollFinanceHelper;

echo "--- RUNNING TEST: ROLL FINANCE HELPER GET EVENT FEES FALLBACK ---\n";

// Test 1: Null event ID returns defaults
$feesNull = RollFinanceHelper::getEventFees(null, 0);
assert($feesNull['fee_speed'] === 400000);
assert($feesNull['quota_speed_basic'] === 2);
assert($feesNull['quota_std_basic'] === 2);
assert($feesNull['quota_pemula_basic'] === 2);
echo "✓ Test 1: getEventFees(null, 0) returns complete valid default structure [OK]\n";

// Test 2: Fallback when DB query fails completely
class FailingPdoMock {
    public function exec($sql) { throw new \Exception("Table locked or no alter permission"); }
    public function prepare($sql) { throw new \Exception("Table not found"); }
}

$mockDb = new FailingPdoMock();
$feesFail = RollFinanceHelper::getEventFees($mockDb, 999);
assert(is_array($feesFail));
assert($feesFail['fee_speed'] === 400000);
assert($feesFail['fee_speed_extra'] === 0);
assert($feesFail['quota_speed_basic'] === 2);
assert($feesFail['fee_standart'] === 350000);
assert($feesFail['quota_std_basic'] === 2);
assert($feesFail['fee_pemula'] === 250000);
assert($feesFail['quota_pemula_basic'] === 2);
echo "✓ Test 2: getEventFees with failing DB returns complete fallback without throwing 500 [OK]\n";

echo "--- ALL GET EVENT FEES TESTS PASSED! ---\n";
