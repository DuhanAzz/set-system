<?php
/**
 * Shared 2-step Bulk Individual Registration CSV Modal (used by User registration & Admin entry detail).
 * Expects: $csvExportUrl, $csvPreviewUrl, $csvConfirmUrl, $distancesByCat
 * Optional: $csvEntityLabel, $csvExtraNotice
 */
$csvEntityLabel = $csvEntityLabel ?? 'klub Anda';
$csvExtraNotice = $csvExtraNotice ?? '';
?>
<script>
function openRegImportModal() {
    document.getElementById('reg_csv_step1').classList.remove('hidden');
    document.getElementById('reg_csv_step2').classList.add('hidden');
    document.getElementById('reg_csv_file_input').value = '';
    document.getElementById('reg_csv_upload_err').classList.add('hidden');
    document.getElementById('modal-import-reg-csv').classList.remove('hidden');
}

function submitRegCsvPreview() {
    const fileInput = document.getElementById('reg_csv_file_input');
    const errBox = document.getElementById('reg_csv_upload_err');
    const btn = document.getElementById('btn_preview_reg_csv');
    if (!fileInput.files || !fileInput.files.length) {
        errBox.innerText = 'Silakan pilih file CSV terlebih dahulu.';
        errBox.classList.remove('hidden');
        return;
    }

    errBox.classList.add('hidden');
    btn.disabled = true;
    btn.innerText = '⏳ Memeriksa Data CSV...';

    const fd = new FormData();
    fd.append('csv_file', fileInput.files[0]);

    fetch(<?= json_encode($csvPreviewUrl) ?>, {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerText = '🔍 Cek & Preview Validasi';

        if (!res.success) {
            errBox.innerText = res.message || 'Gagal memproses file CSV.';
            errBox.classList.remove('hidden');
            return;
        }

        document.getElementById('reg_csv_token').value = res.token || '';
        document.getElementById('prev_skater_count').innerText = (res.stats?.total_skaters ?? 0) + ' Atlet';
        document.getElementById('prev_entry_count').innerText = (res.stats?.total_entries ?? 0) + ' Nomor';
        document.getElementById('prev_replace_count').innerText = (res.stats?.replaced_entries ?? 0) + ' Entri Lama';
        document.getElementById('prev_est_fee').innerText = 'Rp ' + Number(res.stats?.estimated_total_fee ?? 0).toLocaleString('id-ID');

        const errWrap = document.getElementById('prev_errors_wrap');
        const errList = document.getElementById('prev_errors_list');
        if (res.errors && res.errors.length > 0) {
            errList.innerHTML = res.errors.map(msg => `<li>${msg.replace(/</g, '&lt;')}</li>`).join('');
            errWrap.classList.remove('hidden');
        } else {
            errWrap.classList.add('hidden');
        }

        const tbody = document.getElementById('prev_items_tbody');
        const btnConfirm = document.getElementById('btn_confirm_reg_csv');
        if (!res.preview_items || res.preview_items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-400 font-bold italic">Tidak ada baris atlet yang siap didaftarkan.</td></tr>';
            btnConfirm.disabled = true;
            btnConfirm.className = 'px-6 py-3 rounded-xl bg-slate-300 text-white font-black text-xs uppercase cursor-not-allowed';
        } else {
            tbody.innerHTML = res.preview_items.map((it, idx) => `
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="px-3 py-2 text-center font-bold text-slate-400">${idx + 1}</td>
                    <td class="px-3 py-2">
                        <div class="font-black text-slate-800 uppercase text-[11px]">${String(it.skater_name || '').replace(/</g, '&lt;')}</div>
                        <div class="text-[10px] text-slate-500">${it.gender} &bull; ${it.age} Thn (KU ${it.group_name})</div>
                    </td>
                    <td class="px-3 py-2 text-center font-bold text-indigo-700 text-[11px]">${it.categories}</td>
                    <td class="px-3 py-2 text-[11px] font-semibold text-slate-700">${it.distances}</td>
                    <td class="px-3 py-2 text-center text-[10px] font-bold">
                        ${it.replaced_count > 0 ? `<span class="text-amber-600">Ganti ${it.replaced_count} entri</span>` : `<span class="text-emerald-600">Baru (${it.entry_count})</span>`}
                    </td>
                </tr>
            `).join('');
            btnConfirm.disabled = false;
            btnConfirm.className = 'px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase shadow-md cursor-pointer transition';
        }

        document.getElementById('reg_csv_step1').classList.add('hidden');
        document.getElementById('reg_csv_step2').classList.remove('hidden');
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerText = '🔍 Cek & Preview Validasi';
        errBox.innerText = 'Gagal terhubung ke server saat memvalidasi CSV.';
        errBox.classList.remove('hidden');
    });
}
</script>

