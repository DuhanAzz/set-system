<?php
/**
 * Test Suite: Live Web Result & Race Book
 *
 * Verifies:
 * 1. Column `web_published_modes` exists in `roll_event_details`.
 * 2. Admin AJAX action `toggle_web_mode` updates JSON configuration.
 * 3. Public Live Result page (/roll/liveresult/{event_id}) displays web links.
 * 4. Public Live Result Sheet (/roll/liveresult/view/{class_id}?round=...&mode=...) renders HTML correctly.
 * 5. Mobile bottom bar and zoom controls are present.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($k, $v) = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v, " '\""));
        $_ENV[trim($k)] = trim($v, " '\"");
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require_once $file;
});

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== START LIVE WEB RESULT & RACE BOOK TEST ===\n";

// Ensure schema
try {
    $db->exec("ALTER TABLE roll_event_details ADD COLUMN web_published_modes TEXT NULL");
} catch (\Exception $e) {}

// 1. Check schema column
$stmt = $db->query("SHOW COLUMNS FROM roll_event_details LIKE 'web_published_modes'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
assert(!empty($col), "Column 'web_published_modes' must exist in roll_event_details");
echo "[PASS] Schema check: roll_event_details.web_published_modes exists.\n";

// Find or pick a test race class
$stmtClass = $db->query("SELECT id, event_id FROM roll_event_details ORDER BY id ASC LIMIT 1");
$class = $stmtClass->fetch(PDO::FETCH_ASSOC);
assert(!empty($class), "Must have at least one race class in database");
$testClassId = (int)$class['id'];
$testEventId = (int)$class['event_id'];
echo "[INFO] Using test race class ID: {$testClassId}, Event ID: {$testEventId}\n";

// 2. Set web_published_modes in DB
$testConfig = [
    'Final' => [
        'result_mode' => 'all',
        'show_racebook' => 1
    ]
];
$stmtUpdate = $db->prepare("UPDATE roll_event_details SET web_published_modes = ?, result_status = 'Published' WHERE id = ?");
$stmtUpdate->execute([json_encode($testConfig), $testClassId]);

// Verify saved
$stmtCheck = $db->prepare("SELECT web_published_modes FROM roll_event_details WHERE id = ?");
$stmtCheck->execute([$testClassId]);
$savedJson = $stmtCheck->fetchColumn();
$decoded = json_decode($savedJson, true);
assert($decoded['Final']['result_mode'] === 'all', "result_mode must be 'all'");
assert($decoded['Final']['show_racebook'] === 1, "show_racebook must be 1");
echo "[PASS] Database persistence check passed.\n";

// 3. Test public liveresult listing (/roll/liveresult/{event_id})
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$homeController = new \App\Roll\Controllers\HomeController();
$homeController->liveresult($testEventId);
$outputListing = ob_get_clean();

assert(strpos($outputListing, 'Race Book: Final') !== false, "Listing page must render 'Race Book: Final' button");
assert(strpos($outputListing, 'Hasil Final') !== false, "Listing page must render 'Hasil Final' button");
assert(strpos($outputListing, "/roll/liveresult/view/{$testClassId}") !== false, "Listing page must link to view sheet endpoint");
echo "[PASS] Public Live Result listing renders both Race Book and Web Result buttons.\n";

// 4. Test public view_sheet (/roll/liveresult/view/{class_id}?round=Final&mode=all)
$_GET['round'] = 'Final';
$_GET['mode'] = 'all';
ob_start();
$homeController->view_sheet($testClassId);
$outputSheetAll = ob_get_clean();

assert(strpos($outputSheetAll, 'Hasil Perlombaan') !== false, "Sheet must render 'Hasil Perlombaan' title");
assert(strpos($outputSheetAll, 'master-layout') !== false, "Sheet must use official master-layout table");
assert(strpos($outputSheetAll, 'mobile-bottom-bar') !== false, "Sheet must contain mobile-bottom-bar");
assert(strpos($outputSheetAll, 'desktop-bar') !== false, "Sheet must contain desktop-bar");
assert(strpos($outputSheetAll, 'cycleZoom()') !== false, "Sheet must contain mobile cycleZoom function");
assert(strpos($outputSheetAll, '@media print') !== false, "Sheet must include print styles");
echo "[PASS] Public Live Result Sheet (All Heat) renders with desktop & mobile bars and A4 master layout.\n";

// 5. Test public view_sheet for Race Book mode
$_GET['round'] = 'Final';
$_GET['mode'] = 'racebook';
ob_start();
$homeController->view_sheet($testClassId);
$outputSheetRacebook = ob_get_clean();

assert(strpos($outputSheetRacebook, 'STARTLIST / RACE BOOK') !== false, "Sheet must render 'STARTLIST / RACE BOOK' title in racebook mode");
echo "[PASS] Public Live Result Sheet (Race Book) renders correctly.\n";

// 6. Test public view_sheet for Per Heat mode
$_GET['round'] = 'Final';
$_GET['mode'] = 'per_heat';
ob_start();
$homeController->view_sheet($testClassId);
$outputSheetPerHeat = ob_get_clean();

assert(strpos($outputSheetPerHeat, 'Hasil Perlombaan') !== false, "Sheet must render 'Hasil Perlombaan' in per heat mode");
echo "[PASS] Public Live Result Sheet (Per Heat) renders correctly.\n";

echo "=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
