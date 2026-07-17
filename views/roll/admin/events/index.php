<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-slate-900 text-slate-200 font-sans">
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
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">⚙️</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Setup Kejuaraan</h1>
                    <p class="text-slate-400 mt-2 font-medium">Pengaturan Profil Utama dan Kelas Lomba untuk Event Aktif</p>
                </div>
            </div>
        </div>

        <?php if ($eventId > 0 && $eventProfile): ?>
        <!-- EVENT PROFILE FORM -->
        <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl overflow-hidden backdrop-blur-sm">
            <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80">
                <h3 class="text-lg font-bold text-white uppercase tracking-widest">PROFIL EVENT: <?= htmlspecialchars($eventProfile['event_name']) ?></h3>
            </div>
            <div class="p-6">
                <form action="<?= getenv('APP_URL') ?>/roll/admin/events/updateProfile" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Event</label>
                            <input type="text" name="event_name" value="<?= htmlspecialchars($eventProfile['event_name']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($eventProfile['start_date']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Lokasi</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($eventProfile['location']) ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Logo Baru (Opsional)</label>
                            <input type="file" name="logo" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 transition-all file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all">Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RACE CLASSES MANAGEMENT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add New Class -->
            <div class="lg:col-span-1 bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80">
                    <h3 class="text-lg font-bold text-emerald-400 uppercase tracking-widest">Tambah Kelas Lomba</h3>
                </div>
                <div class="p-6">
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/storeClass" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Jarak (Distance)</label>
                            <select name="distance_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih Jarak -</option>
                                <?php foreach($distances as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['distance_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Kelompok Umur (Age Group)</label>
                            <select name="age_group_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih KU -</option>
                                <?php foreach($ageGroups as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['group_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori (Standar/Speed)</label>
                            <select name="category_name" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-emerald-500" required>
                                <option value="Standar">Standar</option>
                                <option value="Speed">Speed</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-emerald-500/25 transition-all mt-4">+ Tambah Kelas</button>
                    </form>
                </div>
            </div>

            <!-- List of Classes -->
            <div class="lg:col-span-2 bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl overflow-hidden backdrop-blur-sm">
                <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/80">
                    <h3 class="text-lg font-bold text-white uppercase tracking-widest">Daftar Kelas Lomba (<?= count($classes) ?>)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-700">
                                <th class="p-4 font-bold">ID</th>
                                <th class="p-4 font-bold">Kategori</th>
                                <th class="p-4 font-bold">Kelompok Umur</th>
                                <th class="p-4 font-bold">Jarak</th>
                                <th class="p-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-sm">
                            <?php if(empty($classes)): ?>
                                <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada kelas lomba yang ditambahkan.</td></tr>
                            <?php else: ?>
                                <?php foreach($classes as $c): ?>
                                <tr class="hover:bg-slate-700/20 transition-colors">
                                    <td class="p-4 text-slate-500">#<?= $c['id'] ?></td>
                                    <td class="p-4 font-bold <?= $c['category_name']=='Speed'?'text-fuchsia-400':'text-amber-400' ?>"><?= htmlspecialchars($c['category_name']) ?></td>
                                    <td class="p-4 text-white font-medium"><?= htmlspecialchars($c['group_name']) ?></td>
                                    <td class="p-4 text-white"><?= htmlspecialchars($c['distance_name']) ?></td>
                                    <td class="p-4 text-right">
                                        <form action="<?= getenv('APP_URL') ?>/roll/admin/events/deleteClass/<?= $c['id'] ?>" method="POST" onsubmit="return confirm('Hapus kelas lomba ini?');">
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
            <div class="bg-slate-800/50 rounded-2xl border border-slate-700/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-300 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu untuk melakukan setup.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
