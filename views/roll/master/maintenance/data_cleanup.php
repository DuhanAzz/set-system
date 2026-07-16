<?php
include __DIR__ . '/../../../../views/layout/sidebar_roll.php';
include __DIR__ . '/../../../../views/layout/topbar_roll.php';
?>

<div class="p-6 max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Data Cleanup</h2>
            <p class="text-slate-500 font-bold text-sm mt-1">Sapu Bersih Pendaftaran Kadaluarsa.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="mb-8 px-4 py-3 rounded-xl <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?> font-bold shadow-sm">
            <?= $_SESSION['flash_msg'] ?>
        </div>
        <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="max-w-xl">
        <div class="bg-red-50 border border-red-200 rounded-3xl p-8 relative overflow-hidden shadow-sm group">
            <div class="absolute -right-6 -top-6 text-9xl opacity-5 group-hover:scale-110 transition duration-500">🗑</div>
            <h3 class="text-xl font-black text-red-700 uppercase tracking-tight mb-2 relative z-10">Pendaftaran Kadaluarsa</h3>
            <p class="text-red-500 font-medium text-sm mb-6 relative z-10">Menghapus baris pada tabel `roll_entries` yang berstatus <strong>Unpaid</strong> lebih dari 30 hari.</p>
            
            <div class="mb-8 p-4 bg-white/60 rounded-xl border border-red-100 relative z-10">
                <span class="block text-xs uppercase font-black text-red-400 mb-1">Ditemukan Data Sampah</span>
                <span class="text-3xl font-black text-red-700"><?= $expiredCount ?> <span class="text-lg">baris</span></span>
            </div>

            <form action="<?= getenv('APP_URL') ?>/roll/maintenance/data_cleanup" method="POST" class="relative z-10" onsubmit="return confirm('Tindakan ini tidak bisa dibatalkan! Yakin hapus pendaftaran kadaluarsa?');">
                <input type="hidden" name="action" value="clear_expired">
                <button type="submit" <?= $expiredCount == 0 ? 'disabled' : '' ?> class="w-full bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-red-600/30 transition transform hover:-translate-y-1">
                    Sapu Bersih Pendaftaran Kadaluarsa
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../../views/layout/footer_master.php'; ?>
