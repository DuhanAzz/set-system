<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-black uppercase tracking-tighter italic text-slate-900">Manajemen Roster</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Kelola data atlet yang akan didaftarkan</p>
    </div>
    <a href="<?= getenv('APP_URL') ?>/swim/swimmers/create" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-black text-sm uppercase tracking-widest shadow-md transition transform hover:scale-105 flex items-center gap-2">
        <span>+</span> TAMBAH ATLET
    </a>
</div>

<?php if (isset($success)): ?>
    <div class="mb-6 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl p-4 text-sm font-bold shadow-sm">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="mb-6 bg-red-50 text-red-600 border border-red-200 rounded-xl p-4 text-sm font-bold shadow-sm">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b-2 border-slate-200">
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest w-16 text-center">No</th>
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest">Nama Atlet</th>
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">Gender</th>
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">Tanggal Lahir</th>
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">KU (Otomatis)</th>
                    <th class="p-4 text-xs font-black text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($swimmers)): ?>
                <tr>
                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada atlet di roster Anda.</td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($swimmers as $s): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-sm text-slate-500 text-center font-bold"><?= $no++ ?></td>
                        <td class="p-4 text-sm font-black text-slate-800 uppercase"><?= htmlspecialchars($s['nama_atlet']) ?></td>
                        <td class="p-4 text-center">
                            <?php if(strtoupper($s['jenis_kelamin']) == 'L' || strtoupper($s['jenis_kelamin']) == 'PUTRA' || strtoupper($s['jenis_kelamin']) == 'MALE'): ?>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Putra</span>
                            <?php else: ?>
                                <span class="bg-pink-100 text-pink-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest">Putri</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-sm text-slate-600 text-center font-bold">
                            <?= date('d M Y', strtotime($s['tanggal_lahir'])) ?>
                        </td>
                        <td class="p-4 text-center">
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg text-xs font-bold tracking-widest whitespace-nowrap">
                                KU <?= htmlspecialchars($s['kelompok_umur']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?= getenv('APP_URL') ?>/swim/swimmers/edit/<?= $s['id'] ?>" class="bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-600 p-2 rounded-lg transition" title="Edit">
                                    ✏️
                                </a>
                                <form action="<?= getenv('APP_URL') ?>/swim/swimmers/delete/<?= $s['id'] ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus atlet ini dari roster?');" class="inline">
                                    <button type="submit" class="bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 p-2 rounded-lg transition" title="Hapus">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
