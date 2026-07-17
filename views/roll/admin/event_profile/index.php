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
                <span class="text-9xl">🏛️</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Setup Kejuaraan</h1>
                    <p class="text-slate-500 mt-2 font-medium">Pengaturan Profil dan Kelas Lomba</p>
                </div>
            </div>
        </div>

        <?php if(empty($row)): ?>
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan buat atau pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profil Kejuaraan -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6 lg:col-span-1">
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-200 pb-2">Profil Utama</h3>
                <form action="<?= getenv('APP_URL') ?>/roll/admin/events/update_profile" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Kejuaraan</label>
                            <input type="text" name="event_name" value="<?= htmlspecialchars($row['event_name'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Mulai</label>
                                <input type="date" name="start_date" value="<?= htmlspecialchars($row['start_date'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Selesai</label>
                                <input type="date" name="end_date" value="<?= htmlspecialchars($row['end_date'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Lokasi (Opsional)</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($row['location'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4 mt-4 space-y-4">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Pejabat Pengesah</h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Technical Delegate (TD)</label>
                            <input type="text" name="td_name" value="<?= htmlspecialchars($row['td_name'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" placeholder="Nama TD">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Chief Referee</label>
                            <input type="text" name="cr_name" value="<?= htmlspecialchars($row['cr_name'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" placeholder="Nama Chief Referee">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Ketua Panitia</label>
                            <input type="text" name="kp_name" value="<?= htmlspecialchars($row['kp_name'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" placeholder="Nama Ketua Panitia">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4 mt-4">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-2">Logo Sponsor</h4>
                        <input type="file" name="sponsors[]" multiple accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-slate-700 file:text-slate-800 hover:file:bg-slate-600">
                        <p class="text-xs text-slate-500 mt-1">Bisa pilih lebih dari satu gambar (JPG, PNG).</p>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-slate-800 font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all mt-4">Simpan Profil</button>
                </form>
            </div>

            <!-- Kelas Lomba -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm lg:col-span-2 flex flex-col">
                <div class="p-6 border-b border-slate-200/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Daftar Kelas Lomba</h3>
                </div>
                
                <!-- Add New Class -->
                <div class="p-4 bg-white/50 border-b border-slate-200/50">
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/store_class" method="POST" class="flex flex-col md:flex-row gap-3">
                        <div class="flex-1">
                            <select name="distance_id" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih Jarak -</option>
                                <?php foreach($distances as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['distance_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <select name="age_group_id" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Pilih KU -</option>
                                <?php foreach($ageGroups as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['group_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <select name="category_name" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                                <option value="">- Kategori -</option>
                                <option value="Putra">Putra</option>
                                <option value="Putri">Putri</option>
                                <option value="Campuran">Campuran</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-slate-800 font-bold py-2 px-6 rounded-lg shadow-lg text-sm transition-all whitespace-nowrap">+ Tambah</button>
                    </form>
                </div>

                <!-- List of Classes -->
                <div class="p-6 flex-1 overflow-auto">
                    <?php if(empty($classes)): ?>
                        <div class="text-center text-slate-500 py-8">Belum ada kelas lomba yang ditambahkan.</div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($classes as $c): ?>
                                <div class="bg-white border border-slate-200 rounded-xl p-4 flex justify-between items-center group hover:border-slate-500 transition-colors">
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($c['group_name']) ?> - <?= htmlspecialchars($c['distance_name']) ?></div>
                                        <div class="text-xs text-blue-400"><?= htmlspecialchars($c['category_name']) ?></div>
                                    </div>
                                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/delete_class/<?= $c['id'] ?>" method="POST" onsubmit="return confirm('Hapus kelas ini?');">
                                        <button type="submit" class="text-red-500 opacity-50 group-hover:opacity-100 hover:text-red-400 p-2 rounded hover:bg-red-500/10 transition-all">
                                            &times;
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>
