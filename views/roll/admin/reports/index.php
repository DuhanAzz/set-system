<?php
$statusFilter     = $statusFilter ?? 'all';
$typeFilter       = $typeFilter ?? 'all';
$search           = $search ?? '';
$totals           = $totals ?? ['paid' => 0, 'paid_klub' => 0, 'paid_token' => 0, 'pending' => 0, 'unpaid' => 0, 'rejected' => 0, 'potensi' => 0, 'count_paid' => 0, 'count_pending' => 0, 'count_unpaid' => 0, 'count_rejected' => 0];
$categories       = $categories ?? [];
$packageBreakdown = $packageBreakdown ?? [];
$exportQuery      = http_build_query(['status' => $statusFilter, 'type' => $typeFilter, 'search' => $search]);
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
            <button type="button" id="btnToggleCategoryReport"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <span>📊</span> Cek Laporan per Kategori
            </button>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/invoices?<?= $exportQuery ?>"
               class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-1.5">
                <span>🧾</span> Detail Invoice Klub
            </a>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/export_excel?<?= $exportQuery ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel / CSV
            </a>
            <a id="btnPrintReport"
               href="<?= getenv('APP_URL') ?>/roll/admin/reports/print_data?<?= $exportQuery ?>"
               target="_blank"
               class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-2">
                <span>🖨️</span> Cetak Dokumen A4
            </a>
        </div>
    </div>

    <!-- Panel Rincian Laporan Per Kategori & Paket Pembayaran (Dinamis Sesuai Tarif Event) -->
    <div id="categoryPackageReportPanel" class="hidden bg-white rounded-2xl border-2 border-amber-300 shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span>📊</span> Rincian Pemasukan per Kategori &amp; Paket Pendaftaran
                </h2>
                <p class="text-[11px] text-slate-600 font-medium mt-0.5">
                    Dihitung otomatis sesuai tarif event aktif (Pemula Dasar / Tambahan, Standart Reguler / Tambahan / Mix, Speed Dasar / Tambahan, &amp; Token/Manual).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/export_excel?mode=category"
                   class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black uppercase tracking-wider transition shadow-sm">
                    📥 Export CSV Kategori
                </a>
                <a href="<?= getenv('APP_URL') ?>/roll/admin/reports/print_data?cfg=1&show_summary=1&show_categories=1"
                   target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-black uppercase tracking-wider transition shadow-sm">
                    🖨️ Print Laporan Kategori
                </a>
                <button type="button" onclick="document.getElementById('categoryPackageReportPanel').classList.add('hidden')"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-[11px] font-bold uppercase cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Uraian Kategori / Paket Pendaftaran</th>
                        <th class="px-5 py-3.5 text-center w-36">Jml Atlet (Lunas)</th>
                        <th class="px-5 py-3.5 text-center w-36">Pending / Unpaid</th>
                        <th class="px-5 py-3.5 text-right w-40">Tarif Satuan</th>
                        <th class="px-5 py-3.5 text-right w-48">Total Pemasukan (Paid)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($packageBreakdown)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-bold italic">
                                Belum ada transaksi pendaftaran pada event ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $sumPkgPaidSkaters   = 0;
                        $sumPkgUnpaidSkaters = 0;
                        $sumPkgPaidAmount    = 0.0;
                        foreach ($packageBreakdown as $idx => $pkg):
                            $sumPkgPaidSkaters   += (int)($pkg['paid_skaters'] ?? 0);
                            $sumPkgUnpaidSkaters += (int)($pkg['pending_unpaid_skaters'] ?? 0);
                            $sumPkgPaidAmount    += (float)($pkg['paid_amount'] ?? 0);

                            $badgeClass = match ($pkg['cat_group'] ?? 'Lainnya') {
                                'Pemula'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'Standart' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'Speed'    => 'bg-fuchsia-100 text-fuchsia-800 border-fuchsia-200',
                                default    => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 text-center font-bold text-slate-400 text-xs"><?= $idx + 1 ?></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded border text-[9px] font-black uppercase <?= $badgeClass ?>">
                                        <?= htmlspecialchars($pkg['cat_group'] ?? 'Lainnya') ?>
                                    </span>
                                    <span class="font-bold text-slate-800 text-xs"><?= htmlspecialchars($pkg['label'] ?? '-') ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center font-black text-emerald-700">
                                <?= number_format((int)($pkg['paid_skaters'] ?? 0)) ?> <span class="text-[10px] font-semibold text-slate-400">atlet</span>
                            </td>
                            <td class="px-5 py-3 text-center text-xs font-bold text-amber-600">
                                <?= (int)($pkg['pending_unpaid_skaters'] ?? 0) > 0 ? number_format((int)$pkg['pending_unpaid_skaters']) . ' atlet' : '-' ?>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-xs font-bold text-slate-600 whitespace-nowrap">
                                <?= !empty($pkg['is_adjustment']) ? '-' : ('Rp ' . number_format((float)($pkg['unit_price'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-5 py-3 text-right font-black text-slate-900 whitespace-nowrap">
                                Rp <?= number_format((float)($pkg['paid_amount'] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($packageBreakdown)): ?>
                <tfoot>
                    <tr class="bg-slate-900 text-white font-black">
                        <td colspan="2" class="px-5 py-3.5 text-xs uppercase tracking-wider text-right">Total Pemasukan Lunas (Paid)</td>
                        <td class="px-5 py-3.5 text-center text-xs"><?= number_format($sumPkgPaidSkaters) ?> atlet</td>
                        <td class="px-5 py-3.5 text-center text-xs text-amber-300"><?= $sumPkgUnpaidSkaters > 0 ? number_format($sumPkgUnpaidSkaters) . ' atlet' : '-' ?></td>
                        <td class="px-5 py-3.5"></td>
                        <td class="px-5 py-3.5 text-right text-sm text-emerald-400 whitespace-nowrap">Rp <?= number_format($sumPkgPaidAmount, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
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
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
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
                                    <a href="<?= getenv('APP_URL') . ($t['print_invoice_url'] ?? ($t['detail_url'] ?? '/roll/admin/entries')) ?>" target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-black uppercase transition flex items-center gap-1">
                                        <span>🖨️</span> Cetak Invoice
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
    const btnToggle = document.getElementById('btnToggleCategoryReport');
    const panel = document.getElementById('categoryPackageReportPanel');
    if (!btnToggle || !panel) return;
    btnToggle.addEventListener('click', function () {
        const willOpen = panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        if (willOpen) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
})();
</script>
