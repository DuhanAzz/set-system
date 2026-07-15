<div class="mb-6 flex gap-3 items-center">
    <a href="<?= getenv('APP_URL') ?>/swim/explore/detail/<?= $event['id'] ?>" class="w-10 h-10 bg-slate-200 hover:bg-slate-300 rounded-full flex items-center justify-center text-slate-600 transition shrink-0">⬅</a>
    <div>
        <h1 class="text-2xl font-black uppercase italic text-slate-900">Pendaftaran Individu</h1>
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Event: <?= $event ? htmlspecialchars($event['event_name']) : 'TIDAK ADA EVENT AKTIF' ?></p>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-green-100 text-green-700">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
        ❌ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if(!$event): ?>
    <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center">
        <div class="text-6xl mb-4 opacity-50">🏆</div>
        <h3 class="text-xl font-black text-slate-800 uppercase italic">Belum Ada Event Aktif</h3>
        <p class="text-sm font-bold text-slate-500 mt-2">Pendaftaran akan dibuka ketika penyelenggara mengaktifkan event.</p>
    </div>
<?php else: ?>

    <?php if($isClosed): ?>
        <div class="mb-6 bg-red-100 border border-red-200 p-4 rounded-2xl flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div>
                <h4 class="font-black text-red-800 uppercase italic">Pendaftaran Telah Ditutup</h4>
                <p class="text-xs font-bold text-red-600">Batas waktu pendaftaran sudah berakhir pada <?= date('d M Y', strtotime($event['registration_deadline'])) ?>.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if($isLocked): ?>
        <div class="mb-6 bg-amber-100 border border-amber-200 p-4 rounded-2xl flex items-center gap-3">
            <span class="text-2xl">🔒</span>
            <div>
                <h4 class="font-black text-amber-800 uppercase italic">Pendaftaran Terkunci</h4>
                <p class="text-xs font-bold text-amber-700">Status pembayaran Anda sedang diproses atau sudah lunas. Pendaftaran dikunci untuk menghindari perubahan tagihan.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest">Nama Atlet</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Gender</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Kategori Umur</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Jml Lomba</th>
                    <th class="p-4 text-xs font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($swimmers)): ?>
                    <tr><td colspan="5" class="p-12 text-center text-sm font-bold text-slate-400 italic">Belum ada atlet di roster Anda.</td></tr>
                <?php else: ?>
                    <?php foreach($swimmers as $s): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4">
                                <p class="font-black text-sm uppercase text-blue-600"><?= htmlspecialchars($s['nama_atlet']) ?></p>
                                <p class="text-[10px] font-bold text-slate-400"><?= htmlspecialchars($s['uid']) ?></p>
                            </td>
                            <td class="p-4 text-center">
                                <?php if(strtoupper($s['jenis_kelamin']) == 'L'): ?>
                                    <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-700">PUTRA</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-pink-100 text-pink-700">PUTRI</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">KU <?= htmlspecialchars($s['kelompok_umur']) ?></span>
                            </td>
                            <td class="p-4 text-center">
                                <?php if($s['entry_count'] > 0): ?>
                                    <span class="font-black text-lg text-emerald-500"><?= $s['entry_count'] ?></span>
                                <?php else: ?>
                                    <span class="font-bold text-slate-300">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <?php if($isClosed || $isLocked): ?>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase italic">Terkunci</span>
                                <?php else: ?>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= getenv('APP_URL') ?>/swim/registration/create/<?= $event['id'] ?>/<?= $s['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition shadow-sm">
                                            <?= $s['entry_count'] > 0 ? 'Edit Lomba' : 'Daftarkan Lomba' ?>
                                        </a>
                                        <?php if($s['entry_count'] > 0): ?>
                                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/registration/delete/<?= $event['id'] ?>/<?= $s['id'] ?>" onsubmit="return confirm('Batalkan seluruh pendaftaran atlet ini?');">
                                            <button type="submit" class="bg-red-50 hover:bg-red-500 hover:text-white text-red-500 border border-red-100 px-3 py-2 rounded-xl text-[10px] font-black uppercase transition">Batal</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
