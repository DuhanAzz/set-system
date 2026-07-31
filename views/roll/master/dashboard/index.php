    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                Master Dashboard
            </h1>
            <p class="text-sm text-slate-500 font-medium">
                Selamat Datang, Super Admin! Berikut laporan sistem Roll hari ini.
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="<?= getenv('APP_URL') ?>/roll/maintenance/system_health" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold text-xs uppercase hover:bg-slate-100 transition shadow-sm flex items-center gap-2">
                <span>🛡️</span> System Health
            </a>
        </div>
    </div>

    <!-- ACTION REQUIRED ALERTS -->
    <?php if($stats['pending_users'] > 0 || $stats['pending_uids'] > 0): ?>
    <div class="mb-8 space-y-4">
        <?php if($stats['pending_users'] > 0): ?>
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg flex items-center justify-between group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl">⚠️</div>
                <div>
                    <h3 class="font-black text-lg">Action Required: <?= $stats['pending_users'] ?> Akun Pending</h3>
                    <p class="text-sm text-orange-100 font-medium mt-1">Ada pengguna (Klub/EO) baru yang menunggu persetujuan Anda untuk bisa login.</p>
                </div>
            </div>
            <a href="<?= getenv('APP_URL') ?>/roll/users/index" class="bg-white text-orange-600 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-50 transition transform group-hover:scale-105 shadow-md">Tinjau Sekarang</a>
        </div>
        <?php endif; ?>

        <?php if($stats['pending_uids'] > 0): ?>
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg flex items-center justify-between group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl">🆔</div>
                <div>
                    <h3 class="font-black text-lg">System Alert: <?= $stats['pending_uids'] ?> Pesepatu Roda Tanpa UID</h3>
                    <p class="text-sm text-blue-100 font-medium mt-1">Ada pesepatu roda yang terdaftar namun belum memiliki UID.</p>
                </div>
            </div>
            <a href="<?= getenv('APP_URL') ?>/roll/skaters/index" class="bg-white text-blue-700 px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-blue-50 transition transform group-hover:scale-105 shadow-md">Generate UID</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 4 KARTU METRIK UTAMA -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-gradient-to-br from-indigo-600 to-blue-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Event</p>
                <h2 class="text-4xl font-black"><?= number_format(count($liveEvents)) /* we can also pass totalEvents from controller */ ?></h2>
                <div class="mt-4 text-[10px] font-bold bg-white/20 inline-block px-2 py-1 rounded">Di Sistem</div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition duration-500 text-white">
                <div class="text-8xl">📅</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-emerald-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Klub Aktif</p>
                    <h2 class="text-4xl font-black text-slate-800 group-hover:text-emerald-600 transition"><?= number_format($stats['clubs']) ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Total Klub Terdaftar</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">🏫</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-purple-500 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Global Skaters</p>
                    <h2 class="text-4xl font-black text-slate-800 group-hover:text-purple-600 transition"><?= number_format($stats['athletes']) ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Database Nasional</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center text-2xl shadow-inner">🛼</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border-b-4 border-orange-400 shadow-sm hover:shadow-lg transition-all duration-300 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Entri Lomba</p>
                    <h2 class="text-4xl font-black text-slate-800 group-hover:text-orange-500 transition"><?= number_format($stats['entries']) ?></h2>
                    <p class="text-[10px] text-slate-400 mt-1">Status Apapun</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-2xl shadow-inner">🎟️</div>
            </div>
        </div>

    </div>

    <!-- GRAFIK PENGUNJUNG -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-black text-slate-800 uppercase italic text-sm tracking-widest">📈 Grafik Pengunjung (7 Hari Terakhir)</h3>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="visitorChart"></canvas>
        </div>
    </div>

    <!-- EARLY WARNING SYSTEM & LAINNYA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <!-- EARLY WARNING SYSTEM -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-8 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-black text-slate-800 uppercase italic text-sm tracking-widest">⚠️ Peringatan: Status Pembayaran Gantung</h3>
                    <span class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-1 rounded uppercase">Early Warning</span>
                </div>
                <div class="p-6">
                    <?php if(empty($pendingEntries)): ?>
                        <div class="text-center py-6 text-emerald-600 font-bold text-sm bg-emerald-50 rounded-xl border border-emerald-200">
                            Mantap! Tidak ada entri gantung yang butuh pantauan khusus.
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach($pendingEntries as $pe): ?>
                            <div class="flex items-center justify-between p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition">
                                <div>
                                    <h4 class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($pe['skater_name']) ?></h4>
                                    <p class="text-xs text-slate-400 mt-1">Didaftarkan: <?= date('d M Y, H:i', strtotime($pe['created_at'])) ?></p>
                                </div>
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-full tracking-wider">
                                    Menunggu
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REGISTRASI USER TERBARU -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-8 py-4 border-b border-slate-100">
                    <h3 class="font-black text-slate-800 uppercase italic text-sm tracking-widest">👤 Registrasi User Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach($recentUsers as $u): ?>
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-slate-700"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                                        <?php if(($u['account_status']??'') == 'pending'): ?>
                                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse" title="Menunggu Verifikasi"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">@<?= htmlspecialchars($u['username']) ?></div>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="px-2 py-1 rounded text-[9px] font-black uppercase 
                                            <?= $u['role']=='admin' ? 'bg-slate-800 text-white' : 'bg-blue-100 text-blue-600' ?>">
                                            <?= $u['role'] == 'admin' ? 'Event Org' : 'Club' ?>
                                        </span>
                                        <?php if(($u['account_status']??'') == 'pending'): ?>
                                            <span class="px-2 py-1 rounded text-[9px] font-black uppercase bg-orange-100 text-orange-600 border border-orange-200">
                                                Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-600">
                                                Verified
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-right text-[10px] text-slate-400 font-mono">
                                    <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="space-y-8">
            
            <div class="bg-slate-800 rounded-[2rem] p-8 text-white shadow-xl">
                <h3 class="font-black uppercase italic text-sm tracking-widest mb-6 text-slate-400">⚡ Akses Cepat</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="<?= getenv('APP_URL') ?>/roll/users/index" class="bg-slate-700 hover:bg-blue-600 p-4 rounded-xl text-center transition group">
                        <div class="text-2xl mb-2 group-hover:scale-110 transition">👥</div>
                        <span class="text-[9px] font-bold uppercase tracking-wider">User Manager</span>
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/roll/masterFinance/revenue" class="bg-slate-700 hover:bg-emerald-600 p-4 rounded-xl text-center transition group">
                        <div class="text-2xl mb-2 group-hover:scale-110 transition">💰</div>
                        <span class="text-[9px] font-bold uppercase tracking-wider">Keuangan</span>
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/roll/masterSettings/public_page" class="bg-slate-700 hover:bg-indigo-600 p-4 rounded-xl text-center transition group">
                        <div class="text-2xl mb-2 group-hover:scale-110 transition">🎨</div>
                        <span class="text-[9px] font-bold uppercase tracking-wider">Editor Web</span>
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/roll/maintenance/data_cleanup" class="bg-slate-700 hover:bg-red-600 p-4 rounded-xl text-center transition group">
                        <div class="text-2xl mb-2 group-hover:scale-110 transition">🧹</div>
                        <span class="text-[9px] font-bold uppercase tracking-wider">Bersihkan Data</span>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full mx-auto flex items-center justify-center text-3xl mb-4">
                    🚀
                </div>
                <h4 class="font-black text-slate-800 uppercase tracking-tight"><?= htmlspecialchars($heroTitle) ?></h4>
                <p class="text-xs text-slate-500 mt-2">Versi 1.0.0 (Beta)</p>
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-[10px] text-slate-400 uppercase font-bold">Waktu Server</p>
                    <p class="text-lg font-mono font-bold text-slate-700"><?= date('H:i') ?> <span class="text-xs text-slate-400">WIB</span></p>
                </div>
            </div>

        </div>

    </div>
