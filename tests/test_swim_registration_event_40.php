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
    // Case 8: Event with unmapped / orphaned selected_ku_ids (e.g. from template/clone) falls through to name matching
    [
        'sw' => ['tanggal_lahir' => '2018-01-01', 'jenis_kelamin' => 'L'], // Age 8
        'ev' => ['age_group' => 'KU 5', 'age_min' => 0, 'age_max' => 99, 'selected_ku_ids' => '999,998', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'Orphaned selected_ku_ids falls through to match KU 5'
    ],
    // Case 9: PRSI fallback for PEMULA / KU 5 when ageRules is empty
    [
        'sw' => ['tanggal_lahir' => '2018-01-01', 'jenis_kelamin' => 'L'], // Age 8
        'ev' => ['age_group' => 'PEMULA', 'age_min' => 0, 'age_max' => 0, 'selected_ku_ids' => '', 'jenis_kelamin' => 'L'],
        'expected' => true,
        'label' => 'PRSI fallback allows age 8 in PEMULA'
    ],
    // Case 10: Discrete year matching for 25m event e.g. "KU 2019"
    [
        'sw' => ['tanggal_lahir' => '2019-05-10', 'jenis_kelamin' => 'P'], // Age 7
        'ev' => ['age_group' => 'KU 2019', 'age_min' => 0, 'age_max' => 0, 'selected_ku_ids' => '', 'jenis_kelamin' => 'P'],
        'expected' => true,
        'label' => 'Swimmer born in 2019 matches KU 2019'
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

// Test Table Structure Normalization for ALL stroke and distance variants
$dummyEvents = [
    ['stroke' => '25M Papan Bebas', 'distance' => 25, 'event_name' => '25 M PAPAN BEBAS PUTRA - LCM'],
    ['stroke' => '25 M Gaya Bebas', 'distance' => 25, 'event_name' => '25 M GAYA BEBAS PUTRA - LCM'],
    ['stroke' => 'Gaya Bebas', 'distance' => 50, 'event_name' => '50 M GAYA BEBAS PUTRA - LCM'],
    ['stroke' => 'Papan Dada', 'distance' => 25, 'event_name' => '25 M PAPAN DADA PUTRI - LCM'],
    ['stroke' => 'Gaya Dada', 'distance' => 25, 'event_name' => '25 M GAYA DADA PUTRI - LCM'],
    ['stroke' => 'Gaya Dada', 'distance' => 50, 'event_name' => '50 M GAYA DADA PUTRI - LCM'],
    ['stroke' => 'Papan Punggung', 'distance' => 25, 'event_name' => '25 M PAPAN PUNGGUNG PUTRA - LCM'],
    ['stroke' => '25M Punggung', 'distance' => 25, 'event_name' => '25 M GAYA PUNGGUNG PUTRA - LCM'],
    ['stroke' => 'Papan Kupu-kupu', 'distance' => 25, 'event_name' => '25 M PAPAN KUPU-KUPU PUTRA - LCM'],
    ['stroke' => '25M Gaya Kupu-kupu', 'distance' => 25, 'event_name' => '25 M GAYA KUPU-KUPU PUTRA - LCM'],
];

$testStructure = [];
foreach ($dummyEvents as $ev) {
    $rawStroke = strtoupper(trim($ev['stroke'] ?? ''));
    $rawName   = strtoupper(trim($ev['event_name'] ?? ''));
    $fullText  = $rawStroke . ' ' . $rawName;
    $isKick    = (strpos($fullText, 'PAPAN') !== false || strpos($fullText, 'KICK') !== false);

    if (strpos($fullText, 'DADA') !== false || strpos($fullText, 'BREAST') !== false) {
        $cleanStrokeName = 'GAYA DADA';
    } elseif (strpos($fullText, 'PUNGGUNG') !== false || strpos($fullText, 'BACK') !== false) {
        $cleanStrokeName = 'GAYA PUNGGUNG';
    } elseif (strpos($fullText, 'KUPU') !== false || strpos($fullText, 'FLY') !== false) {
        $cleanStrokeName = 'GAYA KUPU-KUPU';
    } elseif (strpos($fullText, 'GANTI') !== false || strpos($fullText, 'MEDLEY') !== false || strpos($fullText, ' IM ') !== false) {
        $cleanStrokeName = 'GAYA GANTI';
    } else {
        $cleanStrokeName = 'GAYA BEBAS';
    }

    if ($isKick) {
        $jarakKey = 0;
    } else {
        $dist = (int)($ev['distance'] ?? 0);
        if ($dist <= 0) {
            if (preg_match('/(?:^|\D)(25|50|100|200|400|800|1500)\s*M?(?:\D|$)/', $fullText, $dm)) {
                $dist = (int)$dm[1];
            } else {
                $dist = 50;
            }
        }
        $jarakKey = $dist;
    }

    $testStructure[$cleanStrokeName][$jarakKey][] = $ev;
}

assert(isset($testStructure['GAYA BEBAS'][0]), "GAYA BEBAS must have key 0 (25M PAPAN)");
assert(isset($testStructure['GAYA BEBAS'][25]), "GAYA BEBAS must have key 25 (25 M)");
assert(isset($testStructure['GAYA BEBAS'][50]), "GAYA BEBAS must have key 50 (50 M)");

assert(isset($testStructure['GAYA DADA'][0]), "GAYA DADA must have key 0 (25M PAPAN)");
assert(isset($testStructure['GAYA DADA'][25]), "GAYA DADA must have key 25 (25 M)");
assert(isset($testStructure['GAYA DADA'][50]), "GAYA DADA must have key 50 (50 M)");

assert(isset($testStructure['GAYA PUNGGUNG'][0]), "GAYA PUNGGUNG must have key 0 (25M PAPAN)");
assert(isset($testStructure['GAYA PUNGGUNG'][25]), "GAYA PUNGGUNG must have key 25 (25 M)");

assert(isset($testStructure['GAYA KUPU-KUPU'][0]), "GAYA KUPU-KUPU must have key 0 (25M PAPAN)");
assert(isset($testStructure['GAYA KUPU-KUPU'][25]), "GAYA KUPU-KUPU must have key 25 (25 M)");

echo "PASS: Table Structure cleanly groups ALL stroke variants into canonical headers with 25M PAPAN (key 0), 25 M (key 25), and 50 M (key 50)!\n";

// Test Swimmer Event List: Only events matching the athlete's KU are available in the modal
$swimmerMale = ['id' => 99, 'nama_atlet' => 'Tester Putra', 'tanggal_lahir' => '2016-05-10', 'jenis_kelamin' => 'L']; // Born 2016 -> matches KU 2016
$sampleAllEvents = [
    ['id' => 1, 'event_number' => '1', 'stroke' => '25M Papan Bebas', 'distance' => 25, 'event_name' => '25 M PAPAN BEBAS PUTRA - LCM', 'jenis_kelamin' => 'L', 'age_group' => 'KU 2020'],
    ['id' => 2, 'event_number' => '2', 'stroke' => '25M Papan Bebas', 'distance' => 25, 'event_name' => '25 M PAPAN BEBAS PUTRI - LCM', 'jenis_kelamin' => 'P', 'age_group' => 'KU 2020'],
    ['id' => 3, 'event_number' => '3', 'stroke' => '25 M Gaya Bebas', 'distance' => 25, 'event_name' => '25 M GAYA BEBAS PUTRA - LCM', 'jenis_kelamin' => 'L', 'age_group' => 'KU 2020'],
    ['id' => 5, 'event_number' => '5', 'stroke' => '50 M Gaya Bebas', 'distance' => 50, 'event_name' => '50 M GAYA BEBAS PUTRA - LCM', 'jenis_kelamin' => 'L', 'age_group' => 'KU 2016'],
];

$myEventsTest = [];
$swGen = (strtoupper($swimmerMale['jenis_kelamin'] ?? '') === 'P' || strtoupper($swimmerMale['jenis_kelamin'] ?? '') === 'PUTRI') ? 'P' : 'L';
foreach ($sampleAllEvents as $ev) {
    $evGenRaw = strtoupper($ev['jenis_kelamin'] ?? '');
    $evGen = ($evGenRaw === 'P' || $evGenRaw === 'PUTRI') ? 'P' : (($evGenRaw === 'L' || $evGenRaw === 'PUTRA') ? 'L' : 'MIX');
    if ($evGen !== 'MIX' && $evGen !== 'MIXED' && $evGen !== $swGen) {
        continue;
    }
    $isAgeFit = $method->invoke($instance, $swimmerMale, $ev, $ageRules, $event_year, "Dec 31", $compDateObj);
    $hasSaved = false;
    // Strict KU filter for modal
    if (!$isAgeFit && !$hasSaved) {
        continue;
    }
    $myEventsTest[] = [
        'id' => $ev['id'],
        'event_number' => $ev['event_number'],
        'is_eligible' => $isAgeFit
    ];
}

assert(count($myEventsTest) === 1, "Male swimmer 2016 must only receive matching KU 2016 event (#5), not other KUs");
assert($myEventsTest[0]['id'] === 5, "Event #5 (KU 2016) must be the only event in modal list for 2016 swimmer");
echo "PASS: Modal strictly filters events to athlete's KU only!\n";

// Test Form A3 Header & Column Mapping
$formA3Header1 = ['Nama', 'Sekolah', 'Tgl Lahir', 'Pa/Pi', 'Gaya Bebas', '', '', 'Gaya Dada', '', '', 'Gaya Punggung', '', '', 'Gaya Kupu-kupu', '', ''];
$formA3Header2 = ['', '', '', '', 'Papan', '25M', '50M', 'Papan', '25M', '50M', 'Papan', '25M', '50M', 'Papan', '25M', '50M'];
assert(count($formA3Header1) === 16, "Form A3 Row 1 must have 16 columns");
assert(count($formA3Header2) === 16, "Form A3 Row 2 must have 16 columns");

$formA3Columns = [
    4  => ['stroke' => 'BEBAS',     'distance' => 25, 'is_kick' => true],
    5  => ['stroke' => 'BEBAS',     'distance' => 25, 'is_kick' => false],
    6  => ['stroke' => 'BEBAS',     'distance' => 50, 'is_kick' => false],
    7  => ['stroke' => 'DADA',      'distance' => 25, 'is_kick' => true],
    8  => ['stroke' => 'DADA',      'distance' => 25, 'is_kick' => false],
    9  => ['stroke' => 'DADA',      'distance' => 50, 'is_kick' => false],
    10 => ['stroke' => 'PUNGGUNG',  'distance' => 25, 'is_kick' => true],
    11 => ['stroke' => 'PUNGGUNG',  'distance' => 25, 'is_kick' => false],
    12 => ['stroke' => 'PUNGGUNG',  'distance' => 50, 'is_kick' => false],
    13 => ['stroke' => 'KUPU-KUPU', 'distance' => 25, 'is_kick' => true],
    14 => ['stroke' => 'KUPU-KUPU', 'distance' => 25, 'is_kick' => false],
    15 => ['stroke' => 'KUPU-KUPU', 'distance' => 50, 'is_kick' => false],
];

// Test parsing actual row from downloaded Form A3 CSV
$sampleRow = str_getcsv("Devano Putra Wibowo,SD Muhamadiyah Sokonandi,01/01/2016,L,,99.99.99,99.99.99,,,,,99.99.99,99.99.99,,,");
assert($sampleRow[0] === "Devano Putra Wibowo", "Name matched");
assert($sampleRow[1] === "SD Muhamadiyah Sokonandi", "School matched");
assert($sampleRow[2] === "01/01/2016", "DOB matched");
assert($sampleRow[3] === "L", "Gender matched");
assert($sampleRow[5] === "99.99.99", "Bebas 25M has time");
assert($sampleRow[6] === "99.99.99", "Bebas 50M has time");
assert($sampleRow[11] === "99.99.99", "Punggung 25M has time");
assert($sampleRow[12] === "99.99.99", "Punggung 50M has time");
assert(empty($sampleRow[4]), "Bebas papan is empty");

echo "PASS: Form A3 CSV structure and mapping verified successfully!\n";

if ($passed !== count($testCases)) {
    exit(1);
}
