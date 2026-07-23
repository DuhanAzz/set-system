<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-2xl relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="relative z-10">
                <a href="<?= getenv('APP_URL') ?>/roll/admin/bibs" class="inline-flex items-center text-indigo-300 hover:text-white text-xs font-bold uppercase tracking-widest mb-3 transition-colors">
                    <span class="mr-2">←</span> Kembali ke Daftar Klub
                </a>
                <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase"><?= htmlspecialchars($clubName) ?></h1>
                <p class="text-slate-500 mt-2 font-medium">Detail Atlet dan Nomor Punggung</p>
            </div>
            <div class="mt-4 md:mt-0 relative z-10 opacity-50 text-6xl">
                🏃
            </div>
        </div>

        <!-- TABEL ATLET -->
        <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6 w-full">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b-2 border-slate-200">
                            <th class="p-4 font-bold w-24">Nomor BIB</th>
                            <th class="p-4 font-bold">Nama Atlet</th>
                            <th class="p-4 font-bold w-32 text-center">Gender</th>
                            <th class="p-4 font-bold">Nomor Lomba (Kelas)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php if (empty($athletes)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400 font-medium italic">Tidak ada atlet yang terdaftar dari klub ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($athletes as $a): 
                                $rowClass = ($a['gender'] == 'Putra') ? 'bg-blue-50/30 hover:bg-blue-100/50' : 'bg-pink-50/30 hover:bg-pink-100/50';
                            ?>
                                <tr class="<?= $rowClass ?> transition-colors">
                                    <td class="p-4">
                                        <?php if (!empty($a['bib_number'])): ?>
                                            <span class="inline-block bg-slate-800 text-white font-black text-xl px-4 py-2 rounded-lg shadow-inner tracking-widest border-2 border-slate-600">
                                                <?= htmlspecialchars($a['bib_number']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 italic font-bold">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 font-bold text-slate-800 text-lg uppercase"><?= htmlspecialchars($a['skater_name']) ?></td>
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest border <?= ($a['gender'] == 'Putra') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-pink-50 text-pink-600 border-pink-200' ?>">
                                            <?= htmlspecialchars($a['gender']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-2">
                                            <?php 
                                            $classes = explode('||', $a['classes']);
                                            foreach ($classes as $cls): 
                                                if (trim($cls) == '') continue;
                                            ?>
                                                <span class="bg-white border border-slate-200 text-slate-600 font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm whitespace-nowrap">
                                                    <?= htmlspecialchars($cls) ?>
                                                </span>
                                            <?php endforeach; ?>
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
