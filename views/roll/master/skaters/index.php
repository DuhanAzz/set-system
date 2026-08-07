<?php 
// FILE: views/roll/master/skaters/index.php
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
                <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Database Skater</h1>
                <p class="text-xs text-slate-500 font-medium">Manajemen Data & Afiliasi Klub</p>
            </div>
            
            <div class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-2">
                <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex w-full sm:w-auto mb-2 sm:mb-0">
                    <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/index" class="px-4 py-2 w-full text-center sm:w-auto rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Daftar Skater</a>
                    <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/history_transfer" class="px-4 py-2 w-full text-center sm:w-auto rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Riwayat Mutasi</a>
                </div>

                <form method="GET" class="relative w-full sm:w-auto">
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                           class="pl-10 pr-4 py-2.5 text-xs font-bold border rounded-lg w-full md:w-80 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Cari Nama Atlet atau Klub...">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-[1.5rem] shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="p-5">Profil Skater</th>
                            <th class="p-5">Klub Asal</th>
                            <th class="p-5 text-center">Tgl Lahir / Umur</th>
                            <th class="p-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($skaters)): ?>
                            <tr>
                                <td colspan="4" class="p-12 text-center">
                                    <div class="text-4xl mb-2">🛼</div>
                                    <div class="text-slate-400 font-bold italic">Belum ada data skater terdaftar.</div>
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
                            ?>
                            <tr class="hover:bg-slate-50 transition duration-200 group">
                                <td class="p-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black border-2 <?= strtoupper($s['gender']) == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                            <?= strtoupper($s['gender']) == 'M' ? 'Pa' : 'Pi' ?>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-800 text-sm uppercase"><?= htmlspecialchars($s['skater_name']) ?></div>
                                            <div class="font-mono text-slate-400 text-[10px] tracking-wide mt-0.5">
                                                Reg: <span class="font-bold text-slate-500"><?= date('d M Y', strtotime($s['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-5">
                                    <?php if(!empty($s['club_name'])): ?>
                                        <div class="font-bold text-slate-700 text-xs uppercase bg-slate-100 px-2 py-1 rounded inline-block">
                                            <?= htmlspecialchars($s['club_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] italic text-slate-400 font-bold">TIDAK ADA KLUB</span>
                                    <?php endif; ?>
                                </td>

                                <td class="p-5 text-center align-middle">
                                    <div class="font-black text-slate-700 text-sm"><?= !empty($s['birth_date']) ? date('d/m/Y', strtotime($s['birth_date'])) : '-' ?></div>
                                    <div class="text-[10px] text-amber-500 font-black uppercase mt-0.5 tracking-wider"><?= $umur ?></div>
                                </td>

                                <td class="p-5 text-center align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <button class="text-slate-400 bg-white border border-slate-200 hover:border-amber-500 hover:text-amber-600 px-3 py-2 rounded-lg text-xs font-bold transition shadow-sm" onclick='openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8") ?>)'>
                                            Edit
                                        </button>
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
</div>

<!-- Modal Edit Skater -->
<div id="modal-edit" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="bg-slate-900 p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <div><h3 class="font-black uppercase tracking-widest italic text-lg leading-none">Edit Biodata Skater</h3></div>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-red-500 transition">✕</button>
        </div>
        <form action="<?= getenv('APP_URL') ?>/roll/master/skaters/update" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="space-y-3">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-1">Detail Profil</h4>
                
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Nama Lengkap Skater</label>
                    <input type="text" name="skater_name" id="edit_skater_name" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Jenis Kelamin</label>
                        <select name="gender" id="edit_gender" required class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none">
                            <option value="M">Putra (Male)</option>
                            <option value="F">Putri (Female)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="edit_birth_date" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-amber-500 text-white font-black py-4 rounded-xl shadow-lg transition uppercase tracking-widest text-xs mt-4">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_skater_name').value = data.skater_name;
    document.getElementById('edit_gender').value = data.gender.toUpperCase();
    document.getElementById('edit_birth_date').value = data.birth_date;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
