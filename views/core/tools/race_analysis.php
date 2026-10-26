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
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* NAVBAR STYLE */
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
            background-color: #f25822; 
            transition: width 0.3s ease; 
        }
        .nav-link:hover::after { width: 100%; }
        .nav-link:hover { color: #f25822; }

        /* PRINT STYLES - STRICT 1 PAGE A4 */
        @media print {
            @page {
                size: A4 portrait;
                margin: 7mm 8mm 7mm 8mm;
            }
            body { 
                background: white !important; 
                color: #000 !important;
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important; 
                padding: 0 !important;
                font-size: 11px;
            }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #print-report-container { 
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .a4-page {
                width: 100%;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            table th, table td {
                border: 1px solid #334155 !important;
                padding: 3px 5px !important;
            }
            .badge-print {
                border: 1px solid #cbd5e1 !important;
                padding: 2px 4px !important;
                border-radius: 4px !important;
            }
        }

        @media screen {
            .print-visible { display: none; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR -->
    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 px-6 md:px-10 print-hidden">
        <div class="max-w-7xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/" class="flex items-center gap-3">
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-10 md:h-12 w-auto object-contain" alt="SET System">
                <span class="text-white font-teko text-2xl tracking-widest hidden sm:inline-block">SET SYSTEM</span>
            </a>
            
            <div class="flex items-center gap-6">
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?= getenv('APP_URL') ?>/tools/pace-calculator" class="nav-link text-xs">Kalkulator Pace</a>
                    <a href="<?= getenv('APP_URL') ?>/swim" class="nav-link text-xs">Sistem Renang</a>
                    <a href="<?= getenv('APP_URL') ?>/roll" class="nav-link text-xs">Sepatu Roda</a>
                </div>
                <a href="<?= getenv('APP_URL') ?>/" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white px-3 py-2 rounded font-bold uppercase tracking-wider transition">
                    &larr; Portal Utama
                </a>
            </div>
        </div>
    </nav>

    <!-- MAIN INTERACTIVE APP (SCREEN ONLY) -->
    <main class="flex-grow pt-28 pb-20 px-4 md:px-8 max-w-7xl mx-auto w-full print-hidden">
        
        <!-- HEADER TITLE -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#f25822]/10 border border-[#f25822]/30 rounded-full text-[#f25822] text-xs font-bold uppercase tracking-widest mb-3">
                🏊 Tools Biomekanika & Analisis Lomba
            </div>
            <h1 class="font-teko text-4xl md:text-6xl font-black uppercase tracking-wide text-white mb-2">
                Race Performance Analyzer
            </h1>
            <p class="text-slate-400 text-sm md:text-base max-w-2xl mx-auto">
                Analisis waktu putaran (*Split Times*), frekuensi kayuhan (*Stroke Rate*), jarak per kayuhan (*DPS*), dan efisiensi *SWOLF*. Dirancang khusus untuk evaluasi cepat pelatih dan pembelajaran atlet KU.
            </p>
        </div>

        <!-- ACTION TOOLBAR -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/80 rounded-2xl p-4 mb-8 flex flex-wrap items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mode Kerja:</span>
                <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-lg text-xs font-bold">
                    ⚡ Auto-Calculate Aktif
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="loadSampleData()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow">
                    <span>💡</span> Muat Contoh Data (Demo)
                </button>
                <button type="button" onclick="resetForm()" class="px-4 py-2 bg-slate-700/60 hover:bg-rose-900/40 text-slate-300 hover:text-rose-300 text-xs font-bold rounded-xl transition">
                    Reset
                </button>
                <button type="button" onclick="printReport()" class="px-5 py-2.5 bg-gradient-to-r from-[#f25822] to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white text-xs font-black uppercase tracking-widest rounded-xl transition shadow-lg shadow-orange-500/20 flex items-center gap-2 transform hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg>
                    Cetak Laporan A4
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT COLUMN: INPUT FOR COACH (5 COLS) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- CARD: IDENTITAS ATLET & EVENT -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <h3 class="font-teko text-xl font-bold tracking-wider text-orange-400 uppercase mb-4 flex items-center gap-2">
                        <span>👤</span> 1. Data Atlet & Pertandingan
                    </h3>
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Nama Atlet</label>
                            <input type="text" id="athleteName" placeholder="Contoh: Budi Santoso" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Klub / Sekolah</label>
                                <input type="text" id="clubName" placeholder="Nama Perkumpulan" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Kelompok Umur (KU)</label>
                                <select id="athleteKU" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                                    <option value="KU 4 (11 Thn Kebawah)">KU 4 (≤ 11 Tahun)</option>
                                    <option value="KU 3 (12 - 13 Tahun)">KU 3 (12 - 13 Tahun)</option>
                                    <option value="KU 2 (14 - 15 Tahun)">KU 2 (14 - 15 Tahun)</option>
                                    <option value="KU 1 (16 - 18 Tahun)">KU 1 (16 - 18 Tahun)</option>
                                    <option value="Senior (> 18 Tahun)">Senior (> 18 Tahun)</option>
                                    <option value="Master">Master</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Gender</label>
                                <select id="athleteGender" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                                    <option value="Putra">Putra</option>
                                    <option value="Putri">Putri</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Tanggal</label>
                                <input type="date" id="raceDate" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Nama Event / Kejuaraan / Simulasi</label>
                            <input type="text" id="eventName" placeholder="Contoh: Kejurprov Renang 2026 / Time Trial" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                    </div>
                </div>

                <!-- CARD: PRESET KONFIGURASI LOMBA -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <h3 class="font-teko text-xl font-bold tracking-wider text-orange-400 uppercase mb-4 flex items-center gap-2">
                        <span>⚙️</span> 2. Konfigurasi Nomor Lomba
                    </h3>

                    <!-- Ukuran Kolam -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-300 mb-2">Panjang Kolam:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="setPoolLength(50)" id="btnPool50" class="py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-orange-600 border-orange-500 text-white shadow">
                                <span>🏊</span> 50 Meter (LCM)
                            </button>
                            <button type="button" onclick="setPoolLength(25)" id="btnPool25" class="py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-slate-900 border-slate-700 text-slate-400 hover:text-white">
                                <span>⚡</span> 25 Meter (SCM)
                            </button>
                        </div>
                    </div>

                    <!-- Gaya Renang -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-300 mb-2">Gaya Renang:</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="setStrokeStyle('Gaya Bebas')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-orange-600 border-orange-500 text-white">Bebas</button>
                            <button type="button" onclick="setStrokeStyle('Gaya Dada')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">Dada</button>
                            <button type="button" onclick="setStrokeStyle('Gaya Punggung')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">Punggung</button>
                            <button type="button" onclick="setStrokeStyle('Gaya Kupu-Kupu')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">Kupu-kupu</button>
                            <button type="button" onclick="setStrokeStyle('Ganti Perorangan (IM)')" class="btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white col-span-2">Ganti (IM)</button>
                        </div>
                    </div>

                    <!-- Jarak Lomba -->
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">Jarak Lomba:</label>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            <button type="button" onclick="setDistance(50)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">50m</button>
                            <button type="button" onclick="setDistance(100)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-orange-600 border-orange-500 text-white">100m</button>
                            <button type="button" onclick="setDistance(200)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">200m</button>
                            <button type="button" onclick="setDistance(400)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">400m</button>
                            <button type="button" onclick="setDistance(800)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">800m</button>
                            <button type="button" onclick="setDistance(1500)" class="btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white">1500m</button>
                        </div>
                    </div>
                </div>

                <!-- CARD: CATATAN & SARAN PELATIH -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-teko text-xl font-bold tracking-wider text-orange-400 uppercase flex items-center gap-2">
                            <span>📝</span> 4. Catatan Evaluasi Pelatih
                        </h3>
                        <button type="button" onclick="generateCoachAdvice()" class="text-[11px] font-bold text-orange-400 hover:text-orange-300 underline">
                            Saran Otomatis ✨
                        </button>
                    </div>
                    <p class="text-slate-400 text-xs mb-3">
                        Pesan motivasi atau arahan teknis yang akan dicetak di lembar laporan untuk dipelajari atlet dan orang tua:
                    </p>
                    <textarea id="coachNotes" rows="4" placeholder="Tulis catatan evaluasi untuk atlet di sini..." class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 font-medium text-xs text-slate-200 focus:outline-none focus:border-orange-500 transition leading-relaxed"></textarea>
                    
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Nama Pelatih</label>
                            <input type="text" id="coachName" placeholder="Nama Pelatih" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Tanggal Ttd</label>
                            <input type="text" id="signDate" value="<?= date('d/m/Y') ?>" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 font-semibold text-white focus:outline-none focus:border-orange-500 transition">
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: LAP INPUT TABLE & LIVE METRICS (7 COLS) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- CARD: INPUT TABEL LAP BY LAP -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="font-teko text-2xl font-bold tracking-wider text-white uppercase flex items-center gap-2">
                                <span>⏱️</span> 3. Input Putaran Lap (Split & Kayuhan)
                            </h3>
                            <p class="text-slate-400 text-xs">
                                Masukkan **Waktu Split** (contoh: <code class="text-orange-400 font-bold">31.40</code> atau <code class="text-orange-400 font-bold">1:04.20</code>) dan **Jumlah Kayuhan**.
                            </p>
                        </div>
                        <span id="lapCountBadge" class="self-start sm:self-auto px-3 py-1 bg-slate-900 border border-slate-700 rounded-xl text-orange-400 font-bold text-xs uppercase tracking-wider">
                            Total 2 Lap (100m)
                        </span>
                    </div>

                    <!-- LAP TABLE CONTAINER -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-700 bg-slate-900/60 mb-4">
                        <table class="w-full text-xs text-left" id="lapsInputTable">
                            <thead class="bg-slate-900 text-slate-400 uppercase font-bold text-[11px] tracking-wider border-b border-slate-700">
                                <tr>
                                    <th class="py-3 px-3 text-center">Lap</th>
                                    <th class="py-3 px-3 text-center">Jarak</th>
                                    <th class="py-3 px-3 text-center min-w-[110px]">Waktu Split <span class="text-rose-400">*</span></th>
                                    <th class="py-3 px-3 text-center min-w-[85px]">Kayuhan <span class="text-rose-400">*</span></th>
                                    <th class="py-3 px-3 text-center">Laju (m/s)</th>
                                    <th class="py-3 px-3 text-center">SR (SPM)</th>
                                    <th class="py-3 px-3 text-center">DPS (m)</th>
                                    <th class="py-3 px-3 text-center font-black text-orange-400">SWOLF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800" id="lapsTableBody">
                                <!-- Dynamic rows rendered by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                        <span>💡 Tips: Tekan <kbd class="px-1.5 py-0.5 bg-slate-700 rounded text-slate-200">Tab</kbd> atau <kbd class="px-1.5 py-0.5 bg-slate-700 rounded text-slate-200">Enter</kbd> untuk lompat ke baris berikutnya.</span>
                        <span class="text-slate-500 font-medium">Panjang Lap: <span id="poolDistLabel">50m</span></span>
                    </div>
                </div>

                <!-- CARD: RINGKASAN EDUKASI KU KECIL (KID-FRIENDLY CARDS) -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <h3 class="font-teko text-2xl font-bold tracking-wider text-orange-400 uppercase mb-4 flex items-center gap-2">
                        <span>🌟</span> Hasil & Kartu Pembelajaran Atlet
                    </h3>

                    <!-- 3 VISUAL HIGHLIGHT CARDS -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        
                        <!-- SWOLF CARD -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-700/80 rounded-2xl p-4 text-center relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-500/10 rounded-full blur-xl"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-blue-400 block mb-1">
                                Indeks Efisiensi (SWOLF)
                            </span>
                            <div class="text-3xl font-black font-teko text-white my-1" id="kpiSwolf">
                                0.0
                            </div>
                            <div id="swolfBadge" class="inline-block px-2.5 py-0.5 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2">
                                Siap Hitung
                            </div>
                            <p class="text-[11px] text-slate-400 leading-tight">
                                Waktu + Kayuhan. Makin kecil skor, makin gesit & hemat tenaga!
                            </p>
                        </div>

                        <!-- DPS CARD -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-700/80 rounded-2xl p-4 text-center relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-500/10 rounded-full blur-xl"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 block mb-1">
                                Jarak Per Kayuhan (DPS)
                            </span>
                            <div class="text-3xl font-black font-teko text-white my-1" id="kpiDps">
                                0.00 <span class="text-xs font-sans text-slate-400 font-bold">m</span>
                            </div>
                            <div id="dpsBadge" class="inline-block px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2">
                                Dorongan Bersih
                            </div>
                            <p class="text-[11px] text-slate-400 leading-tight">
                                Jarak tempuh badanmu dalam 1 ayunan tangan penuh.
                            </p>
                        </div>

                        <!-- STROKE RATE CARD -->
                        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-700/80 rounded-2xl p-4 text-center relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-500/10 rounded-full blur-xl"></div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-400 block mb-1">
                                Irama Kayuhan (SR)
                            </span>
                            <div class="text-3xl font-black font-teko text-white my-1" id="kpiSr">
                                0.0 <span class="text-xs font-sans text-slate-400 font-bold">SPM</span>
                            </div>
                            <div id="srBadge" class="inline-block px-2.5 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2">
                                Frekuensi Kayuhan
                            </div>
                            <p class="text-[11px] text-slate-400 leading-tight">
                                Jumlah putaran kayuhan per menit (ritme ritmis).
                            </p>
                        </div>

                    </div>

                    <!-- TOTAL TIME & PACING SUMMARY STRIP -->
                    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Waktu Akhir Lomba</span>
                            <div class="font-teko text-3xl font-black text-orange-400" id="kpiTotalTime">00:00.00</div>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Laju Rata-rata</span>
                            <div class="font-teko text-3xl font-black text-white" id="kpiAvgSpeed">0.00 m/s</div>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Strategi Pacing</span>
                            <div class="text-xs font-bold text-emerald-400 mt-1 flex items-center gap-1.5" id="kpiPacingType">
                                <span>🎯</span> Belum ada data
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD: GRAFIK VISUAL INTERAKTIF (CHART.JS) -->
                <div class="bg-slate-800/90 border border-slate-700 rounded-3xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-teko text-2xl font-bold tracking-wider text-white uppercase flex items-center gap-2">
                            <span>📊</span> Visualisasi Pacing & Kinematika Kayuhan
                        </h3>
                        <span class="text-xs text-slate-400 font-medium">Tren Antar Lap</span>
                    </div>

                    <!-- CANVAS CONTAINER -->
                    <div class="w-full h-64 bg-slate-900/60 rounded-2xl p-3 border border-slate-700/70">
                        <canvas id="raceChart"></canvas>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-6 text-[11px] text-slate-400 font-medium">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span> Waktu Putaran (detik)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Irama Kayuhan (SPM)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Jarak Kayuhan (DPS x10)</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- BOTTOM STICKY CALL TO ACTION -->
        <div class="mt-12 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 border border-orange-500/40 rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-500/20 border border-orange-500/40 flex items-center justify-center text-2xl flex-shrink-0">
                    🖨️
                </div>
                <div>
                    <h4 class="font-teko text-2xl font-black text-white uppercase tracking-wider mb-0.5">Siap Berikan Laporan ke Atlet?</h4>
                    <p class="text-slate-400 text-xs">Cetak laporan visual 1 halaman A4 penuh untuk dievaluasi bersama atlet dan orang tua.</p>
                </div>
            </div>
            <button type="button" onclick="printReport()" class="w-full md:w-auto px-8 py-4 bg-gradient-to-r from-[#f25822] to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-orange-500/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <span>🖨️ CETAK LAPORAN RESMI A4</span>
            </button>
        </div>

    </main>

    <!-- PRINT REPORT CONTAINER (DISESUAIKAN KHUSUS 1 HALAMAN A4) -->
    <div id="print-report-container" class="print-visible bg-white text-slate-900 p-2">
        <div class="a4-page border border-slate-300 rounded-lg p-5">
            
            <!-- HEADER RESMI LAPORAN A4 -->
            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-3 mb-3">
                <div class="flex items-center gap-3">
                    <img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-12 w-auto object-contain" alt="SET System">
                    <div>
                        <h2 class="font-teko text-2xl font-black tracking-wider text-slate-900 uppercase leading-none">UNIVERSAL SET SYSTEM</h2>
                        <p class="text-[9px] font-bold tracking-widest uppercase text-slate-600">Swimming Race & Biomechanics Analysis Report</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2.5 py-1 bg-slate-900 text-white font-teko text-base tracking-widest uppercase rounded">
                        LAPORAN PERFORMA ATLET
                    </span>
                    <p class="text-[9px] text-slate-500 font-semibold mt-0.5" id="printDocDate">Tgl Cetak: <?= date('d/m/Y') ?></p>
                </div>
            </div>

            <!-- PROFIL ATLET & DETAIL LOMBA -->
            <div class="grid grid-cols-2 gap-2 bg-slate-100 p-2.5 rounded-lg border border-slate-200 text-[10px] mb-3">
                <div class="space-y-1">
                    <div><span class="text-slate-500 font-bold uppercase">Nama Atlet:</span> <strong class="text-slate-900 text-xs" id="pAthleteName">-</strong></div>
                    <div><span class="text-slate-500 font-bold uppercase">Klub / Sekolah:</span> <strong class="text-slate-900" id="pClubName">-</strong></div>
                    <div><span class="text-slate-500 font-bold uppercase">Kelompok Umur / Gender:</span> <strong class="text-slate-900" id="pAthleteKU">-</strong> (<span id="pAthleteGender">-</span>)</div>
                </div>
                <div class="space-y-1 text-right">
                    <div><span class="text-slate-500 font-bold uppercase">Nomor Lomba:</span> <strong class="text-slate-900 text-xs" id="pRaceEvent">-</strong></div>
                    <div><span class="text-slate-500 font-bold uppercase">Kolam & Event:</span> <strong class="text-slate-900" id="pPoolEvent">-</strong></div>
                    <div><span class="text-slate-500 font-bold uppercase">Waktu Akhir Lomba:</span> <strong class="text-orange-600 text-sm font-black font-teko tracking-wider" id="pFinalTime">00:00.00</strong></div>
                </div>
            </div>

            <!-- 3 KARTU EDUKASI METRIK KU KECIL (PRINT) -->
            <div class="grid grid-cols-3 gap-2 text-center mb-3">
                <div class="border border-blue-200 bg-blue-50/70 p-2 rounded-lg">
                    <span class="text-[8px] font-black uppercase text-blue-800 tracking-wider block">SKOR EFISIENSI (SWOLF)</span>
                    <div class="font-teko text-2xl font-black text-blue-900 leading-none my-0.5" id="pSwolf">0.0</div>
                    <span class="inline-block px-1.5 py-0.2 text-[8px] font-extrabold rounded bg-blue-200 text-blue-900 mb-0.5" id="pSwolfBadge">Bagus</span>
                    <p class="text-[8px] text-blue-700 leading-tight">Waktu + Kayuhan. Makin kecil skor, makin gesit & hemat tenaga.</p>
                </div>
                <div class="border border-emerald-200 bg-emerald-50/70 p-2 rounded-lg">
                    <span class="text-[8px] font-black uppercase text-emerald-800 tracking-wider block">JARAK PER KAYUHAN (DPS)</span>
                    <div class="font-teko text-2xl font-black text-emerald-900 leading-none my-0.5" id="pDps">0.00 m</div>
                    <span class="inline-block px-1.5 py-0.2 text-[8px] font-extrabold rounded bg-emerald-200 text-emerald-900 mb-0.5" id="pDpsBadge">Dorongan Kuat</span>
                    <p class="text-[8px] text-emerald-700 leading-tight">Setiap 1 kali kayuh, badan atlet meluncur sejauh ini.</p>
                </div>
                <div class="border border-amber-200 bg-amber-50/70 p-2 rounded-lg">
                    <span class="text-[8px] font-black uppercase text-amber-800 tracking-wider block">IRAMA KAYUHAN (SR)</span>
                    <div class="font-teko text-2xl font-black text-amber-900 leading-none my-0.5" id="pSr">0.0 SPM</div>
                    <span class="inline-block px-1.5 py-0.2 text-[8px] font-extrabold rounded bg-amber-200 text-amber-900 mb-0.5" id="pSrBadge">Irama Teratur</span>
                    <p class="text-[8px] text-amber-700 leading-tight">Frekuensi kayuhan per menit (tempo ritme atlet).</p>
                </div>
            </div>

            <!-- TABEL ANALISIS LAP DETAIL (PRINT) -->
            <div class="mb-3">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-800 mb-1 flex items-center justify-between">
                    <span>Tabel Analisis Per Putaran Lap</span>
                    <span class="text-[9px] font-bold text-slate-500" id="pPacingStrategy">Strategi: -</span>
                </div>
                <table class="w-full text-[9px] text-center border-collapse">
                    <thead class="bg-slate-200 text-slate-800 uppercase font-black text-[8px]">
                        <tr>
                            <th class="py-1 px-1">Lap</th>
                            <th class="py-1 px-1">Jarak</th>
                            <th class="py-1 px-1">Waktu Split</th>
                            <th class="py-1 px-1">Kayuhan</th>
                            <th class="py-1 px-1">Laju (m/s)</th>
                            <th class="py-1 px-1">Stroke Rate (SPM)</th>
                            <th class="py-1 px-1">DPS (m)</th>
                            <th class="py-1 px-1 bg-slate-300">SWOLF</th>
                            <th class="py-1 px-1">Evaluasi Lap</th>
                        </tr>
                    </thead>
                    <tbody id="pLapsTableBody">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>

            <!-- GRAFIK PRINT (IMAGE EXPORTED FROM CANVAS) -->
            <div class="mb-3 border border-slate-200 rounded-lg p-2 bg-slate-50">
                <div class="text-[9px] font-black uppercase tracking-wider text-slate-700 mb-1 flex items-center justify-between">
                    <span>Grafik Visual Pacing & Kinematika Kayuhan</span>
                    <span class="text-[8px] text-slate-500">Orange = Waktu Lap (s) | Biru = Stroke Rate (SPM) | Hijau = DPS (x10 m)</span>
                </div>
                <div class="w-full flex justify-center items-center h-44 bg-white rounded border border-slate-200">
                    <img id="printChartImg" class="max-h-full max-w-full object-contain" alt="Grafik Performa Lomba">
                </div>
            </div>

            <!-- CATATAN PEMBELAJARAN ATLET & TANDA TANGAN -->
            <div class="grid grid-cols-12 gap-3 items-end pt-1 border-t border-slate-300">
                <div class="col-span-8 bg-amber-50/60 border border-amber-200 rounded-lg p-2 text-[9px]">
                    <strong class="text-amber-900 uppercase font-black text-[9px] block mb-0.5">📌 Catatan Pembelajaran & Evaluasi Pelatih:</strong>
                    <p class="text-slate-800 whitespace-pre-line leading-relaxed" id="pCoachNotes">
                        Bagus sekali, pertahankan daya dorong kayuhan tangan dan konsistensi pernapasan di setiap lap.
                    </p>
                </div>
                <div class="col-span-4 text-center text-[9px]">
                    <p class="text-slate-500 font-bold mb-8">Tanggal: <span id="pSignDate"><?= date('d/m/Y') ?></span></p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <div class="border-b border-slate-900 h-6"></div>
                            <span class="text-[8px] font-bold text-slate-700 block mt-0.5">Ttd. Atlet</span>
                        </div>
                        <div>
                            <div class="border-b border-slate-900 h-6"></div>
                            <strong class="text-[8px] font-black text-slate-900 block mt-0.5" id="pSignCoachName">Pelatih</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER INFO -->
            <div class="text-center mt-3 pt-2 border-t border-slate-200 text-[8px] text-slate-400 font-bold tracking-widest uppercase">
                &copy; <?= date('Y') ?> SET SYSTEM Swimming Biomechanics Platform &bull; www.setsystem.id
            </div>

        </div>
    </div>

    <!-- SCRIPT APPLICATION LOGIC -->
    <script>
        // APPLICATION STATE
        let poolLength = 50; // 50m (LCM) or 25m (SCM)
        let strokeStyle = 'Gaya Bebas';
        let totalDistance = 100; // 50, 100, 200, 400, 800, 1500
        let raceChart = null;

        // ON PAGE LOAD
        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            generateLapInputs();
            calculateAll();
        });

        // 1. CONFIGURATION HANDLERS
        function setPoolLength(len) {
            poolLength = parseInt(len);
            document.getElementById('btnPool50').className = poolLength === 50 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-orange-600 border-orange-500 text-white shadow'
                : 'py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-slate-900 border-slate-700 text-slate-400 hover:text-white';
            
            document.getElementById('btnPool25').className = poolLength === 25 
                ? 'py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-orange-600 border-orange-500 text-white shadow'
                : 'py-2.5 px-3 rounded-xl border text-xs font-extrabold uppercase transition flex items-center justify-center gap-1.5 bg-slate-900 border-slate-700 text-slate-400 hover:text-white';

            document.getElementById('poolDistLabel').innerText = poolLength + 'm';
            generateLapInputs();
            calculateAll();
        }

        function setStrokeStyle(styleName) {
            strokeStyle = styleName;
            document.querySelectorAll('.btn-stroke').forEach(btn => {
                if (btn.innerText.trim() === styleName || (styleName.startsWith('Ganti') && btn.innerText.includes('Ganti'))) {
                    btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-orange-600 border-orange-500 text-white ' + (btn.classList.contains('col-span-2') ? 'col-span-2' : '');
                } else {
                    btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white ' + (btn.classList.contains('col-span-2') ? 'col-span-2' : '');
                }
            });
            calculateAll();
        }

        function setDistance(dist) {
            totalDistance = parseInt(dist);
            document.querySelectorAll('.btn-dist').forEach(btn => {
                if (parseInt(btn.innerText) === totalDistance) {
                    btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-orange-600 border-orange-500 text-white';
                } else {
                    btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-900 border-slate-700 text-slate-400 hover:text-white';
                }
            });
            generateLapInputs();
            calculateAll();
        }

        // 2. GENERATE LAP INPUT ROWS
        function generateLapInputs() {
            const lapCount = Math.max(1, Math.round(totalDistance / poolLength));
            document.getElementById('lapCountBadge').innerText = `Total ${lapCount} Lap (${totalDistance}m)`;

            const tbody = document.getElementById('lapsTableBody');
            tbody.innerHTML = '';

            for (let i = 1; i <= lapCount; i++) {
                const startM = (i - 1) * poolLength;
                const endM = i * poolLength;

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-800/50 transition';
                tr.innerHTML = `
                    <td class="py-2.5 px-3 text-center font-bold text-white">${i}</td>
                    <td class="py-2.5 px-3 text-center text-slate-400 font-medium">${startM}-${endM}m</td>
                    <td class="py-2 px-2 text-center">
                        <input type="text" 
                               class="lap-time-input w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-orange-400 focus:outline-none focus:border-orange-500 transition text-xs" 
                               data-lap="${i}" 
                               placeholder="00.00" 
                               oninput="calculateAll()" 
                               onkeydown="handleKeyNav(event, ${i}, 'time')">
                    </td>
                    <td class="py-2 px-2 text-center">
                        <input type="number" 
                               class="lap-stroke-input w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-blue-400 focus:outline-none focus:border-blue-500 transition text-xs" 
                               data-lap="${i}" 
                               placeholder="Kayuhan" 
                               min="1" max="150"
                               oninput="calculateAll()" 
                               onkeydown="handleKeyNav(event, ${i}, 'stroke')">
                    </td>
                    <td class="py-2.5 px-3 text-center font-mono text-slate-300 lap-vel">-</td>
                    <td class="py-2.5 px-3 text-center font-mono text-blue-300 font-bold lap-sr">-</td>
                    <td class="py-2.5 px-3 text-center font-mono text-emerald-300 font-bold lap-dps">-</td>
                    <td class="py-2.5 px-3 text-center font-mono text-orange-400 font-black lap-swolf">-</td>
                `;
                tbody.appendChild(tr);
            }
        }

        // KEYBOARD NAVIGATION (ENTER / TAB JUMP)
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

        // PARSE TIME TO SECONDS (Supports 31.40, 1:04.20, 01:04.2)
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

        // FORMAT SECONDS TO STRING (mm:ss.ms)
        function formatSecondsToTime(totalSec) {
            if (!totalSec || isNaN(totalSec) || totalSec <= 0) return '00:00.00';
            const m = Math.floor(totalSec / 60);
            const s = totalSec % 60;
            const mStr = String(m).padStart(2, '0');
            const sStr = s.toFixed(2).padStart(5, '0');
            return `${mStr}:${sStr}`;
        }

        // 3. MAIN CALCULATION FUNCTION
        function calculateAll() {
            const timeInputs = document.querySelectorAll('.lap-time-input');
            const strokeInputs = document.querySelectorAll('.lap-stroke-input');
            const rows = document.querySelectorAll('#lapsTableBody tr');

            let totalTimeSec = 0;
            let totalStrokes = 0;
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
                    totalStrokes += strokes;

                    // METRICS FORMULA:
                    // Velocity (m/s) = poolLength / sec
                    const vel = poolLength / sec;
                    // Stroke Rate (SPM) = (strokes / sec) * 60
                    const sr = (strokes / sec) * 60;
                    // DPS (m) = poolLength / strokes
                    const dps = poolLength / strokes;
                    // SWOLF = sec + strokes
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

            // SUMMARY METRICS
            if (validLapCount > 0) {
                const avgSwolf = lapSwolfs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgDps = lapDPSs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgSr = lapSRs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
                const avgSpeed = (poolLength * validLapCount) / totalTimeSec;

                document.getElementById('kpiSwolf').innerText = avgSwolf.toFixed(1);
                document.getElementById('kpiDps').innerHTML = `${avgDps.toFixed(2)} <span class="text-xs font-sans text-slate-400 font-bold">m</span>`;
                document.getElementById('kpiSr').innerHTML = `${avgSr.toFixed(1)} <span class="text-xs font-sans text-slate-400 font-bold">SPM</span>`;
                document.getElementById('kpiTotalTime').innerText = formatSecondsToTime(totalTimeSec);
                document.getElementById('kpiAvgSpeed').innerText = `${avgSpeed.toFixed(2)} m/s`;

                // SWOLF BADGE EVALUATION (KU Friendly)
                const swolfBadge = document.getElementById('swolfBadge');
                if (poolLength === 50) {
                    if (avgSwolf < 70) {
                        swolfBadge.innerText = '🌟 Sangat Gesit & Efisien!';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    } else if (avgSwolf <= 85) {
                        swolfBadge.innerText = '👍 Bagus & Seimbang';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    } else {
                        swolfBadge.innerText = '💡 Perlu Penguatan Luncuran';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    }
                } else {
                    // 25m pool
                    if (avgSwolf < 35) {
                        swolfBadge.innerText = '🌟 Sangat Gesit & Efisien!';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    } else if (avgSwolf <= 45) {
                        swolfBadge.innerText = '👍 Bagus & Seimbang';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    } else {
                        swolfBadge.innerText = '💡 Perlu Penguatan Luncuran';
                        swolfBadge.className = 'inline-block px-2.5 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-[10px] font-black tracking-wider uppercase mb-2';
                    }
                }

                // DPS BADGE
                const dpsBadge = document.getElementById('dpsBadge');
                if (avgDps >= 1.4) {
                    dpsBadge.innerText = '🚀 Dorongan Sangat Kuat';
                } else if (avgDps >= 1.1) {
                    dpsBadge.innerText = '👌 Luncuran Baik';
                } else {
                    dpsBadge.innerText = '⚠️ Jarak Kayuh Pendek';
                }

                // PACING STRATEGY
                const pacingEl = document.getElementById('kpiPacingType');
                if (validLapCount >= 2) {
                    const firstHalfTime = lapTimes[0];
                    const secondHalfTime = lapTimes[validLapCount - 1];
                    const diffPct = ((secondHalfTime - firstHalfTime) / firstHalfTime) * 100;

                    if (secondHalfTime < firstHalfTime) {
                        pacingEl.innerHTML = `<span class="text-emerald-400 font-bold">🟢 Negative Split (Hebat! Putaran Akhir Lebih Cepat)</span>`;
                    } else if (diffPct <= 4) {
                        pacingEl.innerHTML = `<span class="text-blue-400 font-bold">🔵 Even Pace (Sangat Stabil & Terjaga)</span>`;
                    } else {
                        pacingEl.innerHTML = `<span class="text-amber-400 font-bold">🟠 Positive Split (Drop-off +${diffPct.toFixed(1)}% di Akhir)</span>`;
                    }
                } else {
                    pacingEl.innerText = '🎯 1 Putaran Selesai';
                }

            } else {
                document.getElementById('kpiSwolf').innerText = '0.0';
                document.getElementById('kpiDps').innerHTML = `0.00 <span class="text-xs font-sans text-slate-400 font-bold">m</span>`;
                document.getElementById('kpiSr').innerHTML = `0.0 <span class="text-xs font-sans text-slate-400 font-bold">SPM</span>`;
                document.getElementById('kpiTotalTime').innerText = '00:00.00';
                document.getElementById('kpiAvgSpeed').innerText = '0.00 m/s';
                document.getElementById('kpiPacingType').innerText = '🎯 Belum ada data';
            }

            // UPDATE CHART
            updateChart(lapLabels, lapTimes, lapSRs, lapDPSs);
        }

        // 4. CHART.JS CONFIGURATION
        function initChart() {
            const ctx = document.getElementById('raceChart').getContext('2d');
            raceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lap 1', 'Lap 2'],
                    datasets: [
                        {
                            label: 'Waktu Split (detik)',
                            data: [31.5, 33.8],
                            borderColor: '#f25822',
                            backgroundColor: 'rgba(242, 88, 34, 0.1)',
                            borderWidth: 3,
                            tension: 0.3,
                            yAxisID: 'yTime',
                            pointRadius: 5,
                            pointBackgroundColor: '#f25822'
                        },
                        {
                            label: 'Stroke Rate (SPM)',
                            data: [42.0, 40.5],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'yMetrics',
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6'
                        },
                        {
                            label: 'DPS (Jarak x 10 m)',
                            data: [14.5, 13.5],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            yAxisID: 'yMetrics',
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#94a3b8', font: { weight: 'bold' } }
                        },
                        yTime: {
                            type: 'linear',
                            position: 'left',
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#f25822', font: { weight: 'bold' } },
                            title: { display: true, text: 'Waktu (s)', color: '#f25822' }
                        },
                        yMetrics: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { color: '#60a5fa' },
                            title: { display: true, text: 'Kinematika (SR & DPS)', color: '#60a5fa' }
                        }
                    }
                }
            });
        }

        function updateChart(labels, times, srs, dpss) {
            if (!raceChart) return;
            raceChart.data.labels = labels;
            raceChart.data.datasets[0].data = times;
            raceChart.data.datasets[1].data = srs;
            raceChart.data.datasets[2].data = dpss.map(d => parseFloat((d * 10).toFixed(1)));
            raceChart.update();
        }

        // 5. GENERATE COACH ADVICE (AI SUGGESTION HEURISTIC)
        function generateCoachAdvice() {
            const athlete = document.getElementById('athleteName').value || 'Atlet';
            const ku = document.getElementById('athleteKU').value;
            const timeInps = document.querySelectorAll('.lap-time-input');
            const strokeInps = document.querySelectorAll('.lap-stroke-input');

            const lap1Time = parseTimeToSeconds(timeInps[0]?.value);
            const lap2Time = parseTimeToSeconds(timeInps[timeInps.length - 1]?.value);
            const lap1Stroke = parseFloat(strokeInps[0]?.value) || 0;
            const lap2Stroke = parseFloat(strokeInps[strokeInps.length - 1]?.value) || 0;

            let advice = `Evaluasi untuk ${athlete} (${ku}):\n`;
            
            if (lap1Time > 0 && lap2Time > 0) {
                const diffSec = (lap2Time - lap1Time).toFixed(2);
                if (lap2Time > lap1Time) {
                    advice += `• Di paruh akhir lomba, waktu putaran bertambah +${diffSec} detik. `;
                    if (lap2Stroke > lap1Stroke) {
                        advice += `Jumlah kayuhan bertambah dari ${lap1Stroke} menjadi ${lap2Stroke}, yang menandakan perahu/badan mulai kehilangan daya luncur karena kelelahan (kayuhan terburu-buru).\n`;
                        advice += `• Rekomendasi Latihan: Latih kekuatan dorongan kaki (kick endurance) dan pertahankan jarak jangkauan tangan (catch & pull) agar kayuhan tidak kosong saat lelah.`;
                    } else {
                        advice += `Irama kayuhan melambat di akhir. Latih pacing endurance di 25 meter penutup.\n`;
                    }
                } else {
                    advice += `• Strategi pacing sangat istimewa (Negative Split)! Putaran akhir berhasil lebih cepat ${Math.abs(diffSec)} detik.\n`;
                    advice += `• Daya dorong dan stamina sangat prima. Pertahankan konsistensi luncuran ini di nomor lomba berikutnya!`;
                }
            } else {
                advice += `• Kayuhan awal sudah bertenaga. Pastikan konsistensi tarikan tangan dan pertahankan streamline tubuh di setiap putaran dinding.`;
            }

            document.getElementById('coachNotes').value = advice;
        }

        // 6. LOAD SAMPLE DATA (DEMO)
        function loadSampleData() {
            document.getElementById('athleteName').value = 'Budi Pratama';
            document.getElementById('clubName').value = 'Barracuda Aquatic Club';
            document.getElementById('athleteKU').value = 'KU 4 (11 Thn Kebawah)';
            document.getElementById('athleteGender').value = 'Putra';
            document.getElementById('eventName').value = 'Kejurprov Renang Jawa Timur 2026';
            document.getElementById('coachName').value = 'Coach Hendra';

            setPoolLength(50);
            setStrokeStyle('Gaya Bebas');
            setDistance(100);

            setTimeout(() => {
                const timeInps = document.querySelectorAll('.lap-time-input');
                const strokeInps = document.querySelectorAll('.lap-stroke-input');
                if (timeInps[0]) timeInps[0].value = '30.80';
                if (strokeInps[0]) strokeInps[0].value = '34';
                if (timeInps[1]) timeInps[1].value = '33.60';
                if (strokeInps[1]) strokeInps[1].value = '37';
                
                generateCoachAdvice();
                calculateAll();
            }, 100);
        }

        // 7. RESET FORM
        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset seluruh formulir?')) {
                document.getElementById('athleteName').value = '';
                document.getElementById('clubName').value = '';
                document.getElementById('eventName').value = '';
                document.getElementById('coachNotes').value = '';
                document.querySelectorAll('.lap-time-input').forEach(i => i.value = '');
                document.querySelectorAll('.lap-stroke-input').forEach(i => i.value = '');
                calculateAll();
            }
        }

        // 8. PRINT / PDF REPORT PREPARATION
        function printReport() {
            const athlete = document.getElementById('athleteName').value.trim() || 'Nama Atlet';
            const club = document.getElementById('clubName').value.trim() || '-';
            const ku = document.getElementById('athleteKU').value;
            const gender = document.getElementById('athleteGender').value;
            const event = document.getElementById('eventName').value.trim() || 'Simulasi / Lomba Renang';
            const coach = document.getElementById('coachName').value.trim() || 'Pelatih';
            const signDate = document.getElementById('signDate').value.trim() || '<?= date('d/m/Y') ?>';
            const notes = document.getElementById('coachNotes').value.trim() || 'Pertahankan ritme kayuhan dan tingkatkan daya luncur di setiap putaran lap.';

            document.getElementById('pAthleteName').innerText = athlete;
            document.getElementById('pClubName').innerText = club;
            document.getElementById('pAthleteKU').innerText = ku;
            document.getElementById('pAthleteGender').innerText = gender;
            document.getElementById('pRaceEvent').innerText = `${totalDistance}m ${strokeStyle}`;
            document.getElementById('pPoolEvent').innerText = `Kolam ${poolLength}m &bull; ${event}`;
            document.getElementById('pFinalTime').innerText = document.getElementById('kpiTotalTime').innerText;

            document.getElementById('pSwolf').innerText = document.getElementById('kpiSwolf').innerText;
            document.getElementById('pSwolfBadge').innerText = document.getElementById('swolfBadge').innerText.replace(/[🌟👍💡]/g, '').trim();

            document.getElementById('pDps').innerText = document.getElementById('kpiDps').innerText.replace('m', '').trim() + ' m';
            document.getElementById('pDpsBadge').innerText = document.getElementById('dpsBadge').innerText.replace(/[🚀👌⚠️]/g, '').trim();

            document.getElementById('pSr').innerText = document.getElementById('kpiSr').innerText.replace('SPM', '').trim() + ' SPM';
            document.getElementById('pSrBadge').innerText = document.getElementById('srBadge').innerText.trim();

            document.getElementById('pPacingStrategy').innerText = 'Pacing: ' + document.getElementById('kpiPacingType').innerText.replace(/[🟢🔵🟠🎯]/g, '').trim();
            document.getElementById('pCoachNotes').innerText = notes;
            document.getElementById('pSignCoachName').innerText = coach;
            document.getElementById('pSignDate').innerText = signDate;

            // Render Print Table Rows
            const pTbody = document.getElementById('pLapsTableBody');
            pTbody.innerHTML = '';
            
            const timeInps = document.querySelectorAll('.lap-time-input');
            const strokeInps = document.querySelectorAll('.lap-stroke-input');
            const rows = document.querySelectorAll('#lapsTableBody tr');

            timeInps.forEach((timeInp, idx) => {
                const lapNum = idx + 1;
                const sec = parseTimeToSeconds(timeInp.value);
                const strokes = parseFloat(strokeInps[idx]?.value) || 0;
                const row = rows[idx];

                const startM = (lapNum - 1) * poolLength;
                const endM = lapNum * poolLength;

                const vel = row.querySelector('.lap-vel')?.innerText || '-';
                const sr = row.querySelector('.lap-sr')?.innerText || '-';
                const dps = row.querySelector('.lap-dps')?.innerText || '-';
                const swolf = row.querySelector('.lap-swolf')?.innerText || '-';

                let evalLap = 'Stabil';
                if (idx > 0) {
                    const prevSec = parseTimeToSeconds(timeInps[idx-1]?.value);
                    if (prevSec > 0 && sec > 0) {
                        if (sec < prevSec) evalLap = '⚡ Lebih Cepat!';
                        else if (sec - prevSec > 2) evalLap = '⚠️ Melambat';
                        else evalLap = 'Konsisten';
                    }
                } else {
                    evalLap = 'Lap Pembuka';
                }

                const tr = document.createElement('tr');
                tr.className = idx % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                tr.innerHTML = `
                    <td class="py-1 px-1 font-bold">${lapNum}</td>
                    <td class="py-1 px-1 text-slate-600">${startM}-${endM}m</td>
                    <td class="py-1 px-1 font-mono font-bold text-slate-900">${formatSecondsToTime(sec)}</td>
                    <td class="py-1 px-1 font-mono">${strokes > 0 ? strokes : '-'}</td>
                    <td class="py-1 px-1 font-mono">${vel}</td>
                    <td class="py-1 px-1 font-mono font-bold">${sr}</td>
                    <td class="py-1 px-1 font-mono">${dps}</td>
                    <td class="py-1 px-1 font-mono font-black bg-slate-100">${swolf}</td>
                    <td class="py-1 px-1 text-[8px] font-bold">${evalLap}</td>
                `;
                pTbody.appendChild(tr);
            });

            // Export Chart as Static Image for Razor-sharp Printing
            if (raceChart) {
                const imgData = raceChart.toBase64Image();
                const printImg = document.getElementById('printChartImg');
                printImg.src = imgData;
            }

            // Trigger Browser Print Dialog
            setTimeout(() => {
                window.print();
            }, 250);
        }
    </script>
</body>
</html>
