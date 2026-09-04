<div class="font-sans relative">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                Landing Pages Event
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Manajemen terpusat halaman publikasi dan profil masing-masing event.</p>
        </div>
        <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/event_landing_pages" class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-800 transition">Single Events</a>
            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/series_landing_pages" class="px-4 py-2 text-xs font-bold uppercase tracking-widest bg-blue-50 text-blue-700 rounded-lg shadow-sm">Series</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-xl border <?= $_SESSION['flash_type'] == 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?> font-bold text-sm">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="mb-8 flex justify-end">
        <a href="<?= getenv('APP_URL') ?>/roll/master/settings/create_series" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg transition">
            + Buat Series Baru
        </a>
    </div>

    <div class="space-y-8">
        <?php if (empty($series_list)): ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xl">
                <span class="text-6xl mb-4 block">🏆</span>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Belum Ada Series</h3>
                <p class="text-slate-500 mt-2">Anda belum membuat halaman Series Event.</p>
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
                            
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Event Tergabung</div>
                                    <div class="text-2xl font-black text-slate-800"><?= $s['event_count'] ?> <span class="text-xs text-slate-500">Event</span></div>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Status Klasemen</div>
                                    <div class="text-sm font-bold mt-1 <?= $s['show_standings'] ? 'text-blue-600' : 'text-slate-500' ?>">
                                        <?= $s['show_standings'] ? 'Tampil Publik' : 'Tersembunyi' ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($s['admins'])): ?>
                                <div class="mb-4">
                                    <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Admin Pengelola:</div>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($s['admins'] as $adm): ?>
                                            <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md"><?= htmlspecialchars($adm) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pt-4 border-t border-slate-100 flex gap-2 justify-end">
                            <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($s['slug']) ?>" target="_blank" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">
                                👁️ Lihat
                            </a>
                            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/edit_series?id=<?= $s['id'] ?>" class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-700 font-bold text-xs hover:bg-yellow-200 transition">
                                ✏️ Edit
                            </a>
                            <form action="<?= getenv('APP_URL') ?>/roll/master/settings/delete_series" method="POST" onsubmit="return confirm('Yakin ingin menghapus Series ini?');" class="inline">
                                <input type="hidden" name="series_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-100 text-red-700 font-bold text-xs hover:bg-red-200 transition">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
