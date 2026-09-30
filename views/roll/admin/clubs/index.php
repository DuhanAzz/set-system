<?php include __DIR__ . '/../../../layouts/header.php'; ?>
<?php include __DIR__ . '/../../../layouts/sidebar_roll.php'; ?>

<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    
    <div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Manajemen Klub</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Kelola Master Data Klub & Kontingen</p>
        </div>
        
        <div class="flex gap-3 items-center">
            <button onclick="openModal('modal-add')" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-sm border border-blue-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full">
                <span class="text-lg leading-none">+</span> Tambah Klub
            </button>
            <div class="px-5 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-right">
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Klub</span>
                <span class="block text-xl font-black text-slate-800"><?= count($clubs) ?></span>
            </div>
        </div>
    </div>

    <div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px] p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="clubsTable">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest w-12 rounded-tl-xl">No</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Logo</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Klub</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Email Kontak</th>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($clubs)): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 italic font-bold">Belum ada data klub.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($clubs as $i => $c): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-6 font-black text-slate-300 italic text-xs"><?= $i + 1 ?></td>
                            <td class="py-3 px-4">
                                <?php if(!empty($c['logo_image'])): ?>
                                    <img src="<?= getenv('APP_URL') ?>/<?= str_replace('public/', '', $c['logo_image']) ?>" class="w-10 h-10 object-contain rounded-xl bg-white border border-slate-200">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex justify-center items-center text-xl shadow-sm border border-slate-200">🛼</div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 text-sm"><?= htmlspecialchars($c['club_name']) ?></td>
                            <td class="py-3 px-4 text-xs text-slate-500 font-medium"><?= htmlspecialchars($c['contact_email'] ?? '-') ?></td>
                            <td class="py-3 px-6 text-right">
                                <button onclick="editClub(<?= htmlspecialchars(json_encode($c)) ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold transition">Edit</button>
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
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase italic">Tambah Klub Baru</h3>
            <button onclick="closeModal('modal-add')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="p-6">
            <form action="<?= getenv('APP_URL') ?>/roll/admin/clubs/store" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Nama Klub / Kontingen</label>
                    <input type="text" name="club_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Email Kontak (Optional)</label>
                    <input type="email" name="contact_email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Logo (Optional)</label>
                    <input type="file" name="logo_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
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
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-800 uppercase italic">Edit Klub</h3>
            <button onclick="closeModal('modal-edit')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="p-6">
            <form id="formEdit" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Nama Klub / Kontingen</label>
                    <input type="text" id="edit_club_name" name="club_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Email Kontak (Optional)</label>
                    <input type="email" id="edit_contact_email" name="contact_email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase mb-1">Ubah Logo (Optional)</label>
                    <input type="file" name="logo_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">* Kosongkan jika tidak ingin mengubah logo</p>
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
        $('#clubsTable').DataTable({
            "language": {
                "search": "Cari Klub:",
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

    function editClub(c) {
        document.getElementById('edit_club_name').value = c.club_name;
        document.getElementById('edit_contact_email').value = c.contact_email || '';
        
        document.getElementById('formEdit').action = "<?= getenv('APP_URL') ?>/roll/admin/clubs/update/" + c.id;
        openModal('modal-edit');
    }
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
