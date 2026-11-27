<?php
namespace App\Roll\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\RollFinanceHelper;
use PDO;

class RollAdminReportController extends Controller {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: " . getenv('APP_URL') . "/roll/login");
            exit;
        }
    }

    private function requireActiveEvent(): array {
        $db = Database::getInstance()->getConnection();
        $eventId = (int)($_SESSION['roll_admin_active_event_id'] ?? 0);
        if ($eventId <= 0) {
            $_SESSION['flash_message'] = "Pilih Event terlebih dahulu!";
            $_SESSION['flash_type'] = "warning";
            header("Location: " . getenv('APP_URL') . "/roll/admin/dashboard");
            exit;
        }

        $stmtEvt = $db->prepare("SELECT id, event_name, event_date_start, event_location FROM roll_events WHERE id = ?");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: ['id' => $eventId, 'event_name' => 'Event #' . $eventId];

        return [$db, $eventId, $event];
    }

    public function index() {
        [$db, $eventId, $event] = $this->requireActiveEvent();

        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter   = $_GET['type'] ?? 'all';
        $search       = $_GET['search'] ?? '';

        $report = RollFinanceHelper::buildEventFinanceReport($db, $eventId);
        $filteredTransactions = RollFinanceHelper::filterFinanceTransactions(
            $report['transactions'],
            $statusFilter,
            $typeFilter,
            $search
        );

        $totals = $report['totals'];

        return $this->view('roll/admin/reports/index', [
            'event'           => $event,
            'transactions'    => $filteredTransactions,
            'allCount'        => count($report['transactions']),
            'totals'          => $totals,
            'categories'      => $report['categories'],
            'statusFilter'    => $statusFilter,
            'typeFilter'      => $typeFilter,
            'search'          => $search,
            'totalPendapatan' => $totals['paid'],
            'totalKlub'       => $totals['paid_klub'],
            'totalToken'      => $totals['paid_token'],
            'countPaid'       => $totals['count_paid'],
            'countPending'    => $totals['count_pending'],
        ]);
    }

    public function export_excel() {
        [$db, $eventId, $event] = $this->requireActiveEvent();

        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter   = $_GET['type'] ?? 'all';
        $search       = $_GET['search'] ?? '';

        $report = RollFinanceHelper::buildEventFinanceReport($db, $eventId);
        $rows = RollFinanceHelper::filterFinanceTransactions(
            $report['transactions'],
            $statusFilter,
            $typeFilter,
            $search
        );

        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($event['event_name'] ?? 'Event'));
        $filename = "Laporan_Keuangan_{$safeName}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'No',
            'Tanggal',
            'Jalur Pendaftaran',
            'Klub / Kontingen',
            'Kode / Invoice',
            'Jumlah Atlet',
            'Jumlah Entri Lomba',
            'Nominal Tagihan (Rp)',
            'Status Pembayaran',
            'Bukti Bayar'
        ]);

        $no = 1;
        $sumNominal = 0;
        foreach ($rows as $r) {
            $amt = (float)($r['payment_amount'] ?? 0);
            $sumNominal += $amt;
            fputcsv($out, [
                $no++,
                !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-',
                ($r['type'] ?? 'Klub') === 'Klub' ? 'Klub Reguler' : 'Token / Manual',
                $r['club_name'] ?? '-',
                $r['identifier'] ?? '-',
                (int)($r['skater_count'] ?? 0),
                (int)($r['entry_count'] ?? 0),
                $amt,
                $r['status'] ?? 'Unpaid',
                !empty($r['payment_proof']) ? 'Ada' : 'Belum Upload'
            ]);
        }

        fputcsv($out, ['', '', '', '', '', '', 'TOTAL (Filtered)', $sumNominal, '', '']);
        fclose($out);
        exit;
    }
}
