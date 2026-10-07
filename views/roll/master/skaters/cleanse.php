<?php 
// FILE: views/roll/master/skaters/cleanse.php
?>
<div>
    <div>
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-4 p-4 text-xs font-bold rounded-xl border shadow-sm flex items-center gap-2 <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-50 text-red-800 border-red-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' ?>">
                <span><?= ($_SESSION['flash_type'] == 'error') ? '⚠️' : '💡' ?></span> 
                <div><?= $_SESSION['flash_message'] ?></div>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Analisis Data Skater</h1>
                <p class="text-xs text-slate-500 font-medium">Deteksi duplikat, anomali tanggal lahir, dan pembersihan roster.</p>
            </div>
            
            <div class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-2">
                <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex w-full sm:w-auto mb-2 sm:mb-0">
                    <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/index" class="px-4 py-2 w-full text-center sm:w-auto rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Daftar Skater</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/cleanse" class="px-4 py-2 w-full text-center sm:w-auto rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Cleanse Data</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Exact Duplicates -->
            <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden p-6">
                <h3 class="font-black text-slate-800 uppercase text-lg border-b pb-2 mb-4">1. Duplikat Identik (Nama & Tgl Lahir Sama)</h3>
                <?php if (empty($exactDuplicates)): ?>
                    <p class="text-sm text-emerald-600 font-bold">✨ Bersih! Tidak ada duplikat identik.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($exactDuplicates as $d): ?>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <div>
                                    <div class="font-black text-slate-800 uppercase text-sm"><?= htmlspecialchars($d['skater_name']) ?></div>
                                    <div class="text-xs text-slate-500 font-mono mt-1">
                                        DOB: <span class="font-bold text-slate-700"><?= $d['birth_date'] ?></span> | JK: <?= $d['gender'] ?>
                                    </div>
                                    <div class="text-[10px] text-amber-600 font-bold bg-amber-50 inline-block px-2 py-1 rounded mt-2 uppercase tracking-widest border border-amber-200">
                                        <?= $d['total_entries'] ?> Entri Ditemukan
                                    </div>
                                    <div class="text-xs text-slate-500 mt-2">Klub Asal: <b><?= htmlspecialchars($d['clubs']) ?></b></div>
                                    <div class="text-xs text-slate-400 font-mono mt-1">ID Terkait: <?= $d['ids'] ?></div>
                                </div>
                                <div class="flex gap-2">
                                    <?php 
                                        $idList = explode(',', $d['ids']); 
                                        // Hapus 1 per 1 (biarkan 1 tetap hidup)
                                        $idToDelete = end($idList);
                                    ?>
                                    <form action="<?= getenv('APP_URL') ?>/roll/master/skaters/delete" method="POST" onsubmit="return confirm('Hapus entri duplikat ID <?= $idToDelete ?>?')">
                                        <input type="hidden" name="id" value="<?= $idToDelete ?>">
                                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200 rounded-lg text-xs font-bold uppercase transition">Hapus Salah Satu</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Typos -->
            <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden p-6">
                <h3 class="font-black text-slate-800 uppercase text-lg border-b pb-2 mb-4">2. Potensi Typo (Nama Sama, Tgl Lahir Beda)</h3>
                <?php if (empty($typoCandidates)): ?>
                    <p class="text-sm text-emerald-600 font-bold">✨ Bersih! Tidak ada potensi typo nama ganda.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($typoCandidates as $d): ?>
                            <div class="p-4 bg-orange-50 rounded-xl border border-orange-200 flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <div>
                                    <div class="font-black text-orange-900 uppercase text-sm">⚠️ <?= htmlspecialchars($d['skater_name']) ?></div>
                                    <div class="text-xs text-orange-800 font-mono mt-1">
                                        Variasi DOB: <span class="font-bold"><?= $d['dobs'] ?></span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-2">Klub Terlibat: <b><?= htmlspecialchars($d['clubs']) ?></b></div>
                                    <div class="text-xs text-slate-400 font-mono mt-1">ID Terkait: <?= $d['ids'] ?></div>
                                </div>
                                <div class="text-xs text-slate-500 font-medium max-w-[200px] italic">
                                    Cek silang secara manual dengan klub terkait. 
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Date Anomalies -->
            <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden p-6">
                <h3 class="font-black text-slate-800 uppercase text-lg border-b pb-2 mb-4">3. Anomali Tanggal Lahir</h3>
                <?php if (empty($anomalies)): ?>
                    <p class="text-sm text-emerald-600 font-bold">✨ Bersih! Semua tanggal lahir valid.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach($anomalies as $a): ?>
                            <div class="p-4 bg-red-50 rounded-xl border border-red-200 flex flex-col md:flex-row justify-between md:items-center gap-4">
                                <div>
                                    <div class="font-black text-red-900 uppercase text-sm"><?= htmlspecialchars($a['skater_name']) ?></div>
                                    <div class="text-xs text-red-800 font-mono mt-1">
                                        DOB: <span class="font-bold bg-white px-2 py-0.5 rounded text-red-600"><?= htmlspecialchars($a['birth_date'] ?? 'KOSONG') ?></span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-2">Klub Asal: <b><?= htmlspecialchars($a['club_name'] ?? '-') ?></b></div>
                                </div>
                                <div class="flex gap-2">
                                    <form action="<?= getenv('APP_URL') ?>/roll/master/skaters/delete" method="POST" onsubmit="return confirm('Hapus data cacat ini (ID <?= $a['id'] ?>)?')">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold uppercase transition">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
