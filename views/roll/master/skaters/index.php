<?php 
// FILE: views/roll/master/skaters/index.php
?>
<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Global Skaters</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Radar Atlet Sepatu Roda</p>
    </div>
    
    <div class="flex gap-2">
        <form method="GET" class="relative">
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari Atlet / Klub..." 
                   class="pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm font-bold w-full md:w-64 focus:outline-none focus:border-blue-500 shadow-sm">
            <span class="absolute left-3 top-2 text-slate-400">🔍</span>
        </form>
        <div class="bg-white p-1 rounded-xl shadow-sm border border-slate-200 flex">
            <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/index" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition bg-slate-900 text-white shadow-md">Daftar Skater</a>
            <a href="<?= getenv('APP_URL') ?>/roll/master/skaters/history_transfer" class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase transition text-slate-400 hover:bg-slate-50">Riwayat Mutasi</a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' ?> font-bold shadow-sm">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-slate-50/50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center w-16">No</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest">Nama Atlet</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center">Gender</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-center">Tgl Lahir / Umur</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest">Klub Asal</th>
                    <th class="px-6 py-5 font-black uppercase text-[10px] text-slate-400 tracking-widest text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if(empty($skaters)): ?>
                    <tr><td colspan="6" class="px-8 py-20 text-center text-slate-400 font-bold italic uppercase text-xs">Belum ada atlet.</td></tr>
                <?php else: ?>
                    <?php $no=1; foreach($skaters as $s): 
                        // Hitung Umur berdasarkan Tanggal Lahir (Birth Date)
                        $umur = '-';
                        if(!empty($s['birth_date'])) {
                            $dob = new DateTime($s['birth_date']);
                            $now = new DateTime();
                            // Standar umur biasanya dihitung per 31 Des tahun lomba, tapi untuk master view kita tampilkan umur aktual
                            $umur = $dob->diff($now)->y . ' Thn';
                        }
                    ?>
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4 text-center font-bold text-slate-400"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-800 uppercase italic"><?= htmlspecialchars($s['skater_name']) ?></div>
                            <div class="text-[10px] font-bold text-slate-400 mt-1">Reg: <?= date('d M Y', strtotime($s['created_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if(strtoupper($s['gender']) == 'M'): ?>
                                <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Pa</span>
                            <?php else: ?>
                                <span class="bg-pink-100 text-pink-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Pi</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="font-bold text-slate-700"><?= !empty($s['birth_date']) ? date('d M Y', strtotime($s['birth_date'])) : '-' ?></div>
                            <div class="text-[10px] font-black text-amber-500 uppercase mt-0.5"><?= $umur ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if(!empty($s['club_name'])): ?>
                                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-[10px] font-black uppercase">
                                    <?= htmlspecialchars($s['club_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-xs italic text-slate-400">Tidak ada klub</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <!-- Edit Button Placeholder for Master -->
                            <button class="bg-white border border-slate-200 hover:border-amber-500 hover:text-amber-600 text-slate-400 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm" onclick="alert('Fitur edit detail segera hadir.')">
                                ✏️ Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
