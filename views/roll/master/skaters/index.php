<?php 
// FILE: views/roll/master/skaters/index.php
?>
<div class="space-y-6">
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="p-4 text-xs font-bold rounded-2xl border shadow-sm flex items-center justify-between gap-2 <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-50 text-red-800 border-red-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' ?>">
            <div class="flex items-center gap-2">
                <span><?= ($_SESSION['flash_type'] == 'error') ? '⚠️' : '💡' ?></span> 
                <div><?= $_SESSION['flash_message'] ?></div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-xl opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter">Database Skater & BPJS</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Manajemen Data Atlet, Profil Sekolah/Puspernas, & Status Kepesertaan BPJS</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <!-- Navigasi Tab Master -->
            <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
                <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/index" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Daftar Skater</a>
                <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/cleanse" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50 border-x border-slate-100">Cleanse Data</a>
                <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/history_transfer" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Riwayat Mutasi</a>
            </div>

            <!-- Tombol Export Excel / CSV -->
            <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/export_excel?search=<?= urlencode($search ?? '') ?>&bpjs=<?= urlencode($bpjsFilter ?? 'all') ?>&completeness=<?= urlencode($completenessFilter ?? 'all') ?>" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl shadow-sm font-bold text-xs uppercase tracking-wide transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Atlet Terdaftar</span>
            <div class="text-2xl font-black text-slate-800 mt-1"><?= number_format($summary['total_skaters'] ?? count($skaters)) ?></div>
            <div class="text-[10px] text-slate-400 mt-0.5">Roster keseluruhan</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-emerald-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600">BPJS: Terdaftar</span>
            <div class="text-2xl font-black text-emerald-700 mt-1"><?= number_format($summary['total_bpjs_sudah'] ?? 0) ?></div>
            <div class="text-[10px] text-emerald-600/70 mt-0.5">Dari event & mandiri</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-amber-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-600">BPJS: Belum Terdaftar</span>
            <div class="text-2xl font-black text-amber-700 mt-1"><?= number_format($summary['total_bpjs_belum'] ?? 0) ?></div>
            <div class="text-[10px] text-amber-600/70 mt-0.5">Perlu bantuan panitia/official</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-rose-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-rose-600">Data Belum Lengkap</span>
            <div class="text-2xl font-black text-rose-700 mt-1"><?= number_format($summary['total_incomplete'] ?? 0) ?></div>
            <div class="text-[10px] text-rose-600/70 mt-0.5">Sekolah/wilayah belum diisi</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Filter Pills BPJS -->
        <div class="flex items-center gap-1.5 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <span class="text-[10px] font-black uppercase text-slate-400 mr-1 whitespace-nowrap">Filter BPJS:</span>
            <a href="?search=<?= urlencode($search) ?>&bpjs=all&completeness=<?= urlencode($completenessFilter) ?>" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'all') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Semua
            </a>
            <a href="?search=<?= urlencode($search) ?>&bpjs=sudah&completeness=<?= urlencode($completenessFilter) ?>" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'sudah') ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' ?>">
                ✓ Sudah Terdaftar
            </a>
            <a href="?search=<?= urlencode($search) ?>&bpjs=belum&completeness=<?= urlencode($completenessFilter) ?>" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'belum') ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' ?>">
                ⚠️ Belum Terdaftar
            </a>
        </div>

        <!-- Filter Puspernas & Search Form -->
        <form method="GET" class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
            <input type="hidden" name="bpjs" value="<?= htmlspecialchars($bpjsFilter) ?>">
            
            <select name="completeness" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs font-bold rounded-xl px-3 py-2.5 text-slate-700 outline-none focus:border-blue-500 w-full sm:w-auto">
                <option value="all" <?= ($completenessFilter === 'all') ? 'selected' : '' ?>>Semua Kelengkapan Data</option>
                <option value="incomplete" <?= ($completenessFilter === 'incomplete') ? 'selected' : '' ?>>⚠️ Data Belum Lengkap Saja</option>
                <option value="complete" <?= ($completenessFilter === 'complete') ? 'selected' : '' ?>>✓ Data Lengkap Saja</option>
            </select>

            <div class="relative w-full sm:w-72">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                       class="pl-9 pr-4 py-2 text-xs font-bold border border-slate-200 bg-slate-50 rounded-xl w-full shadow-sm focus:bg-white focus:border-blue-500 outline-none" 
                       placeholder="Cari Nama, Sekolah, Klub...">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Cari</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="p-5">Profil Skater</th>
                        <th class="p-5">Sekolah & Daerah</th>
                        <th class="p-5">Klub Asal</th>
                        <th class="p-5 text-center">Tgl Lahir / Umur</th>
                        <th class="p-5 text-center">Status BPJS</th>
                        <th class="p-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($skaters)): ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="text-4xl mb-2">🛼</div>
                                <div class="text-slate-400 font-bold italic">Tidak ada data skater yang cocok dengan filter.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($skaters as $s): 
                            $umur = '-';
                            if(!empty($s['birth_date'])) {
                                $year = (int)date('Y', strtotime($s['birth_date']));
                                $currentYear = (int)date('Y');
                                $age = $currentYear - $year;
                                $umur = $age . ' Thn';
                            }
                            $isDataIncomplete = empty($s['asal_sekolah']) || empty($s['provinsi']) || empty($s['kota_kabupaten']);
                        ?>
                        <tr class="hover:bg-slate-50 transition duration-200 group">
                            
                            <!-- Profil Skater -->
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black border-2 <?= strtoupper($s['gender']) == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                        <?= strtoupper($s['gender']) == 'M' ? 'Pa' : 'Pi' ?>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-sm uppercase flex items-center gap-2">
                                            <?= htmlspecialchars($s['skater_name']) ?>
                                            <?php if (isset($s['is_pon_veteran']) && $s['is_pon_veteran'] == 1): ?>
                                                <span class="bg-amber-100 text-amber-700 text-[9px] px-2 py-0.5 rounded-full font-black tracking-widest border border-amber-200" title="Veteran PON (Wajib Senior)">🏅 PON</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="font-mono text-slate-400 text-[10px] tracking-wide mt-0.5 flex items-center gap-2">
                                            <span>UID: <strong class="text-blue-600"><?= htmlspecialchars($s['uid'] ?? '-') ?></strong></span>
                                            <span>•</span>
                                            <span>Reg: <?= date('d M Y', strtotime($s['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Sekolah & Daerah -->
                            <td class="p-5">
                                <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                    <span>🏫</span>
                                    <span><?= !empty($s['asal_sekolah']) ? htmlspecialchars($s['asal_sekolah']) : '<span class="text-slate-400 font-normal italic">- Belum diisi -</span>' ?></span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                    <span>📍</span>
                                    <span><?= !empty($s['kota_kabupaten']) ? htmlspecialchars($s['kota_kabupaten']) : '-' ?><?= !empty($s['provinsi']) ? ', ' . htmlspecialchars($s['provinsi']) : '' ?></span>
                                    <?php if ($isDataIncomplete): ?>
                                        <span class="ml-1 text-[9px] bg-rose-50 text-rose-600 border border-rose-200 px-1.5 py-0.2 rounded font-bold">Incomplete</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Klub Asal -->
                            <td class="p-5">
                                <?php if(!empty($s['club_name'])): ?>
                                    <div class="font-bold text-slate-700 text-xs uppercase bg-slate-100 px-2.5 py-1 rounded-lg inline-block">
                                        <?= htmlspecialchars($s['club_name']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] italic text-slate-400 font-bold">TIDAK ADA KLUB</span>
                                <?php endif; ?>
                            </td>

                            <!-- Tanggal Lahir / Umur -->
                            <td class="p-5 text-center align-middle">
                                <div class="font-black text-slate-700 text-sm"><?= !empty($s['birth_date']) ? date('d/m/Y', strtotime($s['birth_date'])) : '-' ?></div>
                                <div class="text-[10px] text-amber-500 font-black uppercase mt-0.5 tracking-wider"><?= $umur ?></div>
                            </td>

                            <!-- Status BPJS -->
                            <td class="p-5 text-center align-middle">
                                <?php if ($s['bpjs_badge'] === 'sudah_event'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Otomatis terdaftar: pernah ikut <?= $s['total_events'] ?> event lomba">
                                        <span>✓</span> Terdaftar (Event)
                                    </span>
                                <?php elseif ($s['bpjs_badge'] === 'sudah_mandiri'): ?>
                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Terdaftar mandiri">
                                        <span>✓</span> Terdaftar (Mandiri)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Belum pernah ikut event & belum terdaftar">
                                        Belum Terdaftar
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi -->
                            <td class="p-5 text-center align-middle">
                                <button class="text-slate-600 bg-white border border-slate-200 hover:border-blue-500 hover:text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" onclick='openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8") ?>)'>
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Skater Master -->
<div id="modal-edit" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="bg-slate-900 p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <div>
                <h3 class="font-black uppercase tracking-widest italic text-lg leading-none">Edit Biodata Skater</h3>
                <p class="text-xs text-slate-400 mt-1">Perbarui profil, data sekolah/daerah, dan status BPJS</p>
            </div>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-red-500 transition">✕</button>
        </div>
        <form action="<?= getenv('APP_URL') ?>/roll/master/skaters/update" method="POST" class="p-8 space-y-4">
            <input type="hidden" name="id" id="edit_id">
            
            <div>
                <label class="text-[10px] font-bold text-slate-500 uppercase">Nama Lengkap Skater *</label>
                <input type="text" name="skater_name" id="edit_skater_name" class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none uppercase" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Jenis Kelamin *</label>
                    <select name="gender" id="edit_gender" required class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none">
                        <option value="M">Putra (Male)</option>
                        <option value="F">Putri (Female)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Tanggal Lahir *</label>
                    <input type="date" name="birth_date" id="edit_birth_date" class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3">
                <label class="text-[10px] font-bold text-slate-700 uppercase flex items-center gap-1.5 mb-1">
                    <span>🏫</span> Nama Asal Sekolah
                </label>
                <input type="text" name="asal_sekolah" id="edit_asal_sekolah" placeholder="Contoh: SMP NEGERI 1 YOGYAKARTA" class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none uppercase">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Kota / Kabupaten</label>
                    <input type="text" name="kota_kabupaten" id="edit_kota_kabupaten" placeholder="Contoh: KOTA YOGYAKARTA" class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none uppercase">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Provinsi</label>
                    <input type="text" name="provinsi" id="edit_provinsi" placeholder="Contoh: D.I. YOGYAKARTA" class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none uppercase">
                </div>
            </div>

            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-start gap-3 mt-2">
                <input type="checkbox" name="status_bpjs" id="edit_status_bpjs" value="sudah" class="mt-1 w-4 h-4 text-emerald-600 bg-white border-emerald-300 rounded focus:ring-emerald-500 focus:ring-2 cursor-pointer">
                <label for="edit_status_bpjs" class="text-xs font-bold text-emerald-900 cursor-pointer">
                    🛡️ Status BPJS: Sudah Terdaftar
                    <p class="text-[10px] text-emerald-700 font-medium mt-0.5 leading-relaxed" id="edit_bpjs_note">
                        Centang jika atlet sudah memiliki BPJS aktif. (Atlet yang pernah ikut event otomatis terhitung sudah terdaftar).
                    </p>
                </label>
            </div>

            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 flex items-start gap-3">
                <input type="checkbox" name="is_pon_veteran" id="edit_is_pon_veteran" value="1" class="mt-1 w-4 h-4 text-amber-600 bg-white border-amber-300 rounded focus:ring-amber-500 focus:ring-2 cursor-pointer">
                <label for="edit_is_pon_veteran" class="text-xs font-bold text-amber-900 cursor-pointer">
                    🏅 Tetapkan sebagai Veteran PON
                    <p class="text-[10px] text-amber-700 font-medium mt-0.5 leading-relaxed">
                        Jika dicentang, atlet ini akan dipaksa turun di Kategori Senior di semua event mendatang (Sesuai regulasi PB Porserosi).
                    </p>
                </label>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-emerald-600 text-white font-black py-3.5 rounded-xl shadow-lg transition uppercase tracking-widest text-xs mt-4">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_skater_name').value = data.skater_name || '';
    document.getElementById('edit_gender').value = (data.gender || 'M').toUpperCase();
    document.getElementById('edit_birth_date').value = data.birth_date || '';
    document.getElementById('edit_asal_sekolah').value = data.asal_sekolah || '';
    document.getElementById('edit_kota_kabupaten').value = data.kota_kabupaten || '';
    document.getElementById('edit_provinsi').value = data.provinsi || '';
    document.getElementById('edit_is_pon_veteran').checked = (data.is_pon_veteran == 1);

    const isBpjsRegistered = (data.status_bpjs === 'sudah' || (parseInt(data.total_events) || 0) > 0);
    document.getElementById('edit_status_bpjs').checked = isBpjsRegistered;

    const note = document.getElementById('edit_bpjs_note');
    if ((parseInt(data.total_events) || 0) > 0) {
        note.innerHTML = '<span class="text-emerald-800 font-bold">✓ Atlet ini sudah otomatis terdaftar BPJS karena memiliki riwayat ' + data.total_events + ' event lomba.</span>';
    } else {
        note.innerHTML = 'Centang jika atlet sudah memiliki BPJS aktif. (Atlet yang pernah ikut event otomatis terhitung sudah terdaftar).';
    }

    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
