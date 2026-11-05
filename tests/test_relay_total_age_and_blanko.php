<?php

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

echo "Running Relay Total Age, Blanko & Results Print Tests...\n";

// 1. Test Swimmer Age Calculation (Dec 31 Mode)
$eventDate = '2026-11-10';
$dob1 = '2014-05-15'; // 2026 - 2014 = 12 Th
$dob2 = '2015-08-20'; // 2026 - 2015 = 11 Th
$dob3 = '2013-01-10'; // 2026 - 2013 = 13 Th
$dob4 = '2016-12-05'; // 2026 - 2016 = 10 Th

$age1 = SwimEventHelper::calculateSwimmerAge($dob1, $eventDate, 'Dec 31');
$age2 = SwimEventHelper::calculateSwimmerAge($dob2, $eventDate, 'Dec 31');
$age3 = SwimEventHelper::calculateSwimmerAge($dob3, $eventDate, 'Dec 31');
$age4 = SwimEventHelper::calculateSwimmerAge($dob4, $eventDate, 'Dec 31');

assert($age1 === 12, "Age 1 should be 12 in Dec 31 mode, got: $age1");
assert($age2 === 11, "Age 2 should be 11 in Dec 31 mode, got: $age2");
assert($age3 === 13, "Age 3 should be 13 in Dec 31 mode, got: $age3");
assert($age4 === 10, "Age 4 should be 10 in Dec 31 mode, got: $age4");
echo "✓ Test 1: calculateSwimmerAge in Dec 31 mode PASSED!\n";

// 2. Test Team Total Age Calculation
$teamDobs = [$dob1, $dob2, $dob3, $dob4];
$totalAge = SwimEventHelper::calculateTeamTotalAge($teamDobs, $eventDate, 'Dec 31');
$expectedTotal = 12 + 11 + 13 + 10; // 46
assert($totalAge === $expectedTotal, "Total age should be 46, got: $totalAge");
echo "✓ Test 2: calculateTeamTotalAge PASSED! Total: $totalAge Years\n";

// 3. Test Total Age Limit Validation
$limit1 = 50; // Limit 50 Th -> 46 <= 50 (Valid)
$limit2 = 45; // Limit 45 Th -> 46 > 45 (Invalid / Over Limit)
$limit3 = 0;  // Limit 0 Th  -> No limit (Valid)

$isValid1 = ($limit1 <= 0) || ($totalAge <= $limit1);
$isValid2 = ($limit2 <= 0) || ($totalAge <= $limit2);
$isValid3 = ($limit3 <= 0) || ($totalAge <= $limit3);

assert($isValid1 === true, "Team with 46 years should be valid for limit 50");
assert($isValid2 === false, "Team with 46 years should be INVALID for limit 45");
assert($isValid3 === true, "Team with 46 years should be valid when limit is 0 (unlimited)");
echo "✓ Test 3: Total Age Limit validation logic PASSED!\n";

// 4. Test Event Distance & Title Formatting for Category 536
$distance = 100;
$isRelay = true;
$stroke = 'Gaya Bebas';
$ageGroup = 'KU 2';
$gender = 'L';
$poolSuffix = ' - LCM';

$distLabel = SwimEventHelper::formatDistance($distance, $isRelay);
assert($distLabel === '4 x 25M', "Relay 100m distance label should be '4 x 25M', got: $distLabel");

$cleanStroke = trim(str_ireplace(['Gaya', 'GAYA', 'ESTAFET', 'RELAY'], '', $stroke));
$gender_label = 'PUTRA';
$judulRaw = $distLabel . " ESTAFET " . strtoupper($cleanStroke) . " - " . $ageGroup . " " . $gender_label . $poolSuffix;
$judul_print = SwimEventHelper::formatEventName($judulRaw, true, $distance);

assert(strpos($judul_print, '4 x 25 M ESTAFET') !== false || strpos($judul_print, '4 x 25M ESTAFET') !== false, "Title should contain '4 x 25 M ESTAFET', got: $judul_print");
assert(strpos($judul_print, '100M') === false && strpos($judul_print, '100 M') === false, "Title must NOT contain '100M', got: $judul_print");
echo "✓ Test 4: Relay Title formatting for results print PASSED! Result: '$judul_print'\n";

// 5. Test Swimmers Age Calculation (Meet Start Mode)
$meetStartDate = '2026-06-01';
$dobJuneLater = '2014-07-15'; // hasn't turned 12 yet on June 1, 2026 -> 11 Th
$ageMeetStart = SwimEventHelper::calculateSwimmerAge($dobJuneLater, $meetStartDate, 'Meet Start');
assert($ageMeetStart === 11, "Meet Start calculation should be 11, got: $ageMeetStart");
echo "✓ Test 5: calculateSwimmerAge in Meet Start mode PASSED!\n";

echo "ALL RELAY TOTAL AGE, BLANKO & RESULTS PRINT TESTS PASSED!\n";

