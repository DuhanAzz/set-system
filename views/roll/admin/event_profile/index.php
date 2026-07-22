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
                <?php if(!empty($row)): ?>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2 md:gap-4">
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/events/print_schedule" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all flex items-center">
                        <span class="mr-2">🖨️</span> Cetak Jadwal & Kelas
                    </a>
                </div>
                <?php endif; ?>
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
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Biaya / Kelas (Rp)</label>
                            <input type="number" name="entry_fee" value="<?= htmlspecialchars($row['entry_fee'] ?? '150000') ?>" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
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
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-blue-500/25 transition-all uppercase tracking-widest text-sm">Simpan Profil & Gambar</button>
                    </div>
                </form>
                
                <!-- Kelas Lomba -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm w-full flex flex-col">
                <div class="p-6 border-b border-slate-200/50 flex justify-between items-center bg-white rounded-t-2xl">
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Manajemen Kelas & Jadwal Lomba</h3>
                </div>
                
                <div class="p-6 flex-1 overflow-auto">
                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/bulk_update_schedule" method="POST">
                        <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                        <div class="overflow-x-auto bg-white rounded-xl border border-slate-200 shadow-sm mb-6">
                            <table class="w-full text-left text-sm text-slate-600">
                                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-black tracking-wider text-slate-400">
                                    <tr>
                                        <th class="p-3 w-24">NO. LOMBA</th>
                                        <th class="p-3 w-32">PUKUL</th>
                                        <th class="p-3 w-40">JARAK LOMBA</th>
                                        <th class="p-3 w-40">KELOMPOK UMUR</th>
                                        <th class="p-3 w-40">ROLLER</th>
                                        <th class="p-3 w-32">GENDER</th>
                                        <th class="p-3 w-24 text-center">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="schedule-matrix" class="divide-y divide-slate-100">
                                    <?php foreach($classes as $c): ?>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="p-2 align-top">
                                            <input type="hidden" name="class_ids[]" value="<?= $c['id'] ?>">
                                            <input type="text" name="race_numbers[]" value="<?= htmlspecialchars($c['race_number'] ?? '') ?>" placeholder="101" required class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 text-center focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="p-2 align-top">
                                            <input type="time" name="race_times[]" value="<?= htmlspecialchars($c['race_time'] ?? '') ?>" required class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="p-2 align-top">
                                            <select name="distance_ids[]" class="distance-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                <option value="">- Jarak -</option>
                                                <?php foreach($distances as $dist): ?>
                                                    <option value="<?= $dist['id'] ?>" data-dist-name="<?= htmlspecialchars($dist['distance_name']) ?>" <?= ($c['distance_id'] == $dist['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dist['distance_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2 align-top">
                                            <select name="age_group_ids[]" class="age-group-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                <option value="">Umum</option>
                                                <?php foreach($ageGroups as $ag): ?>
                                                    <option value="<?= $ag['id'] ?>" data-ag-name="<?= htmlspecialchars($ag['group_name']) ?>" <?= ($c['age_group_id'] == $ag['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ag['group_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2 align-top">
                                            <select name="skate_class_ids[]" class="roller-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                <option value="">- Roller -</option>
                                                <?php foreach($skateClasses as $sc): ?>
                                                    <option value="<?= $sc['id'] ?>" data-roller-name="<?= htmlspecialchars($sc['class_name']) ?>" <?= ($c['skate_class_id'] == $sc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sc['class_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2 align-top">
                                            <select name="genders[]" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                                <option value="Putra" <?= ($c['gender'] == 'Putra') ? 'selected' : '' ?>>Putra</option>
                                                <option value="Putri" <?= ($c['gender'] == 'Putri') ? 'selected' : '' ?>>Putri</option>
                                                <option value="Campuran" <?= ($c['gender'] == 'Campuran') ? 'selected' : '' ?>>Campuran</option>
                                            </select>
                                        </td>
                                        <td class="p-2 text-center align-top whitespace-nowrap">
                                            <button type="button" title="Hapus" onclick="if(confirm('Hapus kelas ini?')) window.location.href='<?= getenv('APP_URL') ?>/roll/admin/events/delete_class/<?= $c['id'] ?>'" class="text-red-500 opacity-50 hover:opacity-100 hover:text-white p-2 rounded hover:bg-red-500 transition-all font-bold text-lg h-8 w-8 inline-flex items-center justify-center">
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-6">
                            <button type="button" onclick="addScheduleRow()" class="bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300 font-bold py-3 px-6 rounded-xl shadow-sm transition-all text-sm w-full border-dashed flex justify-center items-center gap-2">
                                <span class="text-xl">+</span> TAMBAH BARIS JADWAL
                            </button>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-slate-200/50">
                            <button type="submit" onclick="return validateClasses()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all uppercase tracking-widest text-sm flex items-center gap-2">
                                💾 Simpan Semua Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<template id="row-template">
    <tr class="hover:bg-slate-50 transition-colors group">
        <td class="p-2 align-top">
            <input type="hidden" name="class_ids[]" value="">
            <input type="text" name="race_numbers[]" value="" placeholder="101" required class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 text-center focus:ring-2 focus:ring-blue-500">
        </td>
        <td class="p-2 align-top">
            <input type="time" name="race_times[]" value="" required class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
        </td>
        <td class="p-2 align-top">
            <select name="distance_ids[]" class="distance-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                <option value="">- Jarak -</option>
                <?php foreach($distances as $dist): ?>
                    <option value="<?= $dist['id'] ?>" data-dist-name="<?= htmlspecialchars($dist['distance_name']) ?>"><?= htmlspecialchars($dist['distance_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="p-2 align-top">
            <select name="age_group_ids[]" class="age-group-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                <option value="">Umum</option>
                <?php foreach($ageGroups as $ag): ?>
                    <option value="<?= $ag['id'] ?>" data-ag-name="<?= htmlspecialchars($ag['group_name']) ?>"><?= htmlspecialchars($ag['group_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="p-2 align-top">
            <select name="skate_class_ids[]" class="roller-select w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                <option value="">- Roller -</option>
                <?php foreach($skateClasses as $sc): ?>
                    <option value="<?= $sc['id'] ?>" data-roller-name="<?= htmlspecialchars($sc['class_name']) ?>"><?= htmlspecialchars($sc['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="p-2 align-top">
            <select name="genders[]" class="w-full bg-slate-50 border border-slate-200 rounded p-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                <option value="Putra" selected>Putra</option>
                <option value="Putri">Putri</option>
                <option value="Campuran">Campuran</option>
            </select>
        </td>
        <td class="p-2 text-center align-top whitespace-nowrap">
            <button type="button" title="Hapus Baris" onclick="this.closest('tr').remove()" class="text-red-500 opacity-50 hover:opacity-100 hover:text-white p-2 rounded hover:bg-red-500 transition-all font-bold text-lg h-8 w-8 inline-flex items-center justify-center">
                &times;
            </button>
        </td>
    </tr>
</template>

<script>
function attachPorserosiRules(row) {
    if(!row) return;
    const agSel = row.querySelector('.age-group-select');
    const rollerSel = row.querySelector('.roller-select');
    const distSel = row.querySelector('.distance-select');
    
    function applyRules() {
        const agName = agSel.options[agSel.selectedIndex]?.getAttribute('data-ag-name') || '';
        const rollerName = rollerSel.options[rollerSel.selectedIndex]?.getAttribute('data-roller-name') || '';
        
        const isSpeed = rollerName.toUpperCase().includes('SPEED');
        const isStandar = rollerName.toUpperCase().includes('STANDART') || rollerName.toUpperCase().includes('STANDAR');
        const isPemula = rollerName.toUpperCase().includes('PEMULA');
        
        const isKuA = agName.toUpperCase().includes('KU A');
        const isKuB = agName.toUpperCase().includes('KU B');
        const isKuC = agName.toUpperCase().includes('KU C');
        const isKuD = agName.toUpperCase().includes('KU D');
        const isJunior = agName.toUpperCase().includes('JUNIOR');
        const isSenior = agName.toUpperCase().includes('SENIOR');
        
        // Porserosi Rules:
        Array.from(distSel.options).forEach(opt => {
            const distName = (opt.getAttribute('data-dist-name') || '').toUpperCase();
            if (!distName) return; // Skip default empty option
            
            let shouldDisable = true; // Default disable, then enable based on allowed list
            
            if (isSpeed) {
                // Speed: DTT 200, 500+D, 1000m, Elim, PTP
                if (distName.includes('DTT') && distName.includes('200')) shouldDisable = false;
                if (distName.includes('500') && distName.includes('+D')) shouldDisable = false;
                if (distName.includes('1000') && !distName.includes('POINT') && !distName.includes('ELIMINASI')) shouldDisable = false;
                
                // Eliminasi Rules
                if (distName.includes('ELIMINASI')) {
                    if ((isKuA || isKuB) && distName.includes('3000')) shouldDisable = false;
                    if ((isKuC || isKuD) && distName.includes('5000')) shouldDisable = false;
                    if ((isJunior || isSenior) && distName.includes('10.000')) shouldDisable = false;
                }
                
                // PTP / Point to Point Rules
                if (distName.includes('PTP') || distName.includes('POINT')) {
                    if ((isKuC || isKuD) && distName.includes('3000')) shouldDisable = false;
                    if ((isJunior || isSenior) && distName.includes('5000')) shouldDisable = false;
                    // Note: if there is a 10k point race as well, adjust as needed.
                }
            } else if (isStandar) {
                // Standar: 300, 500, 1000
                if (distName.includes('300') && !distName.includes('3000')) shouldDisable = false;
                if (distName.includes('500') && !distName.includes('+D') && !distName.includes('5000')) shouldDisable = false;
                if (distName.includes('1000') && !distName.includes('10.000')) shouldDisable = false;
            } else if (isPemula) {
                // Pemula: 100m, 200m
                if (distName.includes('100') && !distName.includes('1000')) shouldDisable = false;
                if (distName.includes('200')) shouldDisable = false;
            } else {
                // Jika roller belum dipilih, aktifkan semua sementara (atau disable semua)
                shouldDisable = false; 
            }
            
            // Allow ITT 100m explicitly only for Speed Senior (Assuming ITT is Speed)
            if (distName.includes('ITT 100') && isSenior && isSpeed) {
                shouldDisable = false;
            }
            
            opt.disabled = shouldDisable;
            if (shouldDisable && opt.selected) distSel.value = ''; // Reset if selected is now disabled
        });
    }

    agSel.addEventListener('change', applyRules);
    rollerSel.addEventListener('change', applyRules);
}

// Attach rules to existing rows
document.querySelectorAll('tbody#schedule-matrix tr').forEach(row => {
    attachPorserosiRules(row);
    // Trigger initial state
    const agSel = row.querySelector('.age-group-select');
    if(agSel) agSel.dispatchEvent(new Event('change'));
});

function addScheduleRow() {
    const template = document.getElementById('row-template');
    const tbody = document.getElementById('schedule-matrix');
    const clone = template.content.cloneNode(true);
    
    // Attach event listeners to new row
    attachPorserosiRules(clone.querySelector('tr'));
    
    tbody.appendChild(clone);
}
</script>
