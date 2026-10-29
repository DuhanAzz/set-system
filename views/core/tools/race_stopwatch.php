<?php
$appName = $settings['app_name'] ?? 'Universal SET System';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Stopwatch Analisis Lomba') ?> - <?= htmlspecialchars($appName) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@500;600;700;900&family=JetBrains+Mono:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-teko { font-family: 'Teko', sans-serif; }
        .font-mono-timer { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .pulse-live {
            animation: pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .7; transform: scale(1.08); }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-blue-600 selection:text-white">

    <!-- TOP BAR -->
    <header class="bg-slate-900/90 backdrop-blur border-b border-slate-800 sticky top-0 z-30 px-4 py-3">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="<?= getenv('APP_URL') ?>/tools/race-analysis" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold transition border border-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Analisis Lomba</span>
                </a>
                <div class="h-4 w-px bg-slate-700 hidden sm:block"></div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 pulse-live"></span>
                    <span class="text-xs font-bold text-slate-300" id="syncStatus">Sinkronisasi Real-Time Aktif</span>
                </div>
            </div>

            <button type="button" onclick="sendDataToRaceAnalysis(true)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-extrabold shadow-lg shadow-blue-500/20 transition active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Terapkan ke Laporan</span>
            </button>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="flex-grow max-w-4xl w-full mx-auto p-4 md:p-6 space-y-6">

        <!-- 1. ATLET & KONFIGURASI LOMBA CARD -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Data Atlet & Target Nomor Lomba
                </h3>
                <span id="cfgLapTargetBadge" class="text-xs font-extrabold px-2.5 py-0.5 rounded-md bg-blue-900/60 text-blue-300 border border-blue-700/50">
                    Target: 2 Lap (100m)
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">Nama Atlet</label>
                    <input type="text" id="cfgAthleteName" oninput="saveCurrentConfig()" placeholder="Nama Atlet" class="w-full bg-slate-800/80 border border-slate-700 rounded-lg px-2.5 py-1.5 font-bold text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">Gaya Renang</label>
                    <select id="cfgStrokeStyle" onchange="updateRaceParameters()" class="w-full bg-slate-800/80 border border-slate-700 rounded-lg px-2.5 py-1.5 font-bold text-white focus:outline-none focus:border-blue-500">
                        <option value="Gaya Bebas">Gaya Bebas</option>
                        <option value="Gaya Dada">Gaya Dada</option>
                        <option value="Gaya Punggung">Gaya Punggung</option>
                        <option value="Gaya Kupu-kupu">Gaya Kupu-kupu</option>
                        <option value="Ganti Perorangan">Ganti Perorangan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">Kolam</label>
                    <select id="cfgPoolLength" onchange="updateRaceParameters()" class="w-full bg-slate-800/80 border border-slate-700 rounded-lg px-2.5 py-1.5 font-bold text-white focus:outline-none focus:border-blue-500">
                        <option value="50">50 Meter (LCM)</option>
                        <option value="25">25 Meter (SCM)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 font-semibold mb-1">Jarak Lomba</label>
                    <select id="cfgTotalDistance" onchange="updateRaceParameters()" class="w-full bg-slate-800/80 border border-slate-700 rounded-lg px-2.5 py-1.5 font-bold text-white focus:outline-none focus:border-blue-500">
                        <option value="50">50m</option>
                        <option value="100" selected>100m</option>
                        <option value="200">200m</option>
                        <option value="400">400m</option>
                        <option value="800">800m</option>
                        <option value="1500">1500m</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. HERO DIGITAL STOPWATCH -->
        <div class="bg-gradient-to-b from-slate-900 to-slate-900/90 border border-slate-800 rounded-3xl p-6 md:p-8 text-center shadow-2xl relative overflow-hidden">
            <!-- Background Glow Decor -->
            <div class="absolute -top-24 -left-24 w-60 h-60 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-60 h-60 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Active Lap Status Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs font-bold text-slate-300 mb-3">
                <span class="w-2 h-2 rounded-full bg-blue-500" id="lapDotIndicator"></span>
                <span id="currentLapStatusText">PUTARAN AKTIF: LAP 1 (0 - 50m)</span>
            </div>

            <!-- Digital Timer Main Numbers -->
            <div class="font-mono-timer text-6xl sm:text-7xl md:text-8xl font-black text-white tracking-tight my-2 drop-shadow-md select-none" id="displayMainTimer">
                00:00.00
            </div>

            <!-- Current Lap Stroke Counter Mini Badge -->
            <div class="flex items-center justify-center gap-3 mt-2 text-xs">
                <span class="text-slate-400">Waktu Kumulatif Lomba</span>
                <span class="text-slate-600">•</span>
                <span class="font-extrabold text-amber-400 bg-amber-950/60 border border-amber-800/60 px-2.5 py-0.5 rounded-full" id="activeLapStrokeBadge">
                    🏊 0 Kayuhan di Lap Ini
                </span>
            </div>

            <!-- 3 TOMBOL UTAMA (START/STOP, SPLIT, KAYUHAN) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mt-6">
                <!-- 1. Tombol START / STOP -->
                <button type="button" 
                        id="btnStartStop" 
                        onclick="toggleStartStop()" 
                        class="w-full py-4 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl font-black text-base uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" id="iconStartStop">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <span id="lblStartStop">MULAI (START)</span>
                </button>

                <!-- 2. Tombol SPLIT -->
                <button type="button" 
                        id="btnSplit" 
                        onclick="recordSplit()" 
                        class="w-full py-4 px-4 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white rounded-2xl font-black text-base uppercase tracking-wider shadow-lg shadow-blue-600/30 transition transform active:scale-95 flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="lblSplit">SPLIT (LAP 1)</span>
                </button>

                <!-- 3. Tombol KAYUHAN -->
                <div class="flex items-center gap-1.5">
                    <button type="button" 
                            id="btnStrokeAdd" 
                            onclick="incrementStroke()" 
                            class="flex-1 py-4 px-4 bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 rounded-2xl font-black text-base uppercase tracking-wider shadow-lg shadow-amber-500/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span id="lblStrokeCount">+1 KAYUHAN (<span id="currStrokeNum">0</span>)</span>
                    </button>
                    <!-- Undo Stroke (-1) -->
                    <button type="button" 
                            onclick="decrementStroke()" 
                            title="Kurangi 1 Kayuhan"
                            class="h-full px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-2xl border border-slate-700 font-bold transition flex items-center justify-center">
                        -1
                    </button>
                </div>
            </div>

            <!-- Secondary Actions: Reset & Sinkronkan -->
            <div class="flex items-center justify-between border-t border-slate-800 pt-4 mt-6">
                <button type="button" onclick="resetStopwatch()" class="text-xs font-bold text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Stopwatch</span>
                </button>

                <div class="text-[11px] text-slate-400 hidden sm:block">
                    Pintasan Keyboard: <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-700 rounded text-slate-300">Spasi</kbd> Start/Stop • <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-700 rounded text-slate-300">L</kbd> Split • <kbd class="px-1.5 py-0.5 bg-slate-800 border border-slate-700 rounded text-slate-300">K</kbd> Kayuhan
                </div>
            </div>

        </div>

        <!-- 3. TABEL REKAMAN WAKTU SPLIT & KAYUHAN -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 md:p-5 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5 mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Waktu Split & Kayuhan Terekam (<span id="recordedLapsCount">0</span> Lap)
                </h3>

                <button type="button" onclick="sendDataToRaceAnalysis(true)" class="text-xs font-bold text-blue-400 hover:text-blue-300 hover:underline flex items-center gap-1">
                    <span>🚀 Kirim ke Analisis Lomba</span>
                </button>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto rounded-xl border border-slate-800">
                <table class="w-full text-xs text-center" id="stopwatchRecordTable">
                    <thead class="bg-slate-800 text-slate-300 uppercase font-bold text-[11px] border-b border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">Lap</th>
                            <th class="py-2.5 px-3">Jarak</th>
                            <th class="py-2.5 px-3">Waktu Split (Lap)</th>
                            <th class="py-2.5 px-3">Total Kumulatif</th>
                            <th class="py-2.5 px-3">Kayuhan</th>
                            <th class="py-2.5 px-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300" id="stopwatchTableBody">
                        <tr id="emptyRecordRow">
                            <td colspan="6" class="py-6 text-slate-500 font-medium italic">
                                Belum ada putaran lap terekam. Tekan tombol <strong class="text-emerald-400">MULAI</strong> lalu <strong class="text-blue-400">SPLIT</strong> saat perenang menyelesaikan putaran lap.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- NOTIFIKASI TOAST -->
    <div id="toastNotification" class="fixed bottom-6 right-6 z-50 bg-emerald-600 text-white font-bold text-xs px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
        <span id="toastMsg">Tersinkronisasi!</span>
    </div>

    <!-- GLOBAL APP_URL & SCRIPT -->
    <script>window.APP_URL = "<?= getenv('APP_URL') ?>";</script>
    <script src="<?= getenv('APP_URL') ?>/js/tools/race_analysis/race_stopwatch.js"></script>

</body>
</html>
