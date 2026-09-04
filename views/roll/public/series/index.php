<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($series['hero_title'] ?: $series['series_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        :root { --brand-color: <?= htmlspecialchars($series['theme_color'] ?: '#2563eb') ?>; }
        .bg-brand { background-color: var(--brand-color); }
        .text-brand { color: var(--brand-color); }
        .border-brand { border-color: var(--brand-color); }
        
        .hero-bg {
            background: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.6)), 
                        url('<?= !empty($series['hero_slider_images']) ? getenv('APP_URL') . "/public/uploads/series/" . json_decode($series['hero_slider_images'])[0] : "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?q=80&w=2070" ?>') center/cover no-repeat;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand selection:text-white">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <?php if (!empty($series['logo_image'])): ?>
                    <img src="<?= getenv('APP_URL') ?>/public/uploads/series/<?= htmlspecialchars($series['logo_image']) ?>" alt="Logo" class="h-10 object-contain">
                <?php endif; ?>
                <span class="font-black text-xl tracking-tighter uppercase text-slate-900"><?= htmlspecialchars($series['series_name']) ?></span>
            </div>
            <div class="hidden md:flex gap-8 font-bold text-sm text-slate-600">
                <a href="#about" class="hover:text-brand transition">Tentang Series</a>
                <a href="#events" class="hover:text-brand transition">Daftar Event</a>
                <?php if ($series['show_standings'] && !empty($standings)): ?>
                    <a href="#standings" class="hover:text-brand transition">Klasemen</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-32 pb-20 md:pt-48 md:pb-32 hero-bg text-white overflow-hidden min-h-[60vh] flex items-center">
        <div class="max-w-7xl mx-auto px-6 relative z-10 w-full">
            <div class="max-w-3xl">
                <div class="inline-block px-4 py-1.5 rounded-full bg-brand/20 border border-brand/50 text-brand font-black text-xs uppercase tracking-widest mb-6 backdrop-blur-md">
                    Official Series Portal
                </div>
                <h1 class="text-5xl md:text-7xl font-black uppercase italic tracking-tighter leading-tight mb-6">
                    <?= htmlspecialchars($series['hero_title'] ?: $series['series_name']) ?>
                </h1>
                <?php if (!empty($series['hero_subtitle'])): ?>
                    <p class="text-lg md:text-xl text-slate-300 font-medium leading-relaxed max-w-2xl mb-10">
                        <?= nl2br(htmlspecialchars($series['hero_subtitle'])) ?>
                    </p>
                <?php endif; ?>
                <div class="flex flex-wrap gap-4">
                    <a href="#events" class="px-8 py-4 bg-brand text-white font-black uppercase tracking-widest text-sm rounded-xl shadow-lg hover:shadow-brand/50 transition transform hover:-translate-y-1">
                        Lihat Jadwal Event
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Promo Banner -->
    <?php if (!empty($series['promo_image'])): ?>
        <section class="py-12 bg-white border-b border-slate-100">
            <div class="max-w-5xl mx-auto px-6">
                <img src="<?= getenv('APP_URL') ?>/public/uploads/series/<?= htmlspecialchars($series['promo_image']) ?>" alt="Promo" class="w-full rounded-2xl shadow-xl">
            </div>
        </section>
    <?php endif; ?>

    <!-- About Section -->
    <?php if (!empty($series['about_text'])): ?>
        <section id="about" class="py-24 bg-white">
            <div class="max-w-3xl mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-black text-slate-800 uppercase italic tracking-tighter mb-8">Tentang Series Ini</h2>
                <div class="prose prose-lg prose-slate mx-auto text-slate-600 font-medium">
                    <?= nl2br(htmlspecialchars($series['about_text'])) ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Events Section -->
    <section id="events" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-black text-slate-800 uppercase italic tracking-tighter">Agenda Event</h2>
                <p class="text-slate-500 mt-4 font-medium">Daftar kejuaraan yang tergabung dalam seri ini.</p>
            </div>
            
            <?php if (empty($child_events)): ?>
                <div class="text-center p-12 bg-white rounded-2xl border border-slate-200">
                    <p class="text-slate-500 font-bold">Belum ada event yang dijadwalkan.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($child_events as $ev): ?>
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl border border-slate-100 overflow-hidden transition group flex flex-col">
                            <div class="h-32 bg-slate-900 flex items-center justify-center p-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-brand opacity-80 group-hover:opacity-100 transition"></div>
                                <h3 class="text-2xl font-black text-white uppercase italic tracking-tighter text-center relative z-10"><?= htmlspecialchars($ev['hero_title'] ?: $ev['event_name']) ?></h3>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-4 text-sm font-bold text-slate-500">
                                    <span>📅</span> <?= date('d M Y', strtotime($ev['event_date_start'])) ?>
                                </div>
                                <div class="mt-auto pt-6 border-t border-slate-100">
                                    <?php if (!empty($ev['landing_slug'])): ?>
                                        <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($ev['landing_slug']) ?>" class="block w-full py-3 bg-slate-100 text-slate-700 hover:bg-brand hover:text-white text-center font-black uppercase tracking-widest text-xs rounded-xl transition">
                                            Info Lengkap & Daftar
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="block w-full py-3 bg-slate-50 text-slate-400 text-center font-black uppercase tracking-widest text-xs rounded-xl cursor-not-allowed">
                                            Belum Dibuka
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Standings Section -->
    <?php if ($series['show_standings'] && !empty($standings)): ?>
        <section id="standings" class="py-24 bg-white border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-brand/10 text-brand font-black text-xs uppercase tracking-widest mb-4">
                        Klasemen Sementara
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-800 uppercase italic tracking-tighter">Series Standings</h2>
                    <p class="text-slate-500 mt-4 font-medium">Akumulasi perolehan medali dari seluruh event seri yang telah berlangsung.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Klasemen Klub -->
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-widest mb-6 flex items-center gap-3">
                            <span class="text-3xl">🛡️</span> Klasemen Klub
                        </h3>
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden shadow-inner">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-800 text-white">
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-12 text-center">#</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest">Klub</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-16 text-center text-yellow-400">🥇</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-16 text-center text-slate-300">🥈</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-16 text-center text-amber-600">🥉</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <?php $rank = 1; foreach (array_slice($standings, 0, 10) as $row): ?>
                                    <tr class="bg-white hover:bg-slate-50 transition">
                                        <td class="p-4 text-center font-black text-slate-400"><?= $rank++ ?></td>
                                        <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($row['club_name']) ?></td>
                                        <td class="p-4 text-center font-black text-slate-800 bg-yellow-50/50"><?= $row['gold'] ?></td>
                                        <td class="p-4 text-center font-black text-slate-800 bg-slate-50/50"><?= $row['silver'] ?></td>
                                        <td class="p-4 text-center font-black text-slate-800 bg-amber-50/50"><?= $row['bronze'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($standings) > 10): ?>
                            <p class="text-center text-xs text-slate-400 mt-4 italic">Menampilkan top 10 klub...</p>
                        <?php endif; ?>
                    </div>

                    <!-- Top Skaters -->
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-widest mb-6 flex items-center gap-3">
                            <span class="text-3xl">⭐</span> Pemain Terbaik
                        </h3>
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden shadow-inner">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-800 text-white">
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-12 text-center">#</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest">Atlet</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest text-center">Kategori</th>
                                        <th class="p-4 text-xs font-black uppercase tracking-widest w-16 text-center text-yellow-400">🥇</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <?php $rank = 1; foreach (array_slice($bestSkaters, 0, 10) as $row): ?>
                                    <tr class="bg-white hover:bg-slate-50 transition">
                                        <td class="p-4 text-center font-black text-slate-400"><?= $rank++ ?></td>
                                        <td class="p-4">
                                            <div class="font-bold text-slate-700"><?= htmlspecialchars($row['skater_name']) ?></div>
                                            <div class="text-[10px] text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($row['club_name']) ?></div>
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase tracking-widest whitespace-nowrap">
                                                <?= htmlspecialchars($row['class_name']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-center font-black text-slate-800 bg-yellow-50/50"><?= $row['gold'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($bestSkaters) > 10): ?>
                            <p class="text-center text-xs text-slate-400 mt-4 italic">Menampilkan top 10 pemain...</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-slate-900 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <?php if (!empty($series['logo_image'])): ?>
                <img src="<?= getenv('APP_URL') ?>/public/uploads/series/<?= htmlspecialchars($series['logo_image']) ?>" alt="Logo" class="h-12 object-contain mx-auto mb-6 opacity-50 hover:opacity-100 transition">
            <?php endif; ?>
            <p class="text-slate-500 text-sm font-medium">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($series['series_name']) ?>.<br>
                Powered by <a href="#" class="text-brand font-bold hover:underline">SET-SYSTEM</a>
            </p>
        </div>
    </footer>

</body>
</html>
