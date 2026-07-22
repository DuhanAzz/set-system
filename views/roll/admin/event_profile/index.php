<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Flash Messages (Universal Toast) -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div id="toast-message" class="fixed bottom-10 right-10 z-[9999] min-w-[300px] p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-red-600 border-red-500 text-white' ?> flex items-center justify-between shadow-2xl transition-all duration-500 transform translate-y-0 opacity-100">
                <div class="flex items-center space-x-3">
                    <span class="text-xl"><?= $_SESSION['flash_type'] === 'success' ? '✅' : '⚠️' ?></span>
                    <span class="font-bold tracking-wide"><?= $_SESSION['flash_message'] ?></span>
                </div>
                <button onclick="this.parentElement.style.opacity='0'; setTimeout(()=>this.parentElement.remove(), 500)" class="text-2xl ml-6 hover:text-slate-200 transition-colors">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    let toast = document.getElementById('toast-message');
                    if(toast) {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateY(20px)';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
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
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Status Kejuaraan</label>
                            <select name="status" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold">
                                <option value="Draft" <?= ($row['status'] == 'Draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                                <option value="Published" <?= ($row['status'] == 'Published') ? 'selected' : '' ?>>Published</option>
                                <option value="Open Registration" <?= ($row['status'] == 'Open Registration') ? 'selected' : '' ?>>Open Registration</option>
                                <option value="Close Registration" <?= ($row['status'] == 'Close Registration') ? 'selected' : '' ?>>Close Registration</option>
                                <option value="Running" <?= ($row['status'] == 'Running') ? 'selected' : '' ?>>Running</option>
                                <option value="Finished" <?= ($row['status'] == 'Finished') ? 'selected' : '' ?>>Finished</option>
                            </select>
                        </div>
                    </div>

                    <!-- Gambar / Media -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-200">
                        <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-2">Poster Kompetisi</h4>
                        <?php if (!empty($row['poster_image'])): ?>
                            <div class="mb-3">
                                <p class="text-xs text-slate-500 mb-1">Poster Saat Ini:</p>
                                <div class="relative inline-block group">
                                    <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/uploads/logos/<?= ltrim(str_replace(['public/uploads/logos/', 'uploads/logos/'], '', $row['poster_image']), '/') ?>" class="h-32 rounded-lg border border-slate-200 shadow-sm object-cover" alt="Poster">
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
                                                <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/<?= ltrim(str_replace('public/', '', $sponsorFile), '/') ?>" class="h-12 rounded bg-slate-100 border border-slate-200 object-contain p-1" alt="Sponsor">
                                                <button type="button" onclick="if(confirm('Hapus logo sponsor ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_sponsor?id=<?= $row['id'] ?>&file=<?= urlencode($sponsorFile) ?>'" class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full w-5 h-5 items-center justify-center text-xs shadow-md hover:bg-red-600">&times;</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" name="sponsors[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
                            <p class="text-[10px] text-slate-500 mt-2">Pilih lebih dari satu gambar (JPG/PNG).</p>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm md:col-span-2 lg:col-span-3">
                            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-200 pb-2">Logo Header (Dokumen Kop Surat)</h4>
                            
                            <?php 
                            // Support for legacy flat array or new structured array
                            $rawHeader = !empty($row['header_logos']) ? json_decode($row['header_logos'], true) : [];
                            $headerLogos = ['left' => [], 'center' => [], 'right' => []];
                            if (isset($rawHeader[0]) && !is_array($rawHeader[0])) {
                                // Legacy format, put them all in left or distribute
                                $headerLogos['left'] = $rawHeader;
                            } else {
                                $headerLogos = array_merge($headerLogos, $rawHeader);
                            }
                            ?>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php foreach(['left' => 'Kiri', 'center' => 'Tengah', 'right' => 'Kanan'] as $pos => $label): ?>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                                    <p class="text-xs font-bold text-slate-700 uppercase tracking-widest mb-2">Logo <?= $label ?> (Max 2)</p>
                                    
                                    <?php if (!empty($headerLogos[$pos])): ?>
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <?php foreach($headerLogos[$pos] as $logoFile): ?>
                                                <div class="relative group">
                                                    <img src="<?= rtrim(getenv('APP_URL'), '/') ?>/<?= ltrim(str_replace('public/', '', $logoFile), '/') ?>" class="h-10 rounded bg-white border border-slate-200 object-contain p-1" alt="Logo <?= $label ?>">
                                                    <button type="button" onclick="if(confirm('Hapus logo ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_header_logo?id=<?= $row['id'] ?>&file=<?= urlencode($logoFile) ?>&pos=<?= $pos ?>'" class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full w-5 h-5 items-center justify-center text-xs shadow-md hover:bg-red-600">&times;</button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <input type="file" name="header_logos_<?= $pos ?>[]" multiple accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-blue-100 file:text-blue-800 hover:file:bg-blue-200">
                                </div>
                                <?php endforeach; ?>
                            </div>
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
                            <div class="flex flex-col space-y-6">
                                <?php 
                                // Kelompokkan berdasarkan digit pertama dari race_number
                                $groupedClasses = [];
                                $unassignedClasses = [];
                                foreach ($classes as $c) {
                                    $rNo = $c['race_number'] ?? '';
                                    if (strlen($rNo) >= 3 && is_numeric(substr($rNo, 0, 1))) {
                                        $dayNum = substr($rNo, 0, 1);
                                        $groupedClasses["Hari $dayNum"][] = $c;
                                    } else {
                                        $unassignedClasses[] = $c;
                                    }
                                }
                                ksort($groupedClasses);
                                if (!empty($unassignedClasses)) {
                                    $groupedClasses['Belum Ditentukan (Unassigned)'] = $unassignedClasses;
                                }
                                ?>

                                <?php foreach($groupedClasses as $dayLabel => $dayClasses): ?>
                                    <div class="mb-4">
                                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-widest mb-3 bg-slate-100 p-3 rounded-lg border border-slate-200 shadow-sm flex items-center gap-2">
                                            <span class="text-xl">📅</span> <?= htmlspecialchars($dayLabel) ?>
                                        </h3>
                                        <div class="overflow-x-auto bg-white rounded-xl border border-slate-200 shadow-sm">
                                            <table class="w-full text-left text-sm text-slate-600">
                                                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-black tracking-wider text-slate-400">
                                                    <tr>
                                                        <th class="p-3 w-24">Nomor (3 Digit)</th>
                                                        <th class="p-3 w-32">Jam Acara</th>
                                                        <th class="p-3">Kategori Lomba & Jarak</th>
                                                        <th class="p-3 w-24 text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    <?php foreach($dayClasses as $c): ?>
                                                    <tr class="hover:bg-slate-50 transition-colors group">
                                                        <td class="p-2 align-top">
                                                            <input type="hidden" name="class_ids[]" value="<?= $c['id'] ?>">
                                                            <input type="text" name="race_numbers[]" value="<?= htmlspecialchars($c['race_number'] ?? '') ?>" placeholder="101" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 text-center focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                        <td class="p-2 align-top">
                                                            <input type="time" name="race_times[]" value="<?= htmlspecialchars($c['race_time'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                        <td class="p-2">
                                                            <div class="flex flex-col gap-1">
                                                                <div class="flex gap-1">
                                                                    <select name="age_group_ids[]" class="w-1/2 bg-slate-50 border border-slate-200 rounded p-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                                        <option value="">Umum</option>
                                                                        <?php foreach($ageGroups as $ag): ?>
                                                                            <option value="<?= $ag['id'] ?>" <?= ($c['age_group_id'] == $ag['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ag['group_name']) ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <select name="distance_ids[]" class="w-1/2 bg-slate-50 border border-slate-200 rounded p-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                                        <?php foreach($distances as $dist): ?>
                                                                            <option value="<?= $dist['id'] ?>" <?= ($c['distance_id'] == $dist['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dist['distance_name']) ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <input type="text" name="category_names[]" value="<?= htmlspecialchars($c['category_name']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded p-1.5 text-[10px] font-bold text-blue-500 uppercase tracking-widest focus:ring-2 focus:ring-blue-500" placeholder="Kategori">
                                                            </div>
                                                        </td>
                                                        <td class="p-2 text-center align-top whitespace-nowrap">
                                                            <button type="submit" title="Simpan Perubahan" class="text-emerald-500 opacity-50 hover:opacity-100 hover:text-white p-2 rounded hover:bg-emerald-500 transition-all font-bold text-sm h-8 w-8 inline-flex items-center justify-center mr-1">
                                                                💾
                                                            </button>
                                                            <button type="button" title="Hapus" onclick="if(confirm('Hapus kelas ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_class/<?= $c['id'] ?>'" class="text-red-500 opacity-50 hover:opacity-100 hover:text-white p-2 rounded hover:bg-red-500 transition-all font-bold text-lg h-8 w-8 inline-flex items-center justify-center">
                                                                &times;
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
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
