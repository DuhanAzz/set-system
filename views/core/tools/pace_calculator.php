<?php
$appName = $settings['app_name'] ?? 'Universal SET System';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Kalkulator Pace Training') ?> - <?= htmlspecialchars($appName) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        
        /* Hide scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* NAV & HEADER STYLE */
        #navbar { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .nav-link:hover { color: #f97316; }

        /* Print styles */
        @media print {
            @page {
                margin: 0mm; 
            }
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            #print-container {
                padding: 10mm !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            body.is-landscape #print-container {
                display: block;
                padding: 10mm 15mm !important;
            }
            .print-page-row {
                display: flex;
                justify-content: space-between;
                width: 100%;
                page-break-after: always;
                gap: 15mm;
                margin-bottom: 15mm;
            }
            .print-page-row:last-child {
                page-break-after: auto;
            }
            .print-col {
                width: calc(50% - 7.5mm);
            }
            .page-break { page-break-after: always; }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #navbar { display: none !important; }
            
            main { padding: 0 !important; margin: 0 !important; }
            .max-w-4xl { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .rounded-3xl { border: none !important; box-shadow: none !important; padding: 0 !important; }
            
            .excel-table { width: 100%; border-collapse: collapse; margin-top: 0; font-size: 13px; }
            body.is-landscape .excel-table { font-size: 11px; }
            .excel-table th, .excel-table td { border: 1px solid #000; padding: 4px; color: #000; }
            .excel-table .title-row th { font-size: 18px; border: none !important; padding-bottom: 15px; }
            .excel-table .thick-bottom { border-bottom: 2px solid #000 !important; }
            .excel-table .thick-top { border-top: 2px solid #000 !important; }
            .excel-table td[colspan="4"], .excel-table td[colspan="3"] { border: none !important; padding: 2px 4px; }
            .text-red { color: red !important; }
        }
    </style>
    <!-- SheetJS for Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR PARTIAL -->
    <?php require __DIR__ . '/pace_calculator/partials/navbar.php'; ?>

    <!-- CONTENT -->
    <main class="flex-grow pt-32 pb-20 px-4 md:px-6">
        
        <div class="max-w-4xl mx-auto">
            
            <!-- Card Wrapper -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 md:p-10 border border-slate-100">
                
                <div class="text-center mb-10 print-hidden flex flex-col items-center">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-2 relative inline-block">
                        Pace Chart
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <div class="flex items-center gap-4 mt-4">
                        <p class="text-slate-500 font-bold tracking-widest uppercase text-sm mb-0">Generator Pace Training</p>
                        <button id="toggleModeBtn" onclick="toggleMode()" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black tracking-widest uppercase rounded shadow transition">Mode Matriks</button>
                    </div>
                </div>

                <!-- STEP 1: INPUT SECTION (MODE TUNGGAL) -->
                <?php require __DIR__ . '/pace_calculator/partials/single_form.php'; ?>

                <!-- STEP 1B: BULK MATRIX SECTION -->
                <?php require __DIR__ . '/pace_calculator/partials/matrix_form.php'; ?>

                <!-- STEP 2: OUTPUT SECTION -->
                <?php require __DIR__ . '/pace_calculator/partials/output_section.php'; ?>
                
            </div>
        </div>

        <!-- Dedicated Print Container & Footer -->
        <?php require __DIR__ . '/pace_calculator/partials/footer.php'; ?>
        
    </main>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    
    <!-- Pace Calculator Modular Logic -->
    <script src="<?= getenv('APP_URL') ?>/js/tools/pace_calculator/pace_calculator.js"></script>
</body>
</html>
