<?php
$activeTab   = $activeTab ?? 'starting_list';
$printConfig = $printConfig ?? [];
$rawHeader   = !empty($event['header_logos']) ? json_decode($event['header_logos'], true) : [];
$headerLogos = ['left' => [], 'center' => [], 'right' => []];
if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
    $headerLogos['left'] = $rawHeader;
} elseif (is_array($rawHeader)) {
    $headerLogos = array_merge($headerLogos, $rawHeader);
}
$sponsors = !empty($event['sponsor_logos']) ? json_decode($event['sponsor_logos'], true) : [];

$docTitle = ($activeTab === 'bpjs') ? 'Data Peserta, NIK & BPJS' : 'Starting List Peserta';

// Tentukan class awal <body> agar sesuai konfigurasi checkbox URL
$bodyClasses = [];
if (empty($printConfig['show_bib']))     $bodyClasses[] = 'hide-col-bib';
if (empty($printConfig['show_dob']))     $bodyClasses[] = 'hide-col-dob';
if (empty($printConfig['show_club']))    $bodyClasses[] = 'hide-col-club';
if ($activeTab === 'bpjs') {
    if (empty($printConfig['show_nik']))  $bodyClasses[] = 'hide-col-nik';
    if (empty($printConfig['show_bpjs'])) $bodyClasses[] = 'hide-col-bpjs';
} else {
    if (empty($printConfig['show_classes']))   $bodyClasses[] = 'hide-col-classes';
    if (empty($printConfig['page_break_cat'])) $bodyClasses[] = 'no-cat-break';
}

