<?php
/**
 * Self-check: Ensure BPJS status is purely manual data (no automatic event override)
 * and Admin Data Peserta & BPJS filters only Paid entries with NIK & pure status_bpjs.
 */

$filesMustNotContain = [
    __DIR__ . '/../app/Roll/Controllers/User/RollUserAthleteController.php'   => ['sudah_event', 'sudah_mandiri'],
    __DIR__ . '/../app/Roll/Controllers/Master/RollMasterSkaterController.php' => ['sudah_event', 'sudah_mandiri', 'Otomatis (Riwayat Event)'],
    __DIR__ . '/../views/roll/user/athletes/index.php'                          => ['sudah_event', 'Terdaftar (Event)', 'total_events'],
    __DIR__ . '/../views/roll/master/skaters/index.php'                         => ['sudah_event', 'Terdaftar (Event)', 'total_events'],
];

foreach ($filesMustNotContain as $file => $forbiddenList) {
    $src = file_get_contents($file);
    assert($src !== false, "Could not read $file");
    foreach ($forbiddenList as $needle) {
        assert(strpos($src, $needle) === false, "Forbidden auto-BPJS token '$needle' still present in $file");
    }
}
echo "✓ No automatic event-based BPJS status override in User & Master [OK]\n";

$adminCtrl = file_get_contents(__DIR__ . '/../app/Roll/Controllers/Admin/RollSkaterController.php');
assert(strpos($adminCtrl, "pay.status = 'Paid' OR mpay.status = 'Paid'") !== false, 'Admin BPJS view must filter Paid participants');
assert(strpos($adminCtrl, 'export_bpjs_csv') !== false, 'Admin BPJS CSV export method must exist');
echo "✓ Admin RollSkaterController filters Paid participants & exposes export_bpjs_csv [OK]\n";

$adminView = file_get_contents(__DIR__ . '/../views/roll/admin/skaters/index.php');
assert(strpos($adminView, 'Cek NIK & BPJS') !== false, 'Admin view must have Cek NIK & BPJS tab');
assert(strpos($adminView, 'Sudah Terdaftar') !== false && strpos($adminView, 'Belum Terdaftar') !== false, 'Admin view must show BPJS status badges');
echo "✓ Admin skaters view displays NIK and pure BPJS status (read-only) [OK]\n";

echo "--- ALL BPJS PURE DATA & ADMIN CHECK TESTS PASSED! ---\n";
