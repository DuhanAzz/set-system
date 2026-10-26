<div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Ekspor Data Kustom</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Unduh Sertifikat Canva & Laporan Resmi</p>
    </div>

    <!-- FORM FILTER -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form method="GET" action="" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Event -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">1. Pilih Event</label>
                    <select name="event_id" id="eventSelect" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500" onchange="document.getElementById('filterForm').submit()">
                        <?php foreach($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= ($ev['id'] == $selectedEventId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['event_name']) ?> (<?= date('Y', strtotime($ev['event_date_start'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- KU -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">2. Kelompok Umur</label>
                    <select name="ku" id="kuSelect" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500">
                        <option value="ALL">SEMUA KELOMPOK UMUR</option>
                        <?php foreach($ageGroups as $ag): ?>
                            <option value="<?= htmlspecialchars($ag['group_name']) ?>">KU <?= htmlspecialchars($ag['group_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tim -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">3. Tim / Sekolah</label>
                    <select name="team" id="teamSelect" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500">
                        <option value="ALL">SEMUA TIM / SEKOLAH</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Limit / Capaian -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">4. Batasan Hasil</label>
                    <select name="limit_hasil" id="limitSelect" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm font-medium focus:ring-blue-500 focus:border-blue-500">
                        <option value="ALL">Semua Peserta (Finished)</option>
                        <option value="TOP3">Hanya Juara 1-3 (Peraih Medali)</option>
                        <option value="DQ">Hanya Pelanggaran / DQ</option>
                    </select>
                </div>

                <!-- Ranking Mode -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">5. Mode Perangkingan</label>
                    <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <label class="flex-1 text-center cursor-pointer">
                            <input type="radio" name="rank_mode" value="SPLIT" class="peer hidden" checked id="rankSplit">
                            <div class="text-xs font-black uppercase py-2 rounded-lg peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition text-slate-500">PISAH / SPLIT KU</div>
                        </label>
                        <label class="flex-1 text-center cursor-pointer">
                            <input type="radio" name="rank_mode" value="OVERALL" class="peer hidden" id="rankOverall">
                            <div class="text-xs font-black uppercase py-2 rounded-lg peer-checked:bg-white peer-checked:text-emerald-600 peer-checked:shadow-sm transition text-slate-500">GABUNGAN / OVERALL</div>
                        </label>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Mempengaruhi hasil akhir khusus nomor Lomba Gabungan.</p>
                </div>
                
                <div class="md:col-span-2 lg:col-span-3 border-t border-slate-100 mt-2 pt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Komponen Judul (PDF/Excel) -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">A. Komponen Judul Acara (Khusus Laporan)</label>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs font-bold text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_event_no" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Nomor Acara</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_date" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Tanggal & Jam</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_event_name" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Jarak & Gaya</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_group" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Kelompok Umur</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_gender" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Jenis Kelamin</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_pool" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Tipe Kolam</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="cfg_round" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Babak (FINAL)</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none text-amber-700 font-extrabold"><input type="checkbox" id="cfg_show_records" class="rounded border-amber-300 text-amber-600 cfg-cb" checked> Tampilkan Rekor</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none text-emerald-700 font-extrabold col-span-2"><input type="checkbox" id="cfg_medal_tally" class="rounded border-emerald-300 text-emerald-600 cfg-cb" checked> 🏅 Sertakan Klasemen Medali (Medal Tally)</label>
                        </div>
                    </div>
                    
                    <!-- Kolom Tabel Atlet -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">B. Kolom Tabel Data</label>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs font-bold text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_uid" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Kolom UID</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_lahir" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Tahun Lahir</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_ku" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Kolom KU</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_tim" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> TIM / Sekolah</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_waktu" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Waktu Entry</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_hasil" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Waktu Final</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" id="col_ket" class="rounded border-slate-300 text-blue-600 cfg-cb" checked> Keterangan DQ</label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- ACTION CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- CARD 1: CANVA -->
        <div class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-blue-900 rounded-2xl p-7 text-white shadow-xl relative overflow-hidden group border border-indigo-500/30 flex flex-col justify-between">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-2xl font-black flex items-center gap-2 text-white">🎨 Canva Bulk Create</h3>
                    <span class="bg-indigo-500/30 text-indigo-200 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border border-indigo-400/30">Auto E-Sertifikat</span>
                </div>
                <p class="text-indigo-200 text-xs font-medium mb-4 leading-relaxed">Unduh format CSV khusus yang dapat langsung di-upload ke Canva untuk mencetak ribuan E-Sertifikat secara otomatis.</p>
                
                <!-- PILIH FORMAT TEMPLATE CANVA -->
                <div class="bg-indigo-950/80 rounded-xl p-3 border border-indigo-400/40 mb-4">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <div>
                                <p class="text-[11px] font-black uppercase text-indigo-100 tracking-wider">Format Kolom CSV:</p>
                                <p class="text-[10px] text-indigo-300 font-mono font-bold">Nama;Juara;Nomor lomba</p>
                            </div>
                        </div>
                        <select id="canvaTemplateFormat" class="bg-indigo-900 border border-indigo-400/50 text-white rounded-lg text-[11px] font-bold py-1.5 px-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="template" selected>⭐ Template Singkron Canva</option>
                            <option value="full">📊 Format Lengkap (Semua Kolom)</option>
                        </select>
                    </div>
                    <p class="text-[10px] text-indigo-200/80 leading-tight">
                        ✨ Langsung singkron otomatis dengan text box Canva: <code class="bg-indigo-900 px-1 py-0.5 rounded text-amber-300">{{Nama}}</code>, <code class="bg-indigo-900 px-1 py-0.5 rounded text-amber-300">{{Juara}}</code>, dan <code class="bg-indigo-900 px-1 py-0.5 rounded text-amber-300">{{Nomor lomba}}</code> (Pemisah titik koma <code class="bg-indigo-900 px-1 rounded text-white">;</code> tanpa tanda kutip).
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- SEKSI 1: SERTIFIKAT PESERTA -->
                    <div class="bg-indigo-950/60 rounded-xl p-4 border border-indigo-500/30 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-black uppercase tracking-wider text-indigo-200 flex items-center gap-1.5">
                                🎖️ Sertifikat Peserta
                            </span>
                            <span class="text-[10px] font-bold text-indigo-300 bg-indigo-800/60 px-2 py-0.5 rounded">Partisipasi</span>
                        </div>
                        <p class="text-[11px] text-indigo-300 mb-3">Seluruh atlet yang bertanding dengan predikat <b>PESERTA</b> (tanpa label diskualifikasi).</p>
                        
                        <div class="flex items-center gap-2 mb-3">
                            <label class="text-[10px] font-black uppercase tracking-wider text-indigo-300 shrink-0">Format:</label>
                            <select id="canvaParticipantScope" class="flex-1 bg-indigo-900/90 border border-indigo-400/40 text-white rounded-lg text-xs font-bold py-1.5 px-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="event">Per Nomor Lomba (Ada Nomor & Waktu)</option>
                                <option value="swimmer">1 Sertifikat per Atlet (Deduplikasi Nama)</option>
                            </select>
                        </div>

                        <button type="button" onclick="downloadExport('canva', 'peserta')" class="w-full bg-white text-indigo-900 py-3 rounded-xl font-black text-xs uppercase tracking-wider hover:bg-indigo-50 transition shadow-md flex items-center justify-center gap-2">
                            <span>📥 UNDUH CSV SERTIFIKAT PESERTA</span>
                        </button>
                    </div>

                    <!-- DIVIDER / PEMISAH ANTAR KEBUTUHAN -->
                    <div class="relative flex items-center justify-center my-2">
                        <div class="border-t border-indigo-400/30 w-full"></div>
                        <span class="bg-indigo-950 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest text-amber-300 border border-amber-400/40 shadow-sm shrink-0">
                            ⚡ ATAU KHUSUS PODIUM JUARA
                        </span>
                        <div class="border-t border-indigo-400/30 w-full"></div>
                    </div>

                    <!-- SEKSI 2: SERTIFIKAT PEMENANG -->
                    <div class="bg-gradient-to-r from-amber-950/70 to-yellow-950/60 rounded-xl p-4 border border-amber-500/40 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-black uppercase tracking-wider text-amber-200 flex items-center gap-1.5">
                                🏆 Sertifikat Pemenang (Juara 1 - 3)
                            </span>
                            <span class="text-[10px] font-black text-amber-950 bg-amber-400 px-2 py-0.5 rounded shadow-sm">Medali</span>
                        </div>
                        <p class="text-[11px] text-amber-200/90 mb-3">Khusus atlet peraih podium juara 1, 2, dan 3 (Label: Juara 1-3 & Medali Emas, Perak, Perunggu).</p>

                        <button type="button" onclick="downloadExport('canva', 'pemenang')" class="w-full bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 text-amber-950 py-3 rounded-xl font-black text-xs uppercase tracking-wider hover:brightness-105 transition shadow-lg flex items-center justify-center gap-2 border border-amber-200/60">
                            <span>🥇 UNDUH CSV SERTIFIKAT PEMENANG</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl pointer-events-none">🖼️</div>
        </div>

        <!-- CARD 2: LAPORAN -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-7 text-white shadow-xl relative overflow-hidden group border-b-4 border-emerald-500 flex flex-col justify-between">
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-2xl font-black flex items-center gap-2 text-white">📄 Laporan Resmi</h3>
                    <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border border-emerald-400/30">Official Book</span>
                </div>
                <p class="text-slate-300 text-xs font-medium mb-5 leading-relaxed">Cetak Buku Hasil Pertandingan dengan struktur formal. Lengkap dengan Cover Depan, Klasemen Medali, dan Hasil Perlombaan.</p>
                
                <!-- PILIH FORMAT BERKAS -->
                <div class="bg-slate-950/50 rounded-xl p-4 border border-slate-700/60 mb-4">
                    <label class="block text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Pilih Format Berkas:</label>
                    <select id="reportFormat" class="w-full bg-slate-800 border border-slate-600 text-white rounded-xl text-sm font-bold focus:ring-emerald-500 py-2.5 px-3">
                        <option value="pdf">📄 PDF (Print Ready / Buku Resmi)</option>
                        <option value="excel">📊 Microsoft Excel (.xls)</option>
                        <option value="csv">📑 CSV (Spreadsheet Mentah)</option>
                    </select>
                </div>

                <!-- SAMPUL / COVER BUKU LAPORAN -->
                <div class="bg-slate-950/50 rounded-xl p-4 border border-slate-700/60 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-black text-slate-300 uppercase tracking-widest">
                            🎨 Sampul / Cover Buku (Halaman 1)
                        </label>
                        <span id="coverStatusBadge" class="<?= !empty($selectedEvent['report_cover']) ? 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30' : 'bg-slate-800 text-slate-400 border-slate-700' ?> text-[9px] font-black uppercase px-2 py-0.5 rounded-full border">
                            <?= !empty($selectedEvent['report_cover']) ? '✓ Cover Aktif' : 'Belum Ada Cover' ?>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">
                        Unggah gambar cover (portrait A4). Cover akan otomatis disatukan di halaman paling depan laporan PDF.
                    </p>

                    <!-- Preview Thumbnail & Action Container -->
                    <div id="coverContainer" class="flex items-start gap-4 <?= !empty($selectedEvent['report_cover']) ? '' : 'hidden' ?> mb-3 p-3 bg-slate-900/80 rounded-xl border border-slate-800">
                        <div class="relative w-16 h-22 rounded-lg overflow-hidden border border-slate-600 shadow-md shrink-0 bg-slate-950 flex items-center justify-center">
                            <img id="coverPreviewImg" src="<?= !empty($selectedEvent['report_cover']) ? rtrim(getenv('APP_URL') ?: '', '/') . '/' . ltrim(str_replace('public/', '', $selectedEvent['report_cover']), '/') : '' ?>" class="w-full h-full object-cover" alt="Preview Cover">
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                            <div>
                                <p class="text-xs font-bold text-white truncate" id="coverFileName"><?= !empty($selectedEvent['report_cover']) ? basename($selectedEvent['report_cover']) : 'Cover Laporan' ?></p>
                                <p class="text-[10px] text-slate-400">Tersimpan untuk event ini</p>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <button type="button" onclick="deleteCover()" id="btnDeleteCover" class="text-[11px] font-black text-rose-400 hover:text-rose-300 transition flex items-center gap-1">
                                    🗑️ Hapus Cover
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Input Upload File -->
                    <div class="space-y-2">
                        <input type="file" id="coverImageInput" name="cover_image" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleCoverFileSelected(event)">
                        
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('coverImageInput').click()" class="flex-1 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 py-2.5 px-3 rounded-xl font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span>📁 <?= !empty($selectedEvent['report_cover']) ? 'Ganti File Cover...' : 'Pilih File Cover...' ?></span>
                            </button>
                            <button type="button" onclick="uploadCoverDirectly()" id="btnSaveCoverAjax" class="hidden bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 px-3 rounded-xl font-bold text-xs uppercase tracking-wider transition shadow-sm items-center justify-center gap-1">
                                <span>💾 Simpan</span>
                            </button>
                        </div>
                        <p class="text-[9px] text-slate-500 italic">*Format: JPG/PNG/WEBP. Cover juga otomatis tersimpan saat Anda mencetak laporan.</p>
                    </div>
                </div>
            </div>
            
            <div class="relative z-10 pt-2">
                <button type="button" onclick="downloadExport('report')" class="w-full bg-emerald-500 text-white py-3.5 px-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-400 transition shadow-md flex items-center justify-center gap-2">
                    <span>📥 CETAK LAPORAN RESMI (PDF / EXCEL / CSV)</span>
                </button>
            </div>
            <div class="absolute right-[-20px] bottom-[-20px] opacity-10 group-hover:scale-110 transition text-8xl pointer-events-none">📊</div>
        </div>

    </div>

<script>
function handleCoverFileSelected(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('coverPreviewImg').src = e.target.result;
        document.getElementById('coverFileName').innerText = file.name;
        document.getElementById('coverContainer').classList.remove('hidden');
        const btnSave = document.getElementById('btnSaveCoverAjax');
        btnSave.classList.remove('hidden');
        btnSave.classList.add('flex');
    };
    reader.readAsDataURL(file);
}

function uploadCoverDirectly() {
    const fileInput = document.getElementById('coverImageInput');
    const eventId = document.getElementById('eventSelect').value;
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Pilih file gambar terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('cover_image', fileInput.files[0]);

    const btn = document.getElementById('btnSaveCoverAjax');
    btn.disabled = true;
    btn.innerHTML = '<span>⏳ Menyimpan...</span>';

    fetch('<?= getenv('APP_URL') ?>/swim/admin/export/upload_cover', {
        method: 'POST',
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error(text.substring(0, 120) || ('Respon server kosong (HTTP ' + r.status + ')'));
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.classList.add('hidden');
        btn.classList.remove('flex');
        btn.innerHTML = '<span>💾 Simpan</span>';
        if (res.success) {
            const badge = document.getElementById('coverStatusBadge');
            badge.className = 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30 text-[9px] font-black uppercase px-2 py-0.5 rounded-full border';
            badge.innerText = '✓ Cover Aktif';
            alert('Cover berhasil disimpan untuk event ini!');
        } else {
            alert('Gagal menyimpan cover: ' + (res.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>💾 Simpan</span>';
        alert('Error: ' + err.message);
    });
}

function deleteCover() {
    if (!confirm('Apakah Anda yakin ingin menghapus cover untuk event ini?')) return;
    const eventId = document.getElementById('eventSelect').value;

    const formData = new FormData();
    formData.append('event_id', eventId);

    fetch('<?= getenv('APP_URL') ?>/swim/admin/export/delete_cover', {
        method: 'POST',
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error(text.substring(0, 120) || ('Respon server kosong (HTTP ' + r.status + ')'));
        }
    })
    .then(res => {
        if (res.success) {
            document.getElementById('coverContainer').classList.add('hidden');
            document.getElementById('coverImageInput').value = '';
            document.getElementById('coverPreviewImg').src = '';
            const btnSave = document.getElementById('btnSaveCoverAjax');
            btnSave.classList.add('hidden');
            btnSave.classList.remove('flex');
            const badge = document.getElementById('coverStatusBadge');
            badge.className = 'bg-slate-800 text-slate-400 border-slate-700 text-[9px] font-black uppercase px-2 py-0.5 rounded-full border';
            badge.innerText = 'Belum Ada Cover';
        } else {
            alert('Gagal menghapus cover: ' + (res.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => alert('Error: ' + err.message));
}

function downloadExport(type, certType = '') {
    const eventId = document.getElementById('eventSelect').value;
    const ku = document.getElementById('kuSelect').value;
    const team = document.getElementById('teamSelect').value;
    const limit = document.getElementById('limitSelect').value;
    const rankMode = document.getElementById('rankSplit').checked ? 'SPLIT' : 'OVERALL';
    
    let baseUrl = type === 'canva' ? '<?= getenv('APP_URL') ?>/swim/admin/export/download_canva' : '<?= getenv('APP_URL') ?>/swim/admin/export/download_report';
    let params = `?event_id=${eventId}&ku=${encodeURIComponent(ku)}&team=${encodeURIComponent(team)}&limit=${limit}&rank_mode=${rankMode}`;
    
    if (type === 'canva') {
        if (certType) {
            params += `&cert_type=${certType}`;
        }
        const templateFormatEl = document.getElementById('canvaTemplateFormat');
        if (templateFormatEl) {
            params += `&template_format=${templateFormatEl.value}`;
        }
        const scopeEl = document.getElementById('canvaParticipantScope');
        if (scopeEl && certType === 'peserta') {
            params += `&scope=${scopeEl.value}`;
        }
        window.open(baseUrl + params, '_blank');
        return;
    }

    // Konfigurasi Checkbox Khusus Laporan
    const format = document.getElementById('reportFormat').value;
    const cfgs = [
        'cfg_event_no', 'cfg_date', 'cfg_event_name', 'cfg_group', 'cfg_gender', 'cfg_pool', 'cfg_round', 'cfg_show_records', 'cfg_medal_tally',
        'col_uid', 'col_lahir', 'col_ku', 'col_tim', 'col_waktu', 'col_hasil', 'col_ket'
    ];

    const fileInput = document.getElementById('coverImageInput');
    const hasNewCover = fileInput && fileInput.files && fileInput.files.length > 0;

    // Gunakan Form POST dinamis dengan target="_blank"
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = baseUrl;
    form.target = '_blank';
    form.enctype = 'multipart/form-data';

    const addField = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };

    addField('event_id', eventId);
    addField('ku', ku);
    addField('team', team);
    addField('limit', limit);
    addField('rank_mode', rankMode);
    addField('format', format);

    cfgs.forEach(id => {
        const el = document.getElementById(id);
        if (el && el.checked) {
            addField(id, '1');
        } else {
            addField(id, '0');
        }
    });

    if (hasNewCover) {
        const parent = fileInput.parentNode;
        const nextSib = fileInput.nextSibling;
        form.appendChild(fileInput);
        document.body.appendChild(form);
        form.submit();
        
        // Kembalikan file input ke tempat semula di DOM
        if (nextSib) {
            parent.insertBefore(fileInput, nextSib);
        } else {
            parent.appendChild(fileInput);
        }
        document.body.removeChild(form);

        const badge = document.getElementById('coverStatusBadge');
        if (badge) {
            badge.className = 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30 text-[9px] font-black uppercase px-2 py-0.5 rounded-full border';
            badge.innerText = '✓ Cover Aktif';
        }
        const btnSave = document.getElementById('btnSaveCoverAjax');
        if (btnSave) {
            btnSave.classList.add('hidden');
            btnSave.classList.remove('flex');
        }
    } else {
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}
</script>