<!-- HEADER -->
<div class="max-w-7xl mx-auto mb-6 flex flex-col lg:flex-row justify-between items-start gap-6 print:hidden">
    <div>
        <h1 class="text-4xl font-black uppercase tracking-tighter italic text-slate-900 leading-none">Global Race Book</h1>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">
            Pusat Kendali Cetak Buku & Auto-Seeding
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl shrink-0">👟</div>
        <div>
            <p class="text-3xl font-black text-slate-900"><?= number_format($totalPaidAthletes) ?></p>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Atlet Lunas</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl shrink-0">🏁</div>
        <div>
            <p class="text-3xl font-black text-slate-900"><?= number_format($totalClasses) ?></p>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kelas Lomba Siap Di-generate</p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-8">
        
        <?php if($totalPaidAthletes == 0): ?>
            <div class="py-10 opacity-50 text-center">
                <div class="text-5xl mb-4 grayscale">⚠️</div>
                <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Atlet Terdaftar</h3>
                <p class="text-sm text-slate-400 mt-2">Pastikan peserta sudah mendaftar dan pembayaran telah diverifikasi.</p>
            </div>
        <?php else: ?>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6 border-b border-slate-100 pb-6">
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest text-slate-800">Cetak Full Race Book</h2>
                    <p class="text-slate-500 font-bold uppercase text-[10px] mt-1">
                        Pilih konfigurasi halaman dan informasi yang ingin ditampilkan.
                    </p>
                </div>
                
                <?php if($hasGenerated): ?>
                    <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-full border border-emerald-200 text-xs font-bold uppercase tracking-widest">
                        <span>✅</span> Race Book Sudah Di-generate
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/printFull" target="_blank" enctype="multipart/form-data" class="space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Header Options -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Pengaturan Header</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_event_name" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Nama Event</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_date" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Tanggal Event</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_group" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Kelompok Umur</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_gender" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Gender</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="cfg_distance" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Tampilkan Jarak Lomba</span>
                            </label>
                        </div>
                    </div>

                    <!-- Column Options -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Pengaturan Kolom Tabel</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_lane" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Lane / Urut (Start Grid)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_bib" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">No. BIB</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_nama" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Nama Atlet</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="col_klub" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-bold text-slate-700">Klub / Kontingen</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest bg-slate-100 p-3 rounded-lg mb-4 text-slate-700">Tambahan Halaman</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Custom Cover (Opsional)</label>
                            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Gambar Jadwal (Opsional)</label>
                            <input type="file" name="schedule_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" name="show_schedule_auto" checked class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-600">Gunakan Tabel Rekap Jadwal Otomatis (Jika tidak upload gambar)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end items-center gap-4 pt-6 border-t border-slate-100">
                    <!-- TOMBOL DOWNLOAD PDF -->
                    <button type="submit" <?= !$hasGenerated ? 'disabled' : '' ?> class="<?= $hasGenerated ? 'bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:-translate-y-1' : 'bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none' ?> text-white font-black uppercase tracking-widest text-sm py-4 px-8 rounded-xl transition-all flex items-center gap-3">
                        <span class="text-xl">🖨️</span>
                        <span>Generate & Print PDF</span>
                    </button>
                </div>
                
                <?php if(!$hasGenerated): ?>
                    <p class="text-[10px] text-right text-red-500 mt-2 font-bold uppercase tracking-widest">
                        ⚠️ Generate Ulang Auto-Seeding (di bawah) terlebih dahulu untuk mengaktifkan tombol Cetak PDF
                    </p>
                <?php endif; ?>

            </form>
        <?php endif; ?>
    </div>
</div>

