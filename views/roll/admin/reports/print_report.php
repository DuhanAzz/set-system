<?php
$statusFilter = $statusFilter ?? 'all';
$typeFilter   = $typeFilter ?? 'all';
$search       = $search ?? '';
$totals           = $totals ?? ['paid' => 0, 'paid_klub' => 0, 'paid_token' => 0, 'pending' => 0, 'unpaid' => 0, 'rejected' => 0, 'potensi' => 0, 'count_paid' => 0, 'count_pending' => 0, 'count_unpaid' => 0, 'count_rejected' => 0];
$categories       = $categories ?? [];
$packageBreakdown = $packageBreakdown ?? [];
$transactions     = $transactions ?? [];
$printConfig      = $printConfig ?? [];

$rawHeader    = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
$headerLogos  = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} elseif (is_array($rawHeader)) {
    $headerLogos = array_merge($headerLogos, $rawHeader);
}
$sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];

$bodyClasses = [];
if (empty($printConfig['show_summary']))      $bodyClasses[] = 'hide-sec-summary';
if (empty($printConfig['show_categories']))   $bodyClasses[] = 'hide-sec-categories';
if (isset($printConfig['show_transactions']) && empty($printConfig['show_transactions'])) $bodyClasses[] = 'hide-sec-transactions';
if (empty($printConfig['show_date']))         $bodyClasses[] = 'hide-col-date';
if (empty($printConfig['show_type']))         $bodyClasses[] = 'hide-col-type';
if (empty($printConfig['show_counts']))       $bodyClasses[] = 'hide-col-counts';
if (empty($printConfig['show_status']))       $bodyClasses[] = 'hide-col-status';
if (empty($printConfig['show_proof']))        $bodyClasses[] = 'hide-col-proof';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Event - <?= htmlspecialchars($event['event_name'] ?? 'Event') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & COLOR SETTINGS --- */
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 64px 0 0 0;
            background-color: #525659;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* --- FLOATING PRINT TOOLBAR (SCREEN ONLY) --- */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .toolbar-options {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .toolbar-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-right: 4px;
        }
        .opt-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            border: 1px solid #334155;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s ease;
        }
        .opt-chip:hover { background: #334155; }
        .opt-chip input { cursor: pointer; accent-color: #3b82f6; }

        .toolbar-actions { display: flex; align-items: center; gap: 10px; }
        .btn-print, .btn-close {
            border: none;
            padding: 9px 18px;
            border-radius: 7px;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
            text-transform: uppercase;
            transition: transform 0.15s ease;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-close { background: #475569; color: #fff; }
        .btn-print:hover, .btn-close:hover { transform: scale(1.03); }

        /* --- STRUKTUR KERTAS (A4) --- */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 18px auto 30px auto;
            padding: 8mm 8mm 5mm 8mm;
            position: relative;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
        }

        /* --- HEADER (KOP SURAT) --- */
        .kop-surat {
            width: 100%;
            border: none;
            margin-bottom: 14px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .kop-surat td { padding: 0; border: none; }

        /* --- MAIN TABLE --- */
        table.master-layout { width: 100%; border: none; border-collapse: collapse; flex-grow: 1; }
        table.master-layout > thead > tr > td,
        table.master-layout > tbody > tr > td,
        table.master-layout > tfoot > tr > td { padding: 0; border: none; vertical-align: top; }

        .summary-box {
            border: 2px dashed #333;
            padding: 12px 15px;
            margin-bottom: 14px;
            background: #fdfdfd;
            border-radius: 8px;
        }

        .section-heading {
            font-size: 10.5pt;
            font-weight: 900;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            display: inline-block;
            padding-bottom: 2px;
            page-break-after: avoid;
            break-after: avoid;
        }

        table.schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        table.schedule-table tr { page-break-inside: avoid; break-inside: avoid; }
        table.schedule-table thead { display: table-header-group; }
        table.schedule-table th, table.schedule-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            text-align: left;
            font-size: 9pt;
            vertical-align: middle;
        }
        table.schedule-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        .badge-type {
            display: inline-block;
            padding: 1px 5px;
            border: 1px solid #94a3b8;
            background: #f1f5f9;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            border: 1px solid #333;
        }
        .status-paid { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .status-pending { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .status-rejected { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .status-unpaid { background: #f1f5f9; color: #475569; border-color: #94a3b8; }

        /* Dynamic Visibility Classes */
        body.hide-sec-summary      .sec-summary,
        body.hide-sec-categories   .sec-categories,
        body.hide-sec-transactions .sec-transactions,
        body.hide-col-date         .col-date,
        body.hide-col-type         .col-type,
        body.hide-col-counts       .col-counts,
        body.hide-col-status       .col-status,
        body.hide-col-proof        .col-proof {
            display: none !important;
        }

        /* --- FOOTER --- */
        .sponsor-footer {
            margin-top: auto;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            width: 100%;
        }
        .sponsor-footer img {
            height: 35px;
            width: auto;
            object-fit: contain;
            margin: 0 10px;
        }
        .footer-spacer { display: none; }

        @media print {
            body { background: white; margin: 0; padding: 0 !important; }
            .print-toolbar { display: none !important; }
            .sheet {
                margin: 0;
                box-shadow: none;
                border: none;
                display: block;
                position: static !important;
            }
            .sponsor-footer {
                position: fixed;
                bottom: 0;
                left: 8mm;
                right: 8mm;
                width: auto;
                background: white;
                margin-top: 0;
                padding-bottom: 5mm;
                z-index: 1000;
            }
            .footer-spacer {
                display: block;
                height: 80px;
            }
            @page { margin: 0; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body class="<?= implode(' ', $bodyClasses) ?>">

    <!-- TOOLBAR PENGATURAN CETAK -->
    <div class="print-toolbar">
        <div class="toolbar-options">
            <span class="toolbar-label"><i class="fas fa-sliders-h"></i> Tampilkan:</span>

            <label class="opt-chip" style="border-color: #3b82f6;">
                <input type="checkbox" data-col-toggle="show_summary" data-body-class="hide-sec-summary" <?= !empty($printConfig['show_summary']) ? 'checked' : '' ?>>
                <span>Ringkasan</span>
            </label>
            <label class="opt-chip" style="border-color: #3b82f6;">
                <input type="checkbox" data-col-toggle="show_categories" data-body-class="hide-sec-categories" <?= !empty($printConfig['show_categories']) ? 'checked' : '' ?>>
                <span>Rekap Kategori</span>
            </label>
            <label class="opt-chip" style="border-color: #3b82f6;">
                <input type="checkbox" data-col-toggle="show_transactions" data-body-class="hide-sec-transactions" <?= (!isset($printConfig['show_transactions']) || !empty($printConfig['show_transactions'])) ? 'checked' : '' ?>>
                <span>Rincian Transaksi</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_date" data-body-class="hide-col-date" <?= !empty($printConfig['show_date']) ? 'checked' : '' ?>>
                <span>Tanggal</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_type" data-body-class="hide-col-type" <?= !empty($printConfig['show_type']) ? 'checked' : '' ?>>
                <span>Jalur &amp; Invoice</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_counts" data-body-class="hide-col-counts" <?= !empty($printConfig['show_counts']) ? 'checked' : '' ?>>
                <span>Atlet / Entri</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_status" data-body-class="hide-col-status" <?= !empty($printConfig['show_status']) ? 'checked' : '' ?>>
                <span>Status</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_proof" data-body-class="hide-col-proof" <?= !empty($printConfig['show_proof']) ? 'checked' : '' ?>>
                <span>Bukti Bayar</span>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="button" onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>
            <button type="button" onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak Dokumen</button>
        </div>
    </div>

    <div class="sheet">
        <table class="master-layout">
            <!-- REPEATING HEADER (KOP SURAT) -->
            <thead>
                <tr>
                    <td>
                        <table class="kop-surat">
                            <tr>
                                <td style="width: 25%; text-align: left; vertical-align: middle;">
                                    <?php if (!empty($headerLogos['left'])): ?>
                                        <?php foreach ($headerLogos['left'] as $logo): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-right: 5px;">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 50%; text-align: center; vertical-align: middle;">
                                    <?php if (!empty($headerLogos['center'])): ?>
                                        <div style="margin-bottom: 8px;">
                                            <?php foreach ($headerLogos['center'] as $logo): ?>
                                                <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin: 0 5px;">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <h1 style="margin: 0; font-size: 16pt; text-transform: uppercase;">Laporan Keuangan Event</h1>
                                    <p style="margin: 4px 0 0 0; font-size: 12pt; font-weight: bold; color: #333;"><?= htmlspecialchars($event['event_name'] ?? 'Event') ?></p>
                                    <?php if (!empty($event['event_date_start']) && $event['event_date_start'] !== '0000-00-00'): ?>
                                        <p style="font-size: 9.5pt; margin: 2px 0 0 0; color: #555;">
                                            Tanggal: <?= date('d M Y', strtotime($event['event_date_start'])) ?>
                                            <?php if (!empty($event['event_date_end']) && $event['event_date_end'] !== '0000-00-00' && $event['event_date_end'] !== $event['event_date_start']): ?>
                                                - <?= date('d M Y', strtotime($event['event_date_end'])) ?>
                                            <?php endif; ?>
                                            <?php if (!empty($event['event_location'])): ?>
                                                &bull; <?= htmlspecialchars($event['event_location']) ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 25%; text-align: right; vertical-align: middle;">
                                    <?php if (!empty($headerLogos['right'])): ?>
                                        <?php foreach ($headerLogos['right'] as $logo): ?>
                                            <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $logo), '/') ?>" style="height: 60px; max-width: 100px; object-fit: contain; margin-left: 5px;">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>

            <!-- MAIN CONTENT -->
            <tbody>
                <tr>
                    <td>
                        <!-- RINGKASAN KEUANGAN -->
                        <div class="summary-box sec-summary">
                            <table style="width: 100%; border: none; font-size: 9.5pt;">
                                <tr>
                                    <td style="width: 52%; vertical-align: top; padding-right: 10px;">
                                        <p style="margin: 0; font-size: 8.5pt; color: #555; text-transform: uppercase; font-weight: bold;">Total Pemasukan Lunas (Paid)</p>
                                        <h2 style="margin: 3px 0 6px 0; font-size: 16pt; font-weight: 900; color: #065f46;">
                                            Rp <?= number_format((float)$totals['paid'], 0, ',', '.') ?>
                                        </h2>
                                        <div style="font-size: 8.5pt; color: #334155; line-height: 1.5;">
                                            <div>&bull; Klub Reguler: <strong>Rp <?= number_format((float)$totals['paid_klub'], 0, ',', '.') ?></strong></div>
                                            <div>&bull; Token / Manual: <strong>Rp <?= number_format((float)$totals['paid_token'], 0, ',', '.') ?></strong></div>
                                            <div style="margin-top: 4px;">
                                                Filter: <strong>Status = <?= htmlspecialchars(strtoupper($statusFilter)) ?></strong>
                                                &bull; <strong>Jalur = <?= htmlspecialchars(strtoupper($typeFilter)) ?></strong>
                                                <?php if ($search !== ''): ?>
                                                    &bull; Cari: "<strong><?= htmlspecialchars($search) ?></strong>"
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 48%; vertical-align: top;">
                                        <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 2px 0; font-weight: bold; color: #065f46;">Lunas (<?= (int)$totals['count_paid'] ?>)</td>
                                                <td style="text-align: right; font-weight: bold; color: #065f46;">: Rp <?= number_format((float)$totals['paid'], 0, ',', '.') ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0; font-weight: bold; color: #92400e;">Pending (<?= (int)$totals['count_pending'] ?>)</td>
                                                <td style="text-align: right; font-weight: bold; color: #92400e;">: Rp <?= number_format((float)$totals['pending'], 0, ',', '.') ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0; font-weight: bold; color: #991b1b;">Unpaid (<?= (int)$totals['count_unpaid'] ?>)</td>
                                                <td style="text-align: right; font-weight: bold; color: #991b1b;">: Rp <?= number_format((float)$totals['unpaid'], 0, ',', '.') ?></td>
                                            </tr>
                                            <tr style="border-top: 1px solid #cbd5e1;">
                                                <td style="padding: 4px 0 0 0; font-weight: 900;">Potensi Total Event</td>
                                                <td style="padding: 4px 0 0 0; text-align: right; font-weight: 900;">: Rp <?= number_format((float)$totals['potensi'], 0, ',', '.') ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- REKAP PEMASUKAN PER KATEGORI & PAKET PENDAFTARAN (PAID) -->
                        <div class="sec-categories" style="margin-bottom: 14px;">
                            <div class="section-heading">Rekap Pemasukan Lunas (Paid) per Kategori &amp; Paket</div>
                            <?php if (!empty($packageBreakdown)): ?>
                                <table class="schedule-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 34px;">No</th>
                                            <th>Uraian Kategori / Paket Pendaftaran</th>
                                            <th class="text-center" style="width: 90px;">Jml Atlet</th>
                                            <th class="text-right" style="width: 115px;">Tarif Satuan</th>
                                            <th class="text-right" style="width: 135px;">Total Pemasukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $pkgSumSkaters = 0;
                                        $pkgSumAmount  = 0.0;
                                        foreach ($packageBreakdown as $pIdx => $pkg):
                                            $pkgSumSkaters += (int)($pkg['paid_skaters'] ?? 0);
                                            $pkgSumAmount  += (float)($pkg['paid_amount'] ?? 0);
                                        ?>
                                            <tr>
                                                <td class="text-center font-bold"><?= $pIdx + 1 ?></td>
                                                <td class="font-bold">
                                                    <span style="display: inline-block; padding: 1px 4px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 7pt; margin-right: 4px; text-transform: uppercase;">
                                                        <?= htmlspecialchars($pkg['cat_group'] ?? 'Lainnya') ?>
                                                    </span>
                                                    <?= htmlspecialchars($pkg['label'] ?? '-') ?>
                                                </td>
                                                <td class="text-center font-bold"><?= number_format((int)($pkg['paid_skaters'] ?? 0)) ?></td>
                                                <td class="text-right" style="white-space: nowrap;">
                                                    <?= !empty($pkg['is_adjustment']) ? '-' : ('Rp ' . number_format((float)($pkg['unit_price'] ?? 0), 0, ',', '.')) ?>
                                                </td>
                                                <td class="text-right font-bold" style="white-space: nowrap;">
                                                    Rp <?= number_format((float)($pkg['paid_amount'] ?? 0), 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f0f0f0;">
                                            <td colspan="2" class="text-right font-bold" style="text-transform: uppercase;">Total Pemasukan Lunas (Paid)</td>
                                            <td class="text-center font-bold"><?= number_format($pkgSumSkaters) ?></td>
                                            <td></td>
                                            <td class="text-right font-bold" style="font-size: 10pt; white-space: nowrap;">Rp <?= number_format($pkgSumAmount, 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php else: ?>
                                <table class="schedule-table">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th class="text-center" style="width: 90px;">Jml Atlet</th>
                                            <th class="text-center" style="width: 110px;">Nomor Tambahan</th>
                                            <th class="text-right" style="width: 150px;">Subtotal Pemasukan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $catLabels = [
                                            'Speed'    => 'Speed',
                                            'Standart' => 'Standar',
                                            'Pemula'   => 'Pemula',
                                            'Lainnya'  => 'Eksebisi / Lainnya',
                                        ];
                                        $catTotalSum = 0;
                                        foreach ($catLabels as $key => $label):
                                            $cData = $categories[$key] ?? ['skaters' => 0, 'extra_count' => 0, 'total' => 0];
                                            $cTotal = (float)($cData['total'] ?? 0);
                                            $catTotalSum += $cTotal;
                                        ?>
                                            <tr>
                                                <td class="font-bold"><?= htmlspecialchars($label) ?></td>
                                                <td class="text-center"><?= number_format((int)($cData['skaters'] ?? 0)) ?></td>
                                                <td class="text-center"><?= number_format((int)($cData['extra_count'] ?? 0)) ?></td>
                                                <td class="text-right font-bold">Rp <?= number_format($cTotal, 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f8fafc;">
                                            <td colspan="3" class="text-right font-bold" style="text-transform: uppercase;">Total Rekap Kategori (Paid)</td>
                                            <td class="text-right font-bold">Rp <?= number_format($catTotalSum, 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php endif; ?>
                        </div>

                        <!-- TABEL RINCIAN TRANSAKSI -->
                        <div class="sec-transactions">
                        <div class="section-heading">Rincian Tagihan &amp; Pembayaran (<?= count($transactions) ?> Data)</div>
                        <table class="schedule-table" id="transactionsPrintTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 34px;">No</th>
                                    <th class="col-date" style="width: 92px;">Tanggal</th>
                                    <th>Klub / Kontingen</th>
                                    <th class="col-type" style="width: 135px;">Jalur &amp; Invoice</th>
                                    <th class="text-center col-counts" style="width: 85px;">Atlet / Entri</th>
                                    <th class="text-right" style="width: 115px;">Nominal</th>
                                    <th class="text-center col-status" style="width: 82px;">Status</th>
                                    <th class="text-center col-proof" style="width: 72px;">Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center" style="padding: 18px; color: #64748b;">Tidak ada data transaksi yang sesuai filter.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $filteredSum = 0;
                                    $sumSkaters = 0;
                                    $sumEntries = 0;
                                    foreach ($transactions as $idx => $t):
                                        $amt = (float)($t['payment_amount'] ?? 0);
                                        $filteredSum += $amt;
                                        $sk = (int)($t['skater_count'] ?? 0);
                                        $en = (int)($t['entry_count'] ?? 0);
                                        $sumSkaters += $sk;
                                        $sumEntries += $en;

                                        $stRaw = $t['status'] ?? 'Unpaid';
                                        $stLower = strtolower((string)$stRaw);
                                        $stClass = match ($stLower) {
                                            'paid'     => 'status-paid',
                                            'pending'  => 'status-pending',
                                            'rejected' => 'status-rejected',
                                            default    => 'status-unpaid',
                                        };
                                    ?>
                                        <tr>
                                            <td class="text-center font-bold"><?= $idx + 1 ?></td>
                                            <td class="col-date" style="font-size: 8pt; white-space: nowrap;">
                                                <?= !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '-' ?>
                                            </td>
                                            <td class="font-bold" style="text-transform: uppercase;">
                                                <?= htmlspecialchars($t['club_name'] ?? '-') ?>
                                            </td>
                                            <td class="col-type">
                                                <span class="badge-type"><?= ($t['type'] ?? '') === 'Klub' ? 'Klub' : 'Token' ?></span>
                                                <div style="font-family: monospace; font-size: 7.5pt; color: #475569; margin-top: 2px;">
                                                    <?= htmlspecialchars($t['identifier'] ?? '-') ?>
                                                </div>
                                            </td>
                                            <td class="text-center col-counts" style="font-size: 8.5pt;">
                                                <strong><?= $sk ?></strong> / <?= $en ?>
                                            </td>
                                            <td class="text-right font-bold" style="white-space: nowrap;">
                                                Rp <?= number_format($amt, 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center col-status">
                                                <span class="status-pill <?= $stClass ?>"><?= htmlspecialchars($stRaw) ?></span>
                                            </td>
                                            <td class="text-center col-proof" style="font-size: 8pt;">
                                                <?= !empty($t['payment_proof']) ? 'Ada' : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($transactions)): ?>
                            <tfoot>
                                <tr style="background: #f0f0f0;">
                                    <td class="text-right font-bold" colspan="1"></td>
                                    <td class="col-date"></td>
                                    <td class="text-right font-bold" style="text-transform: uppercase;">Total (Sesuai Filter)</td>
                                    <td class="col-type"></td>
                                    <td class="text-center font-bold col-counts"><?= number_format($sumSkaters) ?> / <?= number_format($sumEntries) ?></td>
                                    <td class="text-right font-bold" style="font-size: 10pt; white-space: nowrap;">Rp <?= number_format($filteredSum, 0, ',', '.') ?></td>
                                    <td class="col-status"></td>
                                    <td class="col-proof"></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                        </div>

                        <p style="font-size: 8pt; margin-top: 12px; color: #555;">
                            <em>* Laporan keuangan resmi dicetak dari sistem administrasi SET SYSTEM pada <?= date('d M Y H:i') ?> WIB.</em>
                        </p>
                    </td>
                </tr>
            </tbody>

            <!-- REPEATING FOOTER SPACER -->
            <tfoot>
                <tr>
                    <td>
                        <div class="footer-spacer"></div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- ACTUAL FIXED FOOTER -->
        <?php if (!empty($sponsors)): ?>
            <div class="sponsor-footer">
                <p style="font-size: 8pt; color: #888; margin: 0 0 5px 0; text-transform: uppercase; font-weight: bold; letter-spacing: 2px;">Supported By</p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php foreach ($sponsors as $sponsor): ?>
                        <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $sponsor), '/') ?>" alt="Sponsor">
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            const checkboxes = document.querySelectorAll('.print-toolbar input[data-col-toggle]');
            function syncPrintView() {
                const url = new URL(window.location.href);
                url.searchParams.set('cfg', '1');
                checkboxes.forEach(function (cb) {
                    const param = cb.getAttribute('data-col-toggle');
                    const bodyCls = cb.getAttribute('data-body-class');
                    if (cb.checked) {
                        url.searchParams.set(param, '1');
                        if (bodyCls) document.body.classList.remove(bodyCls);
                    } else {
                        url.searchParams.delete(param);
                        if (bodyCls) document.body.classList.add(bodyCls);
                    }
                });
                window.history.replaceState({}, '', url.toString());
            }
            checkboxes.forEach(function (cb) {
                cb.addEventListener('change', syncPrintView);
            });
        })();
    </script>
</body>
</html>
