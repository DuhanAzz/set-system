<?php if(isset($_SESSION['flash_message'])): ?>
    <div class="max-w-5xl mx-auto mb-4 bg-<?= $_SESSION['flash_type'] == 'success' ? 'emerald' : 'red' ?>-100 border border-<?= $_SESSION['flash_type'] == 'success' ? 'emerald' : 'red' ?>-400 text-<?= $_SESSION['flash_type'] == 'success' ? 'emerald' : 'red' ?>-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
        <span><?= $_SESSION['flash_type'] == 'success' ? '✅' : '❌' ?></span> <strong><?= $_SESSION['flash_message'] ?></strong>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
<?php endif; ?>

<div class="max-w-5xl mx-auto">
    <div class="mb-8 flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Publikasi Live Result</h1>
            <p class="text-sm text-slate-500 font-bold mt-1">Kelola dokumen dan Live Result untuk pengguna.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full lg:w-auto">
            <?php if ($eventId > 0): ?>
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 shadow-sm flex items-center justify-between gap-4 w-full sm:w-auto h-[42px] mt-auto">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Publikasikan Hasil?</span>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" onchange="toggleEventPublish(this, <?= $eventId ?>)" <?= (isset($eventInfo['is_result_published']) && $eventInfo['is_result_published'] == 1) ? 'checked' : '' ?>>
                    <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                </label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(empty($classes)): ?>
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
                        <th class="px-6 py-4 w-24 text-center">No.</th>
                        <th class="px-6 py-4">Kategori Lomba</th>
                        <th class="px-6 py-4 text-center w-32">Status Data</th>
                        <th class="px-6 py-4 text-right w-40">Live Result?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($classes as $index => $c): 
                        $isPublished = ($c['result_status'] == 'Published');
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center font-black text-slate-400 text-lg">#<?= $index + 1 ?></td>
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-800 uppercase text-sm leading-tight mb-1">
                                <?= htmlspecialchars($c['distance_name'] ?? '') ?> 
                            </div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                <?= htmlspecialchars($c['category_name'] ?? '') ?> • <?= htmlspecialchars($c['group_name'] ?? '') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($isPublished): ?>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase tracking-widest">✅ Published</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase tracking-widest">⏳ Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" onchange="togglePublish(this, <?= $c['id'] ?>)" <?= $isPublished ? 'checked' : '' ?>>
                                <div class="relative w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                            </label>
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

    fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
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

function togglePublish(checkboxElem, classId) {
    const isChecked = checkboxElem.checked ? 'Published' : 'Draft';
    const formData = new FormData();
    formData.append('action', 'toggle_publish');
    formData.append('class_id', classId);
    formData.append('is_published', isChecked);

    fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
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
            }).then(() => {
                location.reload(); // Reload to update status pill
            });
        } else {
            checkboxElem.checked = !checkboxElem.checked; 
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(error => {
        checkboxElem.checked = !checkboxElem.checked;
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
    });
}
</script>
