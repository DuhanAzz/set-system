<?php

require_once __DIR__ . '/../app/Helpers/SwimEventHelper.php';

use App\Helpers\SwimEventHelper;

function assertSameValue($expected, $actual, string $msg): void {
    if ($expected !== $actual) {
        fwrite(STDERR, "[FAIL] $msg\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n");
        exit(1);
    }
}

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("
    CREATE TABLE swim_events (
        id INTEGER PRIMARY KEY,
        user_id INTEGER NOT NULL,
        event_name TEXT NOT NULL,
        event_date_start TEXT,
        pool_type TEXT
    );
");
$pdo->exec("
    INSERT INTO swim_events (id, user_id, event_name, event_date_start, pool_type) VALUES
    (15, 10, 'Kejuaraan Admin A Lama', '2026-01-10', 'LCM'),
    (20, 10, 'Kejuaraan Admin A Baru', '2026-05-10', 'LCM'),
    (41, 20, 'Kejuaraan Admin B (ID 41)', '2026-06-10', 'LCM');
");

$_SESSION = [];
$_GET = [];
$_POST = [];

// 1. Stale $_SESSION['active_event_id'] = 41 ketika login sebagai Admin A (uid=10)
$_SESSION['active_event_id'] = 41;
$res1 = SwimEventHelper::resolveAdminActiveEvent($pdo, 10, 'admin', null, false);
assertSameValue(20, $res1['eventId'], 'Admin A must not be forced onto event 41 from stale session');
assertSameValue([20, 15], array_map(fn($e) => (int)$e['id'], $res1['allEvents']), 'Admin A only sees own events');

// 2. Explicit $_GET['event_id'] = 41 milik EO lain harus ditolak untuk Admin A
$_GET['event_id'] = '41';
$res2 = SwimEventHelper::resolveAdminActiveEvent($pdo, 10, 'admin', null, false);
assertSameValue(20, $res2['eventId'], 'Admin A must not resolve cross-owner ?event_id=41');
assertSameValue(null, SwimEventHelper::findAccessibleEvent($pdo, 41, 10, 'admin'), 'findAccessibleEvent blocks cross-owner event');
assertSameValue(20, (int)SwimEventHelper::findAccessibleEvent($pdo, 20, 10, 'admin')['id'], 'findAccessibleEvent allows own event');

// 3. Admin A memilih event miliknya sendiri via ?event_id=15
$_GET['event_id'] = '15';
$res3 = SwimEventHelper::resolveAdminActiveEvent($pdo, 10, 'admin', null, false);
assertSameValue(15, $res3['eventId'], 'Admin A can switch between own events');

// 4. Admin baru yang belum punya event (uid=99) tidak boleh fallback ke ID 41
$_GET = [];
$_SESSION['active_event_id'] = 41;
$res4 = SwimEventHelper::resolveAdminActiveEvent($pdo, 99, 'admin', null, false);
assertSameValue(0, $res4['eventId'], 'Admin without events must get eventId=0, never 41');
assertSameValue(null, $res4['activeEvent'], 'Admin without events gets null activeEvent');
assertSameValue([], $res4['allEvents'], 'Admin without events gets empty list');

// 5. Master / Superadmin dapat mengakses semua event termasuk ID 41
$_GET['event_id'] = '41';
$res5 = SwimEventHelper::resolveAdminActiveEvent($pdo, 1, 'master', null, false);
assertSameValue(41, $res5['eventId'], 'Master can access event 41');
assertSameValue([41, 20, 15], array_map(fn($e) => (int)$e['id'], $res5['allEvents']), 'Master sees all events');
assertSameValue(41, (int)SwimEventHelper::findAccessibleEvent($pdo, 41, 1, 'superadmin')['id'], 'Superadmin can access event 41');

echo "ALL ADMIN EVENT SCOPING CHECKS PASSED\n";

