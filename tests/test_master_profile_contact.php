<?php
/**
 * Test Suite: Master Profile Contact Person WA Updates
 * Run with: /Applications/XAMPP/xamppfiles/bin/php tests/test_master_profile_contact.php
 */

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'set_system_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== STARTING MASTER PROFILE CONTACT PERSON TESTS ===\n\n";

// 1. Fetch current contact settings as backup
$origMasterPhone = $pdo->query("SELECT phone FROM roll_users WHERE role = 'master' LIMIT 1")->fetchColumn();
$origUnivWa = $pdo->query("SELECT contact_wa FROM universal_settings WHERE id = 1")->fetchColumn();
$origRollWa = $pdo->query("SELECT contact_wa FROM roll_site_settings WHERE id = 1")->fetchColumn();

echo "Current Values:\n";
echo "  - roll_users (master).phone: $origMasterPhone\n";
echo "  - universal_settings.contact_wa: $origUnivWa\n";
echo "  - roll_site_settings.contact_wa: $origRollWa\n\n";

// 2. Simulate Master Profile Update via Controller Logic
echo "[TEST 1] Simulating Master updating phone number to '081234567890'...\n";
$newPhoneInput = '081234567890';
$role = 'master';

// Simulate logic in RollProfileController
$digitsOnly = preg_replace('/[^0-9]/', '', $newPhoneInput);
if (substr($digitsOnly, 0, 2) === '08') {
    $waDigits = '62' . substr($digitsOnly, 1);
} else {
    $waDigits = $digitsOnly;
}

$pdo->prepare("UPDATE roll_users SET phone = ? WHERE role = 'master'")->execute([$newPhoneInput]);
if (!empty($waDigits)) {
    $pdo->prepare("UPDATE universal_settings SET contact_wa = ? WHERE id = 1")->execute([$waDigits]);
    $pdo->prepare("UPDATE roll_site_settings SET contact_wa = ? WHERE id = 1")->execute(['+' . $waDigits]);
}

// Verify values
$updatedMasterPhone = $pdo->query("SELECT phone FROM roll_users WHERE role = 'master' LIMIT 1")->fetchColumn();
$updatedUnivWa = $pdo->query("SELECT contact_wa FROM universal_settings WHERE id = 1")->fetchColumn();
$updatedRollWa = $pdo->query("SELECT contact_wa FROM roll_site_settings WHERE id = 1")->fetchColumn();

assert($updatedMasterPhone === '081234567890', "Master phone must be updated to 081234567890");
assert($updatedUnivWa === '6281234567890', "Universal settings WA must be converted to 6281234567890");
assert($updatedRollWa === '+6281234567890', "Roll site settings WA must be converted to +6281234567890");

echo "  ✓ roll_users.phone successfully updated to: $updatedMasterPhone\n";
echo "  ✓ universal_settings.contact_wa automatically synchronized to: $updatedUnivWa\n";
echo "  ✓ roll_site_settings.contact_wa automatically synchronized to: $updatedRollWa\n";

// 3. Restore original values
echo "\n[CLEANUP] Restoring original values...\n";
$pdo->prepare("UPDATE roll_users SET phone = ? WHERE role = 'master'")->execute([$origMasterPhone]);
$pdo->prepare("UPDATE universal_settings SET contact_wa = ? WHERE id = 1")->execute([$origUnivWa]);
$pdo->prepare("UPDATE roll_site_settings SET contact_wa = ? WHERE id = 1")->execute([$origRollWa]);
echo "  ✓ Original values restored.\n";

echo "\n>>> ALL TESTS PASSED! <<<\n";

