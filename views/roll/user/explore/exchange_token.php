<div class="max-w-md mx-auto font-sans mt-20">
    <div class="bg-white rounded-3xl p-10 shadow-xl border border-slate-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-full -mr-10 -mt-10 opacity-50"></div>
        
        <div class="relative z-10">
            <h2 class="text-2xl font-black uppercase tracking-widest text-slate-800 mb-2">🔑 Jalur Khusus</h2>
            <p class="text-xs font-bold text-slate-500 mb-8">Tukarkan token yang diberikan oleh Admin untuk mendaftarkan tim Anda secara manual pada event: <strong class="text-slate-800"><?= htmlspecialchars($event['event_name']) ?></strong></p>

            <?php if(isset($_SESSION['flash_message'])): ?>
                <div class="mb-6 p-4 rounded-xl text-xs font-bold text-red-600 bg-red-50 border border-red-200">
                    <?= $_SESSION['flash_message'] ?>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <form action="<?= getenv('APP_URL') ?>/roll/user/token/verify" method="POST" class="space-y-6">
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kode Token <span class="text-red-500">*</span></label>
                    <input type="text" name="token_code" required placeholder="Contoh: T-8A9F2C" class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-xl font-black text-slate-800 tracking-widest uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 transition text-center text-xl">
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-xl font-black uppercase text-xs tracking-widest shadow-lg shadow-emerald-200 transition transform hover:-translate-y-1">
                    Validasi Token & Masuk
                </button>
                
                <div class="text-center mt-4">
                    <a href="<?= getenv('APP_URL') ?>/roll/user/explore/detail/<?= $event['id'] ?>" class="text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition">
                        &larr; Batal & Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
