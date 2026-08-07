<div class="mb-8 font-sans flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl md:text-4xl font-black text-slate-800 uppercase italic">Roster Skater</h1>
        <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">Kelola data atlet klub Anda</p>
    </div>
    <button onclick="document.getElementById('addSkaterModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-6 rounded-2xl shadow-lg shadow-blue-200 transition-all uppercase text-[10px] tracking-widest flex items-center gap-2">
        <span>+</span> Tambah Skater
    </button>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
        <div>
            <?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?>
        </div>
        <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
    </div>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 font-sans overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900 text-slate-300 font-black uppercase text-[10px] tracking-widest">
                <tr>
                    <th class="px-6 py-5 whitespace-nowrap">Nama Skater</th>
                    <th class="px-6 py-5 text-center whitespace-nowrap">L/P</th>
                    <th class="px-6 py-5 text-center whitespace-nowrap">Tgl Lahir</th>
                    <th class="px-6 py-5 text-center whitespace-nowrap">Usia & KU (<?= date('Y') ?>)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($skaters)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold italic">Belum ada skater yang terdaftar di roster klub Anda.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($skaters as $s): ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4 font-black text-slate-800 uppercase italic">
                                <?= htmlspecialchars($s['skater_name']) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($s['gender'] == 'M'): ?>
                                    <span class="inline-block w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black mx-auto shadow-inner text-xs">L</span>
                                <?php else: ?>
                                    <span class="inline-block w-8 h-8 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center font-black mx-auto shadow-inner text-xs">P</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">
                                <?= date('d M Y', strtotime($s['birth_date'])) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <p class="text-sm font-black text-slate-800"><?= $s['dynamic_age'] ?> Tahun</p>
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest inline-block mt-1">
                                    <?= htmlspecialchars($s['dynamic_ku']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Skater -->
<div id="addSkaterModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex justify-center items-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h2 class="text-xl font-black text-slate-800 uppercase italic">Tambah Skater</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registrasi Roster Baru</p>
            </div>
            <button onclick="document.getElementById('addSkaterModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 text-2xl outline-none">&times;</button>
        </div>
        
        <div class="p-6 overflow-y-auto">
            <form action="<?= getenv('APP_URL') ?>/roll/user/skaters/store" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Lengkap Skater <span class="text-red-500">*</span></label>
                    <input type="text" name="skater_name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 uppercase transition-all" placeholder="Misal: BUDI SANTOSO">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="M">Laki-laki (Putra)</option>
                        <option value="F">Perempuan (Putri)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="birth_date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                    <p class="text-[9px] text-blue-500 font-bold uppercase tracking-wider mt-2 bg-blue-50 p-2 rounded-lg">* Usia & Kelas Umur (KU) akan otomatis dihitung berdasarkan tahun ini.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-200 transition-all uppercase text-[10px] tracking-widest">
                        Simpan Skater ➜
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
