<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 font-sans">
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <?php
            $fType = $_SESSION['flash_type'] ?? 'success';
            $fClass = $fType === 'success'
                ? 'text-emerald-800 bg-emerald-50 border-emerald-200'
                : ($fType === 'warning'
                    ? 'text-amber-900 bg-amber-50 border-amber-300'
                    : 'text-red-800 bg-red-50 border-red-200');
            $fIcon = $fType === 'success' ? '💡' : ($fType === 'warning' ? '⚠️' : '❌');
        ?>
        <div class="mb-4 p-4 text-xs <?= $fClass ?> rounded-xl border shadow-sm flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span><?= $fIcon ?></span> 
                <div><?= $_SESSION['flash_message'] ?></div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-xl opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Detail Laporan Import CSV -->
    <?php if (!empty($_SESSION['csv_import_report'])): ?>
        <?php $rep = $_SESSION['csv_import_report']; unset($_SESSION['csv_import_report']); ?>
        <?php if (!empty($rep['nik_warnings']) || !empty($rep['errors'])): ?>
            <div class="mb-5 p-4 text-xs bg-white border border-amber-300 rounded-xl shadow-sm space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-black uppercase tracking-wider text-amber-900 text-xs flex items-center gap-1.5">
                        <span>📋</span> Detail Catatan Import CSV
                    </h4>
                    <button onclick="this.closest('.mb-5').remove()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
                </div>
                <?php if (!empty($rep['nik_warnings'])): ?>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="font-black text-amber-900 text-[11px] mb-1.5">
                            ⚠️ <?= count($rep['nik_warnings']) ?> Atlet Masuk dengan Warning NIK (Klik tombol Edit di baris atlet untuk melengkapi NIK 16 digit):
                        </div>
                        <ul class="list-disc pl-4 space-y-0.5 max-h-40 overflow-y-auto text-[11px] text-amber-800 font-mono">
                            <?php foreach ($rep['nik_warnings'] as $nw): ?>
                                <li><?= htmlspecialchars($nw) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (!empty($rep['errors'])): ?>
                    <div class="bg-rose-50 border border-rose-200 rounded-lg p-3">
                        <div class="font-black text-rose-900 text-[11px] mb-1.5">
                            ❌ <?= count($rep['errors']) ?> Baris Dilewati (Nama / TTL / Gender tidak valid):
                        </div>
                        <ul class="list-disc pl-4 space-y-0.5 max-h-40 overflow-y-auto text-[11px] text-rose-800 font-mono">
                            <?php foreach ($rep['errors'] as $er): ?>
                                <li><?= htmlspecialchars($er) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- NIK Missing Reminder Banner -->
    <?php if (($missingNikCount ?? 0) > 0): ?>
        <div class="mb-5 p-4 text-xs text-rose-800 bg-rose-50 border-rose-200 rounded-xl border shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="font-black uppercase tracking-wider text-rose-900 text-sm">Peringatan: Terdapat <?= $missingNikCount ?> Atlet Belum Mengisi / Perlu Perbaikan NIK!</h4>
                    <p class="mt-0.5 text-rose-700 font-medium">Setiap atlet wajib melengkapi NIK 16 digit (KTP/KIA/KK) untuk verifikasi berkas & BPJS. Silakan klik tombol <strong>Edit</strong> pada atlet yang ditandai merah untuk melengkapinya.</p>
                </div>
            </div>
            <button type="button" id="btn-filter-missing-nik" onclick="toggleMissingNikFilter()"
                    class="shrink-0 px-3.5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-black text-[11px] uppercase tracking-wide shadow-sm transition cursor-pointer">
                🔍 Tampilkan NIK Bermasalah (<?= $missingNikCount ?>)
            </button>
        </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Database Atlet</h1>
            <p class="text-xs text-slate-500 font-medium">Manajemen Data & Profil Roster</p>
        </div>
        
        <div class="w-full md:w-auto flex flex-wrap sm:flex-nowrap items-center gap-2">
            
            <?php if(($_SESSION['roll_club_type'] ?? '') === 'contingent'): ?>
                <button onclick="document.getElementById('modal-draft-pool').classList.remove('hidden')" class="w-full sm:w-auto justify-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-lg shadow-sm text-xs font-bold tracking-wide transition flex items-center gap-2 whitespace-nowrap">
                    <span>🏛️</span> Tarik Atlet Daerah (Pool)
                </button>
            <?php endif; ?>

            <a href="<?= getenv('APP_URL') ?>/roll/user/athletes/export_csv"
               class="w-full sm:w-auto justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg shadow-sm text-xs font-bold tracking-wide transition flex items-center gap-1.5 whitespace-nowrap">
                <span>📤</span> Export CSV
            </a>

            <button type="button" onclick="document.getElementById('modal-import-csv').classList.remove('hidden')"
                    class="w-full sm:w-auto justify-center bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-sm text-xs font-bold tracking-wide transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                <span>📥</span> Import CSV (Massal)
            </button>

            <button onclick="document.getElementById('modal-add').classList.remove('hidden')" style="background-color: #3b82f6; color: white;" class="w-full sm:w-auto justify-center hover:opacity-80 px-4 py-2.5 rounded-lg shadow-sm text-xs font-bold tracking-wide transition flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Atlet Baru
            </button>

            <form method="GET" class="relative w-full sm:w-auto">
                <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                       class="pl-10 pr-4 py-2.5 text-xs font-bold border rounded-lg w-full md:w-80 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Cari Nama Atlet, Sekolah, Kota...">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Profil Atlet</th>
                        <th class="px-6 py-4">Sekolah & Daerah</th>
                        <th class="px-6 py-4 text-center">Jenis Kelamin</th>
                        <th class="px-6 py-4 text-center">Tanggal Lahir</th>
                        <th class="px-6 py-4 text-center">Status BPJS</th>
                        <th class="px-6 py-4 text-center">Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($athletes)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-slate-500 text-xs font-bold">Belum ada atlet yang didaftarkan.</td></tr>
                    <?php else: ?>
                        <?php foreach($athletes as $s): ?>
                        <tr class="athlete-row hover:bg-slate-50 transition duration-150 group" data-nik-missing="<?= $s['is_nik_missing'] ? '1' : '0' ?>">
                            
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= $s['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                        <?= substr($s['skater_name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($s['skater_name']) ?></span>
                                            <?php if (!empty($s['uid'])): ?>
                                                <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-mono font-bold">UID: <?= htmlspecialchars($s['uid']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($s['nik'])): ?>
                                                <span class="text-[9px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono font-bold tracking-wider">NIK: <?= htmlspecialchars($s['nik']) ?></span>
                                            <?php else: ?>
                                                <span class="text-[9px] bg-rose-50 text-rose-600 border border-rose-200 px-1.5 py-0.5 rounded font-bold animate-pulse">⚠️ Belum Ada NIK</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="font-mono text-slate-400 text-[10px] tracking-wide mt-0.5">
                                            ID: <span class="text-slate-600 font-bold"><?= str_pad($s['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                            <?php if ($s['is_data_incomplete']): ?>
                                                <span class="ml-2 inline-flex items-center gap-0.5 text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded text-[9px] border border-amber-200">
                                                    ⚠️ Data Belum Lengkap
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-3">
                                <div class="text-xs font-bold text-slate-800 flex items-start gap-1.5">
                                    <span class="mt-0.5">🏫</span>
                                    <div>
                                        <div><?= !empty($s['asal_sekolah']) ? htmlspecialchars($s['institusi_puspernas'] ?? $s['asal_sekolah']) : '<span class="text-slate-400 font-normal italic">- Belum diisi -</span>' ?></div>
                                        <?php if (!empty($s['transition_info']['needs_update'])): ?>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded text-[9px] font-bold" title="<?= htmlspecialchars($s['transition_info']['message']) ?>">
                                                    <span>💡</span> Saran: <?= htmlspecialchars($s['transition_info']['suggested_level']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full text-[10px] font-bold border border-slate-200">
                                    <?= $s['gender'] == 'M' ? 'Putra' : 'Putri' ?>
                                </span>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <div class="font-bold text-slate-700 text-xs"><?= !empty($s['birth_date']) ? date('d/m/Y', strtotime($s['birth_date'])) : '-' ?></div>
                                <div class="text-[10px] text-slate-400 font-medium"><?= htmlspecialchars($s['age_group']) ?></div>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <?php if (($s['status_bpjs'] ?? 'belum') === 'sudah'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                        <span>✓</span> Sudah Terdaftar
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                        Belum Terdaftar
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg shadow-sm text-[10px] font-bold tracking-wide transition flex items-center gap-1.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/destroy/<?= $s['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Hapus data atlet ini?');">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-2.5 py-1.5 rounded-lg shadow-sm text-[10px] font-bold tracking-wide transition flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
</div>

<!-- Modal Tambah -->
<div id="modal-add" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" onclick="document.getElementById('modal-add').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-black uppercase tracking-wide">Tambah Atlet Baru</h3>
                    <p class="text-xs text-slate-300 font-medium mt-0.5">Lengkapi data profil atlet untuk keperluan lomba & Puspernas</p>
                </div>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto bg-slate-50 p-6">
                <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/store" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap Atlet *</label>
                        <input type="text" name="skater_name" required placeholder="Contoh: BAGUS KURNIAWAN" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">NIK Atlet (16 Digit KTP / KIA / KK) *</label>
                        <input type="text" name="nik" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="Contoh: 3304012345670001 (16 digit angka)" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none font-mono tracking-wider">
                        <span class="text-[10px] text-slate-400">Wajib 16 digit angka sesuai KTP/KIA/KK untuk verifikasi berkas lomba & BPJS.</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jenis Kelamin *</label>
                            <select name="gender" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">- Pilih -</option>
                                <option value="M">Putra (Male)</option>
                                <option value="F">Putri (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Lahir *</label>
                            <input type="date" name="birth_date" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                            <span>🏫</span> Nama Asal Sekolah
                        </label>
                        <input type="text" name="asal_sekolah" placeholder="Contoh: SMP NEGERI 1 YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        <span class="text-[10px] text-slate-400">Diperlukan untuk verifikasi keabsahan data Puspernas / O2SN</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Kota / Kabupaten</label>
                            <input type="text" name="kota_kabupaten" placeholder="Contoh: KOTA YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Provinsi</label>
                            <input type="text" name="provinsi" placeholder="Contoh: D.I. YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-start gap-3 mt-2">
                        <input type="checkbox" name="status_bpjs" id="add_status_bpjs" value="sudah" class="mt-1 w-4 h-4 text-emerald-600 bg-white border-emerald-300 rounded focus:ring-emerald-500 focus:ring-2 cursor-pointer">
                        <label for="add_status_bpjs" class="text-xs font-bold text-emerald-900 cursor-pointer">
                            🛡️ Sudah Memiliki Kepesertaan BPJS
                            <p class="text-[10px] text-emerald-700 font-medium mt-0.5 leading-relaxed">
                                Centang jika atlet sudah memiliki BPJS aktif. Kosongkan jika belum terdaftar BPJS.
                            </p>
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 mt-6 pt-6">
                        <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-6 py-2.5 rounded-lg text-slate-600 font-bold text-xs uppercase hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-bold text-xs uppercase hover:bg-blue-700 shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Atlet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" onclick="document.getElementById('modal-edit').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-black uppercase tracking-wide">Edit Biodata Atlet</h3>
                    <p class="text-xs text-slate-300 font-medium mt-0.5">Perbarui informasi profil dan kelengkapan data Puspernas</p>
                </div>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto bg-slate-50 p-6">
                <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/update" method="POST" class="space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <!-- Smart School Transition Alert Box -->
                    <div id="edit_transition_alert" class="hidden p-3.5 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 flex items-start gap-2.5">
                        <span class="text-lg">💡</span>
                        <div>
                            <strong class="font-bold text-indigo-950 block">Pengingat Kenaikan Jenjang Sekolah:</strong>
                            <p id="edit_transition_msg" class="text-[11px] text-indigo-700 mt-0.5 leading-relaxed"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap Atlet *</label>
                        <input type="text" name="skater_name" id="edit_skater_name" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">NIK Atlet (16 Digit KTP / KIA / KK) *</label>
                        <input type="text" name="nik" id="edit_nik" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="Contoh: 3304012345670001 (16 digit angka)" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none font-mono tracking-wider">
                        <span class="text-[10px] text-slate-400">Wajib 16 digit angka sesuai KTP/KIA/KK untuk verifikasi identitas atlet.</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jenis Kelamin *</label>
                            <select name="gender" id="edit_gender" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="M">Putra (Male)</option>
                                <option value="F">Putri (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Lahir *</label>
                            <input type="date" name="birth_date" id="edit_birth_date" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                            <span>🏫</span> Nama Asal Sekolah
                        </label>
                        <input type="text" name="asal_sekolah" id="edit_asal_sekolah" placeholder="Contoh: SMP NEGERI 1 YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        <span class="text-[10px] text-slate-400">Diperlukan untuk verifikasi keabsahan data Puspernas / O2SN</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Kota / Kabupaten</label>
                            <input type="text" name="kota_kabupaten" id="edit_kota_kabupaten" placeholder="Contoh: KOTA YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Provinsi</label>
                            <input type="text" name="provinsi" id="edit_provinsi" placeholder="Contoh: D.I. YOGYAKARTA" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex items-start gap-3 mt-2">
                        <input type="checkbox" name="status_bpjs" id="edit_status_bpjs" value="sudah" class="mt-1 w-4 h-4 text-emerald-600 bg-white border-emerald-300 rounded focus:ring-emerald-500 focus:ring-2 cursor-pointer">
                        <label for="edit_status_bpjs" class="text-xs font-bold text-emerald-900 cursor-pointer">
                            🛡️ Sudah Memiliki Kepesertaan BPJS
                            <p class="text-[10px] text-emerald-700 font-medium mt-0.5 leading-relaxed" id="edit_bpjs_note">
                                Centang jika atlet sudah memiliki BPJS aktif. Kosongkan jika belum terdaftar BPJS.
                            </p>
                        </label>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 mt-6 pt-6">
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-6 py-2.5 rounded-lg text-slate-600 font-bold text-xs uppercase hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white font-bold text-xs uppercase hover:bg-emerald-700 shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_skater_name').value = data.skater_name || '';
    document.getElementById('edit_nik').value = data.nik || '';
    document.getElementById('edit_gender').value = (data.gender || 'M').toUpperCase();
    document.getElementById('edit_birth_date').value = data.birth_date || '';
    document.getElementById('edit_asal_sekolah').value = data.asal_sekolah || '';
    document.getElementById('edit_provinsi').value = data.provinsi || '';
    document.getElementById('edit_kota_kabupaten').value = data.kota_kabupaten || '';

    const isBpjsRegistered = (data.status_bpjs === 'sudah');
    const chk = document.getElementById('edit_status_bpjs');
    chk.checked = isBpjsRegistered;

    const note = document.getElementById('edit_bpjs_note');
    note.innerHTML = 'Centang jika atlet sudah memiliki BPJS aktif. Kosongkan jika belum terdaftar BPJS.';

    // Smart Transition Alert Box
    const transAlert = document.getElementById('edit_transition_alert');
    const transMsg = document.getElementById('edit_transition_msg');
    if (data.transition_info && data.transition_info.needs_update) {
        transMsg.innerText = data.transition_info.message + ' (Saran: Jenjang ' + data.transition_info.suggested_level + ')';
        transAlert.classList.remove('hidden');
    } else {
        transAlert.classList.add('hidden');
    }

    document.getElementById('modal-edit').classList.remove('hidden');
}

let poolSearchTimer = null;
function searchPoolAthletes() {
    clearTimeout(poolSearchTimer);
    const q = document.getElementById('pool_search_input').value.trim();
    const container = document.getElementById('pool_results_container');

    if (q.length < 2) {
        container.innerHTML = '<div class="text-center py-12 text-slate-400 text-xs font-bold">Ketik minimal 2 karakter untuk mencari atlet...</div>';
        return;
    }

    container.innerHTML = '<div class="text-center py-12 text-purple-600 text-xs font-bold animate-pulse">Sedang mencari data atlet di database daerah...</div>';

    poolSearchTimer = setTimeout(() => {
        fetch('<?= getenv("APP_URL") ?>/roll/user/athletes/search_pool?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(res => {
                if (!res.data || res.data.length === 0) {
                    container.innerHTML = '<div class="text-center py-12 text-slate-400 text-xs font-bold">Tidak ada atlet yang cocok dengan kata kunci "' + q + '".</div>';
                    return;
                }
                let html = '';
                res.data.forEach(item => {
                    const dob = item.birth_date ? item.birth_date : '-';
                    const jk = (item.gender === 'M' || item.gender === 'L') ? 'Laki-laki' : 'Perempuan';
                    const sekolah = item.asal_sekolah ? item.asal_sekolah : 'Sekolah Belum Diisi';
                    const kota = item.kota_kabupaten || item.club_city || '-';
                    const prov = item.provinsi || '-';
                    const klub = item.origin_club || 'Klub Asal';

                    html += `
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:border-purple-300 transition shadow-sm">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-black text-slate-800 text-sm">${item.skater_name}</span>
                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">UID: ${item.uid || '-'}</span>
                                    ${item.nik ? `<span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">NIK: ${item.nik}</span>` : ''}
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-100">${jk} • ${dob}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium mt-1 flex flex-wrap gap-x-3 gap-y-0.5">
                                    <span>🏫 ${sekolah}</span>
                                    <span>📍 ${kota}, ${prov}</span>
                                    <span class="text-purple-600 font-bold">🏠 Asal: ${klub}</span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                ${item.already_drafted 
                                    ? '<span class="inline-block px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold">✓ Sudah Ada di Roster</span>'
                                    : `<form action="<?= getenv('APP_URL') ?>/roll/user/athletes/draft_athlete" method="POST" onsubmit="return confirm('Tarik atlet ${item.skater_name} ke dalam kontingen Anda?')">
                                         <input type="hidden" name="skater_id" value="${item.id}">
                                         <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                                             <span>📥</span> Tarik ke Kontingen
                                         </button>
                                       </form>`
                                }
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div class="text-center py-12 text-red-500 text-xs font-bold">Gagal memuat data atlet. Silakan coba lagi.</div>';
            });
    }, 300);
}

let showingOnlyMissingNik = false;
function toggleMissingNikFilter() {
    showingOnlyMissingNik = !showingOnlyMissingNik;
    const rows = document.querySelectorAll('tr.athlete-row');
    const btn = document.getElementById('btn-filter-missing-nik');
    rows.forEach(row => {
        if (showingOnlyMissingNik && row.getAttribute('data-nik-missing') !== '1') {
            row.classList.add('hidden');
        } else {
            row.classList.remove('hidden');
        }
    });
    if (btn) {
        if (showingOnlyMissingNik) {
            btn.textContent = '✓ Tampilkan Semua Atlet';
            btn.classList.replace('bg-rose-600', 'bg-slate-800');
            btn.classList.replace('hover:bg-rose-700', 'hover:bg-slate-900');
        } else {
            btn.textContent = '🔍 Tampilkan NIK Bermasalah (<?= (int)($missingNikCount ?? 0) ?>)';
            btn.classList.replace('bg-slate-800', 'bg-rose-600');
            btn.classList.replace('hover:bg-slate-900', 'hover:bg-rose-700');
        }
    }
}
</script>

<!-- Modal Draft Pool (Khusus Official Kontingen) -->
<div id="modal-draft-pool" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-purple-100 flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🏛️</span>
                <div>
                    <h3 class="text-base font-black text-slate-800 uppercase tracking-tight">Tarik Atlet dari Database Daerah (Pool Drafting)</h3>
                    <p class="text-xs text-purple-600 font-bold">Klaim atlet dari klub-klub di daerah Anda ke dalam roster resmi kontingen</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-draft-pool').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <div class="py-4">
            <div class="relative">
                <input type="text" id="pool_search_input" onkeyup="searchPoolAthletes()" placeholder="Ketik nama atlet, UID, atau sekolah..." 
                       class="w-full pl-10 pr-4 py-3 bg-purple-50/50 border border-purple-200 rounded-xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-white transition">
                <span class="absolute left-3.5 top-3.5 text-slate-400">🔍</span>
            </div>
        </div>

        <div id="pool_results_container" class="flex-1 overflow-y-auto min-h-[250px] max-h-[400px] border border-slate-100 rounded-xl p-3 bg-slate-50 space-y-2">
            <div class="text-center py-12 text-slate-400 text-xs font-bold">
                Ketik minimal 2 karakter untuk mencari atlet dari database daerah...
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="document.getElementById('modal-draft-pool').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Import CSV Massal -->
<div id="modal-import-csv" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-200">
        <div class="bg-slate-800 p-6 text-white flex justify-between items-center">
            <div>
                <h3 class="text-lg font-black uppercase tracking-wide flex items-center gap-2">
                    <span>📥</span> Import Massal Data Atlet (CSV)
                </h3>
                <p class="text-xs text-slate-300 font-medium mt-0.5">Daftarkan atau perbarui puluhan hingga ratusan atlet sekaligus lewat file CSV</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-csv').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
        </div>

        <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/import_csv" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 bg-slate-50">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 space-y-2">
                <div class="font-black uppercase tracking-wider text-[11px] flex items-center justify-between gap-2">
                    <span>Panduan Format CSV</span>
                    <a href="<?= getenv('APP_URL') ?>/roll/user/athletes/download_template"
                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[10px] font-black uppercase shadow-sm transition">
                        📄 Download Template CSV
                    </a>
                </div>
                <ul class="list-disc pl-4 space-y-1 text-[11px] text-blue-800">
                    <li>Anda dapat mengisi <strong>Template CSV</strong> dari nol, atau langsung mengedit file hasil <strong>Export CSV</strong> (untuk melengkapi/menambah atlet).</li>
                    <li><strong>Data Wajib Saat Import:</strong> <strong>Nama Lengkap Atlet</strong>, <strong>Jenis Kelamin</strong> (<code>M</code>/<code>Putra</code> atau <code>F</code>/<code>Putri</code>), dan <strong>Tanggal Lahir format Indonesia <code>DD/MM/YYYY</code></strong> (mis. <code>12/05/2015</code>).</li>
                    <li><strong>NIK (16 Digit):</strong> File Export memakai format <code>="3304..."</code> agar tidak berubah jadi <code>E+15</code> di Excel. Namun jika NIK di CSV terlanjur rusak (<code>E+15</code>, kosong, <code>#N/A</code>, atau belum 16 digit), atlet <strong>tetap masuk dulu dengan tanda warning</strong> agar bisa dilengkapi manual lewat tombol <strong>Edit</strong> (NIK lama yang sudah valid di database tidak akan tertimpa).</li>
                    <li><strong>Status BPJS:</strong> isi <code>sudah</code> atau <code>belum</code>.</li>
                    <li>Jika <strong>ID / NIK / (Nama + Tanggal Lahir)</strong> sudah ada di klub Anda, datanya otomatis <strong>diperbarui</strong> (tidak ganda).</li>
                </ul>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-1.5">Pilih File CSV (.csv) *</label>
                <input type="file" name="csv_file" accept=".csv,text/csv" required
                       class="w-full text-xs font-bold text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-slate-800 file:text-white hover:file:bg-slate-900 border border-slate-300 rounded-xl p-1.5 bg-white cursor-pointer">
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-import-csv').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-lg text-slate-600 font-bold text-xs uppercase hover:bg-slate-200 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase shadow-md transition flex items-center gap-2 cursor-pointer">
                    <span>📤</span> Mulai Import CSV
                </button>
            </div>
        </form>
    </div>
</div>
