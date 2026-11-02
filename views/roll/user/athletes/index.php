<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-50 font-sans">
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-4 p-4 text-xs <?= $_SESSION['flash_type'] === 'success' ? 'text-emerald-800 bg-emerald-50 border-emerald-200' : 'text-red-800 bg-red-50 border-red-200' ?> rounded-xl border shadow-sm flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span><?= $_SESSION['flash_type'] === 'success' ? '💡' : '⚠️' ?></span> 
                <div><?= $_SESSION['flash_message'] ?></div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-xl opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Database Atlet</h1>
            <p class="text-xs text-slate-500 font-medium">Manajemen Data & Profil Roster</p>
        </div>
        
        <div class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-2">
            
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
                        <tr class="hover:bg-slate-50 transition duration-150 group">
                            
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= $s['gender'] == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                        <?= substr($s['skater_name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($s['skater_name']) ?></span>
                                            <?php if (!empty($s['uid'])): ?>
                                                <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-mono font-bold"><?= htmlspecialchars($s['uid']) ?></span>
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
                                <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🏫</span>
                                    <span><?= !empty($s['asal_sekolah']) ? htmlspecialchars($s['asal_sekolah']) : '<span class="text-slate-400 font-normal italic">- Belum diisi -</span>' ?></span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5 flex items-center gap-1">
                                    <span>📍</span>
                                    <span><?= !empty($s['kota_kabupaten']) ? htmlspecialchars($s['kota_kabupaten']) : '-' ?><?= !empty($s['provinsi']) ? ', ' . htmlspecialchars($s['provinsi']) : '' ?></span>
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
                                <?php if ($s['bpjs_badge'] === 'sudah_event'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Otomatis terdaftar karena pernah ikut <?= $s['total_events'] ?> event">
                                        <span>✓</span> Terdaftar (Event)
                                    </span>
                                <?php elseif ($s['bpjs_badge'] === 'sudah_mandiri'): ?>
                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Didaftarkan mandiri oleh klub/official">
                                        <span>✓</span> Terdaftar (Mandiri)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase" title="Belum pernah ikut event & belum terdaftar">
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
                            🛡️ Sudah Memiliki Kepesertaan BPJS / Asuransi Mandiri
                            <p class="text-[10px] text-emerald-700 font-medium mt-0.5 leading-relaxed">
                                Centang jika atlet sudah memiliki BPJS aktif. Jika atlet pernah/sudah mengikuti event lomba di sistem, status kepesertaan akan otomatis ditandai terdaftar oleh sistem.
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
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap Atlet *</label>
                        <input type="text" name="skater_name" id="edit_skater_name" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
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
                            🛡️ Sudah Memiliki Kepesertaan BPJS / Asuransi Mandiri
                            <p class="text-[10px] text-emerald-700 font-medium mt-0.5 leading-relaxed" id="edit_bpjs_note">
                                Centang jika atlet sudah memiliki BPJS aktif. Jika atlet pernah/sudah mengikuti event lomba di sistem, status kepesertaan akan otomatis ditandai terdaftar.
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
    document.getElementById('edit_gender').value = (data.gender || 'M').toUpperCase();
    document.getElementById('edit_birth_date').value = data.birth_date || '';
    document.getElementById('edit_asal_sekolah').value = data.asal_sekolah || '';
    document.getElementById('edit_provinsi').value = data.provinsi || '';
    document.getElementById('edit_kota_kabupaten').value = data.kota_kabupaten || '';

    const isBpjsRegistered = (data.status_bpjs === 'sudah' || (parseInt(data.total_events) || 0) > 0);
    const chk = document.getElementById('edit_status_bpjs');
    chk.checked = isBpjsRegistered;

    const note = document.getElementById('edit_bpjs_note');
    if ((parseInt(data.total_events) || 0) > 0) {
        note.innerHTML = '<span class="text-emerald-800 font-bold">✓ Atlet ini sudah otomatis terdaftar BPJS karena memiliki riwayat ' + data.total_events + ' event lomba.</span>';
    } else {
        note.innerHTML = 'Centang jika atlet sudah memiliki BPJS aktif. Jika atlet pernah/sudah mengikuti event lomba di sistem, status kepesertaan akan otomatis ditandai terdaftar.';
    }

    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
