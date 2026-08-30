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

    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Rekap Medali Klub -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">
            <div>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">REKAPITULASI</div>
                <div class="font-black text-slate-800 text-sm">🏆 Juara Umum (Klub)</div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full xl:w-auto">
                <?php if (!empty($eventInfo['medal_tally_pdf'])): ?>
                    <div class="flex gap-1.5 w-full xl:w-48">
                        <button type="button" onclick="deleteEventPdf(<?= $eventId ?>, 'medal_tally')" class="flex-1 px-2 py-2 bg-red-50 text-red-600 border border-red-200 text-[10px] font-bold rounded-lg hover:bg-red-100 transition-colors text-center" title="Hapus PDF">🗑️</button>
                        <a href="<?= getenv('APP_URL') ?>/uploads/results/<?= htmlspecialchars($eventInfo['medal_tally_pdf']) ?>" target="_blank" class="flex-[3] px-2 py-2 bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-bold rounded-lg hover:bg-blue-100 transition-colors text-center flex items-center justify-center">Lihat PDF</a>
                    </div>
                <?php endif; ?>
                <input type="file" id="pdf_medal_tally" class="hidden" accept=".pdf" onchange="uploadEventPdf(this, <?= $eventId ?>, 'medal_tally')">
                <button type="button" onclick="document.getElementById('pdf_medal_tally').click()" class="w-full xl:w-32 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    <?= !empty($eventInfo['medal_tally_pdf']) ? 'Ganti PDF' : 'Unggah PDF' ?>
                </button>
            </div>
        </div>
        
        <!-- Rekap Pesepatu Roda Terbaik -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">
            <div>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">REKAPITULASI</div>
                <div class="font-black text-slate-800 text-sm">🛼 Pesepatu Roda Terbaik</div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full xl:w-auto">
                <?php if (!empty($eventInfo['best_skater_pdf'])): ?>
                    <div class="flex gap-1.5 w-full xl:w-48">
                        <button type="button" onclick="deleteEventPdf(<?= $eventId ?>, 'best_skater')" class="flex-1 px-2 py-2 bg-red-50 text-red-600 border border-red-200 text-[10px] font-bold rounded-lg hover:bg-red-100 transition-colors text-center" title="Hapus PDF">🗑️</button>
                        <a href="<?= getenv('APP_URL') ?>/uploads/results/<?= htmlspecialchars($eventInfo['best_skater_pdf']) ?>" target="_blank" class="flex-[3] px-2 py-2 bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-bold rounded-lg hover:bg-blue-100 transition-colors text-center flex items-center justify-center">Lihat PDF</a>
                    </div>
                <?php endif; ?>
                <input type="file" id="pdf_best_skater" class="hidden" accept=".pdf" onchange="uploadEventPdf(this, <?= $eventId ?>, 'best_skater')">
                <button type="button" onclick="document.getElementById('pdf_best_skater').click()" class="w-full xl:w-32 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    <?= !empty($eventInfo['best_skater_pdf']) ? 'Ganti PDF' : 'Unggah PDF' ?>
                </button>
            </div>
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
                        <th class="px-6 py-4 w-32 text-center">No. Lomba</th>
                        <th class="px-6 py-4">Kategori Lomba</th>
                        <th class="px-6 py-4 text-center w-32">Gender</th>
                        <th class="px-6 py-4 text-center w-32">Status Data</th>
                        <th class="px-6 py-4 text-right w-48">Unggah PDF Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($classes as $index => $c): 
                        $isPublished = ($c['result_status'] == 'Published');
                        $gender = $c['gender'] ?: 'CAMPURAN';
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center font-black text-slate-800 text-sm">
                            <?= htmlspecialchars($c['race_number'] ?: 'No. Lomba ' . ($index + 1)) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-800 uppercase text-sm leading-tight mb-1">
                                <?= htmlspecialchars($c['class_name'] ?? '') ?> - <?= htmlspecialchars($c['distance_name'] ?? '') ?> 
                            </div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                <?= htmlspecialchars($c['category_name'] ?? '') ?> • <?= htmlspecialchars($c['group_name'] ?? '') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border <?= strtolower($gender) == 'putra' ? 'bg-blue-50 text-blue-600 border-blue-200' : (strtolower($gender) == 'putri' ? 'bg-pink-50 text-pink-600 border-pink-200' : 'bg-purple-50 text-purple-600 border-purple-200') ?>">
                                <?= htmlspecialchars($gender) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($isPublished): ?>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase tracking-widest">✅ Published</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-[9px] font-black uppercase tracking-widest">⏳ Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-2">
                                <?php foreach ($c['available_rounds'] as $roundName): ?>
                                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 flex items-center justify-between gap-3">
                                        <div class="text-[10px] font-black text-slate-600 uppercase tracking-widest w-24 whitespace-nowrap">
                                            <?= htmlspecialchars($roundName) ?>
                                        </div>
                                        <div class="flex flex-col gap-1.5 w-32 ml-auto">
                                            <?php if (isset($c['pdfs'][$roundName])): ?>
                                                <div class="flex gap-1.5 w-full">
                                                    <button type="button" onclick="deletePdf(<?= $c['id'] ?>, '<?= htmlspecialchars($roundName) ?>')" class="flex-1 px-2 py-1.5 bg-red-50 text-red-600 border border-red-200 text-[10px] font-bold rounded-lg hover:bg-red-100 transition-colors text-center" title="Hapus PDF">🗑️</button>
                                                    <a href="<?= getenv('APP_URL') ?>/uploads/results/<?= htmlspecialchars($c['pdfs'][$roundName]) ?>" target="_blank" class="flex-[3] px-2 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-bold rounded-lg hover:bg-blue-100 transition-colors text-center">Lihat PDF</a>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" id="pdf_<?= $c['id'] ?>_<?= md5($roundName) ?>" class="hidden" accept=".pdf" onchange="uploadPdf(this, <?= $c['id'] ?>, '<?= htmlspecialchars($roundName) ?>')">
                                            <button type="button" onclick="document.getElementById('pdf_<?= $c['id'] ?>_<?= md5($roundName) ?>').click()" class="w-full px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold rounded-lg transition-colors shadow-sm">
                                                <?= isset($c['pdfs'][$roundName]) ? 'Ganti PDF' : 'Unggah PDF' ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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