<!-- Modal Import / Export CSV Massal Pendaftaran Lomba (Khusus Nomor Individu) -->
<div id="modal-import-reg-csv" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full shadow-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]">
        <div class="bg-slate-900 p-5 text-white flex justify-between items-center">
            <div>
                <h3 class="text-base font-black uppercase tracking-wider flex items-center gap-2">
                    <span>📥</span> Pendaftaran Massal Lomba Individu (CSV)
                </h3>
                <p class="text-[11px] text-slate-300 mt-0.5">Khusus nomor lomba individu &bull; Nomor beregu/relay tetap terpisah &amp; tidak terganggu</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-reg-csv').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
        </div>

        <!-- STEP 1: DOWNLOAD & UPLOAD -->
        <div id="reg_csv_step1" class="p-6 overflow-y-auto bg-slate-50 space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-blue-200/70">
                    <span class="font-black uppercase tracking-wider text-[11px]">Langkah 1: Unduh Form CSV Event Ini</span>
                    <a href="<?= htmlspecialchars($csvExportUrl) ?>"
                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-black uppercase shadow-sm transition inline-flex items-center gap-1.5">
                        <span>📤</span> Export / Download Template CSV Lomba
                    </a>
                </div>
                <ul class="list-disc pl-4 space-y-1 text-[11px] text-blue-800">
                    <li>File hasil download <strong>otomatis berisi seluruh daftar atlet <?= htmlspecialchars($csvEntityLabel) ?></strong> lengkap dengan umur per 31 Des tahun lomba &amp; entri yang sudah terdaftar.</li>
                    <li>Di Excel, cukup isi kolom <strong>Kategori</strong> (<code>Pemula</code>, <code>Standart</code>, atau <code>Speed</code>) lalu ketik angka <strong><code>1</code></strong> pada kolom jarak yang diikuti (kosongkan baris atlet yang tidak ikut).</li>
                    <li>Sistem otomatis mendeteksi KU, gender, validasi PON, limit nomor lomba, &amp; kelas wajib (<code>*</code>).</li>
                    <li><strong>Kebijakan Update:</strong> Untuk setiap atlet yang diisi di CSV, entri individu sebelumnya pada event ini akan <strong>diganti seluruhnya</strong> sesuai isian CSV baru (entri tim/relay &amp; token tidak terganggu).</li>
                    <?php if ($csvExtraNotice !== ''): ?>
                        <li><?= $csvExtraNotice ?></li>
                    <?php endif; ?>
                </ul>
            </div>

            <?php if (!empty($distancesByCat)): ?>
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 text-[11px]">
                <p class="font-black text-slate-700 uppercase tracking-wider mb-2 text-[10px]">Daftar Jarak Individu yang Dibuka di Event Ini:</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <?php foreach (['Pemula', 'Standart', 'Speed'] as $catL): ?>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                            <div class="font-black text-slate-800 uppercase text-[10px] mb-1"><?= $catL ?></div>
                            <div class="text-slate-600 leading-relaxed">
                                <?= !empty($distancesByCat[$catL]) ? htmlspecialchars(implode(', ', $distancesByCat[$catL])) : '<span class="italic text-slate-400">Tidak tersedia / Tutup</span>' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-2">
                <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest">Langkah 2: Upload File CSV Hasil Pengisian *</label>
                <input type="file" id="reg_csv_file_input" accept=".csv,text/csv"
                       class="w-full text-xs font-bold text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:bg-slate-800 file:text-white hover:file:bg-slate-900 border border-slate-300 rounded-xl p-1.5 bg-slate-50 cursor-pointer">
                <div id="reg_csv_upload_err" class="hidden p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-bold"></div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-import-reg-csv').classList.add('hidden')" class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-600 text-xs font-bold uppercase cursor-pointer">Batal</button>
                <button type="button" id="btn_preview_reg_csv" onclick="submitRegCsvPreview()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase shadow-md cursor-pointer transition">
                    🔍 Cek &amp; Preview Validasi
                </button>
            </div>
        </div>

        <!-- STEP 2: PREVIEW VALIDASI & ESTIMASI BIAYA -->
        <div id="reg_csv_step2" class="hidden p-6 overflow-y-auto bg-slate-50 space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-white p-3.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] font-black text-slate-400 uppercase">Atlet Valid</div>
                    <div id="prev_skater_count" class="text-lg font-black text-slate-800 mt-0.5">0 Atlet</div>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] font-black text-slate-400 uppercase">Total Nomor Masuk</div>
                    <div id="prev_entry_count" class="text-lg font-black text-blue-700 mt-0.5">0 Nomor</div>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] font-black text-slate-400 uppercase">Entri Lama Diganti</div>
                    <div id="prev_replace_count" class="text-lg font-black text-amber-600 mt-0.5">0 Entri</div>
                </div>
                <div class="bg-emerald-600 text-white p-3.5 rounded-xl shadow-sm">
                    <div class="text-[10px] font-black text-emerald-100 uppercase">Estimasi Total Tagihan</div>
                    <div id="prev_est_fee" class="text-lg font-black mt-0.5">Rp 0</div>
                </div>
            </div>

            <div id="prev_errors_wrap" class="hidden bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-800">
                <div class="font-black uppercase text-[11px] mb-1">⚠️ Baris Gagal / Dilewati (Silakan perbaiki di Excel jika diperlukan):</div>
                <ul id="prev_errors_list" class="list-disc pl-4 space-y-0.5 max-h-32 overflow-y-auto text-[11px]"></ul>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="max-h-64 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-[10px] font-black uppercase text-slate-500 sticky top-0">
                            <tr>
                                <th class="px-3 py-2.5 text-center w-10">No</th>
                                <th class="px-3 py-2.5">Atlet &amp; KU</th>
                                <th class="px-3 py-2.5 text-center">Kategori</th>
                                <th class="px-3 py-2.5">Nomor Terpilih</th>
                                <th class="px-3 py-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="prev_items_tbody"></tbody>
                    </table>
                </div>
            </div>

            <form action="<?= htmlspecialchars($csvConfirmUrl) ?>" method="POST" class="flex justify-between items-center gap-3 pt-2">
                <input type="hidden" name="import_token" id="reg_csv_token">
                <button type="button" onclick="document.getElementById('reg_csv_step2').classList.add('hidden'); document.getElementById('reg_csv_step1').classList.remove('hidden');"
                        class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-600 text-xs font-bold uppercase cursor-pointer">
                    &larr; Upload Ulang CSV
                </button>
                <button type="submit" id="btn_confirm_reg_csv" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase shadow-md cursor-pointer transition">
                    ✅ Konfirmasi &amp; Daftarkan Sekarang
                </button>
            </form>
        </div>
    </div>
</div>
