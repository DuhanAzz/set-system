<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl"><?= $type === 'Speed' ? '⚡' : ($type === 'Standart' ? '🏃' : '🛼') ?></span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">
                        Kategori <?= htmlspecialchars($type) ?>
                    </h1>
                    <p class="text-slate-400 mt-2 font-medium">Pilih Nomor Lomba (Jarak) untuk mengatur serinya.</p>
                </div>
            </div>
        </div>

        <?php if(empty($distances)): ?>
            <div class="bg-white rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Belum Ada Nomor Lomba</h3>
                <p class="text-slate-500">Kategori ini belum memiliki nomor lomba yang dipertandingkan di kelas lomba (matriks).</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-8">
                <?php foreach ($distances as $d): ?>
                <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons?type=<?= urlencode($type) ?>&distance=<?= urlencode($d['distance_name']) ?>" 
                   class="group relative bg-white border border-slate-200/60 shadow-lg rounded-2xl p-6 hover:shadow-indigo-500/20 hover:border-indigo-500 transition-all transform hover:-translate-y-1 overflow-hidden flex flex-col justify-center items-center h-40">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="text-3xl mb-3 z-10 opacity-70 group-hover:opacity-100 transition-opacity group-hover:scale-110">🏁</span>
                    <h3 class="z-10 font-black text-slate-700 text-lg uppercase tracking-widest text-center group-hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($d['distance_name']) ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>
