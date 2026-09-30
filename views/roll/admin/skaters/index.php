<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Starting List Peserta</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Daftar Atlet pada <?= htmlspecialchars($eventName ?? 'Event') ?></p>
    </div>
    
    <div class="flex gap-3 items-center">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters/export_csv" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm border border-emerald-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full print:hidden">
            <span>📊</span> Export CSV
        </a>
    </div>
</div>

<div class="max-w-[95%] mx-auto space-y-8">
    <?php if(empty($grouped)): ?>
        <div class="bg-white rounded-[2rem] p-12 text-center border border-slate-200 shadow-sm">
            <div class="text-5xl mb-4 grayscale opacity-50">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Peserta Terdaftar</h3>
        </div>
    <?php else: ?>
        
        <?php 
            $orderCats = ['Speed', 'Standart', 'Pemula', 'Lainnya'];
            foreach ($orderCats as $cat):
                if (!isset($grouped[$cat])) continue;
        ?>
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden mb-8">
            <!-- Header Kategori -->
            <div class="bg-slate-800 p-6 text-white text-center border-b-4 border-blue-500">
                <h2 class="text-2xl font-black uppercase tracking-widest italic">Kategori <?= htmlspecialchars($cat) ?></h2>
            </div>
            
            <div class="p-6 space-y-8">
                <?php 
                    ksort($grouped[$cat]);
                    foreach ($grouped[$cat] as $ku => $genders): 
                ?>
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        <div class="bg-slate-100 py-3 px-6 border-b border-slate-200">
                            <h3 class="font-black text-slate-700 uppercase tracking-widest">KU: <?= htmlspecialchars($ku) ?></h3>
                        </div>
                        
                        <div class="p-0">
                            <?php 
                                // Order Gender: Putri then Putra
                                $orderGenders = ['Putri', 'Putra'];
                                foreach ($orderGenders as $gender):
                                    if (!isset($genders[$gender])) continue;
                            ?>
                                <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full <?= $gender == 'Putra' ? 'bg-blue-500' : 'bg-pink-500' ?>"></span>
                                    <h4 class="font-black <?= $gender == 'Putra' ? 'text-blue-600' : 'text-pink-600' ?> uppercase text-sm tracking-widest"><?= $gender ?></h4>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-white border-b border-slate-100">
                                            <tr>
                                                <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-12 text-center">No</th>
                                                <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">BIB</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Atlet</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Klub / Kontingen</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kelas / Nomor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <?php foreach ($genders[$gender] as $i => $s): ?>
                                            <tr class="hover:bg-slate-50 transition">
                                                <td class="py-3 px-6 font-bold text-slate-400 text-center text-xs"><?= $i + 1 ?></td>
                                                <td class="py-3 px-6 text-center">
                                                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-800 font-black rounded-lg text-xs border border-amber-200 min-w-[3rem]">
                                                        <?= htmlspecialchars($s['bib_number'] ?? '-') ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-800 text-sm"><?= htmlspecialchars($s['skater_name']) ?></td>
                                                <td class="py-3 px-4 text-xs font-semibold text-slate-600 flex items-center gap-2">
                                                    <span class="text-slate-400">🏢</span> <?= htmlspecialchars($s['club_name'] ?? '-') ?>
                                                </td>
                                                <td class="py-3 px-4 text-xs font-semibold text-blue-600 flex items-center gap-2">
                                                    <span class="text-blue-300">🏷️</span> <?= htmlspecialchars($s['distances'] ?: ($s['class_name'] ?? '-')) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
    <?php endif; ?>
</div>

<style>
@media print {
    body {
        background-color: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    #main-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
    .bg-slate-50 { background-color: white !important; }
    .max-w-\[95\%\] { max-width: 100% !important; margin: 0 !important; }
    .shadow-sm { box-shadow: none !important; }
    .border-slate-200 { border-color: #ddd !important; }
    .rounded-\[2rem\], .rounded-2xl { border-radius: 0 !important; border: none !important; }
    
    /* Hide sidebar and header */
    aside, header, nav, .print\:hidden { display: none !important; }
    
    .bg-slate-800 { background-color: #333 !important; color: white !important; }
    
    /* Page break rules */
    .border-slate-200 { break-inside: avoid; }
}
</style>
