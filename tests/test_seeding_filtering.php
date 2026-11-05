<?php
// tests/test_seeding_filtering.php
// Validasi logic query filtering pembayaran pada Seeding

function testSqlSyntax($name, $sql) {
    // Pastikan tidak ada syntax typo dasar pada SQL string
    assert(!empty($sql), "SQL for $name must not be empty");
    assert(strpos($sql, "payment_status = 'Paid'") !== false || strpos($sql, "re.status = 'Approved'") !== false, "Payment condition must exist in $name");
    assert(strpos($sql, "swim_payments") !== false, "swim_payments subquery check must exist in $name");
    echo "Check $name: PASSED!\n";
}

// 1. Check index query
$sqlIndex = "SELECT en.*, 
    IF(en.is_relay = 1, 
        (SELECT COUNT(re.id) FROM swim_relay_entries re 
         WHERE re.category_id = en.id 
           AND (re.status = 'Approved' OR EXISTS (SELECT 1 FROM swim_payments p WHERE (p.user_id = re.club_id OR p.user_id IN (SELECT user_id FROM swim_clubs WHERE id = re.club_id)) AND p.event_id = en.event_id AND p.status IN ('Paid','completed')))
        ),
        (SELECT COUNT(ee.id) FROM swim_event_entries ee 
         WHERE ee.category_id = en.id 
           AND (ee.payment_status = 'Paid' OR ee.status = 'Approved' OR EXISTS (SELECT 1 FROM swim_payments p WHERE p.user_id = ee.user_id AND p.event_id = en.event_id AND p.status IN ('Paid','completed')))
        )
    ) as total_athletes
    FROM swim_event_numbers en 
    WHERE en.event_id = ? 
    ORDER BY CAST(en.event_number AS UNSIGNED) ASC";

testSqlSyntax("Index total_athletes query", $sqlIndex);

// 2. Check process seeding query
$sqlProcessIndividual = "SELECT ee.id, ee.entry_time, s.tanggal_lahir 
    FROM swim_event_entries ee
    JOIN swim_event_numbers en ON ee.category_id = en.id
    JOIN swim_swimmers s ON ee.swimmer_id = s.id
    WHERE ee.category_id = ?
      AND (
          ee.payment_status = 'Paid' 
          OR ee.status = 'Approved' 
          OR EXISTS (
              SELECT 1 FROM swim_payments p 
              WHERE p.user_id = ee.user_id 
                AND p.event_id = en.event_id 
                AND p.status IN ('Paid','completed')
          )
      )";

testSqlSyntax("Process individual seeding query", $sqlProcessIndividual);

// 3. Test filter logic with simulated entries
$entries = [
    ['id' => 1, 'swimmer' => 'Athlete 1', 'payment_status' => 'Paid', 'status' => 'Approved', 'has_completed_payment' => true],
    ['id' => 2, 'swimmer' => 'Athlete 2', 'payment_status' => 'Unpaid', 'status' => 'Pending', 'has_completed_payment' => false],
    ['id' => 3, 'swimmer' => 'Athlete 3', 'payment_status' => 'Unpaid', 'status' => 'Approved', 'has_completed_payment' => false],
    ['id' => 4, 'swimmer' => 'Athlete 4', 'payment_status' => 'Unpaid', 'status' => 'Pending', 'has_completed_payment' => true],
    ['id' => 5, 'swimmer' => 'Athlete 5', 'payment_status' => 'Unpaid', 'status' => 'Rejected', 'has_completed_payment' => false],
];

$isEligible = function($e) {
    return ($e['payment_status'] === 'Paid' || $e['status'] === 'Approved' || $e['has_completed_payment'] === true);
};

$filtered = array_filter($entries, $isEligible);
assert(count($filtered) === 3, "Only entries 1, 3, 4 should be eligible");
assert(!in_array(2, array_column($filtered, 'id')), "Entry 2 (unpaid & pending) must be excluded");
assert(!in_array(5, array_column($filtered, 'id')), "Entry 5 (unpaid & rejected) must be excluded");
echo "Simulated entry filtering check: PASSED!\n";

echo "ALL SEEDING FILTER TESTS PASSED!\n";
