<?php
/**
 * Test Suite: Swim Live Web Result & Official A4 Result Sheet
 *
 * Verifies:
 * 1. print_input.php renders as realistic A4 .sheet layout (no screen-wide 100vw fixed headers).
 * 2. swim/admin/results/ includes status badge, mode ranking badge, and direct A4 print link.
 * 3. swim/admin/results/publish includes rank_mode toggle and A4 preview.
 * 4. swim/live_result/index/{id} renders lightweight catalog linking to A4 viewer.
 * 5. swim/live_result/view/{id} renders official A4 result sheet with desktop & mobile tab bars.
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

echo "=== START SWIM LIVE WEB RESULT & OFFICIAL A4 TEST ===\n";

// Pick an active swim event and event number (e.g. event 36)
$stmt = $db->query("SELECT id FROM swim_events WHERE id = 36 LIMIT 1");
$eventRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$eventRow) {
    $stmt = $db->query("SELECT id FROM swim_events ORDER BY id DESC LIMIT 1");
    $eventRow = $stmt->fetch(PDO::FETCH_ASSOC);
}
assert(!empty($eventRow), "Must have at least one swim event in database");
$testEventId = (int)$eventRow['id'];

// Pick an event number in this event
$stmtNum = $db->prepare("SELECT id FROM swim_event_numbers WHERE event_id = ? ORDER BY id ASC LIMIT 1");
$stmtNum->execute([$testEventId]);
$numRow = $stmtNum->fetch(PDO::FETCH_ASSOC);
assert(!empty($numRow), "Must have at least one event number for test event");
$testNumberId = (int)$numRow['id'];
echo "[INFO] Using test event ID: {$testEventId}, Event Number ID: {$testNumberId}\n";

// Set event and number as published for test
$db->prepare("UPDATE swim_events SET is_result_published = 1 WHERE id = ?")->execute([$testEventId]);
$db->prepare("UPDATE swim_event_numbers SET is_published = 1 WHERE id = ?")->execute([$testNumberId]);

$_SERVER['REQUEST_METHOD'] = 'GET';

// 1. Verify print_input.php A4 .sheet layout
$_GET['category_id'] = $testNumberId;
$_GET['print'] = '1';
$_SESSION['swim_role'] = 'admin';
$_SESSION['swim_user_id'] = 60;

ob_start();
$resultsController = new \App\Swim\Controllers\ResultsController();
try {
    $resultsController->input();
} catch (\Exception $e) {}
$outputPrint = ob_get_clean();

assert(strpos($outputPrint, 'class="sheet"') !== false, "print_input must render .sheet container");
assert(strpos($outputPrint, 'master-layout') !== false, "print_input must use official master-layout table");
assert(strpos($outputPrint, 'kop-surat') !== false, "print_input must have kop-surat inside .sheet");
assert(strpos($outputPrint, 'sponsor-footer') !== false, "print_input must have sponsor-footer inside .sheet");
assert(strpos($outputPrint, 'btn-print') !== false, "print_input must have floating action buttons");
assert(strpos($outputPrint, '@media print') !== false, "print_input must include print styles");
echo "[PASS] Test 1: print_input.php renders as realistic A4 .sheet layout.\n";

// 2. Verify swim/admin/results/ (index.php)
unset($_GET['print']);
unset($_GET['category_id']);
ob_start();
$resultsController->index();
$outputIndex = ob_get_clean();

assert(strpos($outputIndex, 'Publikasi Live Result') !== false, "Results index must link to Publikasi Live Result");
assert(strpos($outputIndex, 'Preview A4') !== false, "Results index must have direct Preview A4 button");
assert(strpos($outputIndex, 'LIVE') !== false || strpos($outputIndex, 'DRAFT') !== false, "Results index must show publication status badge");
echo "[PASS] Test 2: swim/admin/results/ includes navigation and direct A4 preview.\n";

// 3. Verify swim/admin/results/publish toggle_rank_mode AJAX
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'toggle_rank_mode';
$_POST['event_number_id'] = $testNumberId;
$_POST['rank_mode'] = 'overall';

ob_start();
$resultsController->publish();
$ajaxRes = ob_get_clean();
$json = json_decode($ajaxRes, true);
assert($json['success'] === true, "toggle_rank_mode must succeed");
assert($json['rank_mode'] === 'overall', "rank_mode must be updated to 'overall'");

// Verify in DB
$stmtCheck = $db->prepare("SELECT rank_mode FROM swim_event_numbers WHERE id = ?");
$stmtCheck->execute([$testNumberId]);
assert($stmtCheck->fetchColumn() === 'overall', "Database rank_mode must be 'overall'");
echo "[PASS] Test 3: swim/admin/results/publish toggle_rank_mode AJAX works.\n";

// 4. Verify public Live Result listing (/swim/live_result/index/{event_id})
$_SERVER['REQUEST_METHOD'] = 'GET';
unset($_POST['action']);
ob_start();
$liveResultController = new \App\Swim\Controllers\LiveResultController();
$liveResultController->index($testEventId);
$outputLiveIndex = ob_get_clean();

assert(strpos($outputLiveIndex, 'Lihat Hasil Resmi (A4)') !== false, "Public Live Result must link to A4 viewer");
assert(strpos($outputLiveIndex, "/swim/live_result/view/{$testNumberId}") !== false, "Public Live Result must link to view endpoint");
assert(strpos($outputLiveIndex, 'Race Book') === false, "Public Live Result must NOT contain Race Book");
echo "[PASS] Test 4: swim/live_result/index renders fast catalog without Race Book.\n";

// 5. Verify public Live Result A4 sheet (/swim/live_result/view/{id})
ob_start();
$liveResultController->view($testNumberId);
$outputViewSheet = ob_get_clean();

assert(strpos($outputViewSheet, 'class="sheet"') !== false, "Viewer must render .sheet container");
assert(strpos($outputViewSheet, 'desktop-bar') !== false, "Viewer must have desktop floating bar");
assert(strpos($outputViewSheet, 'mobile-bottom-bar') !== false, "Viewer must have mobile bottom tab bar");
assert(strpos($outputViewSheet, 'cycleZoom()') !== false, "Viewer must have mobile cycleZoom function");
assert(strpos($outputViewSheet, 'navModalSheet') !== false, "Viewer must have mobile acara drawer");
assert(strpos($outputViewSheet, '@media print') !== false, "Viewer must include print styles");
echo "[PASS] Test 5: swim/live_result/view renders official A4 result sheet with desktop & mobile bars.\n";

echo "=== ALL SWIM LIVE WEB RESULT TESTS PASSED SUCCESSFULLY! ===\n";
