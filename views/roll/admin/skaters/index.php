<?php $activeTab = $activeTab ?? 'starting_list'; ?>
<div class="max-w-[95%] mx-auto mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">
            <?= $activeTab === 'bpjs' ? 'Data Peserta, NIK & BPJS' : 'Athlete List' ?>
        </h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
            Daftar Atlet Lunas (Paid) pada <?= htmlspecialchars($eventName ?? 'Event') ?>
        </p>
    </div>
    
    <div class="flex flex-wrap gap-2.5 items-center print:hidden">
        <?php if ($activeTab === 'bpjs'): ?>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters?tab=starting_list"
               class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl shadow-sm border border-slate-900 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2">
                <span>📋</span> Starting List
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters/export_bpjs_csv?search=<?= urlencode($search ?? '') ?>&bpjs=<?= urlencode($bpjsFilter ?? 'all') ?>&nik=<?= urlencode($nikFilter ?? 'all') ?>"
               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm border border-emerald-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2">
                <span>📊</span> Export CSV BPJS
            </a>
        <?php else: ?>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters?tab=bpjs"
               class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm border border-indigo-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2">
                <span>🛡️</span> Data BPJS
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/skaters/export_csv"
               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm border border-emerald-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2">
                <span>📊</span> Export CSV
            </a>
        <?php endif; ?>

        <button type="button" id="btnTogglePrintCfg" onclick="document.getElementById('printCfgPanel').classList.toggle('hidden')"
                class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 rounded-xl shadow-sm border border-slate-200 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 cursor-pointer">
            <span>⚙️</span> Atur Print
        </button>

        <a id="btnPrintSkaters"
           href="<?= getenv('APP_URL') ?>/roll/admin/skaters/print_data?tab=<?= urlencode($activeTab) ?>&cfg=1&show_bib=1&show_club=1<?= $activeTab === 'bpjs' ? '&show_dob=1&show_nik=1&show_bpjs=1&search=' . urlencode($search ?? '') . '&bpjs=' . urlencode($bpjsFilter ?? 'all') . '&nik=' . urlencode($nikFilter ?? 'all') : '&show_classes=1&page_break_cat=1' ?>"
           target="_blank"
           class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-sm border border-indigo-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2">
            <span>🖨️</span> Cetak Dokumen A4
        </a>
    </div>
</div>

<!-- Panel Pengaturan Kolom Print (Checkbox) -->
<div id="printCfgPanel" class="max-w-[95%] mx-auto mb-6 hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-4 print:hidden">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">⚙️ Pengaturan Tampilan Cetak A4</p>
            <p class="text-[11px] text-slate-500 font-medium">Centang data/kolom yang ingin ditampilkan saat mencetak dokumen (bisa juga diubah langsung di halaman preview cetak).</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-slate-700">
            <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_bib" checked>
                <span>No. BIB</span>
            </label>
            <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_dob" <?= $activeTab === 'bpjs' ? 'checked' : '' ?>>
                <span>Tanggal Lahir</span>
            </label>
            <?php if ($activeTab === 'bpjs'): ?>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_nik" checked>
                    <span>NIK</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_club" checked>
                    <span>Klub / Kontingen</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_bpjs" checked>
                    <span>Status BPJS</span>
                </label>
            <?php else: ?>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_club" checked>
                    <span>Klub / Kontingen</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="show_classes" checked>
                    <span>Kelas / Nomor</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-indigo-100">
                    <input type="checkbox" class="print-cfg-cb rounded text-indigo-600" name="page_break_cat" checked>
                    <span>Pisah Halaman per Kategori</span>
                </label>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($activeTab === 'bpjs'): ?>
