<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Result - <?= htmlspecialchars($event['event_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;0,900;1,400;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            letter-spacing: -0.01em;
        }
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-logo-item { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        #navbar.scrolled { background-color: rgba(11, 19, 41, 0.95); backdrop-filter: blur(12px); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5); border-color: rgba(30, 41, 59, 0.5); }
        
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0b1329; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    </style>
</head>
<body class="bg-[#0b1329] min-h-screen pb-24 text-slate-100">

   <nav id="navbar" class="fixed w-full z-50 top-0 start-0 transparent px-10">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/swim"><img src="<?= getenv('APP_URL') ?>/public/img/logo.png" class="h-24 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex justify-between items-center h-24 transition-all duration-300" id="nav-container">
                <div>
                    <a href="<?= getenv('APP_URL') ?>/swim/results" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-700/60 hover:border-blue-500 rounded-xl bg-slate-900/80 text-xs font-black text-slate-300 hover:text-white uppercase tracking-widest transition-all shadow-md">
                        &larr; Kembali
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 pt-32">
        
        <!-- HERO BANNER -->
        <div class="bg-gradient-to-br from-slate-900 via-[#111c44] to-[#0b1329] text-white p-6 sm:p-8 rounded-[2rem] shadow-2xl mb-8 relative overflow-hidden border border-slate-800/80">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.08),transparent_45%)]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-block px-3 py-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-md shadow-blue-500/20 animate-pulse">🔴 LIVE RESULT</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Hasil Resmi Akuatik</span>
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black uppercase italic leading-tight mb-2 tracking-tight text-white">
                        <?= htmlspecialchars($event['event_name']) ?>
                    </h1>
                    <p class="text-xs text-blue-300 font-bold uppercase tracking-widest flex items-center gap-x-4 gap-y-1 flex-wrap opacity-90">
                        <span class="flex items-center gap-1.5">📍 <?= htmlspecialchars($event['event_location']) ?><?= !empty($event['event_city']) ? ' - ' . htmlspecialchars($event['event_city']) : '' ?></span>
                        <span class="flex items-center gap-1.5">📅 <?= date('d F Y', strtotime($event['event_date_start'])) ?></span>
                    </p>
                </div>

                <?php if(!empty($event['logo_left'])): ?>
                    <div class="hidden md:block shrink-0 bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                        <img src="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $event['logo_left']), '/') ?>" alt="Logo Event" class="h-14 w-14 object-contain">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DOKUMEN REKAPITULASI (Jika ada) -->
        <?php if (!empty($documents)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <?php foreach ($documents as $doc): ?>
            <div class="bg-slate-900 rounded-2xl border border-blue-500/30 shadow-xl p-5 flex items-center justify-between transition-all hover:border-blue-500/60">
                <div>
                    <div class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1"><?= htmlspecialchars(str_replace('_', ' ', $doc['kategori'] ?? 'DOKUMEN')) ?></div>
                    <div class="text-sm font-black text-white">📄 <?= htmlspecialchars($doc['judul_file'] ?? 'Dokumen Perlombaan') ?></div>
                </div>
                <a href="<?= getenv('APP_URL') ?>/<?= ltrim(str_replace('public/', '', $doc['file_path']), '/') ?>" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-colors shadow-md flex items-center gap-1.5">
                    <span>Unduh PDF</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- KOTAK PENCARIAN REALTIME -->
        <div class="bg-slate-900/80 backdrop-blur p-2 rounded-full shadow-2xl border border-slate-800 mb-8 flex items-center gap-2 sticky top-24 z-40 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-950/50 transition-all">
            <span class="text-lg ml-4 opacity-40">🔍</span>
            <input type="text" id="searchInput" placeholder="Cari nomor acara, gaya, atau kategori..." class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm font-bold text-slate-100 uppercase placeholder:text-slate-500 placeholder:normal-case placeholder:font-medium py-2">
        </div>

        <?php if (empty($publishedNumbers)): ?>
            <div class="bg-slate-900/60 p-12 text-center rounded-3xl border border-slate-800 shadow-xl">
                <span class="text-5xl block mb-5 opacity-20">⏳</span>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Hasil Belum Tersedia</p>
                <p class="text-[10px] text-slate-500 mt-2">Panitia belum mempublikasikan hasil resmi untuk nomor perlombaan di event ini.</p>
            </div>
        <?php else: ?>
            <div id="resultContainer" class="space-y-4">
                <?php foreach ($publishedNumbers as $num): 
                    $genderLabel = (strtoupper($num['jenis_kelamin'] ?? '') === 'L') ? 'PUTRA' : ((strtoupper($num['jenis_kelamin'] ?? '') === 'P') ? 'PUTRI' : 'CAMPURAN');
                    $acaraName = "ACARA #{$num['event_number']} - {$num['distance']}M {$num['stroke']} - {$num['age_group']} {$genderLabel}";
                    $rankModeLabel = ($num['rank_mode'] ?? 'split') === 'overall' ? 'OVERALL' : 'SPLIT KU';
                ?>
                    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden result-card transition-all duration-300 hover:border-slate-700" data-search="<?= strtolower(htmlspecialchars($acaraName)) ?>">
                        <div class="w-full flex flex-col md:flex-row md:items-center justify-between px-5 sm:px-6 py-4 bg-gradient-to-r from-slate-800/40 to-slate-800/5 text-left transition-colors group gap-4">
                            <div class="flex items-center gap-3">
                                <div class="shrink-0 w-12 h-12 rounded-xl bg-blue-600/20 border border-blue-500/30 text-blue-400 flex flex-col items-center justify-center font-black">
                                    <span class="text-[8px] uppercase tracking-wider text-slate-400">Acara</span>
                                    <span class="text-base leading-tight">#<?= htmlspecialchars($num['event_number']) ?></span>
                                </div>
                                <div>
                                    <h2 class="text-xs sm:text-sm font-black text-white uppercase italic tracking-tight group-hover:text-blue-300 transition-colors">
                                        <?= htmlspecialchars($num['distance']) ?>M <?= htmlspecialchars($num['stroke']) ?>
                                    </h2>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800 px-2 py-0.5 rounded border border-slate-700">
                                            <?= htmlspecialchars($num['age_group'] ?? 'OPEN') ?> • <?= $genderLabel ?>
                                        </span>
                                        <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-wider bg-indigo-950/50 px-2 py-0.5 rounded border border-indigo-900">
                                            <?= $rankModeLabel ?>
                                        </span>
                                        <?php if (!empty($num['count_results'])): ?>
                                            <span class="text-[9px] font-bold text-emerald-400 bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-900">
                                                ✅ <?= $num['count_results'] ?> Atlet
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                <a href="<?= getenv('APP_URL') ?>/swim/live_result/view/<?= $num['id'] ?>" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:from-blue-500 hover:to-indigo-500 transition-all flex items-center gap-1.5 whitespace-nowrap shadow-md shadow-blue-600/20">
                                    <span>⚡</span> Lihat Hasil Resmi (A4)
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Realtime Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();
                const cards = document.querySelectorAll('.result-card');
                
                cards.forEach(card => {
                    const searchData = card.getAttribute('data-search') || '';
                    if (searchData.includes(keyword)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
        
        // Navbar Scroll Effect
        const navbar = document.getElementById('navbar');
        const navContainer = document.getElementById('nav-container');
        window.addEventListener('scroll', () => { 
            if(window.scrollY > 50) { 
                navbar.classList.add('scrolled'); 
                if(navContainer) navContainer.classList.replace('h-24', 'h-16');
            } else { 
                navbar.classList.remove('scrolled'); 
                if(navContainer) navContainer.classList.replace('h-16', 'h-24');
            }
        });
    </script>
</body>
</html>
