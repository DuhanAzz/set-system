<?php include __DIR__ . '/../../../layouts/header.php'; ?>
<?php include __DIR__ . '/../../../layouts/sidebar_roll.php'; ?>

<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    
    <div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Manajemen Skater</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Kelola Master Data Atlet</p>
        </div>
        
        <div class="flex gap-3 items-center">
            <button onclick="openModal('modal-add')" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-sm border border-blue-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full">
                <span class="text-lg leading-none">+</span> Tambah Skater
            </button>
            <div class="px-5 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-right">
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Skater</span>
                <span class="block text-xl font-black text-slate-800"><?= count($skaters) ?></span>
            </div>
        </div>
    </div>

    <div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px] p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="skatersTable">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest w-12 rounded-tl-xl">No</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Skater</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Klub</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">L/P</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Tanggal Lahir</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Kelompok Umur</th>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($skaters)): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 italic font-bold">Belum ada data skater.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($skaters as $i => $s): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-6 font-black text-slate-300 italic text-xs"><?= $i + 1 ?></td>
                            <td class="py-3 px-4 font-bold text-slate-800 text-sm"><?= htmlspecialchars($s['skater_name']) ?></td>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-600"><?= htmlspecialchars($s['club_name'] ?? '-') ?></td>
                            <td class="py-3 px-4 text-center">
                                <?php if(in_array($s['gender'], ['M', 'Putra'])): ?>
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-black uppercase">Putra</span>
                                <?php else: ?>
                                    <span class="bg-pink-100 text-pink-700 px-2 py-0.5 rounded text-[10px] font-black uppercase">Putri</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center text-xs text-slate-500 font-medium">
                                <?= $s['birth_date'] ? date('d M Y', strtotime($s['birth_date'])) : '-' ?>
                            </td>
                            <td class="py-3 px-4 text-center text-xs text-slate-500 font-bold">
                                <?= htmlspecialchars($s['age_group'] ?? '-') ?>
                            </td>
                            <td class="py-3 px-6 text-right">
                                <button onclick="editSkater(<?= htmlspecialchars(json_encode($s)) ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold transition">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div id="modal-add" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase italic">Tambah Skater Baru</h3>
            <button onclick="closeModal('modal-add')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="p-6">
            <form action="<?= getenv('APP_URL') ?>/roll/admin/skaters/store" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="skater_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Klub</label>
                    <select name="club_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                        <option value="">- Pilih Klub -</option>
                        <?php foreach($clubs as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['club_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase mb-1">Jenis Kelamin</label>
                        <select name="gender" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="M">Putra</option>
                            <option value="F">Putri</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase mb-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Kelompok Umur (Optional)</label>
                    <input type="text" name="age_group" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition" placeholder="Contoh: KU A">
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('modal-add')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl text-xs uppercase tracking-widest transition">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-widest shadow-sm transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase italic">Edit Skater</h3>
            <button onclick="closeModal('modal-edit')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="p-6">
            <form id="formEdit" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_skater_name" name="skater_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Klub</label>
                    <select id="edit_club_id" name="club_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                        <option value="">- Pilih Klub -</option>
                        <?php foreach($clubs as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['club_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase mb-1">Jenis Kelamin</label>
                        <select id="edit_gender" name="gender" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                            <option value="M">Putra</option>
                            <option value="F">Putri</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-600 uppercase mb-1">Tanggal Lahir</label>
                        <input type="date" id="edit_birth_date" name="birth_date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Kelompok Umur (Optional)</label>
                    <input type="text" id="edit_age_group" name="age_group" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal('modal-edit')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl text-xs uppercase tracking-widest transition">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs uppercase tracking-widest shadow-sm transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#skatersTable').DataTable({
            "language": {
                "search": "Cari Skater:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data yang tersedia",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function editSkater(s) {
        document.getElementById('edit_skater_name').value = s.skater_name;
        document.getElementById('edit_club_id').value = s.club_id || '';
        document.getElementById('edit_gender').value = s.gender === 'Putri' ? 'F' : (s.gender === 'F' ? 'F' : 'M');
        document.getElementById('edit_birth_date').value = s.birth_date || '';
        document.getElementById('edit_age_group').value = s.age_group || '';
        
        document.getElementById('formEdit').action = "<?= getenv('APP_URL') ?>/roll/admin/skaters/update/" + s.id;
        openModal('modal-edit');
    }
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
