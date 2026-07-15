<div class="max-w-6xl mx-auto">
    <a href="<?= getenv('APP_URL') ?>/swim/pengumuman" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition mb-6">
        &larr; Kembali ke Pusat Informasi
    </a>

    <div class="bg-slate-900 text-white p-8 rounded-3xl shadow-xl mb-8 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 text-9xl opacity-10">🏆</div>
        <div class="relative z-10">
            <span class="inline-block px-3 py-1 bg-blue-600 text-white text-[9px] font-black uppercase tracking-widest rounded-lg mb-3">Live Result Digital</span>
            <h1 class="text-3xl font-black uppercase italic leading-tight mb-2"><?= htmlspecialchars($event['event_name']) ?></h1>
            <p class="text-xs text-slate-300 font-bold uppercase tracking-widest">
                📍 <?= htmlspecialchars($event['event_location']) ?> | 📅 <?= date('d F Y', strtotime($event['event_date_start'])) ?>
            </p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-8 flex items-center gap-4">
        <span class="text-2xl ml-2">🔍</span>
        <input type="text" id="searchInput" placeholder="Cari nama atlet atau tim di sini..." class="w-full bg-transparent border-none focus:ring-0 text-sm font-bold text-slate-700 uppercase placeholder:text-slate-300 placeholder:normal-case outline-none">
    </div>

    <?php if (empty($groupedResults)): ?>
        <div class="bg-white p-12 text-center rounded-3xl border border-slate-200 border-dashed">
            <span class="text-4xl block mb-3 opacity-30">📭</span>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada hasil perlombaan yang diterbitkan.</p>
            <p class="text-[10px] text-slate-400 mt-2">Silakan tunggu panitia memperbarui data atau mengaktifkan saklar Live Result.</p>
        </div>
    <?php else: ?>
        <div id="resultContainer" class="space-y-6 pb-12">
            <?php foreach ($groupedResults as $judul => $atletList): ?>
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden result-card">
                    <div class="bg-slate-100 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-sm font-black text-slate-800 uppercase italic"><?= $judul ?></h2>
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase tracking-widest animate-pulse">🔴 LIVE</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                                    <th class="py-3 px-4 w-12 text-center">Rank</th>
                                    <th class="py-3 px-4">Nama Atlet</th>
                                    <th class="py-3 px-4 text-center w-20">KU</th>
                                    <th class="py-3 px-4"><?= $teamHeaderLabel ?></th>
                                    <th class="py-3 px-4 text-center w-28">Wkt. Prestasi</th>
                                    <th class="py-3 px-4 text-right w-28">Wkt. Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($atletList as $atlet): 
                                    $isMyTeam = ($atlet['swimmer_owner_id'] == $user_id);
                                    $isDQ = ($atlet['is_dq_final'] == 1);
                                    
                                    $rowClass = 'border-b border-slate-100 hover:bg-slate-50 transition-colors';
                                    
                                    if ($isSchoolEvent) {
                                        $displayTeam = !empty($atlet['asal_sekolah']) ? $atlet['asal_sekolah'] : '-';
                                    } else {
                                        $displayTeam = !empty($atlet['nama_klub']) ? $atlet['nama_klub'] : '-';
                                    }

                                    $rankBadge = '-';
                                    $rankClass = 'text-slate-600';
                                    if ($atlet['dynamic_rank'] !== null) {
                                        $rankBadge = $atlet['dynamic_rank'];
                                        if($rankBadge == 1) { $rankBadge = '🥇 1'; $rankClass = 'text-amber-500'; }
                                        if($rankBadge == 2) { $rankBadge = '🥈 2'; $rankClass = 'text-slate-400'; }
                                        if($rankBadge == 3) { $rankBadge = '🥉 3'; $rankClass = 'text-orange-500'; }
                                    }

                                    $waktuDaftar = $atlet['entry_time'];
                                    if (empty($waktuDaftar) || $waktuDaftar === '00:00.00' || $waktuDaftar === '00:00:00') {
                                        $waktuDaftar = 'NT';
                                    }
                                ?>
                                <tr class="searchable-row <?= $rowClass ?>">
                                    
                                    <td class="p-4 text-center font-bold <?= $rankClass ?>">
                                        <?= $rankBadge ?>
                                    </td>
                                    
                                    <td class="p-4">
                                        <div class="font-extrabold text-slate-800 athlete-name <?= $isMyTeam ? 'text-yellow-600' : '' ?>"><?= htmlspecialchars($atlet['nama_atlet']) ?></div>
                                    </td>
                                    
                                    <td class="p-4 text-center font-bold text-slate-500 text-xs">
                                        <?php 
                                            if (stripos($atlet['age_group'], 'GABUNG') !== false) {
                                                echo htmlspecialchars(getAgeGroupLabel($atlet['tanggal_lahir'], $eventYear, $ageGroups));
                                            } else {
                                                echo htmlspecialchars($atlet['age_group']);
                                            }
                                        ?>
                                    </td>
                                    
                                    <td class="p-4 text-sm font-bold text-slate-600 team-name">
                                        <?= htmlspecialchars($displayTeam) ?>
                                    </td>
                                    
                                    <td class="p-4 text-center font-mono text-xs text-slate-400 font-bold">
                                        <?= htmlspecialchars($waktuDaftar) ?>
                                    </td>
                                    
                                    <td class="py-3 px-4 text-right font-mono text-sm font-black text-slate-800">
                                        <?php if($isDQ): 
                                            $reason = $atlet['dq_reason_final'] ?? 'DQ';
                                            if (in_array($reason, ['DNS', 'DNF'])):
                                        ?>
                                            <span class="bg-slate-100 text-slate-500 border border-slate-300 px-2 py-1 rounded text-[10px] uppercase font-sans tracking-wider">
                                                <?= htmlspecialchars($reason) ?>
                                            </span>
                                        <?php else: ?>
                                            <button type="button" onclick="showDqDetail('<?= htmlspecialchars($reason) ?>')" class="bg-red-50 text-red-600 border border-red-300 px-2 py-1 rounded text-[10px] uppercase hover:bg-red-100 transition inline-flex items-center justify-end gap-1 ml-auto cursor-pointer shadow-sm animate-pulse font-sans tracking-wider outline-none">
                                                ⚠️ DQ
                                            </button>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($atlet['time_final'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>
                                    
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 🌟 FASE 4: SWEETALERT & FUNGSI POP-UP -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const dqRulesData = <?= $dqRulesJson ?>;

function showDqDetail(pasal) {
    let deskripsi = dqRulesData[pasal] || "Penjelasan detail untuk pasal ini belum tersedia di sistem.";
    
    Swal.fire({
        title: '<span class="text-red-600 font-black italic">DISKUALIFIKASI!</span>',
        html: `
            <div class="text-left mt-2 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="mb-2">
                    <span class="bg-red-100 border border-red-300 text-red-700 font-black px-2 py-1 rounded text-xs">
                        ${pasal}
                    </span>
                </div>
                <p class="text-slate-700 text-sm font-medium leading-relaxed font-sans">
                    ${deskripsi}
                </p>
            </div>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#3b82f6',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg font-bold px-6'
        }
    });
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let cards = document.querySelectorAll('.result-card');

    cards.forEach(card => {
        let rows = card.querySelectorAll('.searchable-row');
        let cardHasVisibleRow = false;

        rows.forEach(row => {
            let athleteName = row.querySelector('.athlete-name').textContent.toLowerCase();
            let teamName = row.querySelector('.team-name').textContent.toLowerCase();

            if (athleteName.includes(filter) || teamName.includes(filter)) {
                row.style.display = '';
                cardHasVisibleRow = true;
            } else {
                row.style.display = 'none';
            }
        });
        card.style.display = cardHasVisibleRow ? '' : 'none';
    });
});
</script>
