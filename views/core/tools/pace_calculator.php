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
                size: A4 portrait;
                margin: 0mm; /* Memaksa browser menghapus margin default */
            }
            
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            
            #print-container {
                /* Kita gunakan padding di container sebagai pengganti margin halaman */
                padding-top: 10mm !important;
                padding-bottom: 10mm !important;
                padding-left: 10mm !important;
                padding-right: 10mm !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #navbar { display: none !important; }
            
            main { padding: 0 !important; margin: 0 !important; }
            .max-w-4xl { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .rounded-3xl { border: none !important; box-shadow: none !important; padding: 0 !important; }
            
            .excel-table { width: 100%; border-collapse: collapse; margin-top: 0; font-size: 13px; }
            .excel-table th, .excel-table td { border: 1px solid #000; padding: 6px 8px; color: #000; }
            .excel-table .title-row th { font-size: 20px; border: none !important; padding-bottom: 20px; }
            .excel-table .thick-bottom { border-bottom: 2px solid #000 !important; }
            .excel-table .thick-top { border-top: 2px solid #000 !important; }
            .excel-table td[colspan="4"], .excel-table td[colspan="3"] { border: none !important; padding: 4px 8px; }
            .text-red { color: red !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR -->
    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 px-6 md:px-10 print-hidden">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/"><img src="<?= getenv('APP_URL') ?>/img/logo.png" class="h-12 md:h-14 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
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

    <!-- CONTENT -->
    <main class="flex-grow pt-32 pb-20 px-4 md:px-6">
        
        <div class="max-w-4xl mx-auto">
            
            <!-- Card Wrapper -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 md:p-10 border border-slate-100">
                
                <div class="text-center mb-10 print-hidden">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-2 relative inline-block">
                        Pace Chart
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <p class="text-slate-500 font-bold mt-4 tracking-widest uppercase text-sm">Generator Pace Training</p>
                </div>

                <!-- STEP 1: INPUT SECTION -->
                <div id="input-section" class="print-hidden">
                    <div class="space-y-8">
                        
                        <!-- Nama & DOB -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Perenang</label>
                                <input type="text" id="swimmerName" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Masukkan nama perenang">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                                <div class="flex gap-3">
                                    <input type="text" id="dob" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="DD/MM/YYYY" maxlength="10">
                                    <span id="ageDisplay" class="flex-shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl px-4 py-3 font-bold text-sm flex items-center justify-center min-w-[120px]">
                                        0Y 0M 0D
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 py-2">
                            <div class="h-px bg-slate-200 flex-grow"></div>
                            <span class="text-xs font-black tracking-widest text-slate-400 uppercase">Isi salah satu (200m / 150m)</span>
                            <div class="h-px bg-slate-200 flex-grow"></div>
                        </div>

                        <!-- 200m -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-3">Waktu Tes 200 Meter</label>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-4">
                                    <input type="text" id="time200" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="00.00.00">
                                </div>
                                <div class="md:col-span-5">
                                    <select id="style200" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                        <option value="-"> - Pilih Gaya - </option>
                                        <option value="Bebas">Bebas</option>
                                        <option value="Kupu-Kupu">Kupu-Kupu</option>
                                        <option value="Punggung">Punggung</option>
                                        <option value="Dada">Dada</option>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <div id="vel200" class="w-full h-full min-h-[48px] bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">0.00 m/s</div>
                                </div>
                            </div>
                        </div>

                        <!-- 150m -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-3">Waktu Tes 150 Meter</label>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-4">
                                    <input type="text" id="time150" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="00.00.00">
                                </div>
                                <div class="md:col-span-5">
                                    <select id="style150" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                        <option value="-"> - Pilih Gaya - </option>
                                        <option value="Bebas">Bebas</option>
                                        <option value="Kupu-Kupu">Kupu-Kupu</option>
                                        <option value="Punggung">Punggung</option>
                                        <option value="Dada">Dada</option>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <div id="vel150" class="w-full h-full min-h-[48px] bg-blue-600 text-white rounded-xl flex items-center justify-center font-bold text-sm shadow-inner">0.00 m/s</div>
                                </div>
                            </div>
                        </div>

                        <button onclick="generateChart()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-sm tracking-widest uppercase py-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
                            BUAT CHART
                        </button>
                    </div>
                </div>

                <!-- STEP 2: OUTPUT SECTION -->
                <div id="output-section" style="display: none;">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4 print-hidden">
                        <h4 class="font-teko text-3xl font-black uppercase tracking-wide text-blue-900">Hasil Kalkulasi</h4>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button onclick="resetForm()" class="flex-1 sm:flex-none px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs uppercase tracking-widest rounded-lg transition">Edit Data</button>
                            <button onclick="printChart()" class="flex-1 sm:flex-none px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-widest rounded-lg shadow-lg shadow-emerald-500/30 transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg>
                                Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- Info Panel (Tailwind) -->
                    <div class="bg-blue-50/50 border-l-4 border-blue-500 rounded-r-xl p-6 mb-8 print-hidden" id="infoPanel">
                        <!-- Injected via JS -->
                    </div>

                    <!-- Table (Tailwind) -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm print-hidden mb-8">
                        <table class="w-full text-sm text-center" id="chartTable">
                            <thead class="bg-blue-900 text-white font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-4 px-3">Sistem Energi</th>
                                    <th class="py-4 px-3">HR (bpm)</th>
                                    <th class="py-4 px-3">% Speed</th>
                                    <th class="py-4 px-3">50m (110%)</th>
                                    <th class="py-4 px-3">100m (105%)</th>
                                    <th class="py-4 px-3">150m (100%)</th>
                                    <th class="py-4 px-3">200m (100%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 bg-white">
                                <!-- Rows generated by JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <button onclick="resetForm()" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-black text-xs tracking-widest uppercase py-4 rounded-xl shadow-lg transition print-hidden">
                        BUAT DATA BARU
                    </button>
                </div>
                
            </div>
        </div>

        <!-- Dedicated Print Container (Hidden on screen, visible on print) -->
        <div id="print-container" class="hidden print-visible w-full bg-white text-black"></div>
        
    </main>

    <!-- FOOTER -->
    <footer class="bg-slate-950 border-t border-slate-900 py-8 text-center print-hidden mt-auto">
        <p class="text-slate-600 text-[10px] font-black tracking-[0.3em] uppercase">&copy; <?= date('Y') ?> SET SYSTEM. All Rights Reserved.</p>
    </footer>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    
    <!-- Pace Calculator Logic -->
    <script>
        const intensities = [
            { name: "Recovery", percent: 75, type: "aerobic" },
            { name: "EN1", percent: 76, type: "aerobic" },
            { name: "EN1", percent: 80, type: "aerobic" },
            { name: "EN2", percent: 81, type: "aerobic" },
            { name: "EN2", percent: 85, type: "aerobic" },
            { name: "AT", percent: 86, type: "aerobic" },
            { name: "AT", percent: 89, type: "aerobic" },
            { name: "VO2MAX", percent: 90, type: "anaerobic" },
            { name: "VO2MAX", percent: 92, type: "anaerobic" },
            { name: "LP", percent: 93, type: "anaerobic" },
            { name: "LP", percent: 94, type: "anaerobic" },
            { name: "LT", percent: 95, type: "anaerobic" },
            { name: "LT", percent: 96, type: "anaerobic" },
            { name: "PL", percent: 97, type: "anaerobic" },
            { name: "PL", percent: 100, type: "anaerobic" }
        ];

        function calculateAge(dobString) {
            if (!dobString || dobString.length !== 10) return { y: 0, m: 0, d: 0, totalYears: 0 };
            const parts = dobString.split('/');
            if (parts.length !== 3) return { y: 0, m: 0, d: 0, totalYears: 0 };

            const dStr = parseInt(parts[0], 10);
            const mStr = parseInt(parts[1], 10) - 1; 
            const yStr = parseInt(parts[2], 10);

            const dob = new Date(yStr, mStr, dStr);
            const today = new Date();

            let y = today.getFullYear() - dob.getFullYear();
            let m = today.getMonth() - dob.getMonth();
            let d = today.getDate() - dob.getDate();

            if (d < 0) {
                m--;
                d += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
            }
            if (m < 0) {
                y--;
                m += 12;
            }

            const diff_ms = Date.now() - dob.getTime();
            const age_dt = new Date(diff_ms);
            const totalYears = Math.abs(age_dt.getUTCFullYear() - 1970);

            return { y, m, d, totalYears };
        }

        document.addEventListener('DOMContentLoaded', () => {
            flatpickr("#dob", {
                dateFormat: "d/m/Y",
                locale: "id",
                allowInput: true,
                onChange: function (selectedDates, dateStr, instance) {
                    if (dateStr.length === 10) {
                        const age = calculateAge(dateStr);
                        if (age.totalYears > 0 || age.y > 0 || age.m > 0 || age.d > 0) {
                            document.getElementById('ageDisplay').innerText = `${age.y}Thn ${age.m}Bln ${age.d}Hr`;
                        } else {
                            document.getElementById('ageDisplay').innerText = `0Thn 0Bln 0Hr`;
                        }
                    } else {
                        document.getElementById('ageDisplay').innerText = `0Thn 0Bln 0Hr`;
                    }
                }
            });

            document.getElementById('dob').addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, ''); 
                if (val.length > 8) val = val.substring(0, 8); 

                let formatted = '';
                if (val.length > 0) formatted += val.substring(0, 2);
                if (val.length > 2) formatted += '/' + val.substring(2, 4);
                if (val.length > 4) formatted += '/' + val.substring(4, 8);

                e.target.value = formatted;

                if (formatted.length === 10) {
                    const age = calculateAge(formatted);
                    if (age.totalYears > 0 || age.y > 0 || age.m > 0 || age.d > 0) {
                        document.getElementById('ageDisplay').innerText = `${age.y}Thn ${age.m}Bln ${age.d}Hr`;
                    } else {
                        document.getElementById('ageDisplay').innerText = `0Thn 0Bln 0Hr`;
                    }
                } else {
                    document.getElementById('ageDisplay').innerText = `0Thn 0Bln 0Hr`;
                }
            });

            const formatInputAndCalcVel = (e, distance) => {
                let val = e.target.value.replace(/\D/g, ''); 
                if (val.length > 6) val = val.substring(0, 6); 

                let formatted = '';
                if (val.length > 0) formatted += val.substring(0, 2);
                if (val.length > 2) formatted += '.' + val.substring(2, 4);
                if (val.length > 4) formatted += '.' + val.substring(4, 6);

                e.target.value = formatted;

                let m = 0, s = 0, ms = 0;
                if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
                if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
                if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);

                const totalSeconds = (m * 60) + s + (ms / 100);
                const velSpan = document.getElementById(distance === 200 ? 'vel200' : 'vel150');

                if (totalSeconds > 0) {
                    const vBase = distance / totalSeconds;
                    velSpan.innerText = vBase.toFixed(3) + ' m/s';
                } else {
                    velSpan.innerText = '0.00 m/s';
                }
            };

            document.getElementById('time200').addEventListener('input', (e) => formatInputAndCalcVel(e, 200));
            document.getElementById('time150').addEventListener('input', (e) => formatInputAndCalcVel(e, 150));
        });

        function formatTime(totalSeconds) {
            let mins = Math.floor(totalSeconds / 60);
            let secs = Math.floor(totalSeconds % 60);
            let ms = Math.round((totalSeconds % 1) * 100);

            if (ms === 100) { ms = 0; secs++; }
            if (secs === 60) { secs = 0; mins++; }

            let minStr = mins.toString().padStart(2, '0');
            let secStr = secs.toString().padStart(2, '0');
            let msStr = ms.toString().padStart(2, '0');

            return `${minStr}.${secStr},${msStr}`;
        }

        function generateChart() {
            const name = document.getElementById('swimmerName').value || "Tanpa Nama";
            const dobInput = document.getElementById('dob').value;
            const time200Val = document.getElementById('time200').value.replace(/\D/g, '');
            const time150Val = document.getElementById('time150').value.replace(/\D/g, '');
            const style200 = document.getElementById('style200').value;
            const style150 = document.getElementById('style150').value;

            const parseVal = (val) => {
                let m = 0, s = 0, ms = 0;
                if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
                if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
                if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);
                return (m * 60) + s + (ms / 100);
            };

            const totalSeconds200 = parseVal(time200Val);
            const totalSeconds150 = parseVal(time150Val);

            if (totalSeconds200 === 0 && totalSeconds150 === 0) {
                alert("Harap masukkan waktu tes 200m atau 150m!");
                return;
            }

            const ageObj = calculateAge(dobInput);
            const age = ageObj.totalYears;
            const maxHR = 220 - age; 

            let vBase = 0;
            let testInfo = "";
            let selectedStyle = "";

            if (totalSeconds200 > 0) {
                vBase = 200 / totalSeconds200; 
                testInfo = `200m: ${formatTime(totalSeconds200)}`;
                selectedStyle = style200;
            } else {
                vBase = 150 / totalSeconds150; 
                testInfo = `150m: ${formatTime(totalSeconds150)}`;
                selectedStyle = style150;
            }

            const todayStr = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const totalDistance = totalSeconds200 > 0 ? 200 : 150;
            const totalTimeSecs = totalSeconds200 > 0 ? totalSeconds200 : totalSeconds150;
            const formattedTime = formatTime(totalTimeSecs);

            // Tailwind Info Panel
            const infoHtml = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 font-semibold text-sm text-slate-800">
                    <div class="space-y-2">
                        <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Nama Perenang</div><div class="w-4">:</div><div class="flex-1">${name}</div></div>
                        <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Tanggal Lahir</div><div class="w-4">:</div><div class="flex-1">${dobInput || '-'}</div></div>
                        <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Umur</div><div class="w-4">:</div><div class="flex-1">${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln ' + ageObj.d + ' Hr' : '0 Thn 0 Bln 0 Hr'}</div></div>
                        <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Tanggal Dibuat</div><div class="w-4">:</div><div class="flex-1">${todayStr}</div></div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Gaya Renang</div><div class="w-4">:</div><div class="flex-1">${selectedStyle !== '-' ? selectedStyle : '-'}</div></div>
                        <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Jarak (meter)</div><div class="w-4">:</div><div class="flex-1">${totalDistance}</div></div>
                        <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Waktu (detik)</div><div class="w-4">:</div><div class="flex-1 text-red-600 font-bold">${totalTimeSecs.toFixed(2).replace('.', ',')} &nbsp;&nbsp; ${formattedTime.replace('.', ':')}</div></div>
                        <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Kecepatan (m/s)</div><div class="w-4">:</div><div class="flex-1">${vBase.toFixed(9).replace('.', ',')}</div></div>
                    </div>
                </div>
            `;
            document.getElementById('infoPanel').innerHTML = infoHtml;

            const tbody = document.querySelector('#chartTable tbody');
            tbody.innerHTML = ''; 

            intensities.forEach(zone => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50 transition";
                const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
                const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
                const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
                const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
                const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));

                tr.innerHTML = `
                <td class="py-3 px-3">${zone.name}</td>
                <td class="py-3 px-3">${estimatedHR}</td>
                <td class="py-3 px-3">${zone.percent}%</td>
                <td class="py-3 px-3">${formatTime(t50)}</td>
                <td class="py-3 px-3">${formatTime(t100)}</td>
                <td class="py-3 px-3">${formatTime(t150)}</td>
                <td class="py-3 px-3">${formatTime(t200)}</td>
                `;
                tbody.appendChild(tr);
            });

            // Format PDF (Print view)
            let printHtml = `
                <table class="excel-table">
                    <tr class="title-row">
                        <th colspan="7" style="text-align:center; font-weight:bold; font-size: 20px; padding-bottom: 20px;">TRAINING PACE CHART</th>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Nama Perenang : ${name}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Gaya Renang : ${selectedStyle !== '-' ? selectedStyle : '-'}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Tanggal Lahir : ${dobInput || '-'}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Total Jarak (meter) : ${totalDistance}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Umur : ${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln ' + ageObj.d + ' Hr' : '0 Thn 0 Bln 0 Hr'}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Total Waktu (detik) : <span class="text-red">${totalTimeSecs.toFixed(2).replace('.', ',')} &nbsp;&nbsp; ${formattedTime.replace('.', ':')}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Tanggal Dibuat : ${todayStr}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Kecepatan (m/s) : ${vBase.toFixed(9).replace('.', ',')}</td>
                    </tr>
                    
                    <tr class="thick-top">
                        <th rowspan="2" style="text-align:center;">Sistem Energi</th>
                        <th rowspan="2" style="text-align:center;">Estimasi Denyut Nadi<br>(per menit)</th>
                        <th rowspan="2" style="text-align:center;">% Kecepatan Test</th>
                        <th style="text-align:center; font-weight:bold;">50 meter</th>
                        <th style="text-align:center; font-weight:bold;">100 meter</th>
                        <th style="text-align:center; font-weight:bold;">150 meter</th>
                        <th style="text-align:center; font-weight:bold;">200 meter</th>
                    </tr>
                    <tr class="thick-bottom">
                        <th style="text-align:center;">110%</th>
                        <th style="text-align:center;">105%</th>
                        <th style="text-align:center;">100%</th>
                        <th style="text-align:center;">100%</th>
                    </tr>
                    
                    <tr>
                        <th colspan="7" style="text-align:center; font-weight:bold; padding-top: 10px;">Zona Latihan Aerobik</th>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">AEROBIC</td>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">0-170</td>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">80%-89%</td>
                        <td colspan="4" style="font-weight:bold; text-align:center; background:#f0f0f0;">Detik</td>
                    </tr>
            `;
            
            intensities.filter(z => z.type === 'aerobic').forEach(zone => {
                const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
                const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
                const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
                const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
                const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
                
                printHtml += `
                    <tr>
                        <td style="text-align:center;">${zone.name}</td>
                        <td style="text-align:center;">${estimatedHR}</td>
                        <td style="text-align:center;">${zone.percent}%</td>
                        <td style="text-align:center;">${formatTime(t50)}</td>
                        <td style="text-align:center;">${formatTime(t100)}</td>
                        <td style="text-align:center;">${formatTime(t150)}</td>
                        <td style="text-align:center;">${formatTime(t200)}</td>
                    </tr>
                `;
            });
            
            printHtml += `
                    <tr class="thick-top">
                        <th colspan="7" style="text-align:center; font-weight:bold; padding-top: 10px;">Zona Latihan Anaerobik</th>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">ANAEROBIC</td>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">&gt; 170</td>
                        <td style="font-weight:bold; text-align:center; background:#f0f0f0;">90%-100%</td>
                        <td colspan="4" style="font-weight:bold; text-align:center; background:#f0f0f0;">Detik</td>
                    </tr>
            `;

            intensities.filter(z => z.type === 'anaerobic').forEach(zone => {
                const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
                const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
                const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
                const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
                const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
                
                printHtml += `
                    <tr>
                        <td style="text-align:center;">${zone.name}</td>
                        <td style="text-align:center;">${estimatedHR}</td>
                        <td style="text-align:center;">${zone.percent}%</td>
                        <td style="text-align:center;">${formatTime(t50)}</td>
                        <td style="text-align:center;">${formatTime(t100)}</td>
                        <td style="text-align:center;">${formatTime(t150)}</td>
                        <td style="text-align:center;">${formatTime(t200)}</td>
                    </tr>
                `;
            });

            printHtml += `</table>`;
            document.getElementById('print-container').innerHTML = printHtml;

            document.getElementById('input-section').style.display = 'none';
            document.getElementById('output-section').style.display = 'block';
            window.scrollTo(0,0);
        }

        function resetForm() {
            document.getElementById('input-section').style.display = 'block';
            document.getElementById('output-section').style.display = 'none';
            document.querySelector('#chartTable tbody').innerHTML = '';
        }

        function printChart() {
            const originalTitle = document.title;
            const nameInput = document.getElementById('swimmerName');
            let name = "Tanpa_Nama";
            if (nameInput && nameInput.value.trim() !== "") {
                name = nameInput.value.trim();
            }
            
            const dateObj = new Date();
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            
            // Format: "Nama Perenang _Training Chart_Tanggal"
            document.title = `${name} _Training Chart_${dateStr}`;
            
            window.print();
            
            // Restore title after print dialog
            setTimeout(() => {
                document.title = originalTitle;
            }, 1000);
        }
    </script>
</body>
</html>
