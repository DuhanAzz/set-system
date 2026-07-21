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

        <div class="space-y-8">
            <!-- Profil Kejuaraan -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6 w-full">
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-200 pb-2">Profil Utama</h3>
                <form action="<?= getenv('APP_URL') ?>/roll/admin/events/update_profile" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Kejuaraan</label>
                            <input type="text" name="event_name" value="<?= htmlspecialchars($row['event_name'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Mulai</label>
                            <input type="date" name="event_date_start" value="<?= htmlspecialchars($row['event_date_start'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Selesai</label>
                            <input type="date" name="event_date_end" value="<?= htmlspecialchars($row['event_date_end'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Lokasi (Opsional)</label>
                            <input type="text" name="event_location" value="<?= htmlspecialchars($row['event_location'] ?? '') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Gambar / Media -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-200">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-2">Poster Kompetisi</h4>
                        <?php if (!empty($row['poster_image'])): ?>
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Poster Saat Ini:</p>
                                <div class="relative inline-block group">
                                    <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/public/uploads/logos/<?= ltrim(str_replace(['public/uploads/logos/', 'uploads/logos/'], '', $row['poster_image']), '/') ?>" class="h-32 rounded-lg border border-slate-200 shadow-sm object-cover" alt="Poster">
                                    <button type="button" onclick="if(confirm('Hapus poster ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_poster?id=<?= $row['id'] ?>'" class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full w-6 h-6 items-center justify-center text-xs shadow-md hover:bg-red-600">&times;</button>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="poster_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-slate-700 file:text-slate-800 hover:file:bg-slate-600">
                        <p class="text-xs text-slate-500 mt-1">Satu gambar (JPG, PNG). Poster akan muncul di halaman publik.</p>
                    </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-2">Logo Sponsor</h4>
                            
                            <?php 
                            $sponsorsArray = !empty($row['sponsor_logos']) ? json_decode($row['sponsor_logos'], true) : [];
                            if (!empty($sponsorsArray)): 
                            ?>
                                <div class="mb-3">
                                    <p class="text-[10px] text-slate-500 mb-2 uppercase font-bold tracking-wider">Sponsor Saat Ini:</p>
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <?php foreach($sponsorsArray as $sponsorFile): ?>
                                            <div class="relative group">
                                                <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/public/<?= ltrim($sponsorFile, '/') ?>" class="h-12 rounded bg-slate-100 border border-slate-200 object-contain p-1" alt="Sponsor">
                                                <button type="button" onclick="if(confirm('Hapus logo sponsor ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_sponsor?id=<?= $row['id'] ?>&file=<?= urlencode($sponsorFile) ?>'" class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full w-5 h-5 items-center justify-center text-xs shadow-md hover:bg-red-600">&times;</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="sponsors[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
                            <p class="text-[10px] text-slate-500 mt-2">Pilih lebih dari satu gambar (JPG/PNG).</p>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-2">Logo Header (Dokumen)</h4>
                            
                            <?php 
                            $headerLogosArray = !empty($row['header_logos']) ? json_decode($row['header_logos'], true) : [];
                            if (!empty($headerLogosArray)): 
                            ?>
                                <div class="mb-3">
                                    <p class="text-[10px] text-slate-500 mb-2 uppercase font-bold tracking-wider">Logo Saat Ini (Max 4):</p>
                                    <div class="flex flex-wrap gap-2 items-center">
                                        <?php foreach($headerLogosArray as $logoFile): ?>
                                            <div class="relative group">
                                                <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/public/<?= ltrim($logoFile, '/') ?>" class="h-12 rounded bg-slate-100 border border-slate-200 object-contain p-1" alt="Header Logo">
                                                <button type="button" onclick="if(confirm('Hapus logo ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_header_logo?id=<?= $row['id'] ?>&file=<?= urlencode($logoFile) ?>'" class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full w-5 h-5 items-center justify-center text-xs shadow-md hover:bg-red-600">&times;</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="header_logos[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
                            <p class="text-[10px] text-slate-500 mt-2">Maksimal 4 gambar. Urutan upload menjadi urutan posisi dari kiri ke kanan.</p>
                        </div>
                    </div>
                    
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-blue-500/25 transition-all uppercase tracking-widest text-sm">Simpan Profil & Gambar</button>
                    </div>
                </form>
            </div>

            <!-- Kelas Lomba -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm w-full flex flex-col">
            <div class="p-6 border-b border-slate-200/50 flex justify-between items-center bg-white rounded-t-2xl">
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Manajemen Kelas & Jadwal Lomba</h3>
                </div>
                        <!-- Bulk Add Classes -->
                <div class="p-6 bg-white/50 border-b border-slate-200/50">
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/bulk_store_class" method="POST" class="space-y-6">
                        <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Jarak Tempuh -->
                            <div>
                                <h4 class="text-xs font-black text-slate-600 uppercase mb-3">1. Pilih Jarak Tempuh</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                    <?php foreach($distances as $d): ?>
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 cursor-pointer transition">
                                        <input type="checkbox" name="distances[]" value="<?= $d['id'] ?>" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                        <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($d['distance_name']) ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Kelompok Umur -->
                            <div>
                                <h4 class="text-xs font-black text-slate-600 uppercase mb-3">2. Pilih Kelompok Umur</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                    <?php foreach($ageGroups as $a): ?>
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 cursor-pointer transition">
                                        <input type="checkbox" name="age_groups[]" value="<?= $a['id'] ?>" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                        <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($a['group_name']) ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Gender & Submit -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pt-4 border-t border-slate-200 gap-4">
                            <div>
                                <h4 class="text-xs font-black text-slate-600 uppercase mb-2">3. Pilih Gender</h4>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="genders[]" value="Putra" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500" checked>
                                        <span class="text-sm font-bold text-slate-700">Putra</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="genders[]" value="Putri" class="w-4 h-4 text-pink-600 rounded border-slate-300 focus:ring-pink-500" checked>
                                        <span class="text-sm font-bold text-slate-700">Putri</span>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-emerald-500/30 transition-all uppercase tracking-widest text-sm w-full sm:w-auto flex-shrink-0">
                                ➕ Buat Kelas Massal
                            </button>
                        </div>
                    </form>
                </div>   </div>

                <!-- List of Classes -->
                <div class="p-6 flex-1 overflow-auto">
                    <?php if(empty($classes)): ?>
                        <div class="text-center text-slate-500 py-8">Belum ada kelas lomba yang ditambahkan.</div>
                    <?php else: ?>
                        <form action="<?= getenv('APP_URL') ?>/roll/admin/events/bulk_update_schedule" method="POST">
                            <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                            <div class="flex flex-col space-y-2">
                                <?php foreach($classes as $c): ?>
                                    <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col md:flex-row justify-between items-start md:items-center group hover:border-slate-500 transition-colors w-full gap-4">
                                        <div class="flex items-center gap-4 flex-1">
                                            <div class="h-10 w-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-bold shadow-sm flex-shrink-0">
                                                🏁
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 text-base flex flex-wrap items-center gap-2">
                                                    <?= htmlspecialchars($c['group_name'] ?: 'Umum') ?> 
                                                    <span class="text-slate-400 font-normal hidden sm:inline">|</span> 
                                                    <?= htmlspecialchars($c['distance_name']) ?>
                                                </div>
                                                <div class="text-xs font-bold text-blue-500 uppercase tracking-widest mt-0.5">
                                                    <?= htmlspecialchars($c['category_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-3 w-full md:w-auto">
                                            <input type="hidden" name="class_ids[]" value="<?= $c['id'] ?>">
                                            <div class="flex-1 md:w-24">
                                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nomor (3 Digit)</label>
                                                <input type="text" name="race_numbers[]" value="<?= htmlspecialchars($c['race_number'] ?? '') ?>" placeholder="Misal: 101" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div class="flex-1 md:w-28">
                                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Jam Acara</label>
                                                <input type="time" name="race_times[]" value="<?= htmlspecialchars($c['race_time'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            
                                            <div class="pt-5">
                                                <button type="button" onclick="if(confirm('Hapus kelas ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_class/<?= $c['id'] ?>'" class="text-red-500 opacity-50 hover:opacity-100 hover:text-white p-2 rounded hover:bg-red-500 transition-all font-bold text-sm h-10 w-10 flex items-center justify-center">
                                                    &times;
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all uppercase tracking-widest text-sm">
                                    💾 Simpan Semua Jadwal
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>
