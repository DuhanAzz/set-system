<?php
include __DIR__ . '/../../../../views/layout/sidebar_roll_master.php';
include __DIR__ . '/../../../../views/layout/topbar_roll.php';
?>

<div class="p-6 max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Ruang Kontrol Sistem</h2>
            <p class="text-slate-500 font-bold text-sm mt-1">Area berbahaya. Pastikan Anda tahu apa yang Anda lakukan.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="mb-8 px-4 py-3 rounded-xl <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?> font-bold shadow-sm">
            <?= $_SESSION['flash_msg'] ?>
        </div>
        <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Hapus Pendaftaran Kadaluarsa -->
        <div class="bg-red-50 border border-red-200 rounded-3xl p-8 relative overflow-hidden shadow-sm group">
            <div class="absolute -right-6 -top-6 text-9xl opacity-5 group-hover:scale-110 transition duration-500">🗑</div>
            <h3 class="text-xl font-black text-red-700 uppercase tracking-tight mb-2 relative z-10">Pendaftaran Kadaluarsa</h3>
            <p class="text-red-500 font-medium text-sm mb-6 relative z-10">Menghapus baris pada tabel `roll_entries` yang berstatus <strong>Unpaid</strong> lebih dari 30 hari.</p>
            
            <div class="mb-8 p-4 bg-white/60 rounded-xl border border-red-100">
                <span class="block text-xs uppercase font-black text-red-400 mb-1">Ditemukan Data Sampah</span>
                <span class="text-3xl font-black text-red-700"><?= $expiredCount ?> <span class="text-lg">baris</span></span>
            </div>

            <form action="<?= getenv('APP_URL') ?>/roll/master/maintenance" method="POST" class="relative z-10" onsubmit="return confirm('Tindakan ini tidak bisa dibatalkan! Yakin hapus pendaftaran kadaluarsa?');">
                <input type="hidden" name="action" value="clear_expired">
                <button type="submit" <?= $expiredCount == 0 ? 'disabled' : '' ?> class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-red-600/30 transition transform hover:-translate-y-1">
                    Eksekusi Pembersihan
                </button>
            </form>
        </div>

        <!-- Optimasi Database -->
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-8 relative overflow-hidden shadow-sm group">
            <div class="absolute -right-6 -top-6 text-9xl opacity-5 group-hover:scale-110 transition duration-500">⚡</div>
            <h3 class="text-xl font-black text-amber-700 uppercase tracking-tight mb-2 relative z-10">Optimasi Database</h3>
            <p class="text-amber-600 font-medium text-sm mb-6 relative z-10">Menjalankan kueri <code>OPTIMIZE TABLE</code> pada seluruh tabel utama Roll (Entries, Skaters, Events, dll) untuk defragmentasi disk.</p>
            
            <div class="mb-8 p-4 bg-white/60 rounded-xl border border-amber-100">
                <span class="block text-xs uppercase font-black text-amber-500 mb-1">Status Kesehatan</span>
                <span class="text-xl font-black text-amber-700">Aman untuk dieksekusi berkala</span>
            </div>

            <form action="<?= getenv('APP_URL') ?>/roll/master/maintenance" method="POST" class="relative z-10" onsubmit="return confirm('Proses optimasi akan mengunci tabel selama beberapa saat. Yakin lanjutkan?');">
                <input type="hidden" name="action" value="optimize_db">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-amber-500/30 transition transform hover:-translate-y-1">
                    Mulai Optimasi Tabel
                </button>
            </form>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../../../views/layout/footer_master.php'; ?>
