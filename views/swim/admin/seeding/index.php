


    <div class="max-w-7xl mx-auto mb-6 flex flex-col lg:flex-row justify-between items-start gap-6">
        <div>
            <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Seeding & Start List</h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Manajemen Lintasan</p>
        </div>
        
        <?php 
            $total_all_entries = !empty($events) ? array_sum(array_column($events, 'total_athletes')) : 0;
            $globalDisabled = $total_all_entries == 0 ? 'opacity-50 cursor-not-allowed grayscale' : 'hover:-translate-y-1 shadow-xl hover:shadow-2xl';
            $partType = strtolower($targetEvent['participation_type'] ?? 'club');
            $isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
        ?>
        
        <div class="flex flex-wrap items-start gap-3">
            <a href="<?= getenv("APP_URL") ?>/swim/admin/seeding/generateAll?event_id=<?= $targetEventId ?>" onclick="return confirm('⚠️ PERINGATAN:\nFitur ini akan mengacak ulang lintasan untuk SEMUA nomor.\nLanjutkan?')" 
               class="bg-indigo-600 text-white pl-6 pr-8 py-4 rounded-[2rem] transition flex items-center gap-4 group hover:-translate-y-1 shadow-xl shadow-indigo-200 hover:bg-indigo-700 h-[72px]">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-indigo-600 transition">⚡</div>
                <div class="text-left">
                    <span class="block text-[9px] font-bold text-indigo-200 uppercase tracking-widest">System</span>
                    <span class="block font-black text-sm uppercase tracking-wider">Auto Seeding</span>
                </div>
            </a>

            <a href="<?= getenv("APP_URL") ?>/swim/admin/seeding/recapClubs?event_id=<?= $targetEventId ?>" 
               class="bg-fuchsia-600 text-white pl-6 pr-8 py-4 rounded-[2rem] transition flex items-center gap-4 group hover:-translate-y-1 shadow-xl shadow-fuchsia-200 hover:bg-fuchsia-700 h-[72px]">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-fuchsia-600 transition">📋</div>
                <div class="text-left">
                    <span class="block text-[9px] font-bold text-fuchsia-200 uppercase tracking-widest">Starting List</span>
                    <span class="block font-black text-sm uppercase tracking-wider">Recap Klub</span>
                </div>
            </a>

            <button type="button" onclick="if(<?= $total_all_entries ?> > 0) { document.getElementById('configForm').submit(); }" class="bg-emerald-600 text-white pl-6 pr-8 py-4 rounded-[2rem] transition flex items-center gap-4 group <?= $globalDisabled ?> h-[72px]">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-emerald-600 transition">🖨️</div>
                <div class="text-left">
                    <span class="block text-[9px] font-bold text-emerald-200 uppercase tracking-widest">Final Book</span>
                    <span class="block font-black text-sm uppercase tracking-wider">Cetak Buku</span>
                </div>
            </button>
        </div>
    </div>

    <?php if($targetEventId == 0): ?>
        <div class="max-w-7xl mx-auto flex flex-col items-center justify-center py-20 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">⚠️</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Anda Belum Memiliki Event Aktif</h3>
        </div>
    <?php else: ?>

        <form id="configForm" action="<?= getenv("APP_URL") ?>/swim/admin/seeding/printFull" method="POST" enctype="multipart/form-data" target="_blank" class="max-w-7xl mx-auto mb-6 bg-white rounded-[2rem] border border-slate-200 shadow-sm p-6 sm:p-8">
            <input type="hidden" name="event_id" value="<?= $targetEventId ?>">
            
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                <span class="text-xl">⚙️</span>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Panel Pengaturan Buku Acara & Kolam Lintasan</h3>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">1. Upload Cover (Opsional)</label>
                        <input type="file" name="cover_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">2. Gambar Jadwal Acara (Opsional)</label>
                        <input type="file" name="schedule_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                    </div>
                    <div class="bg-indigo-50/70 px-3 py-2 rounded-xl border border-indigo-100/70 flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer select-none w-full">
                            <input type="checkbox" name="show_schedule_auto" class="w-4 h-4 rounded text-indigo-600 focus:ring-0 border-indigo-300 config-cb" checked>
                            <div>
                                <span class="block text-[10px] font-black text-indigo-900 uppercase leading-tight">Generate Jadwal Otomatis DB</span>
                                <span class="block text-[8px] text-indigo-400 font-bold">List standar tanpa jeda istirahat</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="border-t lg:border-t-0 lg:border-x border-slate-100 lg:px-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">3. Komponen Judul Acara:</label>
                    <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs font-bold text-slate-600">
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_event_no" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Nomor Acara</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_date" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Tanggal & Jam</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_event_name" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Jarak & Gaya</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_group" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Kelompok Umur</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_gender" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Jenis Kelamin</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_pool" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Tipe Kolam</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="cfg_round" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Babak (FINAL)</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none text-amber-700 font-extrabold"><input type="checkbox" name="cfg_show_records" value="1" class="rounded border-amber-300 text-amber-600 config-cb" checked> Tampilkan Rekor NAS</label>
                        <label class="flex items-center gap-2 cursor-pointer select-none text-indigo-700 font-extrabold"><input type="checkbox" name="cfg_show_event_records" value="1" class="rounded border-indigo-300 text-indigo-600 config-cb" checked> Tampilkan Rekor Event</label>
                    </div>
                </div>

                <div class="flex flex-col justify-between">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">4. Kolom Tabel Atlet:</label>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs font-bold text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_uid" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Kolom UID</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_lahir" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Tahun Lahir</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_ku" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Kolom KU</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_klub" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Nama Klub</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_sekolah" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Nama Sekolah</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_waktu" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Waktu Entry</label>
                            <label class="flex items-center gap-2 cursor-pointer select-none"><input type="checkbox" name="col_hasil" value="1" class="rounded border-slate-300 text-blue-600 config-cb" checked> Lembar Titik</label>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider rounded-xl transition shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            🖨️ PRINT FULL MEET BOOK
                        </button>
                    </div>
                </div>

            </div>
            
            <div class="mt-4 text-[10px] text-slate-400 font-bold bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">
                💡 <b>Tips Multi-Fungsi:</b> Setiap kali Anda mencentang/melepas pilihan di atas, tombol <b>"View"</b> pada list lomba di bawah akan otomatis menyesuaikan konfigurasinya secara langsung.
            </div>
        </form>

        <div class="max-w-7xl mx-auto mb-6 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><span class="text-xl grayscale opacity-40">🔍</span></div>
            <input type="text" id="searchInput" class="w-full pl-12 pr-4 py-4 rounded-2xl border-none ring-1 ring-slate-200 shadow-sm focus:ring-2 focus:ring-blue-500 font-bold text-slate-600 placeholder:text-slate-300 placeholder:font-bold transition" placeholder="Cari Nomor Acara...">
        </div>

        <div class="max-w-7xl mx-auto space-y-4 pb-20" id="eventContainer">
            <?php if($error_msg): ?><div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200 mb-6 font-bold"><?= $error_msg ?></div><?php endif; ?>
            <?php if(!empty($events)): ?>
                <?php foreach($events as $ev): 
                    $isRelay = ($ev['is_relay'] == 1);
                    $distDisplay = \App\Helpers\SwimEventHelper::formatDistance($ev['distance'] ?? 0, $isRelay);
                    $dispEventName = \App\Helpers\SwimEventHelper::formatEventName($ev['event_name'] ?? 'Nomor Lomba', $isRelay, $ev['distance'] ?? 0);
                    $isReady = $ev['total_athletes'] > 0;
                    $cardOpacity = $isReady ? 'opacity-100' : 'opacity-60 grayscale';
                    $genderCode = strtoupper($ev['jenis_kelamin'] ?? 'L'); 
                    if(in_array($genderCode, ['L', 'MALE', 'PUTRA'])) { $bg='bg-blue-50'; $txt='text-blue-600'; $icon='👨'; $lbl='PUTRA'; } 
                    elseif(in_array($genderCode, ['P', 'FEMALE', 'PUTRI'])) { $bg='bg-pink-50'; $txt='text-pink-600'; $icon='👩'; $lbl='PUTRI'; } 
                    else { $bg='bg-purple-50'; $txt='text-purple-600'; $icon='👫'; $lbl='MIXED'; }
                    $searchString = strtolower(($ev['event_number'] ?? '') . " " . $dispEventName . " " . $distDisplay);
                ?>
                <div class="event-item group relative rounded-[2rem] p-5 border border-slate-200 bg-white transition flex flex-col md:flex-row items-center gap-6 <?= $cardOpacity ?>" data-search="<?= $searchString ?>">
                    <div class="shrink-0 w-20 h-20 rounded-3xl bg-slate-900 text-white flex flex-col items-center justify-center shadow-lg">
                        <span class="text-[9px] font-bold text-slate-400 uppercase">Event</span>
                        <span class="text-3xl font-black italic"><?= htmlspecialchars($ev['event_number'] ?? '-') ?></span>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <div class="inline-flex items-center gap-2 mb-1">
                            <span class="px-2 py-1 rounded-md <?= $bg ?> <?= $txt ?> text-[9px] font-black uppercase tracking-widest border border-slate-100"><?= $icon ?> <?= $lbl ?></span>
                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md uppercase border border-slate-200"><?= htmlspecialchars($distDisplay) ?> <?= htmlspecialchars($ev['stroke'] ?? '-') ?> <?= $isRelay ? '(ESTAFET)' : '' ?></span>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md uppercase border border-emerald-200">👥 <?= $ev['total_athletes'] ?> <?= $isRelay ? 'Tim (' . ($ev['total_athletes'] * 4) . ' Atlet)' : 'Atlet' ?></span>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tight"><?= htmlspecialchars($dispEventName) ?></h3>
                    </div>
                    <div class="flex flex-wrap gap-2 w-full md:w-auto items-center justify-end">
                        <?php if($isRelay): ?>
                            <button type="button" onclick="openRelayBlankoModal(<?= $ev['id'] ?>)" class="px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs uppercase shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                                <span>📋</span> Blanko Estafet
                            </button>
                        <?php endif; ?>
                        <?php if($isReady): ?>
                            <a href="<?= getenv("APP_URL") ?>/swim/admin/seeding/viewStartlist?category_id=<?= $ev['id'] ?>" data-base-url="<?= getenv("APP_URL") ?>/swim/admin/seeding/viewStartlist?category_id=<?= $ev['id'] ?>" class="view-startlist-btn px-6 py-3 bg-white hover:bg-slate-50 text-slate-600 rounded-xl font-bold text-xs uppercase border border-slate-200 text-center">View</a>
                            
                            <a href="<?= getenv("APP_URL") ?>/swim/admin/seeding/generateSingle?category_id=<?= $ev['id'] ?>" class="px-6 py-3 bg-slate-900 hover:bg-blue-600 text-white rounded-xl font-bold text-xs uppercase text-center" onclick="return confirm('Seeding ulang nomor ini?')">Generate</a>
                        <?php else: ?>
                            <button disabled class="px-6 py-3 bg-slate-100 text-slate-400 rounded-xl font-bold text-xs uppercase border cursor-not-allowed">Empty</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-20">
                    <p class="text-slate-400 font-bold italic">Belum ada nomor lomba untuk event ini.</p>
                </div>
            <?php endif; ?>
        </div>
