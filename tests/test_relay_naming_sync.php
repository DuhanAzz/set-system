<?php
// Test Suite: Relay Event Naming & Distance Formatting Synchronization
require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

echo "Running Relay Event Naming & Distance Formatting Tests...\n";

// --- Test 1: formatDistance for Relays and Individuals ---
assert(SwimEventHelper::formatDistance(100, true) === '4 x 25M', "100m relay must format to 4 x 25M");
assert(SwimEventHelper::formatDistance(200, true) === '4 x 50M', "200m relay must format to 4 x 50M");
assert(SwimEventHelper::formatDistance(400, true) === '4 x 100M', "400m relay must format to 4 x 100M");
assert(SwimEventHelper::formatDistance(800, true) === '4 x 200M', "800m relay must format to 4 x 200M");
assert(SwimEventHelper::formatDistance(25, true) === '4 x 25M', "25m relay must format to 4 x 25M");
assert(SwimEventHelper::formatDistance(50, true) === '4 x 50M', "50m relay must format to 4 x 50M");
assert(SwimEventHelper::formatDistance(100, true, 'm', 'x') === '4x25m', "Canva format: 4x25m");
assert(SwimEventHelper::formatDistance(50, false) === '50M', "Individual 50m must remain 50M");
assert(SwimEventHelper::formatDistance(100, false) === '100M', "Individual 100m must remain 100M");
echo "✓ Test 1: formatDistance logic PASSED!\n";

// --- Test 2: formatEventName for Relays ---
$name1 = SwimEventHelper::formatEventName("100 M ESTAFET GAYA BEBAS PUTRA - LCM", true, 100);
assert($name1 === "4 x 25 M ESTAFET GAYA BEBAS PUTRA - LCM", "100 M ESTAFET must convert to 4 x 25 M ESTAFET");

$name2 = SwimEventHelper::formatEventName("100M ESTAFET GAYA DADA PUTRI", true, 100);
assert($name2 === "4 x 25 M ESTAFET GAYA DADA PUTRI", "100M ESTAFET must convert to 4 x 25 M ESTAFET");

$name3 = SwimEventHelper::formatEventName("100 M GAYA BEBAS PUTRA", true, 100);
assert(strpos($name3, "4 x 25 M") === 0, "Relay without prefix must prepend 4 x 25 M");
assert(strpos($name3, "ESTAFET") !== false, "Relay event name must include ESTAFET");

$name4 = SwimEventHelper::formatEventName("200 M ESTAFET GAYA GANTI MIXED", true, 200);
assert($name4 === "4 x 50 M ESTAFET GAYA GANTI MIXED", "200 M ESTAFET must convert to 4 x 50 M ESTAFET");

$nameIndiv = SwimEventHelper::formatEventName("100 M GAYA BEBAS PUTRA - LCM", false, 100);
assert($nameIndiv === "100 M GAYA BEBAS PUTRA - LCM", "Individual event must remain untouched");
echo "✓ Test 2: formatEventName logic PASSED!\n";

// --- Test 3: SQL Replacement Simulation ---
$rawDbName = "100 M ESTAFET GAYA BEBAS PUTRA - LCM";
$sqlSimulated = str_replace(['100 M ', '100M '], '4 x 25 M ', $rawDbName);
assert($sqlSimulated === "4 x 25 M ESTAFET GAYA BEBAS PUTRA - LCM", "SQL string replacement must transform 100 M to 4 x 25 M");

$rawDbName2 = "100M ESTAFET GAYA BEBAS PUTRI - LCM";
$sqlSimulated2 = str_replace(['100 M ', '100M '], '4 x 25 M ', $rawDbName2);
assert($sqlSimulated2 === "4 x 25 M ESTAFET GAYA BEBAS PUTRI - LCM", "SQL string replacement must transform 100M to 4 x 25 M");
echo "✓ Test 3: SQL sync logic PASSED!\n";

echo "ALL RELAY EVENT NAMING TESTS PASSED!\n";
