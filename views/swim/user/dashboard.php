



    <div class="mb-8">
        <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900">Dashboard Klub</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Coach') ?></p>
    </div>

    <!-- ACTION REQUIRED ALERTS -->
    <?php if($unpaidInvoices > 0 || $missingUid > 0): ?>
    <div class="mb-8 space-y-4">
        <?php if($unpaidInvoices > 0): ?>
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between group gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl flex-shrink-0">💳</div>
                <div>
                    <h3 class="font-black text-lg">Action Required: <?= $unpaidInvoices ?> Tagihan Belum Lunas</h3>
                    <p class="text-sm text-orange-100 font-medium mt-1">Anda memiliki pendaftaran event yang belum dibayar atau masih menunggu verifikasi panitia.</p>
                </div>
            </div>
            <a href="<?= getenv('APP_URL') ?>/swim/payments" class="whitespace-nowrap bg-white text-orange-600 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition transform group-hover:scale-105 shadow-md">Bayar Sekarang</a>
        </div>
        <?php endif; ?>

        <?php if($missingUid > 0): ?>
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between group gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl flex-shrink-0">🆔</div>
                <div>
                    <h3 class="font-black text-lg">System Alert: <?= $missingUid ?> Atlet Tanpa UID</h3>
                    <p class="text-sm text-blue-100 font-medium mt-1">Segera lengkapi data atlet Anda agar mendapatkan UID untuk bisa didaftarkan ke perlombaan.</p>
                </div>
            </div>
            <a href="<?= getenv('APP_URL') ?>/swim/swimmers/index" class="whitespace-nowrap bg-white text-blue-700 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-50 transition transform group-hover:scale-105 shadow-md">Lengkapi Data</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        
        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-blue-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Atlet</p>
                    <h3 class="text-4xl font-black text-slate-800 group-hover:text-blue-600 transition"><?= $totalSwimmers ?></h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition">🏊</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-purple-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Lomba</p>
                    <h3 class="text-4xl font-black text-slate-800 group-hover:text-purple-600 transition"><?= $totalEntries ?></h3>
                </div>
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-purple-600 group-hover:text-white transition">⚡</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl shadow-sm border-b-4 border-orange-500 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Pembayaran</p>
                    <h3 class="text-xl font-black <?= ($lastPaymentStatus == 'Paid' || $lastPaymentStatus == 'Verified') ? 'text-emerald-500' : 'text-orange-500' ?>">
                        <?= $lastPaymentStatus ?>
                    </h3>
                </div>
                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center text-3xl shadow-inner group-hover:bg-orange-600 group-hover:text-white transition">💳</div>
            </div>
        </div>
    </div>

    <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight mb-4 ml-2">Menu Cepat</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <a href="<?= getenv('APP_URL') ?>/swim/swimmers/index" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-blue-400">
            <span class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">📋</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Data Atlet</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Input biodata perenang baru.</p>
        </a>

        <a href="<?= getenv('APP_URL') ?>/swim/events/explore" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-purple-400">
            <span class="w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">🎯</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Daftar Lomba</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Pilih nomor lomba per atlet.</p>
        </a>

        <?php if($activeRelayEventId): ?>
        <a href="<?= getenv('APP_URL') ?>/swim/events/relay_registration?event_id=<?= $activeRelayEventId ?>" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-pink-400 relative overflow-hidden">
            <div class="absolute -right-6 top-3 bg-pink-500 text-white text-[9px] font-black uppercase px-8 py-1 rotate-45 tracking-widest shadow-lg">NEW</div>
            <span class="w-16 h-16 bg-pink-100 text-pink-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">🏃‍♂️</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic leading-tight">Daftar Estafet</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Daftarkan tim beregu Anda.</p>
        </a>
        <?php endif; ?>

        <a href="<?= getenv('APP_URL') ?>/swim/payments" class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border-b-4 border-orange-400">
            <span class="w-16 h-16 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner group-hover:scale-110 transition">💳</span>
            <h4 class="font-black text-lg text-slate-800 uppercase italic">Pembayaran</h4>
            <p class="text-xs text-slate-500 mt-2 font-medium">Upload bukti transfer.</p>
        </a>

    </div>

