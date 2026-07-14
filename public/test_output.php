<?php
// Mock session
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['swim_role'] = 'admin';
$_SESSION['user_id'] = 1;
$_SESSION['swim_user_id'] = 1;

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Stub the Controller view method to see what $isBackend actually is
class TestController extends \App\Core\Controller {
    public function testView() {
        $viewPath = 'swim/admin/dashboard';
        $isBackend = (strpos($viewPath, 'auth/login') === false && strpos($viewPath, 'home') === false && strpos($viewPath, 'events') === false && strpos($viewPath, 'results') === false && strpos($viewPath, 'login') === false);
        echo "IsBackend: " . ($isBackend ? "TRUE" : "FALSE") . "\n";
    }
}
$t = new TestController();
$t->testView();
