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
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Daftar Pendaftaran (<?= count($clubs) ?> Klub)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/50 text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                <th class="p-4 font-bold">Klub / Pendaftar</th>
                                <th class="p-4 font-bold text-center">Total Atlet</th>
                                <th class="p-4 font-bold text-center">Total Entri</th>
                                <th class="p-4 font-bold text-center">Bukti Bayar</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-sm">
                            <?php if(empty($clubs)): ?>
                                <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada pendaftaran masuk.</td></tr>
                            <?php else: ?>
                                <?php foreach($clubs as $c): ?>
                                <tr class="hover:bg-slate-700/20 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($c['club_name'] ?? 'Independen') ?></div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="text-blue-500 font-bold"><?= $c['total_athletes'] ?></span> Atlet
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="text-amber-500 font-bold"><?= $c['total_entries'] ?></span> Kelas
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if($c['payment_proof']): ?>
                                            <a href="<?= getenv('APP_URL') ?>/<?= $c['payment_proof'] ?>" target="_blank" class="text-xs text-blue-500 underline">Lihat Struk</a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if($c['payment_status'] === 'Paid'): ?>
                                            <span class="inline-block px-3 py-1 bg-emerald-900/30 text-emerald-400 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Paid</span>
                                        <?php elseif($c['payment_status'] === 'Pending'): ?>
                                            <span class="inline-block px-3 py-1 bg-amber-900/30 text-amber-400 border border-amber-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Pending</span>
                                        <?php else: ?>
                                            <span class="inline-block px-3 py-1 bg-red-900/30 text-red-400 border border-red-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">Unpaid</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <button type="button" onclick="showClubDetails(<?= $c['club_id'] ?>, '<?= htmlspecialchars(addslashes($c['club_name'] ?? 'Independen')) ?>')" class="text-blue-400 hover:text-blue-300 font-bold text-xs uppercase tracking-wider px-3 py-1 bg-blue-900/20 rounded hover:bg-blue-900/40 transition">Cek Detail</button>
                    
                                        <?php if($c['payment_status'] !== 'Paid'): ?>
                                        <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/approve_club" method="POST" class="inline" onsubmit="return confirm('Approve pendaftaran seluruh atlet di klub ini?');">
                                            <input type="hidden" name="club_id" value="<?= $c['club_id'] ?>">
                                            <button type="submit" class="text-emerald-400 hover:text-emerald-300 font-bold text-xs uppercase tracking-wider px-3 py-1 bg-emerald-900/20 rounded hover:bg-emerald-900/40 transition">Approve</button>
                                        </form>
                                        <?php endif; ?>
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

<!-- Modal Detail Klub -->
<div id="detailModal" class="hidden fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm overflow-y-auto w-full h-full flex justify-center items-center">
    <div class="relative bg-white rounded-2xl shadow-2xl w-11/12 max-w-4xl border border-slate-200">
        <div class="p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
            <h3 class="text-xl font-bold text-slate-800 uppercase tracking-widest" id="modalClubName">Klub Name</h3>
            <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 text-2xl font-bold">&times;</button>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                        <th class="py-2">Atlet</th>
                        <th class="py-2">Kategori</th>
                        <th class="py-2">Kelas Lomba</th>
                    </tr>
                </thead>
                <tbody id="detailTableBody" class="text-sm divide-y divide-slate-100">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showClubDetails(clubId, clubName) {
    document.getElementById('modalClubName').innerText = clubName;
    document.getElementById('detailTableBody').innerHTML = '<tr><td colspan="3" class="text-center py-8 text-slate-400 font-bold uppercase tracking-widest animate-pulse">Memuat Data Atlet...</td></tr>';
    document.getElementById('detailModal').classList.remove('hidden');
    
    fetch('<?= getenv('APP_URL') ?>/roll/admin/entries/get_club_details/' + clubId)
        .then(response => response.json())
        .then(data => {
            let html = '';
            if(data.length === 0) {
                html = '<tr><td colspan="3" class="text-center py-8 text-slate-400">Tidak ada atlet yang terdaftar.</td></tr>';
            } else {
                data.forEach(e => {
                    html += `<tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-2">
                            <div class="font-bold text-slate-800 text-base">${e.skater_name}</div>
                            <div class="text-xs font-bold text-blue-500 uppercase tracking-wider">${e.gender}</div>
                        </td>
                        <td class="py-4 px-2">
                            <span class="inline-block px-3 py-1 bg-amber-50 text-amber-600 rounded border border-amber-200 font-bold text-xs uppercase tracking-wider">${e.group_name}</span>
                        </td>
                        <td class="py-4 px-2">
                            <div class="font-bold text-slate-700">${e.distance_name}</div>
                            <div class="text-xs text-slate-500">${e.category_name || '-'}</div>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('detailTableBody').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('detailTableBody').innerHTML = '<tr><td colspan="3" class="text-center py-8 text-red-400 font-bold">Gagal memuat data. Periksa koneksi Anda.</td></tr>';
        });
}
</script>