$formatDob = static function ($dob): string {
    if (empty($dob) || $dob === '0000-00-00') return '-';
    $ts = strtotime($dob);
    return $ts ? date('d/m/Y', $ts) : '-';
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($docTitle . ' - ' . ($event['event_name'] ?? 'Event')) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- RESET & COLOR SETTINGS --- */
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 64px 0 0 0; /* Ruang untuk toolbar preview di atas */
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

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
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
            margin-bottom: 16px;
            background: #fdfdfd;
            border-radius: 8px;
        }

        .cat-banner {
            background: #1e293b;
            color: #ffffff;
            padding: 7px 12px;
            font-size: 11pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border: 1px solid #000;
            margin-top: 8px;
            margin-bottom: 0;
            page-break-after: avoid;
            break-after: avoid;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ku-subbar {
            background: #e2e8f0;
            color: #0f172a;
            padding: 5px 10px;
            font-size: 9.5pt;
            font-weight: 800;
            text-transform: uppercase;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-top: 10px;
            page-break-after: avoid;
            break-after: avoid;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table.schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        table.schedule-table tr { page-break-inside: avoid; break-inside: avoid; }
        table.schedule-table thead { display: table-header-group; }
        table.schedule-table th, table.schedule-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            text-align: left;
            font-size: 9.5pt;
            vertical-align: middle;
        }
        table.schedule-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }

        .badge-bib {
            display: inline-block;
            padding: 1px 6px;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            font-weight: 800;
            font-size: 9pt;
            min-width: 34px;
            text-align: center;
        }
        .badge-status-ok {
            display: inline-block;
            padding: 1px 6px;
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-status-warn {
            display: inline-block;
            padding: 1px 6px;
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Pemisah visual antar kategori pada preview layar */
        .page-break-indicator {
            margin: 18px 0 8px 0;
            border-top: 2px dashed #94a3b8;
            text-align: center;
            position: relative;
        }
        .page-break-indicator span {
            position: relative;
            top: -9px;
            background: #fff;
            padding: 0 10px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.08em;
        }
        body.no-cat-break .page-break-indicator { display: none; }

        /* Dynamic Column Toggle Classes */
        body.hide-col-bib     .col-bib,
        body.hide-col-dob     .col-dob,
        body.hide-col-club    .col-club,
        body.hide-col-classes .col-classes,
        body.hide-col-nik     .col-nik,
        body.hide-col-bpjs    .col-bpjs {
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
            .print-toolbar, .page-break-indicator { display: none !important; }
            .sheet {
                margin: 0;
                box-shadow: none;
                border: none;
                display: block;
                position: static !important;
            }
            tbody.cat-break-tbody {
                page-break-before: always;
                break-before: page;
            }
            body.no-cat-break tbody.cat-break-tbody {
                page-break-before: auto;
                break-before: auto;
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

    <!-- BAR PENGATURAN TAMPILAN CETAK & AKSI -->
    <div class="print-toolbar">
        <div class="toolbar-options">
            <span class="toolbar-label"><i class="fas fa-sliders-h"></i> Tampilkan:</span>

            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_bib" data-body-class="hide-col-bib" <?= !empty($printConfig['show_bib']) ? 'checked' : '' ?>>
                <span>No. BIB</span>
            </label>
            <label class="opt-chip">
                <input type="checkbox" data-col-toggle="show_dob" data-body-class="hide-col-dob" <?= !empty($printConfig['show_dob']) ? 'checked' : '' ?>>
                <span>Tgl Lahir</span>
            </label>

            <?php if ($activeTab === 'bpjs'): ?>
                <label class="opt-chip">
                    <input type="checkbox" data-col-toggle="show_nik" data-body-class="hide-col-nik" <?= !empty($printConfig['show_nik']) ? 'checked' : '' ?>>
                    <span>NIK</span>
                </label>
                <label class="opt-chip">
                    <input type="checkbox" data-col-toggle="show_club" data-body-class="hide-col-club" <?= !empty($printConfig['show_club']) ? 'checked' : '' ?>>
                    <span>Klub / Kontingen</span>
                </label>
                <label class="opt-chip">
                    <input type="checkbox" data-col-toggle="show_bpjs" data-body-class="hide-col-bpjs" <?= !empty($printConfig['show_bpjs']) ? 'checked' : '' ?>>
                    <span>Status BPJS</span>
                </label>
            <?php else: ?>
                <label class="opt-chip">
                    <input type="checkbox" data-col-toggle="show_club" data-body-class="hide-col-club" <?= !empty($printConfig['show_club']) ? 'checked' : '' ?>>
                    <span>Klub / Kontingen</span>
                </label>
                <label class="opt-chip">
                    <input type="checkbox" data-col-toggle="show_classes" data-body-class="hide-col-classes" <?= !empty($printConfig['show_classes']) ? 'checked' : '' ?>>
                    <span>Kelas / Nomor</span>
                </label>
                <label class="opt-chip" style="border-color: #3b82f6;">
                    <input type="checkbox" data-col-toggle="page_break_cat" data-body-class="no-cat-break" <?= !empty($printConfig['page_break_cat']) ? 'checked' : '' ?>>
                    <span>Pisah Halaman / Kategori</span>
                </label>
            <?php endif; ?>
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
                                    <h1 style="margin: 0; font-size: 16pt; text-transform: uppercase;"><?= htmlspecialchars($docTitle) ?></h1>
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

            <?php if ($activeTab === 'bpjs'): ?>
            <!-- MODE DATA BPJS -->
            <tbody>
                <tr>
                    <td>
                        <div class="summary-box">
                            <table style="width: 100%; border: none; font-size: 9.5pt;">
                                <tr>
                                    <td style="width: 55%; vertical-align: top;">
                                        <p style="margin: 0; font-size: 8.5pt; color: #555; text-transform: uppercase; font-weight: bold;">Ringkasan Data Peserta Lunas (Paid)</p>
                                        <h2 style="margin: 3px 0 8px 0; font-size: 13pt; font-weight: 900; color: #000; text-transform: uppercase;">
                                            <?= htmlspecialchars($event['event_name'] ?? 'Event') ?>
                                        </h2>
                                        <div style="font-size: 9pt; color: #334155;">
                                            Filter Aktif:
                                            <strong>BPJS = <?= htmlspecialchars(strtoupper($bpjsFilter ?? 'ALL')) ?></strong> &bull;
                                            <strong>NIK = <?= htmlspecialchars(strtoupper($nikFilter ?? 'ALL')) ?></strong>
                                            <?php if (!empty($search)): ?>
                                                &bull; Cari: "<strong><?= htmlspecialchars($search) ?></strong>"
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="width: 45%; vertical-align: top;">
                                        <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
                                            <tr>
                                                <td style="font-weight: bold; padding: 2px 0;">Total Peserta Lunas</td>
                                                <td style="text-align: right; font-weight: bold;">: <?= number_format($bpjsSummary['total'] ?? 0) ?> Atlet</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0;">BPJS Sudah / Belum</td>
                                                <td style="text-align: right;">: <strong style="color: #065f46;"><?= number_format($bpjsSummary['bpjs_sudah'] ?? 0) ?> Sudah</strong> / <strong style="color: #92400e;"><?= number_format($bpjsSummary['bpjs_belum'] ?? 0) ?> Belum</strong></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0;">NIK Kosong / &lt; 16 Digit</td>
                                                <td style="text-align: right; color: #b91c1c; font-weight: bold;">: <?= number_format($bpjsSummary['nik_kosong'] ?? 0) ?> Atlet</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 0;">Jumlah Tercetak</td>
                                                <td style="text-align: right; font-weight: bold;">: <?= count($bpjsParticipants) ?> Baris</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 38px;">No</th>
                                    <th class="text-center col-bib" style="width: 68px;">BIB</th>
                                    <th>Nama Atlet</th>
                                    <th class="text-center" style="width: 68px;">Gender</th>
                                    <th class="text-center col-dob" style="width: 90px;">Tgl Lahir</th>
                                    <th class="col-nik" style="width: 145px;">NIK</th>
                                    <th class="col-club">Klub / Kontingen</th>
                                    <th class="text-center col-bpjs" style="width: 110px;">Status BPJS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bpjsParticipants)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada peserta lunas yang cocok dengan filter.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bpjsParticipants as $idx => $p):
                                        $nikStr       = trim((string)($p['nik'] ?? ''));
                                        $isNikValid   = ($nikStr !== '' && strlen($nikStr) >= 16);
                                        $isGenderMale = in_array(strtoupper($p['gender'] ?? ''), ['M', 'L', 'PUTRA', 'MALE', 'PA']);
                                    ?>
                                        <tr>
                                            <td class="text-center font-bold"><?= $idx + 1 ?></td>
                                            <td class="text-center col-bib">
                                                <span class="badge-bib"><?= htmlspecialchars($p['bib_number'] ?? '-') ?></span>
                                            </td>
                                            <td class="font-bold" style="text-transform: uppercase;"><?= htmlspecialchars($p['skater_name'] ?? '-') ?></td>
                                            <td class="text-center"><?= $isGenderMale ? 'Putra' : 'Putri' ?></td>
                                            <td class="text-center col-dob"><?= htmlspecialchars($formatDob($p['birth_date'] ?? null)) ?></td>
                                            <td class="col-nik" style="font-family: monospace; font-size: 9pt;">
                                                <?php if ($isNikValid): ?>
                                                    <?= htmlspecialchars($nikStr) ?>
                                                <?php elseif ($nikStr !== ''): ?>
                                                    <span style="color: #b91c1c; font-weight: bold;"><?= htmlspecialchars($nikStr) ?> (&lt;16)</span>
                                                <?php else: ?>
                                                    <span style="color: #b91c1c; font-style: italic;">Belum Ada NIK</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-club" style="text-transform: uppercase; font-size: 9pt;"><?= htmlspecialchars($p['club_name'] ?? '-') ?></td>
                                            <td class="text-center col-bpjs">
                                                <?php if (($p['status_bpjs'] ?? 'belum') === 'sudah'): ?>
                                                    <span class="badge-status-ok">Sudah</span>
                                                <?php else: ?>
                                                    <span class="badge-status-warn">Belum</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <p style="font-size: 8pt; margin-top: 12px; color: #555;">
                            <em>* Dicetak dari sistem administrasi SET SYSTEM pada <?= date('d M Y H:i') ?> WIB.</em>
                        </p>
                    </td>
                </tr>
            </tbody>

            <?php else: ?>
            <!-- MODE STARTING LIST -->
            <?php
            $orderCats    = ['Speed', 'Standart', 'Pemula', 'Lainnya'];
            $orderGenders = ['Putri', 'Putra'];
            $renderedCats = array_values(array_filter($orderCats, fn($c) => !empty($grouped[$c])));
            ?>
            <?php if (empty($renderedCats)): ?>
                <tbody>
                    <tr>
                        <td>
                            <div class="summary-box" style="text-align: center; padding: 30px;">
                                <p style="margin: 0; font-weight: bold; color: #64748b;">Belum ada peserta terdaftar pada event ini.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            <?php else: ?>
                <?php foreach ($renderedCats as $catIdx => $cat): ?>
                    <tbody class="<?= ($catIdx > 0) ? 'cat-break-tbody' : '' ?>">
                        <tr>
                            <td>
                                <?php if ($catIdx === 0): ?>
                                    <!-- Summary Box hanya di halaman awal -->
                                    <div class="summary-box">
                                        <table style="width: 100%; border: none; font-size: 9.5pt;">
                                            <tr>
                                                <td style="width: 60%; vertical-align: top;">
                                                    <p style="margin: 0; font-size: 8.5pt; color: #555; text-transform: uppercase; font-weight: bold;">Dokumen Resmi Starting List</p>
                                                    <h2 style="margin: 3px 0 6px 0; font-size: 13pt; font-weight: 900; color: #000; text-transform: uppercase;">
                                                        <?= htmlspecialchars($event['event_name'] ?? 'Event') ?>
                                                    </h2>
                                                    <p style="margin: 0; font-size: 9pt; color: #334155;">
                                                        Total Atlet Unik (Lunas): <strong><?= number_format($totalSkaters ?? 0) ?> Atlet</strong>
                                                    </p>
                                                </td>
                                                <td style="width: 40%; vertical-align: top;">
                                                    <p style="margin: 0 0 4px 0; font-size: 8.5pt; color: #555; text-transform: uppercase; font-weight: bold;">Rekap per Kategori</p>
                                                    <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
                                                        <?php foreach ($renderedCats as $rc): ?>
                                                            <tr>
                                                                <td style="padding: 1px 0; font-weight: bold;"><?= htmlspecialchars($rc) ?></td>
                                                                <td style="text-align: right;">: <?= number_format($categoryCounts[$rc] ?? 0) ?> Entri Atlet</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="page-break-indicator">
                                        <span>Halaman Baru &bull; Kategori <?= htmlspecialchars($cat) ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="cat-banner">
                                    <span>KATEGORI: <?= htmlspecialchars(strtoupper($cat)) ?></span>
                                    <span style="font-size: 9pt; font-weight: 700;"><?= number_format($categoryCounts[$cat] ?? 0) ?> Atlet</span>
                                </div>

                                <?php
                                $kuGroups = $grouped[$cat];
                                ksort($kuGroups);
                                foreach ($kuGroups as $ku => $genders):
                                    foreach ($orderGenders as $gender):
                                        if (empty($genders[$gender])) continue;
                                        $skaterList = $genders[$gender];
                                ?>
                                    <div class="ku-subbar">
                                        <span>KU: <?= htmlspecialchars($ku) ?> &bull; <?= htmlspecialchars(strtoupper($gender)) ?></span>
                                        <span><?= count($skaterList) ?> Atlet</span>
                                    </div>
                                    <table class="schedule-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 36px;">No</th>
                                                <th class="text-center col-bib" style="width: 65px;">BIB</th>
                                                <th>Nama Atlet</th>
                                                <th class="text-center col-dob" style="width: 90px;">Tgl Lahir</th>
                                                <th class="col-club">Klub / Kontingen</th>
                                                <th class="col-classes">Kelas / Nomor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($skaterList as $i => $s): ?>
                                                <tr>
                                                    <td class="text-center font-bold"><?= $i + 1 ?></td>
                                                    <td class="text-center col-bib">
                                                        <span class="badge-bib"><?= htmlspecialchars($s['bib_number'] ?? '-') ?></span>
                                                    </td>
                                                    <td class="font-bold" style="text-transform: uppercase;"><?= htmlspecialchars($s['skater_name'] ?? '-') ?></td>
                                                    <td class="text-center col-dob"><?= htmlspecialchars($formatDob($s['birth_date'] ?? null)) ?></td>
                                                    <td class="col-club" style="font-size: 9pt;"><?= htmlspecialchars($s['club_name'] ?? '-') ?></td>
                                                    <td class="col-classes" style="font-size: 9pt;"><?= htmlspecialchars($s['distances'] ?: ($s['class_name'] ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php
                                    endforeach;
                                endforeach;
                                ?>

                                <?php if ($catIdx === count($renderedCats) - 1): ?>
                                    <p style="font-size: 8pt; margin-top: 12px; color: #555;">
                                        <em>* Dokumen Starting List dicetak resmi dari SET SYSTEM pada <?= date('d M Y H:i') ?> WIB.</em>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php endif; ?>

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
