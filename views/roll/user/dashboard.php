<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 font-sans">
    
    <div class="mb-8">
        <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900">Dashboard Klub</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin') ?> - <?= htmlspecialchars($clubName) ?></p>
    </div>

    <?php if(empty($clubCity ?? null)): ?>
    <div class="bg-orange-50 border-l-4 border-orange-500 text-orange-800 p-6 rounded-2xl mb-8 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-black uppercase tracking-widest text-sm mb-1">⚠️ Profil Klub Belum Lengkap</h3>
            <p class="text-xs font-medium opacity-90">Klub Anda belum mengisi <strong>Kota Asal Klub</strong>. Data ini diperlukan untuk pencetakan piagam/sertifikat dan klasemen medali.</p>
        </div>
        <a href="<?= getenv('APP_URL') ?>/roll/user/profile" class="whitespace-nowrap bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-black uppercase text-[10px] tracking-widest transition shadow-sm">Isi Sekarang</a>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        
        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-blue-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Atlet</p>
                    <h3 class="text-4xl font-black text-slate-800 group-hover:text-blue-600 transition"><?= $stats['total_athletes'] ?? 0 ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition">🛼</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-purple-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Atlet Putra (M)</p>
                    <h3 class="text-4xl font-black text-slate-800 group-hover:text-purple-600 transition"><?= $stats['total_male'] ?? 0 ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-purple-600 group-hover:text-white transition">👨</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-pink-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Atlet Putri (F)</p>
                    <h3 class="text-4xl font-black text-slate-800 group-hover:text-pink-600 transition"><?= $stats['total_female'] ?? 0 ?></h3>
                </div>
                <div class="w-14 h-14 bg-pink-100 text-pink-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-pink-600 group-hover:text-white transition">👩</div>
            </div>
        </div>
    </div>

    <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight mb-4 ml-2">Menu Cepat</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <a href="<?= getenv('APP_URL') ?>/roll/user/athletes" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-blue-400">
            <span class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">📋</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Data Atlet</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Input biodata skater baru.</p>
        </a>

        <a href="<?= getenv('APP_URL') ?>/roll/user/registration" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-purple-400">
            <span class="w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">🎯</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Daftar Lomba</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Pilih nomor lomba per atlet.</p>
        </a>

        <a href="<?= getenv('APP_URL') ?>/roll/user/checkout" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-orange-400">
            <span class="w-16 h-16 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">💳</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Pembayaran</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Status bayar & tagihan.</p>
        </a>

    </div>
</div>
