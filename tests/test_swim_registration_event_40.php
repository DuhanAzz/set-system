<?php
// Test Swimmer Eligibility Logic for Swim Registration
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once __DIR__ . '/../app/Swim/Controllers/RegistrationController.php';

// Reflection to test checkSwimmerEligibility directly
$rc = new ReflectionClass(\App\Swim\Controllers\RegistrationController::class);
$method = $rc->getMethod('checkSwimmerEligibility');
$method->setAccessible(true);

// Mock instance without triggering constructor DB connection
$instance = $rc->newInstanceWithoutConstructor();

$event_year = 2026;
$compDateObj = new DateTime("2026-11-08");

$ageRules = [
    1 => ["min_age" => 10, "max_age" => 10, "group_name" => "KU 2016"],
    2 => ["min_age" => 10, "max_age" => 12, "group_name" => "2014-2016"],
    3 => ["min_age" => 10, "max_age" => 11, "group_name" => "KU 4"],
    4 => ["min_age" => 8, "max_age" => 9, "group_name" => "KU 5"],
];

$testCases = [
    // Case 1: Swimmer 2015 (Age 11) in range 2014-2016
    [
        'sw' => ['tanggal_lahir' => '2015-06-15', 'jenis_kelamin' => 'L'],
        'ev' => ['age_group' => '2014-2016', 'age_min' => 10, 'age_max' => 12, 'selected_ku_ids' => '2', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'Swimmer 2015 inside range 2014-2016'
    ],
    // Case 2: Swimmer 2018 (Age 8) in "2017 & LEBIH MUDA"
    [
        'sw' => ['tanggal_lahir' => '2018-03-20', 'jenis_kelamin' => 'L'],
        'ev' => ['age_group' => '2017 & LEBIH MUDA', 'age_min' => 0, 'age_max' => 9, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'Swimmer 2018 in 2017 & Lebih Muda'
    ],
    // Case 3: Swimmer 2015 (Age 11) in "2017 & LEBIH MUDA" -> should be false
    [
        'sw' => ['tanggal_lahir' => '2015-03-20', 'jenis_kelamin' => 'L'],
        'ev' => ['age_group' => '2017 & LEBIH MUDA', 'age_min' => 0, 'age_max' => 9, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => false,
        'label' => 'Swimmer 2015 in 2017 & Lebih Muda should be false'
    ],
    // Case 4: Event with competition year 2026 in name e.g. "KU 2026" should not lock out normal swimmer
    [
        'sw' => ['tanggal_lahir' => '2016-08-10', 'jenis_kelamin' => 'L'],
        'ev' => ['age_group' => 'KU 2026', 'age_min' => 0, 'age_max' => 99, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'Event year 2026 does not lock out swimmer'
    ],
    // Case 5: Event KU 4 without selected_ku_ids should match KU 4 ageRule (min 10, max 11)
    [
        'sw' => ['tanggal_lahir' => '2018-01-01', 'jenis_kelamin' => 'L'], // Age 8
        'ev' => ['age_group' => 'KU 4', 'age_min' => 0, 'age_max' => 99, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => false,
        'label' => 'Swimmer age 8 cannot enter KU 4 (10-11)'
    ],
    // Case 6: Swimmer age 10 CAN enter KU 4
    [
        'sw' => ['tanggal_lahir' => '2016-01-01', 'jenis_kelamin' => 'L'], // Age 10
        'ev' => ['age_group' => 'KU 4', 'age_min' => 0, 'age_max' => 99, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'Swimmer age 10 can enter KU 4 (10-11)'
    ],
    // Case 7: Gender mismatch
    [
        'sw' => ['tanggal_lahir' => '2016-01-01', 'jenis_kelamin' => 'L'],
        'ev' => ['age_group' => 'KU 2016', 'age_min' => 10, 'age_max' => 10, 'selected_ku_ids' => '1', 'jenis_kelamin' => 'P'],
        'expected' => false,
        'label' => 'Male swimmer cannot enter Putri event'
    ],
];

$passed = 0;
foreach ($testCases as $tc) {
    $result = $method->invoke($instance, $tc['sw'], $tc['ev'], $ageRules, $event_year, "Dec 31", $compDateObj);
    if ($result === $tc['expected']) {
        echo "PASS: {$tc['label']}\n";
        $passed++;
    } else {
        echo "FAIL: {$tc['label']} (Expected " . ($tc['expected'] ? "TRUE" : "FALSE") . ", got " . ($result ? "TRUE" : "FALSE") . ")\n";
    }
}

echo "\nResult: $passed / " . count($testCases) . " passed.\n";
if ($passed !== count($testCases)) {
    exit(1);
}
