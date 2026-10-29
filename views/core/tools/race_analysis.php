<?php
$appName = $settings['app_name'] ?? 'Universal SET System';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Analisis Performa Lomba (Race Analyzer)') ?> - <?= htmlspecialchars($appName) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    
    <!-- External Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        #navbar { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .nav-link:hover { color: #f97316; }
        .norma-item { transition: all 0.2s ease; }
        .badge-ku { font-family: 'Inter', sans-serif; }

        @media print {
            @page { margin: 0; size: A4 portrait; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0 !important; padding: 0 !important; }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #print-container { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .print-sheet { page-break-after: avoid; page-break-inside: avoid; }
            table, tr, td, th { page-break-inside: avoid !important; }
        }

        @media screen {
            .print-visible { display: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR PARTIAL -->
    <?php require __DIR__ . '/race_analysis/partials/navbar.php'; ?>

    <!-- CONTENT WRAPPER -->
    <main class="flex-grow pt-32 pb-20 px-4 md:px-6">
        <div class="max-w-4xl mx-auto">
            <!-- Card Wrapper -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 md:p-10 border border-slate-100 print-hidden">
                
                <!-- Header Title -->
                <div class="text-center mb-8 flex flex-col items-center">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-1 relative inline-block">
                        Race Analyzer
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <p class="text-slate-500 font-bold tracking-widest uppercase text-xs mt-3">Analisis Pacing & Kinematika Kayuhan</p>
                </div>

                <div class="space-y-8">
                    <!-- 1 & 2. Data Atlet & Konfigurasi Lomba -->
                    <?php require __DIR__ . '/race_analysis/partials/athlete_form.php'; ?>

                    <!-- 3. Tabel Putaran Lap -->
                    <?php require __DIR__ . '/race_analysis/partials/laps_table.php'; ?>

                    <!-- 4. Hasil & Norma Evaluasi -->
                    <?php require __DIR__ . '/race_analysis/partials/norma_cards.php'; ?>

                    <!-- 5. Grafik Visual & Tombol Buka Laporan -->
                    <?php require __DIR__ . '/race_analysis/partials/chart_section.php'; ?>
                </div>

            </div>
        </div>

        <!-- Print Container & Footer -->
        <?php require __DIR__ . '/race_analysis/partials/footer.php'; ?>
    </main>

    <!-- MODULAR SCRIPTS (DECOUPLED & CACHED) -->
    <script src="<?= getenv('APP_URL') ?>/js/tools/race_analysis/normative_db.js"></script>
    <script src="<?= getenv('APP_URL') ?>/js/tools/race_analysis/race_chart.js"></script>
    <script src="<?= getenv('APP_URL') ?>/js/tools/race_analysis/race_calculator.js"></script>
    <script src="<?= getenv('APP_URL') ?>/js/tools/race_analysis/race_reporter.js"></script>

</body>
</html>
