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
                       placeholder="Cari Nama Atlet...">
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
                        <th class="px-6 py-4 text-center">Jenis Kelamin</th>
                        <th class="px-6 py-4 text-center">Tanggal Lahir</th>
                        <th class="px-6 py-4 text-center">Umur (Akhir Tahun)</th>
                        <th class="px-6 py-4 text-center">Kontrol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($athletes)): ?>
                        <tr><td colspan="5" class="p-8 text-center text-slate-500 text-xs font-bold">Belum ada atlet yang didaftarkan.</td></tr>
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
                                            <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        </div>
                                        <div class="font-mono text-slate-400 text-[10px] tracking-wide">
                                            ID: <span class="text-blue-600 font-bold"><?= str_pad($s['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full text-[10px] font-bold border border-slate-200">
                                    <?= $s['gender'] == 'M' ? 'Putra' : 'Putri' ?>
                                </span>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <div class="font-bold text-slate-700 text-xs"><?= htmlspecialchars($s['birth_date']) ?></div>
                            </td>

                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">
                                    <?= htmlspecialchars($s['age_group']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-3 flex justify-center items-center gap-2">
                                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg shadow-sm text-[10px] font-bold tracking-wide transition flex items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                                <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/destroy/<?= $s['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Hapus data atlet ini?');">
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg shadow-sm text-[10px] font-bold tracking-wide transition flex items-center gap-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </form>
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
                <h3 class="text-xl font-black uppercase tracking-wide">Tambah Atlet Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto bg-slate-50 p-6">
                <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/store" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap</label>
                        <input type="text" name="skater_name" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jenis Kelamin</label>
                            <select name="gender" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">- Pilih -</option>
                                <option value="M">Putra (Male)</option>
                                <option value="F">Putri (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 mt-6 pt-6">
                        <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-6 py-2 rounded-lg text-slate-600 font-bold text-xs uppercase hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-bold text-xs uppercase hover:bg-blue-700 shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan
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
                <h3 class="text-xl font-black uppercase tracking-wide">Edit Biodata Atlet</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto bg-slate-50 p-6">
                <form action="<?= getenv('APP_URL') ?>/roll/user/athletes/update" method="POST" class="space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap</label>
                        <input type="text" name="skater_name" id="edit_skater_name" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jenis Kelamin</label>
                            <select name="gender" id="edit_gender" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="M">Putra (Male)</option>
                                <option value="F">Putri (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" id="edit_birth_date" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 mt-6 pt-6">
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-6 py-2 rounded-lg text-slate-600 font-bold text-xs uppercase hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 text-white font-bold text-xs uppercase hover:bg-emerald-700 shadow-md transition flex items-center gap-2">
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
    document.getElementById('edit_skater_name').value = data.skater_name;
    document.getElementById('edit_gender').value = data.gender;
    document.getElementById('edit_birth_date').value = data.birth_date;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
