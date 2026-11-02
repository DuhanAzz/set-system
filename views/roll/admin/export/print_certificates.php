<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Sertifikat Puspernas - <?= htmlspecialchars($event['event_name'] ?? 'Event') ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background-color: #525659;
            font-family: 'Times New Roman', Times, serif;
            color: #1a1a1a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* --- SCREEN BAR --- */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 9999;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2);
            font-family: ui-sans-serif, system-ui, sans-serif;
        }
        .btn-print {
            background: #059669;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 13px;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .btn-print:hover { background: #047857; }

        /* --- A4 LANDSCAPE SHEET --- */
        .sheet {
            width: 297mm;
            height: 210mm;
            min-height: 210mm;
            max-height: 210mm;
            background: #ffffff;
            margin: 60px auto 30px auto;
            padding: 10mm;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* --- ORNAMENT BORDERS --- */
        .cert-outer-border {
            border: 4px solid #1e3a8a;
            height: 100%;
            padding: 4px;
            position: relative;
        }
        .cert-inner-border {
            border: 2px solid #b45309;
            height: 100%;
            padding: 8mm 12mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            background: radial-gradient(circle at center, #ffffff 60%, #fefce8 100%);
        }

        /* --- CORNER ACCENTS --- */
        .corner {
            position: absolute;
            width: 24px;
            height: 24px;
            border-color: #b45309;
        }
        .corner-tl { top: 8px; left: 8px; border-top: 4px solid #b45309; border-left: 4px solid #b45309; }
        .corner-tr { top: 8px; right: 8px; border-top: 4px solid #b45309; border-right: 4px solid #b45309; }
        .corner-bl { bottom: 8px; left: 8px; border-bottom: 4px solid #b45309; border-left: 4px solid #b45309; }
        .corner-br { bottom: 8px; right: 8px; border-bottom: 4px solid #b45309; border-right: 4px solid #b45309; }

        /* --- HEADER --- */
        .cert-header {
            margin-bottom: 4px;
        }
        .cert-title {
            font-size: 26pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0;
            line-height: 1;
        }
        .cert-subtitle {
            font-size: 13pt;
            font-weight: bold;
            color: #92400e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 4px 0 0 0;
        }
        .cert-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #64748b;
            margin-top: 2px;
            font-weight: bold;
        }

        /* --- BODY CONTENT --- */
        .given-to {
            font-size: 11pt;
            font-style: italic;
            color: #475569;
            margin: 6px 0 2px 0;
        }
        .athlete-name {
            font-size: 24pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 2px 0;
            border-bottom: 2px solid #0f172a;
            display: inline-block;
            padding: 0 30px;
        }
        .institution-box {
            margin: 4px 0;
        }
        .institution-puspernas {
            font-size: 12pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .club-sub {
            font-size: 9.5pt;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* --- PRESTASI BADGE --- */
        .achievement-text {
            font-size: 10.5pt;
            color: #475569;
            margin: 4px 0;
        }
        .juara-badge {
            display: inline-block;
            font-size: 16pt;
            font-weight: 900;
            color: #78350f;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 4px 28px;
            border-radius: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            margin: 2px 0;
        }
        .race-category {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }
        .event-context {
            font-size: 10pt;
            color: #475569;
            margin: 2px 0 0 0;
        }

        /* --- SIGNATURES & FOOTER --- */
        .cert-footer {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 20mm;
        }
        .sign-block {
            text-align: center;
            width: 65mm;
        }
        .sign-title {
            font-size: 9.5pt;
            color: #475569;
            margin-bottom: 16mm;
        }
        .sign-name {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            text-transform: uppercase;
        }
        .qr-placeholder {
            width: 20mm;
            height: 20mm;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            color: #94a3b8;
            background: #ffffff;
            margin: 0 auto 4px auto;
        }

        /* --- PRINT MEDIA RULES --- */
        @media print {
            body { background: transparent; }
            .no-print-bar { display: none !important; }
            .sheet {
                margin: 0 !important;
                box-shadow: none !important;
                width: 297mm !important;
                height: 210mm !important;
            }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Top floating action bar (screen only) -->
    <div class="no-print-bar">
        <div>
            <strong>Pencetakan Berkas Resmi & Piagam Puspernas</strong>
            <span style="opacity: 0.7; margin-left: 10px;">• Total: <?= count($certificates) ?> Lembar Piagam</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua Piagam (Print / PDF)</button>
        </div>
    </div>

    <?php if (empty($certificates)): ?>
        <div class="sheet" style="justify-content: center; align-items: center;">
            <div style="text-align: center; font-family: ui-sans-serif, system-ui, sans-serif;">
                <div style="font-size: 48px; margin-bottom: 12px;">📄</div>
                <h2 style="margin: 0; color: #475569;">Belum Ada Data Piagam</h2>
                <p style="color: #94a3b8; font-size: 14px; margin-top: 6px;">Tidak ada pemenang atau peserta yang sesuai dengan filter yang dipilih.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($certificates as $c): ?>
        <div class="sheet">
            <div class="cert-outer-border">
                <div class="corner corner-tl"></div>
                <div class="corner corner-tr"></div>
                <div class="corner corner-bl"></div>
                <div class="corner corner-br"></div>

                <div class="cert-inner-border">
                    
                    <!-- Header -->
                    <div class="cert-header">
                        <h1 class="cert-title">Piagam Penghargaan</h1>
                        <div class="cert-subtitle">Kejuaraan Sepatu Roda Pelajar - Puspernas</div>
                        <div class="cert-number">NOMOR: <?= htmlspecialchars($c['no_sertifikat']) ?></div>
                    </div>

                    <!-- Given To -->
                    <div>
                        <div class="given-to">Piagam ini diberikan dengan bangga dan hormat kepada:</div>
                        <div class="athlete-name"><?= htmlspecialchars($c['skater_name']) ?></div>
                        
                        <!-- Satu Kesatuan Sekolah & Daerah Puspernas -->
                        <div class="institution-box">
                            <div class="institution-puspernas">
                                🏫 <?= htmlspecialchars($c['institusi_puspernas']) ?>
                            </div>
                            <?php if (!empty($c['club_name']) && $c['club_name'] !== '-'): ?>
                                <div class="club-sub">Afiliasi Klub: <?= htmlspecialchars($c['club_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Achievement -->
                    <div>
                        <div class="achievement-text">Atas dedikasi, sportivitas, dan prestasi gemilang sebagai:</div>
                        <div class="juara-badge">
                            <?= htmlspecialchars($c['juara']) ?> (<?= htmlspecialchars($c['medali']) ?>)
                        </div>
                        <div class="race-category">
                            Nomor Acara: <?= htmlspecialchars($c['race_name']) ?>
                            <?php if ($c['time'] !== '-'): ?>
                                <span style="font-size: 10pt; color: #475569; font-weight: normal; margin-left: 6px;">(Waktu: <?= htmlspecialchars($c['time']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="event-context">
                            Pada ajang bergengsi <strong><?= htmlspecialchars($c['event_name']) ?></strong>
                            <?php if (!empty($c['location'])): ?> yang diselenggarakan di <?= htmlspecialchars($c['location']) ?><?php endif; ?>
                            <?php if (!empty($c['event_date'])): ?>, tanggal <?= date('d F Y', strtotime($c['event_date'])) ?><?php endif; ?>.
                        </div>
                    </div>

                    <!-- Footer & Signatures -->
                    <div class="cert-footer">
                        <div class="sign-block">
                            <div class="sign-title">
                                Technical Delegate / Perlombaan<br>
                                Perserosi / Panitia Pelaksana
                            </div>
                            <div class="sign-name">DELEGASI TEKNIS</div>
                        </div>

                        <div style="text-align: center;">
                            <div class="qr-placeholder">
                                QR CODE<br>VERIFIKASI
                            </div>
                            <div style="font-size: 7pt; color: #94a3b8; font-family: monospace;">
                                UID: <?= htmlspecialchars($c['uid'] ?? '-') ?>
                            </div>
                        </div>

                        <div class="sign-block">
                            <div class="sign-title">
                                Ketua Penyelenggara / Pengurus<br>
                                Kejuaraan Sepatu Roda
                            </div>
                            <div class="sign-name">KETUA PANITIA</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>