function uploadPdf(inputElem, classId, roundName) {
    if (inputElem.files.length === 0) return;
    const file = inputElem.files[0];
    if (file.type !== 'application/pdf') {
        Swal.fire('Gagal!', 'Hanya file PDF yang diperbolehkan.', 'error');
        inputElem.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_pdf');
    formData.append('class_id', classId);
    formData.append('round', roundName);
    formData.append('result_pdf', file);

    Swal.fire({
        title: 'Mengunggah...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal!', data.message, 'error');
            inputElem.value = '';
        }
    })
    .catch(error => {
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
        inputElem.value = '';
    });
}

function deletePdf(classId, roundName) {
    Swal.fire({
        title: `Hapus PDF ${roundName}?`,
        text: "Jika dihapus, PDF untuk babak ini akan hilang dari layar penonton.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_pdf');
            formData.append('class_id', classId);
            formData.append('round', roundName);

            fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            });
        }
    });
}

function uploadEventPdf(inputElem, eventId, type) {
    if (inputElem.files.length === 0) return;
    const file = inputElem.files[0];
    if (file.type !== 'application/pdf') {
        Swal.fire('Gagal!', 'Hanya file PDF yang diperbolehkan.', 'error');
        inputElem.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'upload_event_pdf');
    formData.append('event_id', eventId);
    formData.append('type', type);
    formData.append('event_pdf', file);

    Swal.fire({
        title: 'Mengunggah...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal!', data.message, 'error');
            inputElem.value = '';
        }
    })
    .catch(error => {
        Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
        inputElem.value = '';
    });
}

function deleteEventPdf(eventId, type) {
    let title = type === 'medal_tally' ? 'Juara Umum' : 'Pesepatu Roda Terbaik';
    Swal.fire({
        title: `Hapus PDF ${title}?`,
        text: "File PDF rekapitulasi ini akan dihapus dari sistem.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_event_pdf');
            formData.append('event_id', eventId);
            formData.append('type', type);

            fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
            });
        }
    });
}
</script>
