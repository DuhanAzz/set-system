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

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Profil Skater</th>
                            <th class="px-6 py-4">Klub Asal</th>
                            <th class="px-6 py-4 text-center">Tgl Lahir / Umur</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(empty($skaters)): ?>
                            <tr><td colspan="4" class="px-8 py-20 text-center text-slate-400 font-bold italic uppercase text-xs">Belum ada atlet terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach($skaters as $s): 
                                $umur = '-';
                                if(!empty($s['birth_date'])) {
                                    $dob = new DateTime($s['birth_date']);
                                    $now = new DateTime();
                                    $umur = $dob->diff($now)->y . ' Thn';
                                }
                            ?>
                            <tr class="hover:bg-slate-50 transition duration-150 group">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 <?= strtoupper($s['gender']) == 'M' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-pink-50 text-pink-600 border-pink-100' ?>">
                                            <?= strtoupper($s['gender']) == 'M' ? 'Pa' : 'Pi' ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($s['skater_name']) ?></span>
                                            </div>
                                            <div class="font-mono text-slate-400 text-[10px] tracking-wide mt-0.5">
                                                Reg: <span class="font-bold text-slate-500"><?= date('d M Y', strtotime($s['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-3">
                                    <?php if(!empty($s['club_name'])): ?>
                                        <div class="font-bold text-slate-700 text-xs uppercase bg-slate-100 px-2 py-1 rounded inline-block">
                                            <?= htmlspecialchars($s['club_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] italic text-slate-400 font-bold">TIDAK ADA KLUB</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-3 text-center">
                                    <div class="font-bold text-slate-700 text-xs"><?= !empty($s['birth_date']) ? date('d M Y', strtotime($s['birth_date'])) : '-' ?></div>
                                    <div class="text-[10px] font-black text-amber-500 uppercase mt-0.5"><?= $umur ?></div>
                                </td>

                                <td class="px-6 py-3 text-right">
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
    </div>
</div>