<div class="max-w-[95%] mx-auto space-y-6">
    <!-- Ringkasan Statistik (Peserta Lunas) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Peserta Lunas</span>
            <div class="text-2xl font-black text-slate-800 mt-1"><?= number_format($bpjsSummary['total'] ?? 0) ?></div>
            <div class="text-[10px] text-slate-400 mt-0.5">Pembayaran berstatus Paid</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-emerald-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600">BPJS: Sudah Terdaftar</span>
            <div class="text-2xl font-black text-emerald-700 mt-1"><?= number_format($bpjsSummary['bpjs_sudah'] ?? 0) ?></div>
            <div class="text-[10px] text-emerald-600/70 mt-0.5">Sesuai data centang klub</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-amber-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-600">BPJS: Belum Terdaftar</span>
            <div class="text-2xl font-black text-amber-700 mt-1"><?= number_format($bpjsSummary['bpjs_belum'] ?? 0) ?></div>
            <div class="text-[10px] text-amber-600/70 mt-0.5">Belum dicentang terdaftar</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm border-b-4 border-rose-500">
            <span class="text-[10px] font-black uppercase tracking-wider text-rose-600">NIK Kosong / &lt; 16 Digit</span>
            <div class="text-2xl font-black text-rose-700 mt-1"><?= number_format($bpjsSummary['nik_kosong'] ?? 0) ?></div>
            <div class="text-[10px] text-rose-600/70 mt-0.5">Perlu dilengkapi klub</div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-1.5 w-full lg:w-auto overflow-x-auto pb-1 lg:pb-0">
            <span class="text-[10px] font-black uppercase text-slate-400 mr-1 whitespace-nowrap">Filter BPJS:</span>
            <a href="?tab=bpjs&search=<?= urlencode($search ?? '') ?>&bpjs=all&nik=<?= urlencode($nikFilter ?? 'all') ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'all') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Semua
            </a>
            <a href="?tab=bpjs&search=<?= urlencode($search ?? '') ?>&bpjs=sudah&nik=<?= urlencode($nikFilter ?? 'all') ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'sudah') ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' ?>">
                ✓ Sudah Terdaftar
            </a>
            <a href="?tab=bpjs&search=<?= urlencode($search ?? '') ?>&bpjs=belum&nik=<?= urlencode($nikFilter ?? 'all') ?>"
               class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition <?= ($bpjsFilter === 'belum') ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' ?>">
                ⚠️ Belum Terdaftar
            </a>
        </div>

        <form method="GET" class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
            <input type="hidden" name="tab" value="bpjs">
            <input type="hidden" name="bpjs" value="<?= htmlspecialchars($bpjsFilter ?? 'all') ?>">

            <select name="nik" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs font-bold rounded-xl px-3 py-2 text-slate-700 outline-none focus:border-blue-500 w-full sm:w-auto">
                <option value="all" <?= ($nikFilter === 'all') ? 'selected' : '' ?>>Semua Status NIK</option>
                <option value="kosong" <?= ($nikFilter === 'kosong') ? 'selected' : '' ?>>⚠️ NIK Kosong / &lt; 16 Digit</option>
                <option value="lengkap" <?= ($nikFilter === 'lengkap') ? 'selected' : '' ?>>✓ NIK Lengkap (16 Digit)</option>
            </select>

            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                   class="px-4 py-2 text-xs font-bold border border-slate-200 bg-slate-50 rounded-xl w-full sm:w-64 focus:bg-white focus:border-blue-500 outline-none"
                   placeholder="Cari Nama, NIK, BIB, Klub...">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-xl transition w-full sm:w-auto">Cari</button>
        </form>
    </div>

    <!-- Tabel Keseluruhan Peserta & BPJS (Read-only) -->
    <div class="bpjs-table-card bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="py-4 px-5 text-center w-14">No</th>
                        <th class="py-4 px-4 text-center w-24">BIB</th>
                        <th class="py-4 px-4">Nama Atlet</th>
                        <th class="py-4 px-4">NIK</th>
                        <th class="py-4 px-4">Klub / Kontingen</th>
                        <th class="py-4 px-4 text-center">Status BPJS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($bpjsParticipants)): ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400 font-bold italic">
                                Tidak ada peserta lunas yang cocok dengan filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bpjsParticipants as $idx => $p):
                            $nikStr = trim((string)($p['nik'] ?? ''));
                            $isNikValid = ($nikStr !== '' && strlen($nikStr) >= 16);
                            $isGenderMale = in_array(strtoupper($p['gender'] ?? ''), ['M', 'L', 'PUTRA', 'MALE']);
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-5 text-center font-bold text-slate-400 text-xs"><?= $idx + 1 ?></td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2 py-1 bg-amber-100 text-amber-800 font-black rounded-lg text-xs border border-amber-200 min-w-[3rem]">
                                    <?= htmlspecialchars($p['bib_number'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($p['skater_name'] ?? '-') ?></div>
                                <div class="text-[10px] text-slate-400 font-bold mt-0.5">
                                    <?= $isGenderMale ? 'Putra' : 'Putri' ?>
                                    <?php if (!empty($p['birth_date'])): ?>
                                        &bull; <?= date('d/m/Y', strtotime($p['birth_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs">
                                <?php if ($isNikValid): ?>
                                    <span class="font-bold text-slate-700"><?= htmlspecialchars($nikStr) ?></span>
                                <?php elseif ($nikStr !== ''): ?>
                                    <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded font-bold"><?= htmlspecialchars($nikStr) ?> (&lt; 16 digit)</span>
                                <?php else: ?>
                                    <span class="bg-rose-50 text-rose-600 border border-rose-200 px-2 py-0.5 rounded text-[10px] font-bold">⚠️ Belum Ada NIK</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-bold text-slate-600 uppercase">
                                <?= htmlspecialchars($p['club_name'] ?? '-') ?>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <?php if ($p['status_bpjs'] === 'sudah'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                        <span>✓</span> Sudah Terdaftar
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-[10px] font-black uppercase">
                                        Belum Terdaftar
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="max-w-[95%] mx-auto space-y-8">
    <?php if(empty($grouped)): ?>
        <div class="bg-white rounded-[2rem] p-12 text-center border border-slate-200 shadow-sm">
            <div class="text-5xl mb-4 grayscale opacity-50">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Peserta Terdaftar</h3>
        </div>
    <?php else: ?>
        
        <?php 
            $orderCats = ['Speed', 'Standart', 'Pemula', 'Lainnya'];
            foreach ($orderCats as $cat):
                if (!isset($grouped[$cat])) continue;
        ?>
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden mb-8">
            <!-- Header Kategori -->
            <div class="bg-slate-800 p-6 text-white text-center border-b-4 border-blue-500">
                <h2 class="text-2xl font-black uppercase tracking-widest italic">Kategori <?= htmlspecialchars($cat) ?></h2>
            </div>
            
            <div class="p-6 space-y-8">
                <?php 
                    ksort($grouped[$cat]);
                    foreach ($grouped[$cat] as $ku => $genders): 
                ?>
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        <div class="bg-slate-100 py-3 px-6 border-b border-slate-200">
                            <h3 class="font-black text-slate-700 uppercase tracking-widest">KU: <?= htmlspecialchars($ku) ?></h3>
                        </div>
                        
                        <div class="p-0">
                            <?php 
                                // Order Gender: Putri then Putra
                                $orderGenders = ['Putri', 'Putra'];
                                foreach ($orderGenders as $gender):
                                    if (!isset($genders[$gender])) continue;
                            ?>
                                <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full <?= $gender == 'Putra' ? 'bg-blue-500' : 'bg-pink-500' ?>"></span>
                                    <h4 class="font-black <?= $gender == 'Putra' ? 'text-blue-600' : 'text-pink-600' ?> uppercase text-sm tracking-widest"><?= $gender ?></h4>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-white border-b border-slate-100">
                                            <tr>
                                                <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-12 text-center">No</th>
                                                <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-24 text-center">BIB</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Atlet</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Klub / Kontingen</th>
                                                <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kelas / Nomor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <?php foreach ($genders[$gender] as $i => $s): ?>
                                            <tr class="hover:bg-slate-50 transition">
                                                <td class="py-3 px-6 font-bold text-slate-400 text-center text-xs"><?= $i + 1 ?></td>
                                                <td class="py-3 px-6 text-center">
                                                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-800 font-black rounded-lg text-xs border border-amber-200 min-w-[3rem]">
                                                        <?= htmlspecialchars($s['bib_number'] ?? '-') ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-800 text-sm"><?= htmlspecialchars($s['skater_name']) ?></td>
                                                <td class="py-3 px-4 text-xs font-semibold text-slate-600">
                                                    <div class="flex items-center gap-2"><span class="text-slate-400">🏢</span> <?= htmlspecialchars($s['club_name'] ?? '-') ?></div>
                                                </td>
                                                <td class="py-3 px-4 text-xs font-semibold text-blue-600">
                                                    <div class="flex items-center gap-2"><span class="text-blue-300">🏷️</span> <?= htmlspecialchars($s['distances'] ?: ($s['class_name'] ?? '-')) ?></div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
(function () {
    const printBtn = document.getElementById('btnPrintSkaters');
    const cbs = document.querySelectorAll('.print-cfg-cb');
    if (!printBtn || !cbs.length) return;

    function updatePrintHref() {
        const url = new URL(printBtn.getAttribute('href'), window.location.origin);
        url.searchParams.set('cfg', '1');
        cbs.forEach(function (cb) {
            if (cb.checked) {
                url.searchParams.set(cb.name, '1');
            } else {
                url.searchParams.delete(cb.name);
            }
        });
        printBtn.setAttribute('href', url.toString());
    }

    cbs.forEach(function (cb) {
        cb.addEventListener('change', updatePrintHref);
    });
    updatePrintHref();
})();
</script>
