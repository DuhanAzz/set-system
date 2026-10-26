<?php
$appName = $settings['app_name'] ?? 'Universal SET System';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Race Analyzer') ?> - <?= htmlspecialchars($appName) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* NAVBAR STYLE (IDENTICAL TO PACE CALCULATOR) */
        #navbar { 
            background-color: #0F172A; 
            height: 85px; 
            border-bottom: 1px solid #1e293b; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
            display: flex; 
            align-items: center; 
        }
        .nav-link { 
            position: relative; 
            color: white; 
            transition: all 0.3s ease; 
            font-size: 0.95rem; 
            font-weight: 800; 
            letter-spacing: 0.05em; 
            text-transform: uppercase; 
        }
        .nav-link::after { 
            content: ''; 
            position: absolute; 
            width: 0; 
            height: 3px; 
            bottom: -8px; 
            left: 0; 
            background-color: #f97316; 
            transition: width 0.3s ease; 
        }
        .nav-link:hover::after { width: 100%; }
        .nav-link:hover { color: #f97316; }

        /* PRINT STYLES (MATCHING PACE CALCULATOR EXCEL TABLE PATTERN) */
        @media print {
            @page {
                margin: 10mm; 
            }
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            #print-container {
                display: block !important;
                padding: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #navbar { display: none !important; }
            footer { display: none !important; }
            
            main { padding: 0 !important; margin: 0 !important; }
            .max-w-4xl { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .rounded-3xl { border: none !important; box-shadow: none !important; padding: 0 !important; }
            
            .excel-table { width: 100%; border-collapse: collapse; margin-top: 0; font-size: 12px; }
            .excel-table th, .excel-table td { border: 1px solid #000; padding: 5px; color: #000; }
            .excel-table .title-row th { font-size: 18px; border: none !important; padding-bottom: 12px; }
            .excel-table .thick-bottom { border-bottom: 2px solid #000 !important; }
            .excel-table .thick-top { border-top: 2px solid #000 !important; }
            .excel-table td[colspan="4"], .excel-table td[colspan="3"], .excel-table td[colspan="2"] { border: none !important; padding: 3px 5px; }
            .text-red { color: red !important; }
            .bg-highlight { background-color: #e2e8f0 !important; font-weight: bold !important; }
        }

        @media screen {
            .print-visible { display: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR (SAME AS PACE CALCULATOR) -->
    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 px-6 md:px-10 print-hidden">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/"><img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-12 md:h-14 w-auto object-contain transition-all duration-300" id="nav-logo" alt="SET System"></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="<?= getenv('APP_URL') ?>/swim" class="nav-link">Sistem Renang</a>
                    <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link">Sistem Sepatu Roda</a>
                    <a href="<?= getenv('APP_URL') ?>/#events" class="nav-link">Jadwal Event</a>
                </div>
                <div class="hidden lg:flex items-center lg:border-l lg:border-white/20 lg:pl-10">
                    <a href="<?= getenv('APP_URL') ?>/core/login" class="bg-[#f25822] hover:bg-orange-600 text-white px-8 py-3 rounded font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:-translate-y-1">Login Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT WRAPPER -->
    <main class="flex-grow pt-32 pb-20 px-4 md:px-6">
        
        <div class="max-w-4xl mx-auto">
            
            <!-- Card Wrapper -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 md:p-10 border border-slate-100 print-hidden">
                
                <!-- HEADER TITLE (SEDERHANA SEPERTI PACE CALCULATOR) -->
                <div class="text-center mb-8 flex flex-col items-center">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-1 relative inline-block">
                        Race Analyzer
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <p class="text-slate-500 font-bold tracking-widest uppercase text-xs mt-3">Analisis Pacing & Kinematika Kayuhan</p>
                </div>

                <div class="space-y-8">
                    
                    <!-- 1. DATA ATLET -->
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3 pb-1 border-b border-slate-100 flex items-center justify-between">
                            <span>1. Data Atlet</span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Atlet</label>
                                <input type="text" id="athleteName" placeholder="Masukkan nama atlet" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Umur (Tahun)</label>
                                <input type="number" id="athleteAge" min="5" max="80" placeholder="Contoh: 12" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
                                <select id="athleteGender" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                                    <option value="Putra">Putra</option>
                                    <option value="Putri">Putri</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                                <input type="date" id="raceDate" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 2. KONFIGURASI NOMOR LOMBA -->
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3 pb-1 border-b border-slate-100">
                            2. Konfigurasi Nomor Lomba
                        </h3>

                        <!-- Panjang Kolam -->
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Panjang Kolam:</label>
                            <div class="grid grid-cols-2 gap-3 max-w-md">
                                <button type="button" onclick="setPoolLength(50)" id="btnPool50" class="py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm">
                                    50 Meter (LCM)
                                </button>
                                <button type="button" onclick="setPoolLength(25)" id="btnPool25" class="py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">
                                    25 Meter (SCM)
                                </button>
                            </div>
                        </div>

                        <!-- Gaya Renang -->
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Gaya Renang:</label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                <button type="button" onclick="setStrokeStyle('Gaya Bebas')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white">Bebas</button>
                                <button type="button" onclick="setStrokeStyle('Gaya Dada')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Dada</button>
                                <button type="button" onclick="setStrokeStyle('Gaya Punggung')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Punggung</button>
                                <button type="button" onclick="setStrokeStyle('Gaya Kupu-Kupu')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Kupu-Kupu</button>
                                <button type="button" onclick="setStrokeStyle('Ganti Perorangan')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">Ganti (IM)</button>
                            </div>
                        </div>

                        <!-- Jarak Lomba -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Jarak Lomba:</label>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                <button type="button" onclick="setDistance(50)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">50m</button>
                                <button type="button" onclick="setDistance(100)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm">100m</button>
                                <button type="button" onclick="setDistance(200)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">200m</button>
                                <button type="button" onclick="setDistance(400)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">400m</button>
                                <button type="button" onclick="setDistance(800)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">800m</button>
                                <button type="button" onclick="setDistance(1500)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200">1500m</button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. INPUT PUTARAN LAP -->
                    <div>
                        <div class="flex items-center justify-between mb-2 pb-1 border-b border-slate-100">
                            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
                                3. Input Putaran Lap
                            </h3>
                            <span id="lapCountBadge" class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-200">
                                2 Lap (100m)
                            </span>
                        </div>

                        <!-- TABEL LAP -->
                        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm mt-3">
                            <table class="w-full text-xs text-center" id="lapsTable">
                                <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                                    <tr>
                                        <th class="py-2.5 px-2">Lap</th>
                                        <th class="py-2.5 px-2">Jarak</th>
                                        <th class="py-2.5 px-2 min-w-[100px]">Waktu Split</th>
                                        <th class="py-2.5 px-2 min-w-[85px]">Kayuhan</th>
                                        <th class="py-2.5 px-2">Laju (m/s)</th>
                                        <th class="py-2.5 px-2">SR (SPM)</th>
                                        <th class="py-2.5 px-2">DPS (m)</th>
                                        <th class="py-2.5 px-2 font-bold text-blue-900">SWOLF</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100" id="lapsTableBody">
                                    <!-- Dynamic rows rendered by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. HASIL & NORMA EVALUASI -->
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 mb-3 pb-1 border-b border-slate-100">
                            4. Hasil Analisis & Norma Standar
                        </h3>

                        <!-- TOTAL WAKTU & KECEPATAN STRIP -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-100 p-4 rounded-2xl mb-4 border border-slate-200">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase block">Total Waktu</span>
                                <div class="font-teko text-3xl font-black text-blue-900 leading-tight" id="kpiTotalTime">00:00.00</div>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase block">Kecepatan Rata-rata</span>
                                <div class="font-teko text-3xl font-black text-slate-800 leading-tight" id="kpiAvgSpeed">0.00 m/s</div>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-[11px] font-bold text-slate-500 uppercase block">Distribusi Pacing</span>
                                <div class="text-xs font-bold text-slate-700 mt-1" id="kpiPacingType">-</div>
                            </div>
                        </div>

                        <!-- 3 KARTU NORMA EVALUASI -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            <!-- NORMA SWOLF -->
                            <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-bold text-slate-600 uppercase">SWOLF Efisiensi</span>
                                        <span id="badgeSwolf" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                                    </div>
                                    <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valSwolf">0.0</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                                    <div class="space-y-1 text-[10px]" id="normaSwolfBox">
                                        <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; 70</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">70 - 80</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">81 - 90</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; 90</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- NORMA DPS -->
                            <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-bold text-slate-600 uppercase">DPS (Jarak/Kayuhan)</span>
                                        <span id="badgeDps" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                                    </div>
                                    <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valDps">0.00 m</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                                    <div class="space-y-1 text-[10px]">
                                        <div class="norma-item flex justify-between p-1 rounded" id="dps_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&ge; 1.50 m</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="dps_baik"><span>Baik (Optimal)</span> <span class="font-mono">1.20 - 1.49 m</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="dps_cukup"><span>Cukup</span> <span class="font-mono">1.00 - 1.19 m</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="dps_kurang"><span>Kurang</span> <span class="font-mono">&lt; 1.00 m</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- NORMA STROKE RATE -->
                            <div class="border border-slate-200 rounded-2xl p-4 bg-white shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-bold text-slate-600 uppercase">Irama Kayuhan (SR)</span>
                                        <span id="badgeSr" class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-600">-</span>
                                    </div>
                                    <div class="text-3xl font-black font-teko text-blue-900 mb-2" id="valSr">0.0 SPM</div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Skala Norma Standar:</span>
                                    <div class="space-y-1 text-[10px]">
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_tinggi"><span>Sprint / Tinggi</span> <span class="font-mono">&gt; 48 SPM</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_sedang"><span>Optimal / Menengah</span> <span class="font-mono">36 - 48 SPM</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_rendah"><span>Tempo Rendah</span> <span class="font-mono">&lt; 36 SPM</span></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- GRAFIK VISUAL (SCREEN) -->
                        <div class="mt-6 border border-slate-200 rounded-2xl p-4 bg-slate-50">
                            <span class="text-xs font-bold text-slate-700 uppercase block mb-2">Grafik Tren Waktu & Kinematika Lap</span>
                            <div class="h-56 w-full">
                                <canvas id="screenChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- TOMBOL CETAK -->
                    <div class="pt-4">
                        <button type="button" onclick="printReport()" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black text-xs tracking-widest uppercase py-4 rounded-xl shadow-lg transition">
                            CETAK LAPORAN
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- DEDICATED PRINT CONTAINER (PERSIS POLA PACE CALCULATOR) -->
        <div id="print-container" class="hidden print-visible w-full bg-white text-black"></div>

    </main>

    <!-- FOOTER (PERSIS POLA PACE CALCULATOR) -->
    <footer class="bg-slate-950 border-t border-slate-900 py-8 text-center print-hidden mt-auto">
        <p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; <?= date('Y') ?> SET SYSTEM. All Rights Reserved.</p>
    </footer>

    <!-- SCRIPT APPLICATION LOGIC -->
    <script>
        let poolLength = 50;
        let strokeStyle = 'Gaya Bebas';
        let totalDistance = 100;
        let chartInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            generateLapInputs();
            calculateAll();
        });

        // 1. CONFIGURATION
        function setPoolLength(len) {
            poolLength = parseInt(len);
            document.getElementById('btnPool50').className = poolLength === 50 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
                : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
            
            document.getElementById('btnPool25').className = poolLength === 25 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
                : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';

            // Update Norma Swolf Box labels (Kolam 50m vs 25m)
            const box = document.getElementById('normaSwolfBox');
            if (poolLength === 50) {
                box.innerHTML = `
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; 70</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">70 - 80</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">81 - 90</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; 90</span></div>
                `;
            } else {
                box.innerHTML = `
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; 35</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">35 - 42</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">43 - 50</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; 50</span></div>
                `;
            }

            generateLapInputs();
            calculateAll();
        }

        function setStrokeStyle(styleName) {
            strokeStyle = styleName;
            document.querySelectorAll('.btn-stroke').forEach(btn => {
                if (btn.innerText.trim() === styleName || (styleName.startsWith('Ganti') && btn.innerText.includes('Ganti'))) {
                    btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm';
                } else {
                    btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
                }
            });
            calculateAll();
        }

        function setDistance(dist) {
            totalDistance = parseInt(dist);
            document.querySelectorAll('.btn-dist').forEach(btn => {
                if (parseInt(btn.innerText) === totalDistance) {
                    btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm';
                } else {
                    btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
                }
            });
            generateLapInputs();
            calculateAll();
        }

        // 2. GENERATE LAP INPUTS
        function generateLapInputs() {
            const lapCount = Math.max(1, Math.round(totalDistance / poolLength));
            document.getElementById('lapCountBadge').innerText = `${lapCount} Lap (${totalDistance}m)`;

            const tbody = document.getElementById('lapsTableBody');
            tbody.innerHTML = '';

            for (let i = 1; i <= lapCount; i++) {
                const startM = (i - 1) * poolLength;
                const endM = i * poolLength;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="py-2 px-2 font-bold text-slate-700">${i}</td>
                    <td class="py-2 px-2 text-slate-500 font-medium">${startM}-${endM}m</td>
                    <td class="py-1.5 px-2">
                        <input type="text" 
                               class="lap-time-input w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs" 
                               data-lap="${i}" 
                               placeholder="00.00" 
                               oninput="calculateAll()" 
                               onkeydown="handleKeyNav(event, ${i}, 'time')">
                    </td>
                    <td class="py-1.5 px-2">
                        <input type="number" 
                               class="lap-stroke-input w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs" 
                               data-lap="${i}" 
                               placeholder="Kayuhan" 
                               min="1" max="150"
                               oninput="calculateAll()" 
                               onkeydown="handleKeyNav(event, ${i}, 'stroke')">
                    </td>
                    <td class="py-2 px-2 font-mono text-slate-700 lap-vel">-</td>
                    <td class="py-2 px-2 font-mono text-slate-700 font-bold lap-sr">-</td>
                    <td class="py-2 px-2 font-mono text-slate-700 font-bold lap-dps">-</td>
                    <td class="py-2 px-2 font-mono text-blue-900 font-black lap-swolf">-</td>
                `;
                tbody.appendChild(tr);
            }
        }

        function handleKeyNav(e, lapIdx, type) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (type === 'time') {
                    const strokeInput = document.querySelector(`.lap-stroke-input[data-lap="${lapIdx}"]`);
                    if (strokeInput) strokeInput.focus();
                } else if (type === 'stroke') {
                    const nextTimeInput = document.querySelector(`.lap-time-input[data-lap="${lapIdx + 1}"]`);
                    if (nextTimeInput) {
                        nextTimeInput.focus();
                    } else {
                        e.target.blur();
                    }
                }
            }
        }

        function parseTimeToSeconds(val) {
            if (!val) return 0;
            val = val.toString().trim();
            if (val.includes(':')) {
                const parts = val.split(':');
                const min = parseFloat(parts[0]) || 0;
                const sec = parseFloat(parts[1]) || 0;
                return (min * 60) + sec;
            } else {
                return parseFloat(val) || 0;
            }
        }

        function formatSecondsToTime(totalSec) {
            if (!totalSec || isNaN(totalSec) || totalSec <= 0) return '00:00.00';
            const m = Math.floor(totalSec / 60);
            const s = totalSec % 60;
            const mStr = String(m).padStart(2, '0');
            const sStr = s.toFixed(2).padStart(5, '0');
            return `${mStr}:${sStr}`;
        }

        // 3. CALCULATION & NORMA HIGHLIGHT
        function calculateAll() {
            const timeInputs = document.querySelectorAll('.lap-time-input');
            const strokeInputs = document.querySelectorAll('.lap-stroke-input');
            const rows = document.querySelectorAll('#lapsTableBody tr');

            let totalTimeSec = 0;
            let validLapCount = 0;

            let lapTimes = [];
            let lapSRs = [];
            let lapDPSs = [];
            let lapSwolfs = [];
            let lapLabels = [];

            timeInputs.forEach((timeInp, idx) => {
                const lapNum = idx + 1;
                const strokeInp = strokeInputs[idx];
                const row = rows[idx];

                const sec = parseTimeToSeconds(timeInp.value);
                const strokes = parseFloat(strokeInp.value) || 0;

                const velCell = row.querySelector('.lap-vel');
                const srCell = row.querySelector('.lap-sr');
                const dpsCell = row.querySelector('.lap-dps');
                const swolfCell = row.querySelector('.lap-swolf');

                lapLabels.push(`Lap ${lapNum}`);

                if (sec > 0 && strokes > 0) {
                    validLapCount++;
                    totalTimeSec += sec;

                    const vel = poolLength / sec;
                    const sr = (strokes / sec) * 60;
                    const dps = poolLength / strokes;
                    const swolf = sec + strokes;

                    velCell.innerText = vel.toFixed(2);
                    srCell.innerText = sr.toFixed(1);
                    dpsCell.innerText = dps.toFixed(2);
                    swolfCell.innerText = swolf.toFixed(1);

                    lapTimes.push(sec);
                    lapSRs.push(parseFloat(sr.toFixed(1)));
                    lapDPSs.push(parseFloat(dps.toFixed(2)));
                    lapSwolfs.push(parseFloat(swolf.toFixed(1)));
                } else if (sec > 0) {
                    const vel = poolLength / sec;
                    velCell.innerText = vel.toFixed(2);
                    srCell.innerText = '-';
                    dpsCell.innerText = '-';
                    swolfCell.innerText = '-';

                    lapTimes.push(sec);
                    lapSRs.push(0);
                    lapDPSs.push(0);
                    lapSwolfs.push(0);
                } else {
                    velCell.innerText = '-';
                    srCell.innerText = '-';
                    dpsCell.innerText = '-';
                    swolfCell.innerText = '-';

                    lapTimes.push(0);
                    lapSRs.push(0);
                    lapDPSs.push(0);
                    lapSwolfs.push(0);
                }
            });

            // RESET HIGHLIGHT NORMA
            document.querySelectorAll('.norma-item').forEach(el => {
                el.className = 'norma-item flex justify-between p-1 rounded text-slate-600 bg-transparent';
            });

            if (validLapCount > 0) {
                const avgSwolf = lapSwolfs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgDps = lapDPSs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgSr = lapSRs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgSpeed = (poolLength * validLapCount) / totalTimeSec;

                document.getElementById('valSwolf').innerText = avgSwolf.toFixed(1);
                document.getElementById('valDps').innerText = `${avgDps.toFixed(2)} m`;
                document.getElementById('valSr').innerText = `${avgSr.toFixed(1)} SPM`;
                document.getElementById('kpiTotalTime').innerText = formatSecondsToTime(totalTimeSec);
                document.getElementById('kpiAvgSpeed').innerText = `${avgSpeed.toFixed(2)} m/s`;

                // EVALUATE NORMA SWOLF
                const badgeSwolf = document.getElementById('badgeSwolf');
                let swolfCategory = '';
                let activeSwolfId = '';

                if (poolLength === 50) {
                    if (avgSwolf < 70) {
                        swolfCategory = 'Sangat Baik';
                        activeSwolfId = 'swolf_sangat_baik';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
                    } else if (avgSwolf <= 80) {
                        swolfCategory = 'Baik (Optimal)';
                        activeSwolfId = 'swolf_baik';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                    } else if (avgSwolf <= 90) {
                        swolfCategory = 'Cukup';
                        activeSwolfId = 'swolf_cukup';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                    } else {
                        swolfCategory = 'Kurang';
                        activeSwolfId = 'swolf_kurang';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
                    }
                } else {
                    if (avgSwolf < 35) {
                        swolfCategory = 'Sangat Baik';
                        activeSwolfId = 'swolf_sangat_baik';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
                    } else if (avgSwolf <= 42) {
                        swolfCategory = 'Baik (Optimal)';
                        activeSwolfId = 'swolf_baik';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                    } else if (avgSwolf <= 50) {
                        swolfCategory = 'Cukup';
                        activeSwolfId = 'swolf_cukup';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                    } else {
                        swolfCategory = 'Kurang';
                        activeSwolfId = 'swolf_kurang';
                        badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
                    }
                }
                badgeSwolf.innerText = swolfCategory;
                const activeSwolfEl = document.getElementById(activeSwolfId);
                if (activeSwolfEl) {
                    activeSwolfEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
                }

                // EVALUATE NORMA DPS
                const badgeDps = document.getElementById('badgeDps');
                let dpsCategory = '';
                let activeDpsId = '';

                if (avgDps >= 1.50) {
                    dpsCategory = 'Sangat Baik';
                    activeDpsId = 'dps_sangat_baik';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
                } else if (avgDps >= 1.20) {
                    dpsCategory = 'Baik (Optimal)';
                    activeDpsId = 'dps_baik';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                } else if (avgDps >= 1.00) {
                    dpsCategory = 'Cukup';
                    activeDpsId = 'dps_cukup';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                } else {
                    dpsCategory = 'Kurang';
                    activeDpsId = 'dps_kurang';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
                }
                badgeDps.innerText = dpsCategory;
                const activeDpsEl = document.getElementById(activeDpsId);
                if (activeDpsEl) {
                    activeDpsEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
                }

                // EVALUATE NORMA STROKE RATE
                const badgeSr = document.getElementById('badgeSr');
                let srCategory = '';
                let activeSrId = '';

                if (avgSr > 48) {
                    srCategory = 'Sprint / Tinggi';
                    activeSrId = 'sr_tinggi';
                    badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                } else if (avgSr >= 36) {
                    srCategory = 'Optimal / Menengah';
                    activeSrId = 'sr_sedang';
                    badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                } else {
                    srCategory = 'Tempo Rendah';
                    activeSrId = 'sr_rendah';
                    badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-200 text-slate-800';
                }
                badgeSr.innerText = srCategory;
                const activeSrEl = document.getElementById(activeSrId);
                if (activeSrEl) {
                    activeSrEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
                }

                // PACING EVALUATION
                const pacingEl = document.getElementById('kpiPacingType');
                if (validLapCount >= 2) {
                    const firstHalfTime = lapTimes[0];
                    const secondHalfTime = lapTimes[validLapCount - 1];
                    const diffPct = ((secondHalfTime - firstHalfTime) / firstHalfTime) * 100;

                    if (secondHalfTime < firstHalfTime) {
                        pacingEl.innerHTML = `<span class="text-emerald-700 font-bold">Negative Split (${Math.abs(diffPct).toFixed(1)}% Lebih Cepat di Akhir)</span>`;
                    } else if (diffPct <= 3) {
                        pacingEl.innerHTML = `<span class="text-blue-700 font-bold">Even Pace (Stabil Antar Lap, Drop ${diffPct.toFixed(1)}%)</span>`;
                    } else {
                        pacingEl.innerHTML = `<span class="text-amber-700 font-bold">Positive Split (Drop-off +${diffPct.toFixed(1)}% di Akhir)</span>`;
                    }
                } else {
                    pacingEl.innerText = '1 Putaran Lap';
                }

            } else {
                document.getElementById('valSwolf').innerText = '0.0';
                document.getElementById('valDps').innerText = '0.00 m';
                document.getElementById('valSr').innerText = '0.0 SPM';
                document.getElementById('kpiTotalTime').innerText = '00:00.00';
                document.getElementById('kpiAvgSpeed').innerText = '0.00 m/s';
                document.getElementById('kpiPacingType').innerText = '-';
                document.getElementById('badgeSwolf').innerText = '-';
                document.getElementById('badgeDps').innerText = '-';
                document.getElementById('badgeSr').innerText = '-';
            }

            updateChart(lapLabels, lapTimes, lapSRs, lapDPSs);
        }

        // 4. CHART.JS
        function initChart() {
            const ctx = document.getElementById('screenChart').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lap 1', 'Lap 2'],
                    datasets: [
                        {
                            label: 'Waktu Split (s)',
                            data: [0, 0],
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'yTime',
                            pointRadius: 4
                        },
                        {
                            label: 'SR (SPM)',
                            data: [0, 0],
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.1)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'yMetrics',
                            pointRadius: 4
                        },
                        {
                            label: 'DPS (x10 m)',
                            data: [0, 0],
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.1)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'yMetrics',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { color: '#f1f5f9' }, ticks: { font: { weight: 'bold' } } },
                        yTime: {
                            type: 'linear',
                            position: 'left',
                            grid: { color: '#f1f5f9' },
                            title: { display: true, text: 'Waktu (detik)', color: '#2563eb' }
                        },
                        yMetrics: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'SR & DPS (x10)', color: '#059669' }
                        }
                    }
                }
            });
        }

        function updateChart(labels, times, srs, dpss) {
            if (!chartInstance) return;
            chartInstance.data.labels = labels;
            chartInstance.data.datasets[0].data = times;
            chartInstance.data.datasets[1].data = srs;
            chartInstance.data.datasets[2].data = dpss.map(d => parseFloat((d * 10).toFixed(1)));
            chartInstance.update();
        }

        // 5. PRINT REPORT GENERATOR (PERSIS SEPERTI PACE CALCULATOR EXCEL TABLE)
        function printReport() {
            const name = document.getElementById('athleteName').value.trim() || '-';
            const age = document.getElementById('athleteAge').value.trim() || '-';
            const gender = document.getElementById('athleteGender').value;
            const date = document.getElementById('raceDate').value || '<?= date('d/m/Y') ?>';
            const totalTime = document.getElementById('kpiTotalTime').innerText;
            const avgSpeed = document.getElementById('kpiAvgSpeed').innerText;

            const swolfVal = document.getElementById('valSwolf').innerText;
            const swolfNorm = document.getElementById('badgeSwolf').innerText;

            const dpsVal = document.getElementById('valDps').innerText;
            const dpsNorm = document.getElementById('badgeDps').innerText;

            const srVal = document.getElementById('valSr').innerText;
            const srNorm = document.getElementById('badgeSr').innerText;

            const pacing = document.getElementById('kpiPacingType').innerText;

            const timeInputs = document.querySelectorAll('.lap-time-input');
            const strokeInputs = document.querySelectorAll('.lap-stroke-input');
            const rows = document.querySelectorAll('#lapsTableBody tr');

            let lapRowsHtml = '';
            timeInputs.forEach((timeInp, idx) => {
                const lapNum = idx + 1;
                const startM = (lapNum - 1) * poolLength;
                const endM = lapNum * poolLength;
                const sec = parseTimeToSeconds(timeInp.value);
                const strokes = strokeInputs[idx]?.value || '-';
                const row = rows[idx];

                const vel = row.querySelector('.lap-vel')?.innerText || '-';
                const sr = row.querySelector('.lap-sr')?.innerText || '-';
                const dps = row.querySelector('.lap-dps')?.innerText || '-';
                const swolf = row.querySelector('.lap-swolf')?.innerText || '-';

                lapRowsHtml += `
                    <tr>
                        <td style="text-align:center;">${lapNum}</td>
                        <td style="text-align:center;">${startM} - ${endM}m</td>
                        <td style="text-align:center; font-weight:bold;">${formatSecondsToTime(sec)}</td>
                        <td style="text-align:center;">${strokes}</td>
                        <td style="text-align:center;">${vel}</td>
                        <td style="text-align:center;">${sr}</td>
                        <td style="text-align:center;">${dps}</td>
                        <td style="text-align:center; font-weight:bold;">${swolf}</td>
                    </tr>
                `;
            });

            const printHtml = `
                <table class="excel-table">
                    <tr class="title-row">
                        <th colspan="8" style="text-align:center; font-weight:bold; font-size: 16px; padding-bottom: 12px;">
                            LAPORAN ANALISIS PERFORMA LOMBA RENANG
                        </th>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Nama Atlet : ${name}</td>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Gaya : ${strokeStyle}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Umur : ${age !== '-' ? age + ' Tahun' : '-'} (${gender})</td>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Jarak Lomba : ${totalDistance}m (Kolam ${poolLength}m)</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Tanggal : ${date}</td>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Waktu Akhir : <span class="text-red">${totalTime}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Kecepatan Rata-rata : ${avgSpeed}</td>
                        <td colspan="4" style="text-align:left; font-weight:bold;">Pacing : ${pacing}</td>
                    </tr>
                    
                    <tr class="thick-top">
                        <th colspan="8" style="text-align:center; font-weight:bold; padding: 4px; background-color: #f1f5f9;">
                            RINGKASAN NORMA EVALUASI
                        </th>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:center; font-weight:bold;">SWOLF Efisiensi : ${swolfVal} (${swolfNorm})</td>
                        <td colspan="3" style="text-align:center; font-weight:bold;">DPS (Jarak/Kayuhan) : ${dpsVal} (${dpsNorm})</td>
                        <td colspan="2" style="text-align:center; font-weight:bold;">Stroke Rate : ${srVal} (${srNorm})</td>
                    </tr>

                    <tr class="thick-top">
                        <th style="text-align:center; font-weight:bold;">Lap</th>
                        <th style="text-align:center; font-weight:bold;">Jarak</th>
                        <th style="text-align:center; font-weight:bold;">Waktu Split</th>
                        <th style="text-align:center; font-weight:bold;">Kayuhan</th>
                        <th style="text-align:center; font-weight:bold;">Laju (m/s)</th>
                        <th style="text-align:center; font-weight:bold;">SR (SPM)</th>
                        <th style="text-align:center; font-weight:bold;">DPS (m)</th>
                        <th style="text-align:center; font-weight:bold;">SWOLF</th>
                    </tr>
                    ${lapRowsHtml}
                    <tr class="thick-bottom">
                        <td colspan="8" style="text-align:center; font-size:10px; color:#475569; padding-top: 8px;">
                            Dicetak melalui SET SYSTEM Universal Biomechanics Platform &bull; www.setsystem.id
                        </td>
                    </tr>
                </table>
            `;

            document.getElementById('print-container').innerHTML = printHtml;

            setTimeout(() => {
                window.print();
            }, 150);
        }
    </script>
</body>
</html>
