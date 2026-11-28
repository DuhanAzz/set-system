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

        $stmtEvt = $db->prepare("SELECT * FROM roll_events WHERE id = ? LIMIT 1");
        $stmtEvt->execute([$eventId]);
        $event = $stmtEvt->fetch(PDO::FETCH_ASSOC) ?: ['id' => $eventId, 'event_name' => 'Event #' . $eventId];

        return [$db, $eventId, $event];
    }

    /**
     * Parse konfigurasi kolom & bagian laporan keuangan yang dicetak.
     */
    public static function resolvePrintConfig(array $query): array {
        $isConfigured = isset($query['cfg']) && (string)$query['cfg'] === '1';

        return [
            'show_summary'      => $isConfigured ? !empty($query['show_summary']) : true,
            'show_categories'   => $isConfigured ? !empty($query['show_categories']) : true,
            'show_transactions' => $isConfigured ? !empty($query['show_transactions']) : true,
            'show_date'         => $isConfigured ? !empty($query['show_date']) : true,
            'show_type'         => $isConfigured ? !empty($query['show_type']) : true,
            'show_counts'       => $isConfigured ? !empty($query['show_counts']) : true,
            'show_status'       => $isConfigured ? !empty($query['show_status']) : true,
            'show_proof'        => $isConfigured ? !empty($query['show_proof']) : false,
        ];
    }

    public function print_data() {
        [$db, $eventId, $event] = $this->requireActiveEvent();

        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter   = $_GET['type'] ?? 'all';
        $search       = $_GET['search'] ?? '';
        $printConfig  = self::resolvePrintConfig($_GET);

        $report = RollFinanceHelper::buildEventFinanceReport($db, $eventId);
        $filteredTransactions = RollFinanceHelper::filterFinanceTransactions(
            $report['transactions'],
            $statusFilter,
            $typeFilter,
            $search
        );

        return $this->view('roll/admin/reports/print_report', [
            'event'            => $event,
            'transactions'     => $filteredTransactions,
            'allCount'         => count($report['transactions']),
            'totals'           => $report['totals'],
            'categories'       => $report['categories'],
            'packageBreakdown' => $report['package_breakdown'] ?? [],
            'statusFilter'     => $statusFilter,
            'typeFilter'       => $typeFilter,
            'search'           => $search,
            'printConfig'      => $printConfig,
        ]);
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
            'event'            => $event,
            'transactions'     => $filteredTransactions,
            'allCount'         => count($report['transactions']),
            'totals'           => $totals,
            'categories'       => $report['categories'],
            'packageBreakdown' => $report['package_breakdown'] ?? [],
            'statusFilter'     => $statusFilter,
            'typeFilter'       => $typeFilter,
            'search'           => $search,
            'totalPendapatan'  => $totals['paid'],
            'totalKlub'        => $totals['paid_klub'],
            'totalToken'       => $totals['paid_token'],
            'countPaid'        => $totals['count_paid'],
            'countPending'     => $totals['count_pending'],
        ]);
    }

    /**
     * Ringkas breakdown paket pendaftaran milik satu invoice klub/token dari skater_breakdowns.
     */
    public static function summarizeInvoicePackages(array $skaterBreakdowns): array {
        $grouped = [];
        foreach ($skaterBreakdowns as $sb) {
            $catGroup = (string)($sb['cat_group'] ?? 'Lainnya');
            $pkgLabel = (string)($sb['package_label'] ?? ($sb['category'] ?? 'Lainnya'));
            $key = $catGroup . '|' . $pkgLabel;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'cat_group'     => $catGroup,
                    'package_label' => $pkgLabel,
                    'skaters'       => 0,
                    'total_amount'  => 0.0,
                ];
            }
            $grouped[$key]['skaters']++;
            $grouped[$key]['total_amount'] += (float)($sb['total'] ?? 0);
        }
        return array_values($grouped);
    }

    /**
     * Bersihkan potongan nama file agar aman di ZIP/OS tanpa mengubah format nama klub yang sudah ada.
     */
    public static function sanitizeInvoiceFilenamePart(string $value, string $fallback = 'Klub'): string {
        $clean = preg_replace('/[\/\\\\:*?"<>|\x00-\x1F]+/', ' ', $value);
        $clean = preg_replace('/\s+/', ' ', (string)$clean);
        $clean = trim((string)$clean, " .\t\n\r\0\x0B");
        return $clean !== '' ? $clean : $fallback;
    }

    /**
     * Siapkan daftar invoice cetak dan nama file PDF sesuai format judul "Tanda Terima - <Klub>.pdf".
     */
    public static function buildInvoiceZipItems(array $transactions): array {
        $baseCounts = [];
        $prepared = [];
        foreach ($transactions as $t) {
            $clubPart = self::sanitizeInvoiceFilenamePart((string)($t['club_name'] ?? ''), 'Klub');
            $base = 'Tanda Terima - ' . $clubPart;
            $baseKey = strtolower($base);
            $baseCounts[$baseKey] = ($baseCounts[$baseKey] ?? 0) + 1;
            $prepared[] = [
                'base'       => $base,
                'base_key'   => $baseKey,
                'club_name'  => (string)($t['club_name'] ?? 'Klub'),
                'identifier' => (string)($t['identifier'] ?? ''),
                'print_url'  => (string)($t['print_invoice_url'] ?? ($t['detail_url'] ?? '/roll/admin/entries')),
            ];
        }

        $usedNames = [];
        $items = [];
        foreach ($prepared as $p) {
            $name = $p['base'];
            if (($baseCounts[$p['base_key']] ?? 0) > 1 && $p['identifier'] !== '') {
                $name .= ' - ' . self::sanitizeInvoiceFilenamePart($p['identifier'], 'INV');
            }

            $candidate = $name;
            $suffix = 2;
            while (isset($usedNames[strtolower($candidate)])) {
                $candidate = $name . ' (' . $suffix++ . ')';
            }
            $usedNames[strtolower($candidate)] = true;

            $items[] = [
                'filename'  => $candidate . '.pdf',
                'club_name' => $p['club_name'],
                'print_url' => $p['print_url'],
            ];
        }

        return $items;
    }

    public function invoices() {
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

        foreach ($filteredTransactions as &$t) {
            $t['package_summary'] = self::summarizeInvoicePackages($t['skater_breakdowns'] ?? []);
        }
        unset($t);

        return $this->view('roll/admin/reports/invoices', [
            'event'        => $event,
            'eventId'      => $eventId,
            'transactions' => $filteredTransactions,
            'zipItems'     => self::buildInvoiceZipItems($filteredTransactions),
            'allCount'     => count($report['transactions']),
            'totals'       => $report['totals'],
            'statusFilter' => $statusFilter,
            'typeFilter'   => $typeFilter,
            'search'       => $search,
        ]);
    }

    public function export_excel() {
        [$db, $eventId, $event] = $this->requireActiveEvent();

        $mode         = $_GET['mode'] ?? 'transactions';
        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter   = $_GET['type'] ?? 'all';
        $search       = $_GET['search'] ?? '';

        $report   = RollFinanceHelper::buildEventFinanceReport($db, $eventId);
        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($event['event_name'] ?? 'Event'));

        if ($mode === 'category') {
            $pkgRows  = $report['package_breakdown'] ?? [];
            $filename = "Laporan_Kategori_Paket_{$safeName}_" . date('Ymd_His') . ".csv";

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Kategori', 'Paket / Skema Pendaftaran', 'Jml Atlet (Lunas)', 'Pending / Unpaid', 'Tarif Satuan (Rp)', 'Total Pemasukan Lunas (Rp)']);
            $no = 1;
            $totSkaters = 0;
            $totPending = 0;
            $totAmount  = 0.0;
            foreach ($pkgRows as $p) {
                $totSkaters += (int)$p['paid_skaters'];
                $totPending += (int)$p['pending_unpaid_skaters'];
                $totAmount  += (float)$p['paid_amount'];
                fputcsv($out, [
                    $no++,
                    $p['cat_group'],
                    $p['label'],
                    (int)$p['paid_skaters'],
                    (int)$p['pending_unpaid_skaters'],
                    (float)$p['unit_price'],
                    (float)$p['paid_amount'],
                ]);
            }
            fputcsv($out, ['', '', 'TOTAL PEMASUKAN LUNAS', $totSkaters, $totPending, '', $totAmount]);
            fclose($out);
            exit;
        }

        $rows = RollFinanceHelper::filterFinanceTransactions(
            $report['transactions'],
            $statusFilter,
            $typeFilter,
            $search
        );

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
