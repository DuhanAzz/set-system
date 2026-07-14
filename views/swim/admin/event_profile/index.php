<div class="font-sans">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">
                <?= ($eventId > 0) ? 'Edit Event Profile' : 'Buat Event Baru' ?>
            </h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">
                ID Event: #<?= $eventId ?>
            </p>
        </div>
    </div>

    <form action="<?= getenv('APP_URL') ?>/swim/event_profile/update" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
                <h3 class="font-black text-slate-800 uppercase italic text-xs tracking-widest mb-6 border-b pb-2">Informasi Utama</h3>
                <div class="space-y-6">
                    <div>
                        <label class="label-text">Nama Event</label>
                        <input type="text" name="nama_event" value="<?= val($row, 'event_name') ?>" class="input-field" required placeholder="Contoh: KEJUARAAN RENANG 2026">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label-text">Tgl Mulai</label>
                            <input type="date" name="event_start_date" value="<?= val($row, 'event_date_start') ?>" class="input-field">
                        </div>
                        <div>
                            <label class="label-text">Tgl Selesai</label>
                            <input type="date" name="event_end_date" value="<?= val($row, 'event_date_end') ?>" class="input-field">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="label-text">Lokasi (Nama Kolam)</label>
                            <input type="text" name="lokasi" value="<?= val($row, 'event_location') ?>" class="input-field" placeholder="Contoh: Stadion Akuatik GBK">
                        </div>
                        <div>
                            <label class="label-text">Kabupaten / Kota</label>
                            <input type="text" name="kota" value="<?= val($row, 'event_city') ?>" class="input-field" placeholder="Contoh: Jakarta Pusat">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 rounded-[2rem] shadow-sm border border-indigo-100 p-8">
                <h3 class="font-black text-indigo-900 uppercase italic text-xs tracking-widest mb-6 border-b border-indigo-200 pb-2">Spesifikasi Teknis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Jumlah Lintasan</label>
                        <input type="number" name="lane_count" value="<?= val($row, 'lane_count', 8) ?>" class="w-full px-4 py-3 rounded-xl border border-indigo-200 font-bold text-indigo-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Hitung Umur Per</label>
                        <select name="age_calculation_type" class="w-full px-4 py-3 rounded-xl border border-indigo-200 font-bold text-slate-700">
                            <?php $ac = val($row, 'age_calculation_type', 'Dec 31'); ?>
                            <option value="Dec 31" <?= $ac == 'Dec 31' ? 'selected' : '' ?>>31 Desember</option>
                            <option value="Meet Start" <?= $ac == 'Meet Start' ? 'selected' : '' ?>>Hari H Lomba</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Tipe Kolam</label>
                        <select name="pool_type" class="w-full px-4 py-3 rounded-xl border border-indigo-200 font-bold text-slate-700">
                            <?php $pt = val($row, 'pool_type', '50m'); ?>
                            <option value="50m" <?= $pt == '50m' ? 'selected' : '' ?>>50 Meter (Olimpik)</option>
                            <option value="25m" <?= $pt == '25m' ? 'selected' : '' ?>>25 Meter (Short Course)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Partisipasi</label>
                        <select name="participation_type" class="w-full px-4 py-3 rounded-xl border border-indigo-200 font-bold text-slate-700">
                            <?php $pp = val($row, 'participation_type', 'club'); ?>
                            <option value="club" <?= $pp == 'club' ? 'selected' : '' ?>>Antar Club</option>
                            <option value="school" <?= $pp == 'school' ? 'selected' : '' ?>>Antar Sekolah</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Lintasan Aktif (Digunakan)</label>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-1">
                            <?php 
                            $savedLanes = (isset($row['used_lanes']) && $row['used_lanes'] !== null) ? explode(',', $row['used_lanes']) : null;
                            for($i=0; $i<=9; $i++): 
                                $checked = '';
                                if ($savedLanes !== null) {
                                    $checked = in_array((string)$i, $savedLanes) ? 'checked' : '';
                                } else {
                                    $checked = ($i >= 1 && $i <= 8) ? 'checked' : '';
                                }
                            ?>
                            <label class="flex items-center gap-3 p-3 bg-white border border-indigo-100 rounded-xl cursor-pointer hover:bg-indigo-50 transition">
                                <input type="checkbox" name="used_lanes[]" value="<?= $i ?>" <?= $checked ?> class="w-4 h-4 text-indigo-600 rounded border-indigo-300 focus:ring-indigo-500">
                                <span class="text-xs font-bold text-indigo-900">Lintasan <?= $i ?></span>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-indigo-400 uppercase mb-2 block">Acuan Rekor Event (Pecah Rekor)</label>
                        <select name="record_package_id" class="w-full px-4 py-3 rounded-xl border border-indigo-200 font-bold text-slate-700">
                            <option value="">-- Tidak Menggunakan Rekor Event Tambahan --</option>
                            <?php foreach($allPackages as $pkg): ?>
                                <option value="<?= $pkg['id'] ?>" <?= (val($row, 'record_package_id') == $pkg['id']) ? 'selected' : '' ?>>
                                    Paket: <?= htmlspecialchars($pkg['package_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-indigo-500 mt-1 italic">Paket ini dikelola oleh Master Admin dan berfungsi sebagai baseline rekor lomba selain Rekor Nasional.</p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 rounded-[2rem] shadow-sm border border-amber-200 p-8">
                <h3 class="font-black text-amber-900 uppercase italic text-xs tracking-widest mb-6 border-b border-amber-200 pb-2">📂 Kelengkapan Dokumen Publikasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="col-span-1 md:col-span-2 border border-dashed border-amber-300 bg-white p-5 rounded-2xl">
                        <label class="label-text text-amber-700">1. Poster Event (JPG/PNG)</label>
                        <p class="text-[9px] text-amber-600 mb-3 font-medium">Poster yang akan tampil di halaman utama / explore lomba.</p>
                        <div class="flex items-center gap-4">
                            <?php if(!empty($row['poster_image'])): ?>
                                <div class="flex flex-col items-center gap-2">
                                    <a href="<?= getUrlPreview($row['poster_image']) ?>" target="_blank" class="shrink-0 h-16 w-16 bg-slate-100 rounded-xl border border-slate-200 overflow-hidden flex items-center justify-center">
                                        <img src="<?= getUrlPreview($row['poster_image']) ?>" class="max-h-full max-w-full object-cover">
                                    </a>
                                    <a href="<?= getenv('APP_URL') ?>/swim/event_profile/delete_image?type=poster_image" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');" class="bg-red-600 hover:bg-red-700 text-white text-[9px] px-2 py-1 rounded-md font-bold text-center w-full block">Hapus</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="poster_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 transition">
                        </div>
                    </div>

                    <div class="border border-dashed border-amber-300 bg-white p-5 rounded-2xl">
                        <label class="label-text text-amber-700">2. Buku Panduan / Juknis (PDF)</label>
                        <p class="text-[9px] text-amber-600 mb-3 font-medium">Buku panduan teknis untuk dibaca oleh klub pendaftar.</p>
                        
                        <?php if($docJuknis): ?>
                            <div class="mb-3 flex items-center justify-between bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                                <span class="text-[10px] font-bold text-green-700">✅ Terunggah</span>
                                <a href="<?= getUrlPreview($docJuknis['file_path']) ?>" target="_blank" class="text-[10px] font-black text-blue-600 hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="juknis_file" accept=".pdf" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer">
                        <span class="text-[8px] text-slate-400 mt-1 block italic">*Upload ulang untuk menimpa Juknis lama.</span>
                    </div>

                    <div class="border border-dashed border-amber-300 bg-white p-5 rounded-2xl">
                        <label class="label-text text-amber-700">3. Form Pendaftaran (Opsional)</label>
                        <p class="text-[9px] text-amber-600 mb-3 font-medium">Formulir pendaftaran manual format Excel / Spreadsheet.</p>
                        
                        <?php if($docForm): ?>
                            <div class="mb-3 flex items-center justify-between bg-green-50 px-3 py-2 rounded-lg border border-green-200">
                                <span class="text-[10px] font-bold text-green-700">✅ Terunggah</span>
                                <a href="<?= getUrlPreview($docForm['file_path']) ?>" target="_blank" class="text-[10px] font-black text-blue-600 hover:underline">Lihat File</a>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="form_file" accept=".xls,.xlsx" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer">
                        <span class="text-[8px] text-slate-400 mt-1 block italic">*Upload ulang untuk menimpa form lama.</span>
                    </div>

                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8">
                <h3 class="font-black text-slate-800 uppercase italic text-xs tracking-widest mb-6 border-b pb-2">Rekening Pembayaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label-text">Nama Bank</label>
                        <input type="text" name="bank_name" value="<?= val($row, 'bank_name') ?>" class="input-field" placeholder="BCA">
                    </div>
                    <div>
                        <label class="label-text">No Rekening</label>
                        <input type="text" name="bank_account_number" value="<?= val($row, 'bank_account_number') ?>" class="input-field" placeholder="123xxx">
                    </div>
                    <div>
                        <label class="label-text">Atas Nama</label>
                        <input type="text" name="bank_account_name" value="<?= val($row, 'bank_account_name') ?>" class="input-field" placeholder="Panitia">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-6"> 
            
            <div class="bg-slate-900 rounded-[2rem] shadow-xl p-6 relative overflow-hidden text-white"> 
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -translate-y-1/2 translate-x-1/2"></div>
                
                <h3 class="font-black uppercase italic text-xs tracking-widest mb-6 text-slate-400 relative z-10">Status Event</h3>
                <div class="space-y-3 relative z-10">
                    <?php 
                    $statuses = [
                        'upcoming' => ['Draft / Upcoming', 'border-slate-600', 'text-slate-400'],
                        'open'     => ['Open Registration', 'border-blue-500', 'text-blue-400'],
                        'closed'   => ['Closed (Running)', 'border-emerald-500', 'text-emerald-400'],
                        'done'     => ['Finished', 'border-red-500', 'text-red-400']
                    ];
                    $curStat = val($row, 'event_status', 'upcoming');
                    
                    foreach($statuses as $key => $val):
                        $active = ($curStat == $key);
                    ?>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer hover:bg-slate-800 transition <?= $active ? $val[1].' bg-slate-800 ring-1 ring-offset-0 ring-'.$val[1] : 'border-slate-700' ?>">
                        <input type="radio" name="status" value="<?= $key ?>" <?= $active ? 'checked' : '' ?> class="accent-blue-500 w-4 h-4 bg-slate-700 border-slate-500">
                        <span class="text-xs font-bold uppercase <?= $active ? 'text-white' : 'text-slate-400' ?>"><?= $val[0] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <hr class="my-6 border-slate-700 relative z-10">
                
                <button type="submit" class="relative z-10 w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl uppercase tracking-widest text-xs shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-1">
                    Simpan Perubahan
                </button>
            </div>

            <div class="bg-white rounded-[2rem] shadow-lg p-6 border border-slate-200">
                <h3 class="font-black text-slate-800 uppercase italic text-xs tracking-widest mb-4">Logo & Branding</h3>
                
                <div class="mb-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Logo Kiri (Utama)</p>
                    <div class="flex items-center gap-3">
                        <?php if(!empty($row['logo_left'])): ?>
                            <div class="flex flex-col items-center gap-1">
                                <img src="<?= getUrlPreview($row['logo_left']) ?>" class="h-12 w-12 object-contain bg-slate-50 rounded-lg border">
                                <a href="<?= getenv('APP_URL') ?>/swim/event_profile/delete_image?type=logo_left" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');" class="bg-red-600 hover:bg-red-700 text-white text-[9px] px-2 py-0.5 rounded flex-shrink-0 font-bold block text-center w-full">Hapus</a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo_left" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <div class="mb-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Logo Kanan</p>
                    <div class="flex items-center gap-3">
                        <?php if(!empty($row['logo_right'])): ?>
                            <div class="flex flex-col items-center gap-1">
                                <img src="<?= getUrlPreview($row['logo_right']) ?>" class="h-12 w-12 object-contain bg-slate-50 rounded-lg border">
                                <a href="<?= getenv('APP_URL') ?>/swim/event_profile/delete_image?type=logo_right" onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');" class="bg-red-600 hover:bg-red-700 text-white text-[9px] px-2 py-0.5 rounded flex-shrink-0 font-bold block text-center w-full">Hapus</a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo_right" class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <hr class="my-4 border-slate-100">

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Sponsor (Bisa Banyak)</p>
                    <input type="file" name="sponsor_files[]" multiple class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 mb-3">
                    
                    <?php if(count($sponsors) > 0): ?>
                        <div class="grid grid-cols-3 gap-2">
                            <?php foreach($sponsors as $sp): ?>
                                <div class="relative group bg-slate-50 border rounded-md h-12 flex items-center justify-center overflow-hidden">
                                    <img src="<?= getUrlPreview($sp['image_path']) ?>" class="max-h-full max-w-full p-1 object-contain">
                                    <a href="<?= getenv('APP_URL') ?>/swim/event_profile/delete_sponsor?id=<?= $sp['id'] ?>" onclick="return confirm('Hapus?')" class="absolute inset-0 bg-red-500/80 text-white flex items-center justify-center text-xs font-bold opacity-0 group-hover:opacity-100 transition cursor-pointer">×</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </form>
</div>

<style>
    .label-text { display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.05em; }
    .input-field { width: 100%; padding: 10px 15px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 700; color: #334155; font-size: 14px; transition: all 0.2s; }
    .input-field:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(isset($_SESSION['swal_type'])): ?>
        Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            icon: '<?= $_SESSION['swal_type'] ?>', title: '<?= $_SESSION['swal_msg'] ?>'
        });
        <?php unset($_SESSION['swal_type'], $_SESSION['swal_msg']); ?>
    <?php endif; ?>
</script>