<?php if($eventId > 0 && !empty($groupedClasses)): ?>
<!-- ========================================== -->
<!-- BAGIAN GENERATE SERI PER KATEGORI -->
<!-- ========================================== -->
<div class="max-w-4xl mx-auto my-10 bg-white p-8 rounded-2xl shadow-xl print-hidden border border-slate-200">
    <div class="flex items-center justify-between border-b pb-4 mb-6">
        <div>
            <h2 class="text-xl font-black uppercase tracking-widest text-slate-800 flex items-center gap-2">
                <span>⚙️</span> Daftar Kelas & Generate Heat
            </h2>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Dikelompokkan berdasarkan kategori (Speed, Standard, Pemula)</p>
        </div>
        
        <form id="formGenerateAuto" method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/pelotons/generateAll" onsubmit="return confirm('⚡ GENERATE RACE BOOK\n\nProses ini akan menyusun daftar peserta untuk seluruh <?= $totalClasses ?> kelas lomba.\n\n• Kelas HEAT → Babak Kualifikasi (Acak Terdistribusi)\n• Kelas STARTING LIST → Daftar langsung final\n\n<?= $hasGenerated ? '⚠️ Data seeding sebelumnya akan DITIMPA!\n\n' : '' ?>Lanjutkan?')">
            <input type="hidden" name="round" value="Kualifikasi">
            <input type="hidden" name="algorithm" value="distributed">
            <input type="hidden" name="max_lanes" value="0">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold uppercase tracking-widest text-xs py-3 px-5 rounded-xl transition-all shadow-md flex items-center gap-2">
                <span class="text-sm">⚡</span>
                <span>Generate Seluruh Heat</span>
            </button>
        </form>
    </div>

    <div class="space-y-6">
        <?php foreach($groupedClasses as $category => $classes): ?>
            <div>
                <h3 class="text-sm font-black text-indigo-700 uppercase tracking-widest bg-indigo-50 px-4 py-2 rounded-lg mb-3 inline-block">
                    <?= htmlspecialchars($category) ?> (<?= count($classes) ?> Kelas)
                </h3>
                
                <div class="space-y-3 pl-2">
                    <?php foreach($classes as $rn => $clsGroup): 
                        $raceNum = str_pad($rn, 3, '0', STR_PAD_LEFT);
                        $genderLabel = implode(' & ', $clsGroup['genders']);
                        
                        // Deteksi default mekanisme
                        // Jika kategori mengandung kata 'Pemula', maka paksa jadi starting_list
                        if (stripos($clsGroup['category_name'], 'Pemula') !== false) {
                            $defaultMech = 'starting_list';
                        } else {
                            $defaultMech = \App\Roll\Controllers\Admin\RollPelotonController::getMechanism($clsGroup['distance_name'])['mechanism'];
                        }
                        
                        $classIdsJson = htmlspecialchars(json_encode($clsGroup['classes']));
                    ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-600 text-white font-black text-sm px-3 py-1.5 rounded-lg shadow-sm">R<?= $raceNum ?></div>
                                <div>
                                    <div class="text-sm font-black text-slate-800 uppercase tracking-widest">
                                        <?= htmlspecialchars($clsGroup['distance_name']) ?> - <?= htmlspecialchars($clsGroup['group_name']) ?> <?= $genderLabel ?>
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5 flex items-center gap-2">
                                        <span class="bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded">
                                            Total: <?= (int)$clsGroup['total_entries'] ?> Atlet 
                                            <?php if((int)$clsGroup['total_pa'] > 0 || (int)$clsGroup['total_pi'] > 0): ?>
                                                (Pa: <?= (int)$clsGroup['total_pa'] ?>, Pi: <?= (int)$clsGroup['total_pi'] ?>)
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <form class="flex items-center gap-2 form-generate-single" data-class-ids="<?= $classIdsJson ?>">
                                <div class="flex flex-col gap-1">
                                    <select name="override_mechanism" class="mech-select border-slate-300 rounded-lg text-xs font-bold text-slate-700 py-1.5 pl-2 pr-6 focus:ring-indigo-500">
                                        <option value="heat" <?= $defaultMech === 'heat' ? 'selected' : '' ?>>🔥 Heat</option>
                                        <option value="starting_list" <?= $defaultMech === 'starting_list' ? 'selected' : '' ?>>📋 Starting List</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1 w-16 input-max-lanes <?= $defaultMech === 'starting_list' ? 'hidden' : '' ?>">
                                    <input type="number" name="max_lanes" value="<?= (int)$clsGroup['max_lanes'] ?>" min="1" max="50" class="border-slate-300 rounded-lg text-xs font-bold text-slate-700 py-1.5 px-2 text-center focus:ring-indigo-500" title="Max Peserta per Heat">
                                </div>
                                <button type="submit" class="btn-generate-single bg-indigo-100 hover:bg-indigo-200 text-indigo-700 p-2 rounded-lg transition-colors flex items-center gap-1" title="Generate Heat Kelas Ini">
                                    <span class="text-sm">⚡</span>
                                    <span class="text-xs font-bold uppercase tracking-widest hidden sm:inline">Gen</span>
                                </button>
                                <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons/detail?class_id=<?= $clsGroup['classes'][0] ?>" target="_blank" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 p-2 rounded-lg transition-colors flex items-center gap-1" title="Lihat Detail Seri">
                                    <span class="text-sm">👁️</span>
                                    <span class="text-xs font-bold uppercase tracking-widest hidden sm:inline">View</span>
                                </a>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Max Lanes input visibility
    document.querySelectorAll('.mech-select').forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            const maxLanesContainer = form.querySelector('.input-max-lanes');
            if(this.value === 'heat') {
                maxLanesContainer.classList.remove('hidden');
            } else {
                maxLanesContainer.classList.add('hidden');
            }
        });
    });

    // Save and Restore Checkbox state for Print Configuration
    const printCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="cfg_"], input[type="checkbox"][name^="col_"], input[type="checkbox"][name="show_schedule_auto"]');
    printCheckboxes.forEach(cb => {
        // Restore from localStorage
        const saved = localStorage.getItem('roll_print_config_' + cb.name);
        if (saved !== null) {
            cb.checked = (saved === 'true');
        }
        // Save on change
        cb.addEventListener('change', function() {
            localStorage.setItem('roll_print_config_' + this.name, this.checked);
        });
    });

    // Handle AJAX form submission for single class generation and State Preservation
    document.querySelectorAll('.form-generate-single').forEach(form => {
        const classIds = form.getAttribute('data-class-ids');
        const mechSelect = form.querySelector('.mech-select');
        const lanesInput = form.querySelector('input[name="max_lanes"]');
        
        // Restore state
        const savedMech = localStorage.getItem('roll_mech_' + classIds);
        if (savedMech) {
            mechSelect.value = savedMech;
            // Trigger change to update UI (hide/show lanes)
            mechSelect.dispatchEvent(new Event('change'));
        }
        
        const savedLanes = localStorage.getItem('roll_lanes_' + classIds);
        if (savedLanes) {
            lanesInput.value = savedLanes;
        }
        
        // Save state on change
        if (mechSelect) {
            mechSelect.addEventListener('change', function() {
                localStorage.setItem('roll_mech_' + classIds, this.value);
            });
        }
        if (lanesInput) {
            lanesInput.addEventListener('change', function() {
                localStorage.setItem('roll_lanes_' + classIds, this.value);
            });
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const classIds = JSON.parse(this.dataset.classIds || '[]');
            const mechanism = this.querySelector('[name="override_mechanism"]').value;
            const maxLanes = this.querySelector('[name="max_lanes"]').value;
            
            const btn = this.querySelector('.btn-generate-single');
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<span class="text-xs font-bold">⏳</span>';
            btn.disabled = true;
            
            try {
                // Eksekusi semua class_id sekaligus menggunakan Promise.all
                const promises = classIds.map(classId => 
                    fetch(`<?= getenv('APP_URL') ?>/roll/admin/pelotons/process?class_id=${classId}&round=Kualifikasi&algorithm=distributed&max_lanes=${maxLanes}&override_mechanism=${mechanism}`)
                    .then(res => res.json())
                );
                
                const results = await Promise.all(promises);
                const allSuccess = results.every(data => data.success);
                
                if (allSuccess) {
                    btn.classList.remove('bg-indigo-100', 'text-indigo-700');
                    btn.classList.add('bg-emerald-500', 'text-white');
                    btn.innerHTML = '<span class="text-xs font-bold">✅</span>';
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.classList.add('bg-indigo-100', 'text-indigo-700');
                        btn.classList.remove('bg-emerald-500', 'text-white');
                        btn.disabled = false;
                    }, 2000);
                } else {
                    const errorMessages = results.filter(d => !d.success).map(d => d.message).join(', ');
                    alert('Gagal: ' + errorMessages);
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat memproses.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    });

    // ----------------------------------------------------------------------
    // STATE PERSISTENCE (Menyimpan form ke localStorage agar tidak reset)
    // ----------------------------------------------------------------------
    const STORAGE_KEY = 'global_racebook_state';
    let savedState = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');

    // Restore state
    // 1. Checkboxes for Print PDF
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        if(savedState[cb.name] !== undefined) {
            cb.checked = savedState[cb.name];
        }
        cb.addEventListener('change', () => {
            savedState[cb.name] = cb.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(savedState));
        });
    });

    // 2. Select and Input for Class Generation
    document.querySelectorAll('.form-generate-single').forEach((form, index) => {
        const mechSelect = form.querySelector('[name="override_mechanism"]');
        const maxLanesInput = form.querySelector('[name="max_lanes"]');
        
        // Buat ID unik berdasarkan raceNum / dataset
        const uniqueId = form.dataset.classIds; 
        
        if (savedState['mech_' + uniqueId]) {
            mechSelect.value = savedState['mech_' + uniqueId];
            // Trigger change untuk toggle visibility maxLanes
            mechSelect.dispatchEvent(new Event('change'));
        }
        
        if (savedState['maxlanes_' + uniqueId]) {
            maxLanesInput.value = savedState['maxlanes_' + uniqueId];
        }

        mechSelect.addEventListener('change', () => {
            savedState['mech_' + uniqueId] = mechSelect.value;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(savedState));
        });

        maxLanesInput.addEventListener('input', () => {
            savedState['maxlanes_' + uniqueId] = maxLanesInput.value;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(savedState));
        });
    });
});
</script>