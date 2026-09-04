<?php include __DIR__ . '/../../layout/header_roll.php'; ?>
<?php include __DIR__ . '/../../layout/sidebar_roll.php'; ?>

<main class="flex-1 ml-64 p-8 bg-slate-50 min-h-screen">
    <div class="font-sans relative">
        <div class="mb-10">
            <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                Pengelola Series
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Mengelola desain dan konten Landing Page Series yang ditugaskan kepada Anda.</p>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-6 p-4 rounded-xl border <?= $_SESSION['flash_type'] == 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?> font-bold text-sm">
                <?= $_SESSION['flash_message'] ?>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="space-y-8">
            <?php if (empty($series_list)): ?>
                <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xl">
                    <span class="text-6xl mb-4 block">📭</span>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Tidak Ada Akses Series</h3>
                    <p class="text-slate-500 mt-2">Anda belum ditugaskan untuk mengelola Series manapun oleh Master.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($series_list as $s): ?>
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 flex flex-col justify-between hover:shadow-2xl transition">
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter"><?= htmlspecialchars($s['series_name']) ?></h3>
                                        <div class="inline-flex items-center gap-1 text-xs font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded mt-1">
                                            setsystem.id/<?= htmlspecialchars($s['slug']) ?>
                                        </div>
                                    </div>
                                    <?php if (($s['status'] ?? 'Draft') == 'Published'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-widest">Published</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[10px] font-bold uppercase tracking-widest">Draft</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 mb-6">Sebagai admin, Anda hanya berhak mengubah desain konten (banner, warna, deskripsi).</p>
                            </div>
                            
                            <div class="pt-4 border-t border-slate-100 flex gap-2 justify-end">
                                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($s['slug']) ?>" target="_blank" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">
                                    👁️ Lihat
                                </a>
                                <a href="<?= getenv('APP_URL') ?>/roll/admin/series/edit?id=<?= $s['id'] ?>" class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs hover:bg-blue-200 transition">
                                    ✏️ Edit Desain
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../layout/footer_roll.php'; ?>