<?php endif; ?>

<!-- MODAL BLANKO SUSUNAN ATLET ESTAFET -->
<div id="relayBlankoModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 items-center justify-center">
    <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xl">📋</span>
                    <h3 class="text-base font-black uppercase italic tracking-tight" id="blankoModalTitle">Blanko Susunan Atlet Estafet</h3>
                </div>
                <p class="text-xs text-slate-300 font-semibold mt-0.5" id="blankoModalSubtitle">Memuat informasi nomor acara...</p>
            </div>
            <div class="flex items-center gap-2">
                <a id="blankoPrintBtn" href="#" target="_blank" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black text-xs uppercase shadow transition flex items-center gap-1.5">
                    <span>🖨️</span> Cetak Blanko
                </a>
                <button type="button" onclick="closeRelayBlankoModal()" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition text-lg font-bold">
                    ✕
                </button>
            </div>
        </div>

        <!-- Limit Info Banner -->
        <div id="blankoLimitBanner" class="px-6 py-2.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between text-xs font-bold text-amber-900">
            <div class="flex items-center gap-2">
                <span>🎂</span>
                <span id="blankoLimitText">Batas Maksimal Total Umur Estafet: -</span>
            </div>
            <span class="text-[11px] text-amber-700 font-medium">Akumulasi umur seluruh atlet dalam satu regu</span>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6 bg-slate-50/60" id="blankoModalBody">
            <div id="blankoLoading" class="py-16 text-center text-slate-400">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-300 border-t-blue-600 mb-2"></div>
                <p class="text-xs font-bold">Mengambil data tim estafet...</p>
            </div>
            <div id="blankoTeamsContainer" class="space-y-6 hidden">
                <!-- Teams rendered dynamically via JS -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 bg-white border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 font-bold">
            <span>💡 Perubahan langsung tersimpan ke database & terintegrasi dengan lembar hasil</span>
            <button type="button" onclick="closeRelayBlankoModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl transition font-bold text-xs uppercase">
                Tutup
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentRelayData = null;

    function openRelayBlankoModal(catId) {
        const modal = document.getElementById('relayBlankoModal');
        const title = document.getElementById('blankoModalTitle');
        const sub = document.getElementById('blankoModalSubtitle');
        const printBtn = document.getElementById('blankoPrintBtn');
        const limitText = document.getElementById('blankoLimitText');
        const loading = document.getElementById('blankoLoading');
        const container = document.getElementById('blankoTeamsContainer');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        container.classList.add('hidden');
        container.innerHTML = '';

        printBtn.href = '<?= getenv("APP_URL") ?>/swim/admin/seeding/print_blanko?category_id=' + catId;

        fetch('<?= getenv("APP_URL") ?>/swim/admin/seeding/get_relay_teams?category_id=' + catId)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                container.classList.remove('hidden');

                if (!data.success) {
                    container.innerHTML = '<div class="p-6 text-center text-red-600 font-bold bg-red-50 rounded-2xl border border-red-200">' + (data.message || 'Gagal memuat data') + '</div>';
                    return;
                }

                currentRelayData = data;
                const cat = data.category;
                title.textContent = 'BLANKO ESTAFET - ACARA #' + (cat.event_number || '') + ' (' + (cat.distance || '') + ' ' + (cat.stroke || '') + ')';
                sub.textContent = (cat.event_name || '') + ' • ' + (cat.age_group || '') + ' ' + (cat.jenis_kelamin || '');

                const maxAge = parseInt(data.max_total_age) || 0;
                if (maxAge > 0) {
                    limitText.innerHTML = 'Batas Maksimal Total Umur: <span class="text-amber-900 font-black text-sm">' + maxAge + ' Tahun</span> (Akumulasi 4 atlet harus ≤ ' + maxAge + ' Th)';
                } else {
                    limitText.innerHTML = 'Batas Maksimal Total Umur: <span class="text-blue-900 font-black text-sm">Bebas (Tanpa Batas Limit)</span>';
                }

                if (!data.teams || data.teams.length === 0) {
                    container.innerHTML = '<div class="p-8 text-center text-slate-400 font-bold bg-white rounded-2xl border border-slate-200 italic">Belum ada tim estafet yang terdaftar pada nomor acara ini.</div>';
                    return;
                }

                renderRelayTeams(data.teams, maxAge, cat.stroke || '');
            })
            .catch(err => {
                loading.classList.add('hidden');
                container.classList.remove('hidden');
                container.innerHTML = '<div class="p-6 text-center text-red-600 font-bold bg-red-50 rounded-2xl border border-red-200">Terjadi kesalahan koneksi: ' + err.message + '</div>';
            });
    }

    function closeRelayBlankoModal() {
        const modal = document.getElementById('relayBlankoModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function renderRelayTeams(teams, maxAge, strokeName) {
        const container = document.getElementById('blankoTeamsContainer');
        container.innerHTML = '';

        const isMedley = /ganti|medley/i.test(strokeName);
        const legLabels = {
            1: isMedley ? 'Leg 1 (Punggung)' : 'Perenang 1',
            2: isMedley ? 'Leg 2 (Dada)' : 'Perenang 2',
            3: isMedley ? 'Leg 3 (Kupu-kupu)' : 'Perenang 3',
            4: isMedley ? 'Leg 4 (Bebas)' : 'Perenang 4'
        };

        teams.forEach(team => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4';
            card.id = 'team-card-' + team.relay_id;

            // Card Header
            let headerHtml = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-black text-slate-900 uppercase">${escapeHtml(team.team_name)}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 uppercase">${escapeHtml(team.club_name)}</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Seed Time: <b>${escapeHtml(team.seed_time || 'NT')}</b></span>
                    </div>
                    <div id="team-age-badge-${team.relay_id}">
                        ${renderAgeBadge(team.total_age, maxAge, team.is_valid_age)}
                    </div>
                </div>
            `;

            // 4 Swimmer Dropdowns
            let legsHtml = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">';
            for (let leg = 1; leg <= 4; leg++) {
                const curSw = team.swimmers[leg] || {};
                const curId = curSw.id || 0;
                const curAge = curSw.age || 0;

                legsHtml += `
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1 flex items-center justify-between">
                            <span>${legLabels[leg]}</span>
                            <span id="leg-age-${team.relay_id}-${leg}" class="text-[10px] font-bold text-indigo-600">${curAge > 0 ? curAge + ' Th' : '-'}</span>
                        </label>
                        <select id="swimmer-${team.relay_id}-${leg}" class="swimmer-select w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none" onchange="onSwimmerChange(${team.relay_id}, ${maxAge})">
                            <option value="" data-age="0">-- Pilih Atlet --</option>
                `;

                if (team.available_swimmers && team.available_swimmers.length > 0) {
                    team.available_swimmers.forEach(sw => {
                        const sel = (sw.id == curId) ? 'selected' : '';
                        legsHtml += `<option value="${sw.id}" data-age="${sw.age}" data-dob="${sw.tanggal_lahir || ''}" ${sel}>${escapeHtml(sw.nama_atlet)} (${sw.age} Th - ${sw.jenis_kelamin || ''})</option>`;
                    });
                }

                legsHtml += `
                        </select>
                    </div>
                `;
            }
            legsHtml += '</div>';

            // Card Footer with Save button & Status text
            let footerHtml = `
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div id="team-status-msg-${team.relay_id}" class="text-[11px] font-bold text-slate-500">
                        Pilih 4 perenang untuk regu ini
                    </div>
                    <button type="button" onclick="saveRelayTeamSwimmers(${team.relay_id}, ${maxAge})" id="btn-save-${team.relay_id}" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-xs uppercase shadow transition flex items-center justify-center gap-1.5">
                        <span>💾</span> Simpan Susunan Atlet
                    </button>
                </div>
            `;

            card.innerHTML = headerHtml + legsHtml + footerHtml;
            container.appendChild(card);
        });
    }

    function renderAgeBadge(totalAge, maxAge, isValid) {
        if (maxAge > 0) {
            if (isValid) {
                return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <span>✅</span> Total Umur: ${totalAge} Th <span class="text-[10px] font-bold text-emerald-600">(≤ ${maxAge} Th)</span>
                </span>`;
            } else {
                return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-red-100 text-red-700 border border-red-300 animate-pulse">
                    <span>⚠️</span> Total Umur: ${totalAge} Th <span class="text-[10px] font-bold text-red-600">(LEBIH DARI ${maxAge} Th!)</span>
                </span>`;
            }
        }
        return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black bg-slate-100 text-slate-700 border border-slate-200">
            <span>🎂</span> Total Umur: ${totalAge > 0 ? totalAge + ' Th' : '-'} (Bebas)
        </span>`;
    }

    function onSwimmerChange(relayId, maxAge) {
        let total = 0;
        let selectedIds = [];
        let hasDuplicate = false;

        for (let leg = 1; leg <= 4; leg++) {
            const sel = document.getElementById(`swimmer-${relayId}-${leg}`);
            const ageSpan = document.getElementById(`leg-age-${relayId}-${leg}`);
            if (sel) {
                const opt = sel.options[sel.selectedIndex];
                const age = parseInt(opt ? opt.getAttribute('data-age') : 0) || 0;
                const id = parseInt(sel.value) || 0;

                if (ageSpan) {
                    ageSpan.textContent = age > 0 ? age + ' Th' : '-';
                }

                if (id > 0) {
                    if (selectedIds.includes(id)) {
                        hasDuplicate = true;
                    }
                    selectedIds.push(id);
                    total += age;
                }
            }
        }

        const isValid = (maxAge <= 0) || (total <= maxAge);
        const badgeContainer = document.getElementById(`team-age-badge-${relayId}`);
        if (badgeContainer) {
            badgeContainer.innerHTML = renderAgeBadge(total, maxAge, isValid);
        }

        const statusMsg = document.getElementById(`team-status-msg-${relayId}`);
        const btnSave = document.getElementById(`btn-save-${relayId}`);
        if (hasDuplicate) {
            if (statusMsg) {
                statusMsg.innerHTML = '<span class="text-red-600 font-bold">⚠️ Perenang tidak boleh dipilih ganda dalam tim yang sama!</span>';
            }
            if (btnSave) btnSave.disabled = true;
        } else {
            if (statusMsg) {
                if (maxAge > 0 && !isValid) {
                    statusMsg.innerHTML = `<span class="text-amber-700 font-bold">⚠️ Total umur (${total} Th) melebihi batas maksimal (${maxAge} Th)</span>`;
                } else if (selectedIds.length === 4) {
                    statusMsg.innerHTML = '<span class="text-emerald-700 font-bold">✓ Susunan 4 perenang lengkap</span>';
                } else {
                    statusMsg.innerHTML = `<span class="text-slate-500 font-bold">${selectedIds.length} dari 4 perenang dipilih</span>`;
                }
            }
            if (btnSave) btnSave.disabled = false;
        }
    }

    function saveRelayTeamSwimmers(relayId, maxAge) {
        const s1 = document.getElementById(`swimmer-${relayId}-1`).value;
        const s2 = document.getElementById(`swimmer-${relayId}-2`).value;
        const s3 = document.getElementById(`swimmer-${relayId}-3`).value;
        const s4 = document.getElementById(`swimmer-${relayId}-4`).value;

        const btnSave = document.getElementById(`btn-save-${relayId}`);
        const originalText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<span>⏳</span> Menyimpan...';

        const formData = new FormData();
        formData.append('relay_id', relayId);
        formData.append('swimmer_1_id', s1);
        formData.append('swimmer_2_id', s2);
        formData.append('swimmer_3_id', s3);
        formData.append('swimmer_4_id', s4);

        fetch('<?= getenv("APP_URL") ?>/swim/admin/seeding/save_relay_swimmers', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;

            if (res.success) {
                const badgeContainer = document.getElementById(`team-age-badge-${relayId}`);
                if (badgeContainer) {
                    badgeContainer.innerHTML = renderAgeBadge(res.total_age, maxAge, res.is_valid_age);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan!',
                    text: res.message + (maxAge > 0 ? (res.is_valid_age ? ' (Total Umur: ' + res.total_age + ' Th)' : ' (⚠️ Total umur ' + res.total_age + ' Th melebihi batas ' + maxAge + ' Th!)') : ''),
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: res.message || 'Terjadi kesalahan sistem'
                });
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = originalText;
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Terputus',
                text: err.message
            });
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>

<script>
    /**
     * 🧠 SCRIPT PINTAR INJEKSI PARAMETER
     * Mengambil seluruh status checkbox di panel atas, lalu menyuntikkannya ke URL tombol "View" secara real-time.
     */
    function updateDynamicViewLinks() {
        const form = document.getElementById('configForm');
        if (!form) return;
        
        const checkboxes = form.querySelectorAll('.config-cb');
        let queryParts = [];
        
        checkboxes.forEach(cb => {
            if (cb.checked) {
                queryParts.push(encodeURIComponent(cb.name) + '=1');
            }
        });
        
        const queryString = queryParts.join('&');
        const viewButtons = document.querySelectorAll('.view-startlist-btn');
        
        viewButtons.forEach(btn => {
            const baseUrl = btn.getAttribute('data-base-url');
            btn.href = baseUrl + (queryString ? '&' + queryString : '');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Logika Live Search Nomor Acara
        const searchInput = document.getElementById('searchInput');
        const items = document.querySelectorAll('.event-item');
        if(searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const term = e.target.value.toLowerCase();
                items.forEach(item => {
                    const searchData = item.getAttribute('data-search');
                    item.style.display = (searchData && searchData.includes(term)) ? "" : "none"; 
                });
            });
        }

        // 2. Pasang Event Listener ke seluruh checkbox di Panel Konfigurasi
        const form = document.getElementById('configForm');
        if (form) {
            form.querySelectorAll('.config-cb').forEach(cb => {
                cb.addEventListener('change', updateDynamicViewLinks);
            });
            // Jalankan sekali di awal load agar link langsung siap bawaan default checked
            updateDynamicViewLinks();
        }
    });
</script>