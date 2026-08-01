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
                    <p class="text-slate-500 mt-2 font-medium">Pengaturan Profil Utama</p>
                </div>
                <?php if(!empty($row)): ?>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2 md:gap-4">
                    <button type="button" onclick="openLandingModal()" class="bg-purple-600 hover:bg-purple-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-purple-500/25 transition-all flex items-center uppercase tracking-widest text-sm">
                        <span class="mr-2">🌍</span> Atur Landing Page
                    </button>
                    <button type="button" onclick="document.getElementById('profile-form').submit()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-blue-500/25 transition-all flex items-center uppercase tracking-widest text-sm">
                        <span class="mr-2">💾</span> Simpan Profil & Gambar
                    </button>
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
                <form id="profile-form" action="<?= getenv('APP_URL') ?>/roll/admin/events/update_profile" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Max Individu / Atlet</label>
                            <input type="number" name="max_individual_races" value="<?= htmlspecialchars($row['max_individual_races'] ?? '2') ?>" min="1" max="10" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Max Beregu / Atlet</label>
                            <input type="number" name="max_team_races" value="<?= htmlspecialchars($row['max_team_races'] ?? '1') ?>" min="0" max="10" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <!-- Kategori Biaya Lomba -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pt-4 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Biaya Speed (Rp)</label>
                            <input type="number" name="fee_speed" value="<?= htmlspecialchars($row['fee_speed'] ?? '450000') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-fuchsia-500 font-bold text-fuchsia-700" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Biaya Standart (Rp)</label>
                            <input type="number" name="fee_standart" value="<?= htmlspecialchars($row['fee_standart'] ?? '350000') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-amber-500 font-bold text-amber-600" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Biaya Pemula (Rp)</label>
                            <input type="number" name="fee_pemula" value="<?= htmlspecialchars($row['fee_pemula'] ?? '350000') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-emerald-500 font-bold text-emerald-600" required>
                        </div>
                    </div>

                    <!-- Kategori Campuran -->
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-4 bg-indigo-50 border border-indigo-100 rounded-xl mb-6">
                        <div>
                            <h4 class="text-sm font-bold text-indigo-900">Izinkan Atlet Pemula Ikut Kelas Standar</h4>
                            <p class="text-[10px] font-bold text-indigo-700/80 uppercase tracking-wider mt-1">Jika aktif, atlet pemula dapat mendaftar di kelas standar sekaligus (akan dikenakan biaya 2x). Atlet Speed tetap tidak bisa turun kelas.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mt-4 md:mt-0">
                            <input type="checkbox" name="allow_pemula_standart_mix" value="1" class="sr-only peer" <?= !empty($row['allow_pemula_standart_mix']) ? 'checked' : '' ?>>
                            <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600 shadow-inner"></div>
                        </label>
                    </div>

                    <!-- Rekening Pembayaran -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pt-4 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="<?= htmlspecialchars($row['bank_name'] ?? '') ?>" placeholder="Misal: BCA" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account" value="<?= htmlspecialchars($row['bank_account'] ?? '') ?>" placeholder="Misal: 1234567890" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Atas Nama</label>
                            <input type="text" name="bank_account_name" value="<?= htmlspecialchars($row['bank_account_name'] ?? '') ?>" placeholder="Misal: Budi Santoso" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 font-bold" required>
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
                
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- LANDING PAGE MODAL -->
<div id="landingModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeLandingModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">
        <div class="sticky top-0 bg-white/80 backdrop-blur-md border-b border-slate-200 px-8 py-5 flex justify-between items-center z-10">
            <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Pengaturan Landing Page</h3>
            <button type="button" onclick="closeLandingModal()" class="text-slate-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form action="<?= getenv('APP_URL') ?>/roll/admin/events/saveLandingPage" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="event_id" value="<?= $row['id'] ?? 0 ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Custom URL Slug (Tanpa Spasi)</label>
                    <div class="flex items-center">
                        <span class="bg-slate-100 text-slate-500 border border-slate-200 border-r-0 rounded-l-lg px-3 py-2 text-sm font-mono">setsystem.id/</span>
                        <input type="text" name="slug" value="<?= htmlspecialchars($landing['slug'] ?? '') ?>" placeholder="indonesiarollerspeedseries" class="w-full bg-white border border-slate-200 rounded-r-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-mono text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Status Halaman</label>
                    <select name="status" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-bold">
                        <option value="Draft" <?= (($landing['status'] ?? 'Draft') == 'Draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                        <option value="Published" <?= (($landing['status'] ?? 'Draft') == 'Published') ? 'selected' : '' ?>>Published (Bisa Diakses)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Title (Judul Utama)</label>
                    <input type="text" name="hero_title" value="<?= htmlspecialchars($landing['hero_title'] ?? '') ?>" placeholder="Contoh: ARENA SPORTS 2026" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Warna Tema (Hex Code)</label>
                    <input type="color" name="theme_color" value="<?= htmlspecialchars($landing['theme_color'] ?? '#2563eb') ?>" class="w-full h-10 p-1 bg-white border border-slate-200 rounded-lg cursor-pointer">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500" placeholder="Contoh: The biggest roller skating competition in Indonesia."><?= htmlspecialchars($landing['hero_subtitle'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Deskripsi Event (About)</label>
                    <textarea name="about_text" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($landing['about_text'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Jadwal & Agenda Lomba</label>
                    <textarea name="schedule_text" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500"><?= htmlspecialchars($landing['schedule_text'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Kontak WhatsApp</label>
                    <input type="text" name="contact_whatsapp" value="<?= htmlspecialchars($landing['contact_whatsapp'] ?? '') ?>" placeholder="6281234567890" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Kontak Email</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($landing['contact_email'] ?? '') ?>" placeholder="info@example.com" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>
            
            <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
                <?php if(!empty($landing['slug'])): ?>
                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($landing['slug']) ?>" target="_blank" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 flex items-center gap-2">
                    Lihat Halaman
                </a>
                <?php endif; ?>
                <button type="button" onclick="closeLandingModal()" class="px-6 py-2.5 rounded-xl text-slate-500 font-bold hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-8 py-2.5 bg-purple-600 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-purple-700 hover:shadow-lg hover:shadow-purple-500/30 transition-all">
                    Simpan Landing Page
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openLandingModal() {
    document.getElementById('landingModal').classList.remove('hidden');
}
function closeLandingModal() {
    document.getElementById('landingModal').classList.add('hidden');
}
</script>
