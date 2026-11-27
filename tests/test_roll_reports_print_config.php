<?php
ob_start();

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';
require_once __DIR__ . '/../app/Roll/Controllers/Admin/RollAdminReportController.php';

use App\Roll\Controllers\Admin\RollAdminReportController;

echo "--- RUNNING TEST: ROLL ADMIN REPORTS PRINT CONFIG & RENDER ---\n";

// 1. Default config (without cfg=1)
$cfg1 = RollAdminReportController::resolvePrintConfig([]);
assert($cfg1['show_summary'] === true);
assert($cfg1['show_categories'] === true);
assert($cfg1['show_date'] === true);
assert($cfg1['show_type'] === true);
assert($cfg1['show_counts'] === true);
assert($cfg1['show_status'] === true);
assert($cfg1['show_proof'] === false);
echo "✓ Case 1: Reports print defaults verified [OK]\n";

// 2. Custom config (cfg=1 with show_proof ON and show_categories OFF)
$cfg2 = RollAdminReportController::resolvePrintConfig([
    'cfg'          => '1',
    'show_summary' => '1',
    'show_date'    => '1',
    'show_status'  => '1',
    'show_proof'   => '1',
]);
assert($cfg2['show_summary'] === true);
assert($cfg2['show_categories'] === false);
assert($cfg2['show_type'] === false);
assert($cfg2['show_counts'] === false);
assert($cfg2['show_proof'] === true);
echo "✓ Case 2: Reports custom checkbox toggles verified [OK]\n";

// 3. Render print_report view
$renderView = static function (array $vars): string {
    extract($vars);
    ob_start();
    include __DIR__ . '/../views/roll/admin/reports/print_report.php';
    return ob_get_clean();
};

$html = $renderView([
    'event' => [
        'event_name'       => 'KEJURNAS ROLL 2026',
        'event_date_start' => '2026-11-20',
        'event_date_end'   => '2026-11-22',
        'event_location'   => 'Bekasi',
        'header_logos'     => json_encode(['left' => ['public/uploads/logo_l.png'], 'center' => [], 'right' => []]),
        'sponsor_logos'    => json_encode(['public/uploads/sp1.png']),
    ],
    'printConfig'  => $cfg2,
    'statusFilter' => 'Paid',
    'typeFilter'   => 'all',
    'search'       => '',
    'totals'       => [
        'paid'           => 1500000,
        'paid_klub'      => 1000000,
        'paid_token'     => 500000,
        'pending'        => 350000,
        'unpaid'         => 0,
        'rejected'       => 0,
        'potensi'        => 1850000,
        'count_paid'     => 2,
        'count_pending'  => 1,
        'count_unpaid'   => 0,
        'count_rejected' => 0,
    ],
    'categories'   => [
        'Speed'    => ['skaters' => 2, 'extra_count' => 1, 'total' => 1000000],
        'Standart' => ['skaters' => 1, 'extra_count' => 0, 'total' => 500000],
        'Pemula'   => ['skaters' => 0, 'extra_count' => 0, 'total' => 0],
        'Lainnya'  => ['skaters' => 0, 'extra_count' => 0, 'total' => 0],
    ],
    'transactions' => [
        [
            'created_at'     => '2026-11-10 09:30:00',
            'type'           => 'Klub',
            'club_name'      => 'KLUB CEPAT JAYA',
            'identifier'     => 'CLB-10',
            'skater_count'   => 2,
            'entry_count'    => 5,
            'payment_amount' => 1000000,
            'status'         => 'Paid',
            'payment_proof'  => 'proof.jpg',
        ],
    ],
]);

assert(strpos($html, 'Laporan Keuangan Event') !== false);
assert(strpos($html, 'hide-sec-categories') !== false);
assert(strpos($html, 'hide-col-type') !== false);
assert(strpos($html, 'KLUB CEPAT JAYA') !== false);
assert(strpos($html, '1.000.000') !== false);
assert(strpos($html, 'Supported By') !== false);
echo "✓ Case 3: print_report HTML rendered with Kop Surat, Summary, Sponsors & column classes [OK]\n";

ob_end_flush();
echo "--- ALL ROLL REPORTS PRINT TESTS PASSED! ---\n";
