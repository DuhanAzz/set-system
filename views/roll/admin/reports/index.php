<?php
$statusFilter = $statusFilter ?? 'all';
$typeFilter   = $typeFilter ?? 'all';
$search       = $search ?? '';
$totals       = $totals ?? ['paid' => 0, 'paid_klub' => 0, 'paid_token' => 0, 'pending' => 0, 'unpaid' => 0, 'rejected' => 0, 'potensi' => 0, 'count_paid' => 0, 'count_pending' => 0, 'count_unpaid' => 0, 'count_rejected' => 0];
$categories   = $categories ?? [];
$exportQuery  = http_build_query(['status' => $statusFilter, 'type' => $typeFilter, 'search' => $search]);
?>

<div class="max-w-[95%] mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter">Laporan Keuangan Event</h1>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                <?= htmlspecialchars($event['event_name'] ?? 'Event Aktif') ?>
                <?php if (!empty($event['event_date_start']) && $event['event_date_start'] !== '0000-00-00'): ?>
                    • 📅 <?= date('d M Y', strtotime($event['event_date_start'])) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 print:hidden">
            <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm">
                Verifikasi Pendaftaran
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/export_excel?<?= $exportQuery ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel / CSV
            </a>
            <button type="button" onclick="document.getElementById('printReportCfgPanel').classList.toggle('hidden')"
                    class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <span>⚙️</span> Atur Print
            </button>
            <a id="btnPrintReport"
               href="<?= getenv('APP_URL') ?>/roll/admin/reports/print_data?<?= $exportQuery ?>&cfg=1&show_summary=1&show_categories=1&show_date=1&show_type=1&show_counts=1&show_status=1"
               target="_blank"
               class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-2">
                <span>🖨️</span> Cetak Dokumen A4
            </a>
        </div>
    </div>

    <!-- Panel Pengaturan Kolom & Bagian Cetak (Checkbox) -->
    <div id="printReportCfgPanel" class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-4 print:hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600">⚙️ Pengaturan Tampilan Cetak A4</p>
                <p class="text-[11px] text-slate-500 font-medium">Pilih bagian & kolom yang ingin ditampilkan saat mencetak Laporan Keuangan (bisa juga diubah langsung di preview cetak).</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 text-xs font-bold text-slate-700">
                <label class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-indigo-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_summary" checked>
                    <span>Ringkasan Keuangan</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-indigo-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_categories" checked>
                    <span>Rekap Kategori</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_date" checked>
                    <span>Tanggal</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_type" checked>
                    <span>Jalur & Invoice</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_counts" checked>
                    <span>Atlet / Entri</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_status" checked>
                    <span>Status</span>
                </label>
                <label class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="checkbox" class="print-report-cb rounded text-indigo-600" name="show_proof">
                    <span>Bukti Bayar</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 shadow-md text-white border-b-4 border-emerald-900">
            <p class="font-bold text-emerald-100 uppercase text-[10px] tracking-widest">Total Pemasukan (Lunas)</p>
            <p class="text-2xl font-black mt-1">Rp <?= number_format($totals['paid'], 0, ',', '.') ?></p>
            <p class="text-[10px] text-emerald-100 mt-1.5 font-semibold">
                <?= $totals['count_paid'] ?> Transaksi • Klub Rp <?= number_format($totals['paid_klub'], 0, ',', '.') ?> | Token Rp <?= number_format($totals['paid_token'], 0, ',', '.') ?>
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 border-b-4 border-amber-500">
            <p class="font-black text-amber-600 uppercase text-[10px] tracking-widest">Menunggu Verifikasi (Pending)</p>
            <p class="text-2xl font-black text-slate-800 mt-1">Rp <?= number_format($totals['pending'], 0, ',', '.') ?></p>
            <p class="text-[10px] text-slate-500 mt-1.5 font-bold"><?= $totals['count_pending'] ?> Transaksi sudah upload bukti</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 border-b-4 border-rose-500">
            <p class="font-black text-rose-600 uppercase text-[10px] tracking-widest">Belum Bayar (Unpaid)</p>
            <p class="text-2xl font-black text-slate-800 mt-1">Rp <?= number_format($totals['unpaid'], 0, ',', '.') ?></p>
            <p class="text-[10px] text-slate-500 mt-1.5 font-bold"><?= $totals['count_unpaid'] ?> Tagihan aktif belum dibayar</p>
        </div>

        <div class="bg-slate-800 rounded-2xl p-5 shadow-md text-white border-b-4 border-blue-500">
            <p class="font-bold text-slate-300 uppercase text-[10px] tracking-widest">Potensi Total Event</p>
            <p class="text-2xl font-black mt-1">Rp <?= number_format($totals['potensi'], 0, ',', '.') ?></p>
            <p class="text-[10px] text-slate-300 mt-1.5 font-semibold">Gabungan Lunas + Pending + Unpaid</p>
        </div>
    </div>

    <!-- Category Breakdown (Paid) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Rekap Pemasukan Lunas (Paid) per Kategori</h3>
            <span class="text-[10px] font-bold text-slate-400">Dihitung dinamis sesuai tarif paket dasar & nomor tambahan event</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php
            $catCards = [
                'Speed' => ['label' => 'Speed', 'style' => 'bg-fuchsia-50 border-fuchsia-200 text-fuchsia-800'],
                'Standart' => ['label' => 'Standar', 'style' => 'bg-amber-50 border-amber-200 text-amber-800'],
                'Pemula' => ['label' => 'Pemula', 'style' => 'bg-emerald-50 border-emerald-200 text-emerald-800'],
                'Lainnya' => ['label' => 'Eksebisi / Lainnya', 'style' => 'bg-slate-50 border-slate-200 text-slate-700'],
            ];
            foreach ($catCards as $key => $cfg):
                $cData = $categories[$key] ?? ['skaters' => 0, 'extra_count' => 0, 'total' => 0];
            ?>
            <div class="rounded-xl border p-3.5 <?= $cfg['style'] ?>">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-wider"><?= $cfg['label'] ?></span>
                    <span class="text-[10px] font-bold"><?= (int)($cData['skaters'] ?? 0) ?> Atlet</span>
                </div>
                <div class="text-base font-black mt-1">Rp <?= number_format((float)($cData['total'] ?? 0), 0, ',', '.') ?></div>
                <?php if (!empty($cData['extra_count'])): ?>
                    <div class="text-[9px] font-bold opacity-80 mt-0.5">Termasuk <?= (int)$cData['extra_count'] ?> nomor tambahan</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 print:hidden">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] font-black uppercase text-slate-400 mr-1">Status:</span>
            <?php
            $statuses = [
                'all' => 'Semua (' . ($allCount ?? count($transactions)) . ')',
                'Paid' => 'Paid (' . $totals['count_paid'] . ')',
                'Pending' => 'Pending (' . $totals['count_pending'] . ')',
                'Unpaid' => 'Unpaid (' . $totals['count_unpaid'] . ')',
                'Rejected' => 'Rejected (' . $totals['count_rejected'] . ')',
            ];
            foreach ($statuses as $stKey => $stLbl):
                $isActive = (strcasecmp($statusFilter, $stKey) === 0);
            ?>
                <a href="?status=<?= urlencode($stKey) ?>&type=<?= urlencode($typeFilter) ?>&search=<?= urlencode($search) ?>"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $isActive ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= htmlspecialchars($stLbl) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full lg:w-auto">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <select name="type" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs font-bold rounded-xl px-3 py-2 text-slate-700 outline-none focus:border-blue-500">
                <option value="all" <?= $typeFilter === 'all' ? 'selected' : '' ?>>Semua Jalur</option>
                <option value="Klub" <?= $typeFilter === 'Klub' ? 'selected' : '' ?>>Klub Reguler</option>
                <option value="Token" <?= $typeFilter === 'Token' ? 'selected' : '' ?>>Token / Manual</option>
            </select>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama klub / invoice..."
                   class="px-3.5 py-2 text-xs font-bold border border-slate-200 bg-slate-50 rounded-xl w-full sm:w-64 focus:bg-white focus:border-blue-500 outline-none">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-xl transition">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden report-table-card">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-black text-slate-800 uppercase tracking-wider text-xs">Rincian Tagihan & Pembayaran Per Klub / Token</h3>
            <span class="text-[11px] font-bold text-slate-500">Menampilkan <?= count($transactions) ?> data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Jalur & Identitas</th>
                        <th class="px-5 py-3.5 text-center">Atlet / Entri</th>
                        <th class="px-5 py-3.5 text-right">Nominal Tagihan</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center print:hidden">Bukti & Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-bold italic">Tidak ada data transaksi yang sesuai filter.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $filteredSum = 0;
                        foreach ($transactions as $t):
                            $amt = (float)($t['payment_amount'] ?? 0);
                            $filteredSum += $amt;
                            $stLower = strtolower((string)($t['status'] ?? 'unpaid'));
                            $statusColor = match($stLower) {
                                'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 text-xs whitespace-nowrap font-medium">
                                <?php if (!empty($t['created_at'])): ?>
                                    <div><?= date('d M Y', strtotime($t['created_at'])) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('H:i', strtotime($t['created_at'])) ?></div>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4">
                                <?php if (($t['type'] ?? '') === 'Klub'): ?>
                                    <span class="inline-block bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider mb-1">KLUB REGULER</span>
                                <?php else: ?>
                                    <span class="inline-block bg-violet-100 text-violet-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider mb-1">TOKEN / MANUAL</span>
                                <?php endif; ?>
                                <div class="font-black text-slate-800 text-xs uppercase"><?= htmlspecialchars($t['club_name'] ?? '-') ?></div>
                                <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($t['identifier'] ?? '-') ?></div>
                            </td>
                            <td class="px-5 py-4 text-center text-xs">
                                <span class="font-black text-slate-800"><?= (int)($t['skater_count'] ?? 0) ?></span> Atlet
                                <div class="text-[10px] text-slate-400"><?= (int)($t['entry_count'] ?? 0) ?> Entri</div>
                            </td>
                            <td class="px-5 py-4 text-right font-black text-slate-800 whitespace-nowrap">
                                Rp <?= number_format($amt, 0, ',', '.') ?>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full border <?= $statusColor ?>">
                                    <?= htmlspecialchars($t['status'] ?? 'Unpaid') ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center print:hidden">
                                <div class="flex items-center justify-center gap-1.5">
                                    <?php if (!empty($t['payment_proof'])): ?>
                                        <a href="<?= getenv('APP_URL') ?>/uploads/payments/<?= htmlspecialchars($t['payment_proof']) ?>" target="_blank"
                                           class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 text-[10px] font-bold uppercase transition">
                                            Bukti
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= getenv('APP_URL') . ($t['detail_url'] ?? '/roll/admin/entries') ?>"
                                       class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-black uppercase transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-slate-50 font-black text-slate-800 border-t-2 border-slate-200">
                            <td colspan="3" class="px-5 py-3.5 text-xs uppercase tracking-wider text-right">Total Nominal (Sesuai Filter)</td>
                            <td class="px-5 py-3.5 text-right text-sm text-emerald-700 whitespace-nowrap">Rp <?= number_format($filteredSum, 0, ',', '.') ?></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const printBtn = document.getElementById('btnPrintReport');
    const cbs = document.querySelectorAll('.print-report-cb');
    if (!printBtn || !cbs.length) return;

    function updateReportPrintHref() {
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
        cb.addEventListener('change', updateReportPrintHref);
    });
    updateReportPrintHref();
})();
</script>
