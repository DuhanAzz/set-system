<?php
$heroTitle    = $event['event_name'] ?? 'LIVE RESULT';
$siteDesc     = $s['site_description'] ?? 'Platform manajemen lomba sepatu roda modern.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Result - <?= htmlspecialchars($heroTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;0,900;1,400;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            letter-spacing: -0.01em;
        }
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-logo-item { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        #navbar.scrolled { background-color: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5); border-color: rgba(30, 41, 59, 0.5); }
        
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }

        /* Style animasi rotasi panah akordion */
        .accordion-arrow { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-[#0f172a] min-h-screen pb-24 text-slate-100">

   <nav id="navbar" class="fixed w-full z-50 top-0 start-0 transparent px-10">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/roll"><img src="<?= getenv('APP_URL') ?>/img/logo.png" onerror="this.src='https://ui-avatars.com/api/?name=SET&background=f97316&color=fff'" class="h-24 w-auto object-contain transition-all duration-300" id="nav-logo"></a>
            
            <div class="flex justify-between items-center h-24 transition-all duration-300" id="nav-container">
                <div>
                    <a href="<?= getenv('APP_URL') ?>/roll/results" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-700/60 hover:border-orange-500 rounded-xl bg-slate-900/80 text-xs font-black text-slate-300 hover:text-white uppercase tracking-widest transition-all shadow-md">
                        &larr; Kembali
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 pt-32">
        
        <div class="bg-gradient-to-br from-slate-900 via-[#1a1210] to-[#0f172a] text-white p-6 sm:p-8 rounded-[2rem] shadow-2xl mb-8 relative overflow-hidden border border-slate-800/80">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.08),transparent_45%)]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-block px-3 py-1 bg-gradient-to-r from-orange-500 to-red-600 text-white text-[9px] font-black uppercase tracking-widest rounded-full shadow-md shadow-orange-500/20 animate-pulse">🔴 LIVE RESULT</span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Hasil Resmi</span>
                    </div>
                    <h1 class="text-xl sm:text-3xl font-black uppercase italic leading-tight mb-2 tracking-tight text-white">
                        <?= htmlspecialchars($heroTitle) ?>
                    </h1>
                    <p class="text-xs text-orange-300 font-bold uppercase tracking-widest flex items-center gap-x-4 gap-y-1 flex-wrap opacity-90">
                        <span class="flex items-center gap-1.5">📍 <?= htmlspecialchars($event['event_location']) ?><?= !empty($event['event_city']) ? ' - ' . htmlspecialchars($event['event_city']) : '' ?></span>
                        <span class="flex items-center gap-1.5">📅 <?= date('d F Y', strtotime($event['event_date_start'])) ?></span>
                    </p>
                </div>

                <?php if(!empty($event['logo_left'])): ?>
                    <div class="hidden md:block shrink-0 bg-slate-950/40 p-3 rounded-2xl border border-slate-800/60">
                        <img src="<?= getenv('APP_URL') . '/public/' . htmlspecialchars(ltrim($event['logo_left'], '/')) ?>" alt="Logo Event" class="h-14 w-14 object-contain">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-slate-900/80 backdrop-blur p-2 rounded-full shadow-2xl border border-slate-800 mb-8 flex items-center gap-2 sticky top-24 z-40 focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-950/50 transition-all">
            <span class="text-lg ml-4 opacity-40">🔍</span>
            <input type="text" id="searchInput" placeholder="Cari nama atlet atau tim..." class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm font-bold text-slate-100 uppercase placeholder:text-slate-500 placeholder:normal-case placeholder:font-medium py-2">
        </div>

        <?php if (empty($groupedResults)): ?>
            <div class="bg-slate-900/60 p-12 text-center rounded-3xl border border-slate-800 shadow-xl">
                <span class="text-5xl block mb-5 opacity-20">⏳</span>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Hasil Belum Tersedia</p>
                <p class="text-[10px] text-slate-500 mt-2">Panitia belum mengunggah waktu finish perlombaan di event ini.</p>
            </div>
        <?php else: ?>
            <div id="resultContainer" class="space-y-4">
                <?php foreach ($groupedResults as $heatName => $atletList): ?>
                    
                    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden result-card transition-all duration-300 hover:border-slate-700">
                        
                        <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-5 sm:px-6 py-4 bg-gradient-to-r from-slate-800/40 to-slate-800/5 text-left focus:outline-none transition-colors hover:bg-slate-800/60 group">
                            <h2 class="text-xs sm:text-sm font-black text-white uppercase italic tracking-tight flex items-center gap-3">
                                <span class="w-1.5 h-3 bg-orange-500 rounded-full block group-hover:bg-orange-400 transition-colors"></span>
                                <?= htmlspecialchars($heatName) ?>
                            </h2>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-white accordion-arrow transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div class="accordion-body hidden border-t border-slate-800/60 transition-all duration-300">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[650px]">
                                    <thead>
                                        <tr class="bg-slate-950/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">
                                            <th class="py-4 px-4 w-14 text-center">Rank</th>
                                            <th class="py-4 px-4">Nama Skater</th>
                                            <th class="py-4 px-4">Klub / Tim</th>
                                            <th class="py-4 px-4 text-right w-28 text-slate-500">Wkt. Final</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/40">
                                        <?php foreach ($atletList as $atlet): ?>
                                            <?php
                                            $rankBadge = $atlet['rank'] ?? '-';
                                            $rankClass = 'text-slate-500';
                                            if ($rankBadge == 1) { $rankBadge = '🥇 1'; $rankClass = 'text-amber-400'; }
                                            elseif ($rankBadge == 2) { $rankBadge = '🥈 2'; $rankClass = 'text-slate-300'; }
                                            elseif ($rankBadge == 3) { $rankBadge = '🥉 3'; $rankClass = 'text-orange-400'; }
                                            ?>
                                        <tr class="searchable-row hover:bg-slate-800/30 transition-colors">
                                            <td class="py-3.5 px-4 text-center text-xs font-bold <?= $rankClass ?>">
                                                <?= $rankBadge ?>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <span class="text-xs sm:text-sm font-extrabold uppercase athlete-name text-slate-100 tracking-tight">
                                                    <?= htmlspecialchars($atlet['skater_name'] ?? '-') ?>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-[10px] font-bold uppercase tracking-widest team-name text-slate-400">
                                                <?= htmlspecialchars($atlet['club_name'] ?: '-') ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-right font-mono text-sm font-black text-white">
                                                <?= htmlspecialchars($atlet['time'] ?? '-') ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Pencarian Atlet
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase();
                const cards = document.querySelectorAll('.result-card');
                
                cards.forEach(card => {
                    let hasMatch = false;
                    const rows = card.querySelectorAll('.searchable-row');
                    
                    rows.forEach(row => {
                        const name = row.querySelector('.athlete-name').innerText.toLowerCase();
                        const team = row.querySelector('.team-name').innerText.toLowerCase();
                        if (name.includes(keyword) || team.includes(keyword)) {
                            row.style.display = '';
                            hasMatch = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Tampilkan atau sembunyikan akordion jika ada yang cocok
                    if (hasMatch) {
                        card.style.display = '';
                        if (keyword !== '') {
                            // Expand otomatis jika ada pencarian
                            const body = card.querySelector('.accordion-body');
                            const arrow = card.querySelector('.accordion-arrow');
                            body.classList.remove('hidden');
                            arrow.classList.add('rotate-180');
                        }
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Accordion Toggle
        function toggleAccordion(btn) {
            const body = btn.nextElementSibling;
            const arrow = btn.querySelector('.accordion-arrow');
            if (body.classList.contains('hidden')) {
                body.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                body.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
        
        // Expand akordion pertama secara default jika ada
        window.addEventListener('load', () => {
            const firstAccordion = document.querySelector('.result-card button');
            if(firstAccordion) {
                toggleAccordion(firstAccordion);
            }
        });

        // NAVBAR SCROLL EFFECT
        const navbar = document.getElementById('navbar');
        const logo = document.getElementById('nav-logo');
        window.addEventListener('scroll', () => { 
            if(window.scrollY > 20) { 
                navbar.classList.add('scrolled'); 
                if(logo) logo.classList.replace('h-24', 'h-16'); 
            } else { 
                navbar.classList.remove('scrolled'); 
                if(logo) logo.classList.replace('h-16', 'h-24'); 
            }
        });
    </script>
</body>
</html>
