<?php
$heroTitle    = $s['hero_title'] ?? 'SET ROLL CHAMPIONSHIP'; 
$appName      = $s['app_name'] ?? 'SET Roll System';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regulasi Kompetisi - <?= htmlspecialchars($heroTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #navbar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); height: 110px; display: flex; align-items: center; }
        #navbar.scrolled { background-color: #0F172A; height: 85px; border-bottom: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .nav-link { position: relative; color: white; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
        .nav-link::after { content: ''; position: absolute; width: 0; height: 3px; bottom: -8px; left: 0; background-color: #f97316; transition: width 0.3s ease; }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }
        .nav-link:hover { color: #f97316; }
        
        .page-header { background-image: url('https://images.unsplash.com/photo-1506141381389-13019318b2c2?q=80&w=2070&auto=format&fit=crop'); background-size: cover; background-position: center; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <nav id="navbar" class="fixed w-full z-50 top-0 start-0 bg-[#0F172A] border-b border-slate-800 px-10 h-[85px] flex items-center">
        <div class="max-w-screen-2xl flex items-center justify-between mx-auto w-full">
            <a href="<?= getenv('APP_URL') ?>/roll" class="text-3xl font-black text-white italic tracking-tighter uppercase"><?= htmlspecialchars($appName) ?></a>
            
            <div class="flex items-center gap-12">
                <div class="hidden lg:flex items-center space-x-10">
                    <a href="<?= getenv('APP_URL') ?>/roll#home" class="nav-link">Home</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/events" class="nav-link">Jadwal</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/results" class="nav-link">Hasil</a> 
                    <a href="<?= getenv('APP_URL') ?>/roll/records" class="nav-link">Rekor Nasional</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/rules" class="nav-link active text-orange-400">Regulasi</a>
                </div>
                <div class="hidden lg:flex items-center border-l border-white/20 pl-10">
                    <a href="<?= getenv('APP_URL') ?>/roll/login" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-3 rounded-full font-black text-xs uppercase tracking-widest shadow-xl transition transform hover:scale-105">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-header relative pt-40 pb-24 mt-[85px]">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>
        <div class="relative max-w-7xl mx-auto px-10 text-center z-10">
            <h1 class="text-5xl md:text-7xl font-black text-white uppercase italic tracking-tighter drop-shadow-lg mb-6">REGULASI & KODE DQ</h1>
            <p class="text-slate-300 font-bold tracking-[0.2em] uppercase text-sm md:text-base max-w-2xl mx-auto">Standar mutlak peraturan pertandingan.</p>
        </div>
    </div>

    <main class="max-w-4xl mx-auto px-6 lg:px-10 py-16 w-full flex-1 -mt-10 relative z-20">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="font-black text-slate-800 uppercase tracking-wider text-lg">Kamus Pelanggaran</h3>
                <span class="bg-red-100 text-red-600 px-3 py-1 rounded font-black text-[10px] tracking-widest uppercase">MUTLAK</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-black tracking-widest border-b border-slate-200">
                        <tr>
                            <th class="px-8 py-5 w-32">KODE DQ</th>
                            <th class="px-8 py-5">PENJELASAN PELANGGARAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($rules)): ?>
                            <tr>
                                <td colspan="2" class="px-8 py-16 text-center text-slate-400 font-bold uppercase tracking-widest">Belum ada regulasi terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $r): ?>
                                <tr class="hover:bg-slate-50 transition group">
                                    <td class="px-8 py-6">
                                        <span class="inline-block bg-red-600 text-white font-mono font-black text-xl px-3 py-1 rounded shadow-sm group-hover:scale-110 transition transform">
                                            <?= htmlspecialchars($r['kode_dq']) ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="font-bold text-slate-700 text-base leading-relaxed">
                                            <?= htmlspecialchars($r['deskripsi']) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-[#0F172A] text-white pt-32 pb-16 border-t-4 border-orange-600 text-center mt-auto">
        <div class="max-w-screen-xl mx-auto px-10">
            <p class="text-slate-600 text-[11px] font-black tracking-[0.6em] uppercase">&copy; <?= date('Y') ?> SET ROLL SYSTEM. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
