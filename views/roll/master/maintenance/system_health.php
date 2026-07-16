<div class="font-sans">

    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                System Health & Security
            </h1>
            <p class="text-sm text-slate-500 font-medium">Backup database dan pantau aktivitas sistem.</p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="download_backup" value="1">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-widest shadow-lg transition transform hover:-translate-y-1 flex items-center gap-2 cursor-pointer">
                <span>💾</span> Download Backup SQL
            </button>
        </form>
    </div>

    <?php if($error_msg): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold border border-red-200">
            ⚠️ <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Total Aktivitas Log</div>
            <div class="text-3xl font-black text-slate-800"><?= number_format($totalLogs) ?></div>
            <div class="text-xs text-slate-500">Rekam jejak sistem</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Aktivitas Hari Ini</div>
            <div class="text-3xl font-black text-blue-600"><?= number_format($todayLog) ?></div>
            <div class="text-xs text-slate-500">Aksi dilakukan user hari ini</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Ukuran Database</div>
            <div class="text-3xl font-black text-emerald-600"><?= $dbSize ?: '0' ?> <span class="text-sm text-slate-400">MB</span></div>
            <div class="text-xs text-slate-500">Estimasi penggunaan disk</div>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-800 px-8 py-5 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-white font-black text-sm uppercase tracking-wider flex items-center gap-2">
                📜 Audit Trail (Log Aktivitas)
            </h3>
            <div class="text-slate-400 text-[10px] font-bold uppercase">Halaman <?= $page ?> dari <?= $totalPages ?: 1 ?></div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">User / Aktor</th>
                        <th class="px-6 py-4">Tipe Aksi</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="5" class="p-8 text-center text-slate-400 italic">
                            Belum ada aktivitas tercatat.
                        </td></tr>
                    <?php else: foreach($logs as $log): ?>
                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="px-6 py-3 whitespace-nowrap text-xs font-mono text-slate-500">
                                <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-800 text-xs">
                                    <?= htmlspecialchars($log['nama_lengkap'] ?? 'System / Deleted User') ?>
                                </div>
                                <span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 border border-slate-200">
                                    <?= htmlspecialchars($log['role'] ?? 'System') ?>
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <?php 
                                    // LOGIKA WARNA BADGE SESUAI DATA BAPAK
                                    $action = strtoupper($log['action_type']);
                                    $color = 'bg-slate-100 text-slate-600'; // Default abu-abu
                                    
                                    if(strpos($action, 'DELETE') !== false || strpos($action, 'HAPUS') !== false) {
                                        $color = 'bg-red-50 text-red-700 border border-red-100';
                                    } 
                                    elseif(strpos($action, 'MERGE') !== false) {
                                        // Ungu untuk Merge
                                        $color = 'bg-purple-50 text-purple-700 border border-purple-100';
                                    }
                                    elseif(strpos($action, 'CHANGE_STATUS') !== false) {
                                        // Oranye untuk ganti status user
                                        $color = 'bg-orange-50 text-orange-700 border border-orange-100';
                                    }
                                    elseif(strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false) {
                                        // Biru untuk Edit/Update
                                        $color = 'bg-blue-50 text-blue-700 border border-blue-100';
                                    }
                                    elseif(strpos($action, 'CREATE') !== false || strpos($action, 'ADD') !== false) {
                                        // Hijau untuk Tambah data
                                        $color = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                                    }
                                    elseif(strpos($action, 'LOGIN') !== false) {
                                        $color = 'bg-indigo-50 text-indigo-700 border border-indigo-100';
                                    }
                                ?>
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wide <?= $color ?>">
                                    <?= htmlspecialchars($action) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-600 font-medium">
                                <?= htmlspecialchars($log['description']) ?>
                                <?php if(!empty($log['target_id']) && $log['target_id'] != 0): ?>
                                    <span class="text-slate-400 text-[10px] ml-1 font-mono">(ID: <?= $log['target_id'] ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-[10px] font-mono text-slate-400">
                                <?= htmlspecialchars($log['ip_address']) ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-center gap-2">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs hover:bg-slate-100 font-bold text-slate-600">← Prev</a>
            <?php endif; ?>
            
            <span class="px-3 py-1 bg-slate-800 text-white border border-slate-800 rounded text-xs font-bold">
                <?= $page ?>
            </span>
            
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs hover:bg-slate-100 font-bold text-slate-600">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

</div>