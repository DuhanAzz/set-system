<?php

require_once __DIR__ . '/../app/Helpers/DateHelper.php';

use App\Helpers\DateHelper;

echo "--- RUNNING TEST: ROLL 31-DEC EVENT YEAR AGE CALCULATION ---\n";

// 1. Event year resolution & fallback
assert(DateHelper::getEventYear('2027-01-15') === 2027);
assert(DateHelper::getEventYear('2026-12-31') === 2026);
$currentYear = (int)date('Y');
assert(DateHelper::getEventYear(null) === $currentYear);
assert(DateHelper::getEventYear('') === $currentYear);
assert(DateHelper::getEventYear('0000-00-00') === $currentYear);
echo "✓ getEventYear parsing & TBA fallback [OK]\n";

// 2. 31 December event-year age calculation (independent of birth month/day & current date)
// Athlete born early or late in 2018 competing in Jan 2027 must both be 9 yrs old (2027 - 2018)
assert(DateHelper::calculateAge('2018-01-05', '2027-01-10') === 9);
assert(DateHelper::calculateAge('2018-12-30', '2027-01-10') === 9);
assert(DateHelper::calculateAge('2018-12-30', '2026-11-27') === 8);
assert(DateHelper::calculateAge('0000-00-00', '2027-01-10') === 0);
assert(DateHelper::calculateAge('2028-01-01', '2027-01-10') === 0);
echo "✓ calculateAge 31-Dec cutoff [OK]\n";

// 3. Veteran PON forced to 25
assert(DateHelper::getSkaterEventAge(['birth_date' => '2015-05-01', 'is_pon_veteran' => 0], '2027-02-01') === 12);
assert(DateHelper::getSkaterEventAge(['birth_date' => '2015-05-01', 'is_pon_veteran' => 1], '2027-02-01') === 25);
echo "✓ getSkaterEventAge PON override [OK]\n";

// 4. Server-side individual class age validation against mock DB
$mockStmt = new class {
    public $row = false;
    public function execute($params) { return true; }
    public function fetch($mode = null) { return $this->row; }
};
$mockDb = new class($mockStmt) {
    private $stmt;
    public function __construct($stmt) { $this->stmt = $stmt; }
    public function prepare($sql) { return $this->stmt; }
};

$skater = ['skater_name' => 'BUDI', 'birth_date' => '2018-11-20', 'is_pon_veteran' => 0]; // 9 yrs in 2027

// KU C (9-10) in 2027 -> eligible
$mockStmt->row = ['category_name' => 'SPEED', 'group_name' => 'KU C', 'min_year' => 9, 'max_year' => 10];
assert(DateHelper::validateSkaterAgeForClass($mockDb, $skater, 10, '2027-01-15') === null);

// KU B (7-8) in 2027 -> rejected (would have passed in 2026, must fail in 2027)
$mockStmt->row = ['category_name' => 'SPEED', 'group_name' => 'KU B', 'min_year' => 7, 'max_year' => 8];
$err = DateHelper::validateSkaterAgeForClass($mockDb, $skater, 9, '2027-01-15');
assert($err !== null && strpos($err, '31 Des 2027') !== false);

// EKSEBISI -> always allowed regardless of age
$mockStmt->row = ['category_name' => 'EKSEBISI', 'group_name' => 'KU B', 'min_year' => 7, 'max_year' => 8];
assert(DateHelper::validateSkaterAgeForClass($mockDb, $skater, 11, '2027-01-15') === null);
echo "✓ validateSkaterAgeForClass rules [OK]\n";

echo "--- ALL EVENT YEAR AGE TESTS PASSED! ---\n";
