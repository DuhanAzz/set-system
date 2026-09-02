<div class="font-sans relative">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter">
                Landing Pages Event
            </h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Manajemen terpusat halaman publikasi dan profil masing-masing event.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-xl border <?= $_SESSION['flash_type'] == 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?> font-bold text-sm">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="space-y-8">
        <?php if (empty($grouped_landing_pages)): ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xl">
                <span class="text-6xl mb-4 block">📭</span>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Belum Ada Landing Page</h3>
                <p class="text-slate-500 mt-2">Penyelenggara event belum mengatur halaman profil/landing page mereka.</p>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_landing_pages as $adminName => $pages): ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-black text-lg">
                                <?= strtoupper(substr($adminName, 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest"><?= htmlspecialchars($adminName) ?></h3>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Penyelenggara Event</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white border-b border-slate-100">
                                    <th class="p-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Nama Event</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">URL Slug</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Status</th>
                                    <th class="p-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages as $lp): ?>
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                                        <td class="p-4">
                                            <div class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($lp['event_name']) ?></div>
                                            <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest"><?= date('d M Y', strtotime($lp['event_date_start'])) ?></div>
                                        </td>
                                        <td class="p-4">
                                            <?php if (!empty($lp['slug'])): ?>
                                                <div class="inline-flex items-center gap-1 text-xs font-mono text-slate-600 bg-slate-100 px-2 py-1 rounded">
                                                    setsystem.id/<?= htmlspecialchars($lp['slug']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-300 text-xs italic">Belum diatur</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4">
                                            <?php if (($lp['status'] ?? 'Draft') == 'Published'): ?>
                                                <span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-[10px] font-bold uppercase tracking-widest">Published</span>
                                            <?php else: ?>
                                                <span class="inline-block px-2 py-1 bg-slate-100 text-slate-500 rounded text-[10px] font-bold uppercase tracking-widest">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-right space-x-2">
                                            <?php if (!empty($lp['slug'])): ?>
                                                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($lp['slug']) ?>" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-600 transition tooltip" title="Lihat Halaman">
                                                    👁️
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= getenv('APP_URL') ?>/roll/master/settings/edit_landing_page?event_id=<?= $lp['event_id'] ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-yellow-100 hover:text-yellow-600 transition tooltip" title="Edit Landing Page">
                                                ✏️
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<style>
.tooltip { position: relative; }
.tooltip:hover::after {
    content: attr(title);
    position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: white; padding: 4px 8px; font-size: 10px; border-radius: 4px;
    white-space: nowrap; margin-bottom: 4px; pointer-events: none; z-index: 10;
}
</style>
