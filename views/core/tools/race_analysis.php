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

        /* PRINT STYLES - PROPORTIONAL & SINGLE-PAGE A4 LAYOUT */
        @media print {
            @page {
                size: A4 portrait;
                margin: 4mm 6mm 4mm 6mm; 
            }
            body { 
                background: white !important; 
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            #print-container {
                display: block !important;
                padding: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #navbar, footer { display: none !important; }
            
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .max-w-4xl, .rounded-3xl { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; max-width: 100% !important; }

            .print-sheet {
                width: 100%;
                box-sizing: border-box;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            table, tr, td, th {
                page-break-inside: avoid !important;
            }
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
                                <input type="text" id="athleteName" placeholder="Masukkan nama atlet" oninput="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Umur (Tahun)</label>
                                <input type="number" id="athleteAge" min="5" max="80" value="13" placeholder="Contoh: 12" oninput="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
                                <select id="athleteGender" onchange="saveAthleteData(); calculateAll()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
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
                        <div class="flex items-center justify-between mb-3 pb-1 border-b border-slate-100">
                            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700">
                                4. Hasil Analisis & Norma Standar
                            </h3>
                            <span id="activeTierBadge" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200">
                                Norma: KU 3 (12 - 13 Thn)
                            </span>
                        </div>

                        <!-- TOTAL WAKTU & KECEPATAN STRIP -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-100 p-4 rounded-2xl mb-4 border border-slate-200">
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase block">Total Waktu</span>
                                <div class="font-teko text-3xl font-black text-blue-900 leading-tight" id="kpiTotalTime">00.00.00</div>
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
                                    <div class="space-y-1 text-[10px]" id="normaDpsBox">
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
                                    <div class="space-y-1 text-[10px]" id="normaSrBox">
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_tinggi"><span>Sprint / Tinggi</span> <span class="font-mono">&gt; 48 SPM</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_sedang"><span>Optimal / Menengah</span> <span class="font-mono">36 - 48 SPM</span></div>
                                        <div class="norma-item flex justify-between p-1 rounded" id="sr_rendah"><span>Tempo Rendah</span> <span class="font-mono">&lt; 36 SPM</span></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- GRAFIK VISUAL (SCREEN) -->
                        <div class="mt-6 border border-slate-200 rounded-2xl p-4 bg-slate-50">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <span class="text-xs font-bold text-slate-700 uppercase">Grafik Tren Waktu Split & Kinematika per Jarak Putaran</span>
                                <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500">
                                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span> Waktu Split (s)</span>
                                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-600 inline-block"></span> SR (SPM)</span>
                                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-600 inline-block"></span> DPS x10 (m)</span>
                                </div>
                            </div>
                            <div class="h-60 w-full">
                                <canvas id="screenChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- TOMBOL BUKA LAPORAN (HALAMAN BARU) -->
                    <div class="pt-4">
                        <button type="button" onclick="printReport()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xs tracking-widest uppercase py-4 rounded-xl shadow-lg shadow-blue-900/40 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>BUKA LAPORAN (PDF / PNG / SCREENSHOT)</span>
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

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function saveAthleteData() {
            try {
                const nameInp = document.getElementById('athleteName');
                const ageInp = document.getElementById('athleteAge');
                const genderInp = document.getElementById('athleteGender');
                if (nameInp) localStorage.setItem('race_analyzer_athlete_name', nameInp.value);
                if (ageInp) localStorage.setItem('race_analyzer_athlete_age', ageInp.value);
                if (genderInp) localStorage.setItem('race_analyzer_athlete_gender', genderInp.value);
            } catch (e) {}
        }

        function restoreAthleteData() {
            try {
                const savedName = localStorage.getItem('race_analyzer_athlete_name');
                const nameInp = document.getElementById('athleteName');
                if (savedName && nameInp && !nameInp.value) {
                    nameInp.value = savedName;
                }
                const savedAge = localStorage.getItem('race_analyzer_athlete_age');
                const ageInp = document.getElementById('athleteAge');
                if (savedAge && ageInp) {
                    ageInp.value = savedAge;
                }
                const savedGender = localStorage.getItem('race_analyzer_athlete_gender');
                const genderInp = document.getElementById('athleteGender');
                if (savedGender && genderInp) {
                    genderInp.value = savedGender;
                }
            } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', () => {
            restoreAthleteData();
            initChart();
            generateLapInputs();
            calculateAll();

            if (window.location.search.includes('preview_print=1')) {
                setTimeout(() => {
                    printReport();
                }, 400);
            }
        });

        // 0. NORMATIVE BENCHMARKS (BY AGE GROUP, GENDER, POOL LENGTH, & STROKE)
        function getNormativeBenchmarks(ageVal, gender, poolLen, stroke) {
            const age = parseInt(ageVal) || 15;
            const isFemale = (gender === 'Putri');
            const isBreaststroke = (stroke === 'Gaya Dada');
            
            let tierName = '';
            let swolfMaxSangatBaik, swolfMaxBaik, swolfMaxCukup;
            let dpsMinSangatBaik, dpsMinBaik, dpsMinCukup;
            let srMinTinggi, srMinSedang;

            if (age <= 11) {
                tierName = 'KU 4 (≤ 11 Thn)';
                if (poolLen === 50) {
                    swolfMaxSangatBaik = 78;
                    swolfMaxBaik = 88;
                    swolfMaxCukup = 98;
                } else {
                    swolfMaxSangatBaik = 40;
                    swolfMaxBaik = 46;
                    swolfMaxCukup = 54;
                }
                dpsMinSangatBaik = 1.25;
                dpsMinBaik = 1.05;
                dpsMinCukup = 0.90;
                
                srMinTinggi = isBreaststroke ? 42 : 52;
                srMinSedang = isBreaststroke ? 30 : 40;
            } else if (age <= 13) {
                tierName = 'KU 3 (12 - 13 Thn)';
                if (poolLen === 50) {
                    swolfMaxSangatBaik = 74;
                    swolfMaxBaik = 82;
                    swolfMaxCukup = 92;
                } else {
                    swolfMaxSangatBaik = 37;
                    swolfMaxBaik = 43;
                    swolfMaxCukup = 50;
                }
                dpsMinSangatBaik = 1.38;
                dpsMinBaik = 1.18;
                dpsMinCukup = 1.00;
                
                srMinTinggi = isBreaststroke ? 40 : 50;
                srMinSedang = isBreaststroke ? 28 : 38;
            } else if (age <= 15) {
                tierName = 'KU 2 (14 - 15 Thn)';
                if (poolLen === 50) {
                    swolfMaxSangatBaik = 68;
                    swolfMaxBaik = 76;
                    swolfMaxCukup = 86;
                } else {
                    swolfMaxSangatBaik = 34;
                    swolfMaxBaik = 40;
                    swolfMaxCukup = 47;
                }
                dpsMinSangatBaik = 1.50;
                dpsMinBaik = 1.28;
                dpsMinCukup = 1.10;
                
                srMinTinggi = isBreaststroke ? 38 : 48;
                srMinSedang = isBreaststroke ? 26 : 36;
            } else {
                tierName = 'KU 1 & Senior (≥ 16 Thn)';
                if (poolLen === 50) {
                    swolfMaxSangatBaik = 64;
                    swolfMaxBaik = 72;
                    swolfMaxCukup = 82;
                } else {
                    swolfMaxSangatBaik = 32;
                    swolfMaxBaik = 38;
                    swolfMaxCukup = 45;
                }
                dpsMinSangatBaik = 1.62;
                dpsMinBaik = 1.35;
                dpsMinCukup = 1.15;
                
                srMinTinggi = isBreaststroke ? 36 : 46;
                srMinSedang = isBreaststroke ? 24 : 34;
            }

            if (isFemale) {
                const swolfOffset = poolLen === 50 ? 2 : 1;
                swolfMaxSangatBaik += swolfOffset;
                swolfMaxBaik += swolfOffset;
                swolfMaxCukup += swolfOffset;

                dpsMinSangatBaik = parseFloat((dpsMinSangatBaik - 0.08).toFixed(2));
                dpsMinBaik = parseFloat((dpsMinBaik - 0.08).toFixed(2));
                dpsMinCukup = parseFloat((dpsMinCukup - 0.08).toFixed(2));
            }

            return {
                tierName,
                swolf: {
                    sangatBaik: swolfMaxSangatBaik,
                    baik: swolfMaxBaik,
                    cukup: swolfMaxCukup,
                    rujukan: `<${swolfMaxSangatBaik} Sgt Baik | ${swolfMaxSangatBaik}-${swolfMaxBaik} Baik | ${swolfMaxBaik+1}-${swolfMaxCukup} Cukup | >${swolfMaxCukup} Kurang`
                },
                dps: {
                    sangatBaik: dpsMinSangatBaik,
                    baik: dpsMinBaik,
                    cukup: dpsMinCukup,
                    rujukan: `≥${dpsMinSangatBaik.toFixed(2)}m Sgt Baik | ${dpsMinBaik.toFixed(2)}-${(dpsMinSangatBaik-0.01).toFixed(2)}m Baik | <${dpsMinCukup.toFixed(2)}m Kurang`
                },
                sr: {
                    tinggi: srMinTinggi,
                    sedang: srMinSedang,
                    rujukan: `>${srMinTinggi} Sprint | ${srMinSedang}-${srMinTinggi} Optimal | <${srMinSedang} Tempo Rendah`
                }
            };
        }

        // 1. CONFIGURATION
        function setPoolLength(len) {
            poolLength = parseInt(len);
            document.getElementById('btnPool50').className = poolLength === 50 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
                : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
            
            document.getElementById('btnPool25').className = poolLength === 25 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
                : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';

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
                                placeholder="00.00.00" 
                                maxlength="8"
                                oninput="formatTimeInput(this)" 
                                onblur="normalizeTimeInput(this)"
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

        function formatTimeInput(input) {
            let val = input.value.replace(/\D/g, ''); 
            if (val.length > 6) val = val.substring(0, 6); 

            let formatted = '';
            if (val.length > 0) formatted += val.substring(0, 2);
            if (val.length > 2) formatted += '.' + val.substring(2, 4);
            if (val.length > 4) formatted += '.' + val.substring(4, 6);

            input.value = formatted;
            calculateAll();
        }

        function normalizeTimeInput(input) {
            let val = input.value.trim();
            if (!val) {
                calculateAll();
                return;
            }
            let digits = val.replace(/\D/g, '');
            if (digits.length === 0) {
                input.value = '';
                calculateAll();
                return;
            }

            let m = 0, s = 0, cs = 0;
            if (digits.length <= 2) {
                s = parseInt(digits, 10) || 0;
            } else if (digits.length <= 4) {
                s = parseInt(digits.substring(0, 2), 10) || 0;
                cs = parseInt(digits.substring(2, 4), 10) || 0;
            } else {
                let padded = digits.padStart(6, '0');
                m = parseInt(padded.substring(0, 2), 10) || 0;
                s = parseInt(padded.substring(2, 4), 10) || 0;
                cs = parseInt(padded.substring(4, 6), 10) || 0;
            }

            if (cs >= 100) { cs = 0; s++; }
            if (s >= 60 && digits.length <= 4) {
                m += Math.floor(s / 60);
                s = s % 60;
            }

            const mStr = String(m).padStart(2, '0');
            const sStr = String(s).padStart(2, '0');
            const csStr = String(cs).padStart(2, '0');
            input.value = `${mStr}.${sStr}.${csStr}`;
            calculateAll();
        }

        function handleKeyNav(e, lapIdx, type) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (type === 'time') {
                    normalizeTimeInput(e.target);
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
            val = val.toString().trim().replace(',', '.');
            if (val.includes(':')) {
                const parts = val.split(':');
                const min = parseFloat(parts[0]) || 0;
                const sec = parseFloat(parts[1]) || 0;
                return (min * 60) + sec;
            }
            if (val.includes('.')) {
                const parts = val.split('.');
                if (parts.length === 3) {
                    const min = parseFloat(parts[0]) || 0;
                    const sec = parseFloat(parts[1]) || 0;
                    const cs = parseFloat(parts[2]) || 0;
                    return (min * 60) + sec + (cs / 100);
                } else if (parts.length === 2) {
                    const sec = parseFloat(parts[0]) || 0;
                    const cs = parseFloat(parts[1]) || 0;
                    return sec + (cs / 100);
                }
            }
            return parseFloat(val) || 0;
        }

        function formatSecondsToTime(totalSec) {
            if (!totalSec || isNaN(totalSec) || totalSec <= 0) return '00.00.00';
            let mins = Math.floor(totalSec / 60);
            let remainingSec = totalSec % 60;
            let secs = Math.floor(remainingSec);
            let cs = Math.round((remainingSec - secs) * 100);

            if (cs >= 100) { cs = 0; secs++; }
            if (secs >= 60) { secs = 0; mins++; }

            const mStr = String(mins).padStart(2, '0');
            const sStr = String(secs).padStart(2, '0');
            const csStr = String(cs).padStart(2, '0');
            return `${mStr}.${sStr}.${csStr}`;
        }

        // 3. CALCULATION & NORMA HIGHLIGHT
        function calculateAll() {
            const timeInputs = document.querySelectorAll('.lap-time-input');
            const strokeInputs = document.querySelectorAll('.lap-stroke-input');
            const rows = document.querySelectorAll('#lapsTableBody tr');

            const ageVal = document.getElementById('athleteAge').value;
            const genderVal = document.getElementById('athleteGender').value;
            const norms = getNormativeBenchmarks(ageVal, genderVal, poolLength, strokeStyle);

            const activeTierBadge = document.getElementById('activeTierBadge');
            if (activeTierBadge) {
                activeTierBadge.innerText = `Norma: ${norms.tierName}`;
            }

            // Update Dynamic Scale Rows in UI
            const swolfBox = document.getElementById('normaSwolfBox');
            if (swolfBox) {
                swolfBox.innerHTML = `
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; ${norms.swolf.sangatBaik}</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">${norms.swolf.sangatBaik} - ${norms.swolf.baik}</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">${norms.swolf.baik + 1} - ${norms.swolf.cukup}</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; ${norms.swolf.cukup}</span></div>
                `;
            }

            const dpsBox = document.getElementById('normaDpsBox');
            if (dpsBox) {
                dpsBox.innerHTML = `
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&ge; ${norms.dps.sangatBaik.toFixed(2)} m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_baik"><span>Baik (Optimal)</span> <span class="font-mono">${norms.dps.baik.toFixed(2)} - ${(norms.dps.sangatBaik - 0.01).toFixed(2)} m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_cukup"><span>Cukup</span> <span class="font-mono">${norms.dps.cukup.toFixed(2)} - ${(norms.dps.baik - 0.01).toFixed(2)} m</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="dps_kurang"><span>Kurang</span> <span class="font-mono">&lt; ${norms.dps.cukup.toFixed(2)} m</span></div>
                `;
            }

            const srBox = document.getElementById('normaSrBox');
            if (srBox) {
                srBox.innerHTML = `
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_tinggi"><span>Sprint / Tinggi</span> <span class="font-mono">&gt; ${norms.sr.tinggi} SPM</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_sedang"><span>Optimal / Menengah</span> <span class="font-mono">${norms.sr.sedang} - ${norms.sr.tinggi} SPM</span></div>
                    <div class="norma-item flex justify-between p-1 rounded" id="sr_rendah"><span>Tempo Rendah</span> <span class="font-mono">&lt; ${norms.sr.sedang} SPM</span></div>
                `;
            }

            let totalTimeSec = 0;
            let validLapCount = 0;

            let lapTimes = [0];
            let lapSRs = [0];
            let lapDPSs = [0];
            let lapSwolfs = [0];
            let lapLabels = ['0m'];

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

                const endM = lapNum * poolLength;
                lapLabels.push(`${endM}m`);

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

                if (avgSwolf < norms.swolf.sangatBaik) {
                    swolfCategory = 'Sangat Baik';
                    activeSwolfId = 'swolf_sangat_baik';
                    badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
                } else if (avgSwolf <= norms.swolf.baik) {
                    swolfCategory = 'Baik (Optimal)';
                    activeSwolfId = 'swolf_baik';
                    badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                } else if (avgSwolf <= norms.swolf.cukup) {
                    swolfCategory = 'Cukup';
                    activeSwolfId = 'swolf_cukup';
                    badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                } else {
                    swolfCategory = 'Kurang';
                    activeSwolfId = 'swolf_kurang';
                    badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
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

                if (avgDps >= norms.dps.sangatBaik) {
                    dpsCategory = 'Sangat Baik';
                    activeDpsId = 'dps_sangat_baik';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
                } else if (avgDps >= norms.dps.baik) {
                    dpsCategory = 'Baik (Optimal)';
                    activeDpsId = 'dps_baik';
                    badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
                } else if (avgDps >= norms.dps.cukup) {
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

                if (avgSr > norms.sr.tinggi) {
                    srCategory = 'Sprint / Tinggi';
                    activeSrId = 'sr_tinggi';
                    badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
                } else if (avgSr >= norms.sr.sedang) {
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
                    const firstHalfTime = lapTimes[1];
                    const secondHalfTime = lapTimes[validLapCount];
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
                document.getElementById('kpiTotalTime').innerText = '00.00.00';
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
            const defaultLabels = poolLength === 50 ? ['0m', '50m', '100m'] : ['0m', '25m', '50m', '75m', '100m'];
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: defaultLabels,
                    datasets: [
                        {
                            label: 'Waktu Split (s)',
                            data: [0, 0, 0],
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'yTime',
                            pointRadius: 4
                        },
                        {
                            label: 'SR (SPM)',
                            data: [0, 0, 0],
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.1)',
                            borderWidth: 2,
                            tension: 0.2,
                            yAxisID: 'yMetrics',
                            pointRadius: 4
                        },
                        {
                            label: 'DPS (x10 m)',
                            data: [0, 0, 0],
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
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    const lbl = items[0].label;
                                    return lbl === '0m' ? 'Titik Start (0m)' : `Posisi Jarak: ${lbl}`;
                                },
                                label: (item) => {
                                    const val = item.raw || 0;
                                    if (item.dataset.label.includes('DPS')) {
                                        return `DPS: ${(val / 10).toFixed(2)} m`;
                                    } else if (item.dataset.label.includes('SR')) {
                                        return `Stroke Rate: ${val} SPM`;
                                    } else {
                                        return `Waktu Split: ${val} dtk`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { color: '#f1f5f9' }, ticks: { font: { weight: 'bold' } } },
                        yTime: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            title: { display: true, text: 'Waktu (detik)', color: '#2563eb' }
                        },
                        yMetrics: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
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
            chartInstance.data.datasets[2].data = dpss.map(d => parseFloat(((d || 0) * 10).toFixed(1)));
            chartInstance.update();
        }

        // 5. PRINT & STANDALONE REPORT GENERATOR (NEW TAB / PDF / PNG / SCREENSHOT)
        function printReport() {
            let reportWin = null;
            if (!window.location.search.includes('preview_print=1')) {
                try {
                    reportWin = window.open('', '_blank');
                } catch (e) {
                    reportWin = null;
                }
            }

            let rawName = document.getElementById('athleteName').value.trim();
            if (!rawName) {
                try {
                    rawName = (localStorage.getItem('race_analyzer_athlete_name') || '').trim();
                } catch (e) {}
            }
            const name = rawName || '-';
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

            const lapCount = timeInputs.length;

            // Calibrated dynamic vertical scaling based on lap count to fit 1 page A4 perfectly (thin margins)
            let chartHeight = 290;
            let tablePad = '7px 6px';
            let cardPad = '9px 12px';
            let sectionGap = '8px';

            if (lapCount > 8) {
                chartHeight = 180;
                tablePad = '3.5px 4px';
                cardPad = '5px 6px';
                sectionGap = '4px';
            } else if (lapCount > 4) {
                chartHeight = 220;
                tablePad = '4.5px 5px';
                cardPad = '6px 8px';
                sectionGap = '6px';
            } else if (lapCount > 2) {
                chartHeight = 260;
                tablePad = '6px 6px';
                cardPad = '8px 10px';
                sectionGap = '7px';
            }

            let totalStrokesSum = 0;
            let lapRowsHtml = '';

            timeInputs.forEach((timeInp, idx) => {
                const lapNum = idx + 1;
                const startM = (lapNum - 1) * poolLength;
                const endM = lapNum * poolLength;
                const sec = parseTimeToSeconds(timeInp.value);
                const strokes = parseFloat(strokeInputs[idx]?.value) || 0;
                totalStrokesSum += strokes;

                const row = rows[idx];
                const vel = row?.querySelector('.lap-vel')?.innerText || '-';
                const sr = row?.querySelector('.lap-sr')?.innerText || '-';
                const dps = row?.querySelector('.lap-dps')?.innerText || '-';
                const swolf = row?.querySelector('.lap-swolf')?.innerText || '-';

                const bgRow = idx % 2 === 1 ? 'background-color:#f8fafc;' : 'background-color:#ffffff;';

                lapRowsHtml += `
                    <tr style="${bgRow} border-bottom: 1px solid #cbd5e1;">
                        <td style="padding: ${tablePad}; text-align:center; font-weight:bold; border-right: 1px solid #cbd5e1;">${lapNum}</td>
                        <td style="padding: ${tablePad}; text-align:center; color:#475569; border-right: 1px solid #cbd5e1;">${startM} - ${endM}m</td>
                        <td style="padding: ${tablePad}; text-align:center; font-weight:800; color:#0f172a; border-right: 1px solid #cbd5e1;">${formatSecondsToTime(sec)}</td>
                        <td style="padding: ${tablePad}; text-align:center; font-weight:600; border-right: 1px solid #cbd5e1;">${strokes > 0 ? strokes : '-'}</td>
                        <td style="padding: ${tablePad}; text-align:center; font-family:monospace; border-right: 1px solid #cbd5e1;">${vel}</td>
                        <td style="padding: ${tablePad}; text-align:center; font-weight:700; color:#b45309; border-right: 1px solid #cbd5e1;">${sr}</td>
                        <td style="padding: ${tablePad}; text-align:center; font-weight:700; color:#047857; border-right: 1px solid #cbd5e1;">${dps}</td>
                        <td style="padding: ${tablePad}; text-align:center; font-weight:800; color:#1d4ed8; background-color:#eff6ff;">${swolf}</td>
                    </tr>
                `;
            });

            const norms = getNormativeBenchmarks(age, gender, poolLength, strokeStyle);

            // Benchmark texts (Disesuaikan dengan Kelompok Umur Atlet)
            const swolfRujukan = `Norma: ${norms.swolf.rujukan}`;
            const dpsRujukan = `Norma: ${norms.dps.rujukan}`;
            const srRujukan = `Norma: ${norms.sr.rujukan}`;

            // Case-insensitive badge color helper
            const getBadgeColor = (text) => {
                if (!text || text === '-') return 'background:#f1f5f9; color:#475569;';
                const lower = text.toLowerCase();
                if (lower.includes('sangat baik')) return 'background:#059669; color:#ffffff;';
                if (lower.includes('baik') || lower.includes('optimal')) return 'background:#2563eb; color:#ffffff;';
                if (lower.includes('cukup') || lower.includes('sprint') || lower.includes('tinggi') || lower.includes('menengah')) return 'background:#d97706; color:#ffffff;';
                return 'background:#dc2626; color:#ffffff;';
            };

            // Format date DD/MM/YYYY
            let formattedDate = date;
            if (date && date.includes('-')) {
                const parts = date.split('-');
                if (parts.length === 3) {
                    formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
            }

            // Chart Snapshot Image
            let chartImgHtml = '';
            if (chartInstance) {
                const chartImgData = chartInstance.toBase64Image();
                chartImgHtml = `
                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; background: #ffffff; margin-top: ${sectionGap}; page-break-inside: avoid;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                            <span style="font-size: 10px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Grafik Tren Waktu Split & Kinematika per Jarak Putaran</span>
                            <span style="font-size: 9px; font-weight: 600; color: #64748b;">
                                <span style="color: #2563eb; font-weight: 900;">■</span> Waktu Split (s) &nbsp;&bull;&nbsp; 
                                <span style="color: #d97706; font-weight: 900;">■</span> Stroke Rate (SPM) &nbsp;&bull;&nbsp; 
                                <span style="color: #059669; font-weight: 900;">■</span> DPS x10 (m)
                            </span>
                        </div>
                        <div style="height: ${chartHeight}px; display:flex; align-items:center; justify-content:center; background: #ffffff; border-radius: 6px;">
                            <img src="${chartImgData}" style="max-height: ${chartHeight}px; width: 100%; object-fit: contain; margin: 0 auto; display:block;" alt="Grafik Pacing">
                        </div>
                    </div>
                `;
            }

            const printHtml = `
                <div class="print-sheet" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #0f172a;">
                    
                    <!-- 1. DATA ATLET & NOMOR LOMBA (2 BARIS TERSTRUKTUR - BEBAS OVERFLOW) -->
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; margin-bottom: 6px; page-break-inside: avoid; box-sizing: border-box;">
                        <!-- Baris 1: Nama Atlet & Nomor Lomba -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px dashed #cbd5e1;">
                            <div style="display: flex; align-items: baseline; gap: 6px; overflow: hidden;">
                                <span style="color: #64748b; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">NAMA ATLET :</span>
                                <span style="font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">${escapeHtml(name)}</span>
                            </div>
                            <div style="display: flex; align-items: baseline; gap: 6px; text-align: right; flex-shrink: 0;">
                                <span style="color: #64748b; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">NOMOR LOMBA :</span>
                                <span style="font-size: 13px; font-weight: 900; color: #1e3a8a;">${totalDistance}m ${escapeHtml(strokeStyle)}</span>
                                <span style="font-size: 10px; color: #64748b; font-weight: 700;">(Kolam ${poolLength}m)</span>
                            </div>
                        </div>
                        <!-- Baris 2: Umur / Gender / KU & Tanggal -->
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10px;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                <span style="color: #64748b; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">UMUR / GENDER :</span>
                                <span style="font-weight: 700; color: #1e293b; white-space: nowrap;">${age !== '-' ? age + ' Tahun' : '-'} (${escapeHtml(gender)})</span>
                                <span style="color: #cbd5e1;">&bull;</span>
                                <span style="display: inline-block; color: #047857; font-weight: 800; background: #ecfdf5; padding: 2px 8px; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 9.5px; white-space: nowrap;">${escapeHtml(norms.tierName)}</span>
                            </div>
                            <div style="text-align: right; color: #64748b; font-size: 10px; flex-shrink: 0;">
                                <span style="color: #94a3b8; font-size: 9px; font-weight: 800; text-transform: uppercase;">TANGGAL :</span>
                                <span style="font-weight: 700; color: #334155; margin-left: 4px;">${formattedDate}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. TOTAL WAKTU, KECEPATAN & PACING (PERSIS HALAMAN AWAL) -->
                    <div style="background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 16px; margin-bottom: 8px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; page-break-inside: avoid;">
                        <div>
                            <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">TOTAL WAKTU</span>
                            <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin-top: 2px;">${totalTime}</div>
                        </div>
                        <div>
                            <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">KECEPATAN RATA-RATA</span>
                            <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1; margin-top: 2px;">${avgSpeed}</div>
                        </div>
                        <div>
                            <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">DISTRIBUSI PACING</span>
                            <div style="font-size: 11px; font-weight: 800; color: #1e293b; margin-top: 6px;">${pacing}</div>
                        </div>
                    </div>

                    <!-- 3. TIGA KARTU NORMA EVALUASI (PERSIS HALAMAN AWAL) -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: ${sectionGap}; page-break-inside: avoid;">
                        
                        <!-- SWOLF CARD -->
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">SWOLF Efisiensi</span>
                                    <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(swolfNorm)}">${swolfNorm}</span>
                                </div>
                                <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${swolfVal}</div>
                            </div>
                            <div>
                                <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                                <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('sangat baik') ? 'background:#059669; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Sangat Baik</span> <span style="font-family: monospace;">&lt; ${norms.swolf.sangatBaik}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('baik') && !swolfNorm.toLowerCase().includes('sangat') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Baik (Optimal)</span> <span style="font-family: monospace;">${norms.swolf.sangatBaik} - ${norms.swolf.baik}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('cukup') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Cukup</span> <span style="font-family: monospace;">${norms.swolf.baik + 1} - ${norms.swolf.cukup}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('kurang') ? 'background:#dc2626; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Kurang</span> <span style="font-family: monospace;">&gt; ${norms.swolf.cukup}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DPS CARD -->
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">DPS (Jarak/Kayuhan)</span>
                                    <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(dpsNorm)}">${dpsNorm}</span>
                                </div>
                                <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${dpsVal}</div>
                            </div>
                            <div>
                                <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                                <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('sangat baik') ? 'background:#059669; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Sangat Baik</span> <span style="font-family: monospace;">&ge; ${norms.dps.sangatBaik.toFixed(2)} m</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('baik') && !dpsNorm.toLowerCase().includes('sangat') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Baik (Optimal)</span> <span style="font-family: monospace;">${norms.dps.baik.toFixed(2)} - ${(norms.dps.sangatBaik - 0.01).toFixed(2)} m</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('cukup') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Cukup</span> <span style="font-family: monospace;">${norms.dps.cukup.toFixed(2)} - ${(norms.dps.baik - 0.01).toFixed(2)} m</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('kurang') ? 'background:#dc2626; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Kurang</span> <span style="font-family: monospace;">&lt; ${norms.dps.cukup.toFixed(2)} m</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STROKE RATE CARD -->
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                    <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Irama Kayuhan (SR)</span>
                                    <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(srNorm)}">${srNorm}</span>
                                </div>
                                <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${srVal}</div>
                            </div>
                            <div>
                                <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                                <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('sprint') || srNorm.toLowerCase().includes('tinggi') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Sprint / Tinggi</span> <span style="font-family: monospace;">&gt; ${norms.sr.tinggi} SPM</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('optimal') || srNorm.toLowerCase().includes('menengah') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Optimal / Menengah</span> <span style="font-family: monospace;">${norms.sr.sedang} - ${norms.sr.tinggi} SPM</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('tempo') || srNorm.toLowerCase().includes('rendah') ? 'background:#64748b; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                        <span>Tempo Rendah</span> <span style="font-family: monospace;">&lt; ${norms.sr.sedang} SPM</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- 3. TABEL PUTARAN LAP -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; margin-top: ${sectionGap}; page-break-inside: avoid;">
                        <thead>
                            <tr style="background-color: #0f172a; color: #ffffff;">
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 8%; border-right: 1px solid #334155;">Lap</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 14%; border-right: 1px solid #334155;">Jarak</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 16%; border-right: 1px solid #334155;">Waktu Split</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">Kayuhan</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">Laju (m/s)</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 13%; border-right: 1px solid #334155;">SR (SPM)</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">DPS (m)</th>
                                <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 13%;">SWOLF</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lapRowsHtml}
                            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                                <td colspan="2" style="padding: ${tablePad}; text-align: center; font-weight: 800; font-size: 9.5px; text-transform: uppercase; border-right: 1px solid #cbd5e1;">TOTAL / RATA-RATA</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 900; color: #ea580c; font-size: 11px; border-right: 1px solid #cbd5e1;">${totalTime}</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 800; border-right: 1px solid #cbd5e1;">${totalStrokesSum > 0 ? totalStrokesSum : '-'}</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 800; border-right: 1px solid #cbd5e1;">${avgSpeed}</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 800; color: #b45309; border-right: 1px solid #cbd5e1;">${srVal}</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 800; color: #047857; border-right: 1px solid #cbd5e1;">${dpsVal}</td>
                                <td style="padding: ${tablePad}; text-align: center; font-weight: 900; color: #1d4ed8; font-size: 11px; background-color: #e0e7ff;">${swolfVal}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- 4. EMBEDDED HIGH-RES GRAPH -->
                    ${chartImgHtml}

                    <!-- 5. FOOTER RESMI -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #cbd5e1; padding-top: 5px; margin-top: ${sectionGap}; font-size: 8px; color: #64748b; page-break-inside: avoid;">
                        <div>Universal SET System &bull; Swimming Biomechanics & Race Performance Analysis &bull; www.setsystem.id</div>
                        <div>Dicetak pada: ${formattedDate}</div>
                    </div>

                </div>
            `;

            // Always update print-container in background as DOM reference
            document.getElementById('print-container').innerHTML = printHtml;

            // If preview_print=1 is in URL, render inline for automated testing/debugging
            if (window.location.search.includes('preview_print=1')) {
                document.querySelectorAll('.print-hidden').forEach(el => el.style.display = 'none');
                if (document.getElementById('navbar')) document.getElementById('navbar').style.display = 'none';
                if (document.querySelector('footer')) document.querySelector('footer').style.display = 'none';
                const pc = document.getElementById('print-container');
                pc.classList.remove('hidden');
                pc.style.display = 'block';
                return;
            }

            // STANDALONE NEW TAB / WINDOW GENERATION
            const rawAthleteName = (name && name !== '-') ? name.trim() : 'Atlet';
            const safeAthleteName = rawAthleteName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '').replace(/^_+|_+$/g, '');
            const safeDist = `${totalDistance}m`;
            const safeStroke = strokeStyle.trim().replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '').replace(/^_+|_+$/g, '');
            let safeDate = '<?= date('d-m-Y') ?>';
            if (date && date.includes('-')) {
                const dp = date.split('-');
                if (dp.length === 3) safeDate = `${dp[2]}-${dp[1]}-${dp[0]}`;
            } else if (date) {
                safeDate = date.replace(/[\/\\]/g, '-').replace(/[^a-zA-Z0-9_-]/g, '');
            }
            const fileBaseName = `${safeAthleteName}_${safeDist}_${safeStroke}_${safeDate}`;

            const fullPageHtml = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${fileBaseName}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"><\/script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0b1120;
            color: #0f172a;
            min-height: 100vh;
            padding: 16px 16px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .toolbar {
            position: sticky;
            top: 12px;
            z-index: 50;
            width: 100%;
            max-width: 820px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #f8fafc;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .toolbar-badge {
            background: #2563eb;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }
        .toolbar-title {
            font-size: 12px;
            font-weight: 700;
            color: #cbd5e1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-png {
            background: #059669;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        .btn-png:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        .btn-pdf {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-pdf:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-share {
            background: #7c3aed;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        .btn-share:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }
        .btn-close {
            background: #334155;
            color: #cbd5e1;
        }
        .btn-close:hover {
            background: #475569;
            color: #ffffff;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        #report-card {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
            border: 1px solid #cbd5e1;
        }
        @media (max-width: 640px) {
            body { padding: 8px 8px 24px; }
            .toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 10px; }
            .toolbar-actions { justify-content: space-between; overflow-x: auto; }
            .btn { padding: 6px 10px; font-size: 10px; }
            #report-card { padding: 10px 12px; border-radius: 8px; }
        }
        @media print {
            .no-print { display: none !important; }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            #report-card {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 4mm 6mm 4mm 6mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div class="toolbar-info">
            <span class="toolbar-badge">Race Analyzer</span>
            <span class="toolbar-title">${escapeHtml(name)} &bull; ${totalDistance}m ${escapeHtml(strokeStyle)}</span>
        </div>
        <div class="toolbar-actions">
            <button id="btn-png" class="btn btn-png" onclick="downloadPNG()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Simpan PNG</span>
            </button>
            <button class="btn btn-pdf" onclick="printPDF()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF</span>
            </button>
            <button id="btn-share" class="btn btn-share" onclick="shareReport()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Bagikan</span>
            </button>
            <button class="btn btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <div id="report-card">
        ${printHtml}
    </div>

    <script>
        const fileBaseName = "${fileBaseName}";

        function printPDF() {
            document.title = fileBaseName;
            window.print();
        }

        function downloadPNG() {
            const btn = document.getElementById('btn-png');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span>⏳ Memproses...</span>';
            btn.disabled = true;

            const element = document.getElementById('report-card');
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                windowWidth: element.scrollWidth
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = fileBaseName + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('Gagal mengekspor PNG. Silakan gunakan tangkapan layar (screenshot) browser.');
                btn.innerHTML = origHtml;
                btn.disabled = false;
            });
        }

        async function shareReport() {
            const element = document.getElementById('report-card');
            if (navigator.share) {
                const btn = document.getElementById('btn-share');
                const origHtml = btn.innerHTML;
                btn.innerHTML = '<span>⏳ Menyiapkan...</span>';
                btn.disabled = true;
                try {
                    const canvas = await html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollX: 0, scrollY: 0 });
                    canvas.toBlob(async (blob) => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                        if (!blob) {
                            downloadPNG();
                            return;
                        }
                        const file = new File([blob], fileBaseName + '.png', { type: 'image/png' });
                        if (navigator.canShare && navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: fileBaseName,
                                text: 'Laporan Analisis Performa Lomba Renang ' + fileBaseName,
                                files: [file]
                            });
                        } else {
                            await navigator.share({
                                title: fileBaseName,
                                text: 'Laporan Analisis Performa Lomba Renang ' + fileBaseName
                            });
                        }
                    }, 'image/png');
                } catch (err) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                    downloadPNG();
                }
            } else {
                downloadPNG();
            }
        }
    <\/script>
</body>
</html>`;

            if (reportWin) {
                reportWin.document.open();
                reportWin.document.write(fullPageHtml);
                reportWin.document.close();
                return;
            }

            // Fallback for popup blocked
            const originalTitle = document.title;
            document.title = fileBaseName;
            setTimeout(() => {
                window.print();
                setTimeout(() => {
                    document.title = originalTitle;
                }, 1000);
            }, 150);
        }
    </script>
</body>
</html>
