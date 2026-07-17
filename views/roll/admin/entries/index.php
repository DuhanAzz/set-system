<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-900/50 border-emerald-500/30 text-emerald-300' : 'bg-red-900/50 border-red-500/30 text-red-300' ?> flex items-center justify-between shadow-lg backdrop-blur-sm">
                <span><?= $_SESSION['flash_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xl">&times;</button>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">💸</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Pintu Kasir</h1>
                    <p class="text-slate-500 mt-2 font-medium">Approval & Validasi Pendaftaran Peserta</p>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0): ?>

        <!-- MANUAL ENTRY & DATA LIST -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Manual Entry -->
            <div class="lg:col-span-1 bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/80">
                    <h3 class="text-lg font-bold text-emerald-400 uppercase tracking-widest">Daftar Manual</h3>
                </div>
                <div class="p-6">
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/store" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih Atlet</label>
                            <select name="skater_id" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih Atlet -</option>
                                <?php foreach($skaters as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['skater_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Kelas Lomba</label>
                            <select name="race_class_id" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-3 text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih Kelas Lomba -</option>
                                <?php foreach($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?> (<?= htmlspecialchars($c['category_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-slate-800 font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-emerald-500/25 transition-all mt-4">+ Daftar</button>
                    </form>
                </div>
            </div>

            <!-- Validation List -->
            <div class="lg:col-span-3 bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl overflow-hidden backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-slate-200/50 bg-slate-50/80 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Daftar Pendaftaran (<?= count($entries) ?>)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/50 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                <th class="p-4 font-bold">ID</th>
                                <th class="p-4 font-bold">Atlet & Klub</th>
                                <th class="p-4 font-bold">Kelas Lomba</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-sm">
                            <?php if(empty($entries)): ?>
                                <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada pendaftaran masuk.</td></tr>
                            <?php else: ?>
                                <?php foreach($entries as $e): ?>
                                <tr class="hover:bg-slate-700/20 transition-colors">
                                    <td class="p-4 text-slate-500">#<?= $e['id'] ?></td>
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($e['skater_name']) ?></div>
                                        <div class="text-xs text-blue-400"><?= htmlspecialchars($e['club_name'] ?? 'Independen') ?></div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-amber-400 font-bold"><?= htmlspecialchars($e['group_name']) ?></span> - 
                                        <span class="text-slate-800"><?= htmlspecialchars($e['distance_name']) ?></span>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($e['category_name']) ?></div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if($e['status'] === 'Paid'): ?>
                                            <span class="inline-block px-3 py-1 bg-emerald-900/30 text-emerald-400 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Paid</span>
                                        <?php elseif($e['status'] === 'Pending'): ?>
                                            <span class="inline-block px-3 py-1 bg-amber-900/30 text-amber-400 border border-amber-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Pending</span>
                                        <?php else: ?>
                                            <span class="inline-block px-3 py-1 bg-red-900/30 text-red-400 border border-red-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <?php if($e['status'] !== 'Paid'): ?>
                                        <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/approvePayment/<?= $e['id'] ?>" method="POST" class="inline">
                                            <button type="submit" class="text-emerald-400 hover:text-emerald-300 font-bold text-xs uppercase tracking-wider px-3 py-1 bg-emerald-900/20 rounded hover:bg-emerald-900/40 transition">Approve</button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/delete/<?= $e['id'] ?>" method="POST" class="inline" onsubmit="return confirm('Hapus entri ini?');">
                                            <button type="submit" class="text-red-400 hover:text-red-300 font-bold text-xs uppercase tracking-wider px-3 py-1 bg-red-900/20 rounded hover:bg-red-900/40 transition">Hapus</button>
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
        
        <?php else: ?>
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
