<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dashboard - Universal SET System</title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- NAVBAR MASTER -->
    <header class="bg-slate-900 text-white shadow-xl sticky top-0 z-50">
        <div class="container mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" alt="Logo" class="h-8 invert brightness-0">
                <span class="text-lg font-black tracking-widest uppercase text-blue-400">Master Portal</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="<?= getenv('APP_URL') ?>/" target="_blank" class="text-sm font-semibold hover:text-blue-400 transition-colors">Lihat Web</a>
                <a href="<?= getenv('APP_URL') ?>/master/settings/global" class="text-sm font-semibold hover:text-blue-400 transition-colors">⚙️ Pengaturan CMS</a>
                <a href="<?= getenv('APP_URL') ?>/logout" class="text-sm font-bold bg-red-500 hover:bg-red-600 px-5 py-2 rounded-lg transition-all shadow-lg shadow-red-500/30 transform hover:-translate-y-0.5">Keluar</a>
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="flex-grow container mx-auto px-6 py-12 max-w-6xl">
        
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Selamat Datang, Master!</h1>
            <p class="text-slate-500 font-medium">Ini adalah pusat komando utama untuk Universal SET System.</p>
        </div>

        <!-- STATISTIK KILAT -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Card 1 -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 font-bold uppercase tracking-wider text-xs">Total Admin</h3>
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <p class="text-5xl font-black text-slate-900"><?= $stats['total_admin'] ?? 0 ?></p>
            </div>

            <!-- Card 2 -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 font-bold uppercase tracking-wider text-xs">Total Pengguna</h3>
                    <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                <p class="text-5xl font-black text-slate-900"><?= $stats['total_user'] ?? 0 ?></p>
            </div>

            <!-- Card 3 -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 font-bold uppercase tracking-wider text-xs">Total Lomba (Event)</h3>
                    <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <p class="text-5xl font-black text-slate-900"><?= $stats['total_lomba'] ?? 0 ?></p>
            </div>
        </div>

        <!-- MENU SHORTCUTS -->
        <h2 class="text-xl font-bold text-slate-900 mb-6">Akses Cepat Pengaturan (CMS)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <a href="<?= getenv('APP_URL') ?>/master/settings/global" class="group block bg-slate-900 p-8 rounded-3xl relative overflow-hidden transition-all hover:-translate-y-2 hover:shadow-2xl shadow-slate-900/20">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10">
                    <h3 class="text-white font-bold text-lg mb-2">🌐 Global Config</h3>
                    <p class="text-slate-400 text-sm font-medium">Ubah Nama Web, SEO, Teks Utama & Kontak.</p>
                </div>
            </a>

            <a href="<?= getenv('APP_URL') ?>/master/settings/hero" class="group block bg-blue-600 p-8 rounded-3xl relative overflow-hidden transition-all hover:-translate-y-2 hover:shadow-2xl shadow-blue-600/30">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10">
                    <h3 class="text-white font-bold text-lg mb-2">🖼️ Hero Slider</h3>
                    <p class="text-blue-200 text-sm font-medium">Upload & atur urutan gambar latar bergerak.</p>
                </div>
            </a>

            <a href="<?= getenv('APP_URL') ?>/master/settings/public" class="group block bg-[#f25822] p-8 rounded-3xl relative overflow-hidden transition-all hover:-translate-y-2 hover:shadow-2xl shadow-orange-500/30">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10">
                    <h3 class="text-white font-bold text-lg mb-2">📸 Public Page</h3>
                    <p class="text-orange-200 text-sm font-medium">Atur Watermark Logo Event & Preview Sistem.</p>
                </div>
            </a>

        </div>

    </main>
</body>
</html>
