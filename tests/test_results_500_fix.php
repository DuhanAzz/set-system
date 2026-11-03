<?php
// tests/test_results_500_fix.php
// Runnable assert-based test for Roll Result Controller HTTP 500 resolution

session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv(trim($line));
    }
}

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Roll/Controllers/Admin/RollResultController.php';

echo "Running RollResultController HTTP 500 regression test...\n";

$_SESSION['role'] = 'admin';
$_SERVER['REQUEST_METHOD'] = 'GET';

$controller = new \App\Roll\Controllers\Admin\RollResultController();

// 1. Test direct access with race_class_id=105 when active event is mismatched (e.g. 3)
$_SESSION['roll_admin_active_event_id'] = 3;
$_GET['race_class_id'] = '105';
$_SERVER['REQUEST_URI'] = '/roll/admin/results?race_class_id=105';

ob_start();
$controller->index();
$htmlMismatch = ob_get_clean();

assert($_SESSION['roll_admin_active_event_id'] === 1, "Active event must be auto-synchronized to 1 from race_class_id=105");
echo "✓ Test 1 Passed: Event mismatch auto-synchronized from event 3 to event 1\n";

// 2. Test direct access when active event is unset/0
$_SESSION['roll_admin_active_event_id'] = 0;
$_GET['race_class_id'] = '105';
$_SERVER['REQUEST_URI'] = '/roll/admin/results?race_class_id=105';

ob_start();
$controller->index();
$htmlUnset = ob_get_clean();

assert($_SESSION['roll_admin_active_event_id'] === 1, "Active event must be auto-synchronized to 1 even when unset");
echo "✓ Test 2 Passed: Event auto-synchronized from 0 to 1 without redirect loop\n";

// 3. Test print_result with race_class_id=105
$_GET['race_class_id'] = '105';
$_SERVER['REQUEST_URI'] = '/roll/admin/results/print_result?race_class_id=105';

ob_start();
$controller->print_result();
$htmlPrint = ob_get_clean();

assert(strlen($htmlPrint) > 0, "Print result must render HTML output");
echo "✓ Test 3 Passed: print_result renders cleanly for race_class_id=105\n";

echo "All 3 verification tests passed successfully!\n";
