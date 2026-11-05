<?php if(isset($_SESSION['success'])): ?>
    <div class="max-w-5xl mx-auto mb-4 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
        <span>✅</span> <strong><?= $_SESSION['success'] ?></strong>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="max-w-5xl mx-auto mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
        <span>❌</span> <strong><?= $_SESSION['error'] ?></strong>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-200 pb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="<?= getenv('APP_URL') ?>/swim/admin/results<?= !empty($eventId) ? '?event_id=' . $eventId : '' ?>" class="text-xs font-bold text-slate-400 hover:text-blue-600 transition flex items-center gap-1">
                    &larr; Input Hasil
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-bold text-slate-600">Publikasi</span>
            </div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Publikasi Live Result</h1>
            <p class="text-sm text-slate-500 font-bold mt-1">Kelola dokumen dan Live Result untuk pengguna.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full lg:w-auto">
            <div class="w-full sm:w-64">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Pilih Event Aktif:</label>
                <select id="eventSelector" onchange="window.location.href='<?= getenv('APP_URL') ?>/swim/admin/results/publish?event_id='+this.value" class="w-full bg-white border border-slate-300 text-slate-700 text-xs font-bold rounded-xl px-4 py-2.5 shadow-sm cursor-pointer transition focus:border-blue-500 focus:outline-none">
                    <?php foreach($myEvents as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= ($ev['id'] == $eventId) ? 'selected' : '' ?>><?= htmlspecialchars($ev['event_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($eventId > 0): ?>
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 shadow-sm flex items-center justify-between gap-4 w-full sm:w-auto h-[42px] mt-auto">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Publikasikan Hasil?</span>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" onchange="toggleEventPublish(this, <?= $eventId ?>)" <?= ($currentEvent && $currentEvent['is_result_published'] == 1) ? 'checked' : '' ?>>
                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                </label>
            </div>

            <a href="<?= getenv('APP_URL') ?>/swim/live_result/index/<?= $eventId ?>" target="_blank" class="h-[42px] px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm flex items-center gap-1.5 transition mt-auto">
                <span>🌐</span> Live Web ↗
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($eventId > 0): ?>
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 mb-8">
        <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-4">📥 Unggah Dokumen Perlombaan</h2>
        <form action="<?= getenv('APP_URL') ?>/swim/admin/results/publish" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row items-end gap-4">
            <input type="hidden" name="event_id" value="<?= $eventId ?>">
            <input type="hidden" name="upload_doc" value="1">
            
            <div class="w-full md:w-1/4">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori</label>
                <select name="kategori" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-3">
                    <option value="buku_acara">Buku Acara (Startlist)</option>
                    <option value="buku_hasil">Buku Hasil (Result)</option>
                </select>
            </div>
            
            <div class="w-full md:w-1/3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Judul Tampilan</label>
                <input type="text" name="judul_file" placeholder="Cth: Startlist Hari 1" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-4 py-3">
            </div>

            <div class="w-full md:w-1/3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">File (PDF)</label>
                <input type="file" name="dokumen" accept=".pdf" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-2.5 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
            </div>

            <div class="w-full md:w-auto">
                <button type="submit" class="w-full bg-slate-900 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition shadow-lg">Upload</button>
            </div>
        </form>

        <?php if (!empty($documents)): ?>
        <div class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-3">📄 Dokumen Yang Terpasang di Live Web:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($documents as $doc): ?>
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider <?= $doc['kategori'] === 'buku_acara' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?> mb-1">
                            <?= $doc['kategori'] === 'buku_acara' ? 'Buku Acara' : 'Buku Hasil' ?>
                        </span>
                        <div class="text-xs font-bold text-slate-800"><?= htmlspecialchars($doc['judul_file']) ?></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?= getenv('APP_URL') . '/' . ltrim($doc['file_path'], '/') ?>" target="_blank" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-slate-700 text-[10px] font-black hover:bg-slate-100 transition shadow-sm">
                            Buka
                        </a>
                        <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/results/publish" onsubmit="return confirm('Hapus dokumen ini?');">
                            <input type="hidden" name="event_id" value="<?= $eventId ?>">
                            <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                            <input type="hidden" name="delete_doc" value="1">
                            <button type="submit" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-[10px] font-black border border-red-200 transition">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if(empty($raceList)): ?>
        <div class="bg-white p-12 text-center rounded-[2rem] shadow-sm border border-slate-200 border-dashed">
            <span class="text-5xl block mb-4 opacity-50">📋</span>
            <h3 class="text-lg font-black text-slate-700 uppercase italic mb-2">Belum Ada Nomor Acara</h3>
            <p class="text-sm text-slate-500 font-medium">Buat nomor lomba terlebih dahulu di menu "Pengaturan Event".</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 w-20 text-center">No.</th>
                        <th class="px-6 py-4">Nomor Lomba</th>
                        <th class="px-6 py-4 text-center w-32">Status Data</th>
                        <th class="px-6 py-4 text-center w-48">Mode Ranking</th>
                        <th class="px-6 py-4 text-center w-36">Live Result?</th>
                        <th class="px-6 py-4 text-right w-36">Aksi A4</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($raceList as $race): 
                        $hasResult = ($race['count_results'] > 0);
                        $isPublished = ($race['is_published'] == 1);
                    ?>
                    <?php
                        $isRelayRace = ($race['is_relay'] == 1);
                        $distLabel = \App\Helpers\SwimEventHelper::formatDistance($race['distance'] ?? 0, $isRelayRace);
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center font-black text-slate-400 text-lg">#<?= htmlspecialchars($race['event_number']) ?></td>
                        <td class="px-6 py-4">
                            <a href="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $race['id'] ?>" class="font-black text-slate-800 hover:text-blue-600 transition uppercase text-sm leading-tight mb-1 block">
                                <?= htmlspecialchars($distLabel) ?> <?= htmlspecialchars($race['stroke']) ?> <?= $isRelayRace ? '(ESTAFET)' : '' ?>
                            </a>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                <?= htmlspecialchars($race['age_group'] ?? 'OPEN') ?> • <?= strtoupper($race['jenis_kelamin'] ?? '') === 'L' ? 'PUTRA' : 'PUTRI' ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($hasResult): ?>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase tracking-widest">✅ Siap (<?= $race['count_results'] ?>)</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase tracking-widest">⏳ Kosong</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <select onchange="updateRankMode(<?= $race['id'] ?>, this.value)" class="text-[10px] font-black border border-slate-200 rounded-lg px-2.5 py-1.5 bg-slate-50 text-slate-700 cursor-pointer focus:border-blue-500 focus:outline-none uppercase tracking-wider">
                                <option value="split" <?= ($race['rank_mode'] ?? 'split') === 'split' ? 'selected' : '' ?>>📊 Per KU (Split)</option>
                                <option value="overall" <?= ($race['rank_mode'] ?? 'split') === 'overall' ? 'selected' : '' ?>>🏆 Gabungan (Overall)</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <label class="inline-flex items-center cursor-pointer <?= !$hasResult ? 'opacity-50' : '' ?>" <?= !$hasResult ? 'title="Input hasil terlebih dahulu!"' : '' ?>>
                                <input type="checkbox" class="sr-only peer" onchange="togglePublish(this, <?= $race['id'] ?>)" <?= $isPublished ? 'checked' : '' ?> <?= !$hasResult ? 'disabled' : '' ?>>
                                <div class="relative w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                            </label>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <a href="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $race['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg border border-blue-200 uppercase tracking-wider transition">
                                    📝 Input
                                </a>
                                <a href="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $race['id'] ?>&print=1" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg border border-slate-300 uppercase tracking-wider transition">
                                    🖨️ A4
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleEventPublish(checkboxElem, eventId) {
    const isChecked = checkboxElem.checked ? 1 : 0;
    const formData = new FormData();
    formData.append('action', 'toggle_event_publish');
    formData.append('event_id', eventId);
    formData.append('is_result_published', isChecked);

    fetch('<?= getenv('APP_URL') ?>/swim/admin/results/publish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Status Publikasi Global Tersimpan',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            checkboxElem.checked = !isChecked; // revert
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(error => {
        checkboxElem.checked = !isChecked;
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    });
}

function togglePublish(checkboxElem, eventNumberId) {
    const isChecked = checkboxElem.checked ? 1 : 0;
    const formData = new FormData();
    formData.append('action', 'toggle_publish');
    formData.append('event_number_id', eventNumberId);
    formData.append('is_published', isChecked);

    fetch('<?= getenv('APP_URL') ?>/swim/admin/results/publish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Status Tersimpan',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            checkboxElem.checked = !isChecked; 
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(error => {
        checkboxElem.checked = !isChecked;
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    });
}

function updateRankMode(eventNumberId, mode) {
    const formData = new FormData();
    formData.append('action', 'toggle_rank_mode');
    formData.append('event_number_id', eventNumberId);
    formData.append('rank_mode', mode);

    fetch('<?= getenv('APP_URL') ?>/swim/admin/results/publish', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const saved = localStorage.getItem('swim_print_results_cfg');
    if (saved) {
        try {
            const cfg = JSON.parse(saved);
            let q = ['print=1', 'cfg_submitted=1'];
            for (const k in cfg) {
                if (cfg[k]) q.push(encodeURIComponent(k) + '=1');
            }
            if (q.length > 0) {
                document.querySelectorAll('a[href*="print=1"]').forEach(btn => {
                    let base = btn.getAttribute('href').split('&print=1')[0];
                    btn.setAttribute('href', base + '&' + q.join('&'));
                });
            }
        } catch(e) {}
    }
});
</script>
