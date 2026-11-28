<?php
$statusFilter = $statusFilter ?? 'all';
$typeFilter   = $typeFilter ?? 'all';
$search       = $search ?? '';
$totals       = $totals ?? ['paid' => 0, 'pending' => 0, 'unpaid' => 0, 'rejected' => 0, 'potensi' => 0, 'count_paid' => 0, 'count_pending' => 0, 'count_unpaid' => 0, 'count_rejected' => 0];
$transactions = $transactions ?? [];
$zipItems     = $zipItems ?? (class_exists(\App\Roll\Controllers\Admin\RollAdminReportController::class)
    ? \App\Roll\Controllers\Admin\RollAdminReportController::buildInvoiceZipItems($transactions)
    : []);
$filterQuery  = http_build_query(['status' => $statusFilter, 'type' => $typeFilter, 'search' => $search]);
$appBasePath  = rtrim((string)(parse_url((string)getenv('APP_URL'), PHP_URL_PATH) ?: ''), '/');
$safeEventZip = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($event['event_name'] ?? 'Event'));
$zipFilename  = 'Invoice_Klub_' . trim((string)$safeEventZip, '_') . '_' . date('Ymd_His') . '.zip';

$filteredSum = 0.0;
$filteredSkaters = 0;
foreach ($transactions as $tx) {
    $filteredSum += (float)($tx['payment_amount'] ?? 0);
    $filteredSkaters += (int)($tx['skater_count'] ?? 0);
}
?>

<div class="max-w-[95%] mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= getenv('APP_URL') ?>/roll/admin/reports?<?= $filterQuery ?>"
               class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm font-bold"
               title="Kembali ke Laporan Keuangan">←</a>
            <div>
                <h1 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter">Ringkasan Detail Invoice per Klub</h1>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                    <?= htmlspecialchars($event['event_name'] ?? 'Event Aktif') ?>
                    <?php if (!empty($event['event_date_start']) && $event['event_date_start'] !== '0000-00-00'): ?>
                        • 📅 <?= date('d M Y', strtotime($event['event_date_start'])) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= getenv('APP_URL') ?>/roll/admin/reports?<?= $filterQuery ?>"
               class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm">
                📊 Kembali ke Laporan
            </a>
            <button type="button" id="btnDownloadAllInvoicesZip"
                    <?= empty($zipItems) ? 'disabled' : '' ?>
                    class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <span>🗜️</span> Download ZIP Invoice (<?= count($zipItems) ?>)
            </button>
            <a href="<?= getenv('APP_URL') ?>/roll/admin/entries"
               class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wide transition shadow-sm">
                Verifikasi Pendaftaran
            </a>
        </div>
    </div>

    <!-- Filter & Ringkasan Singkat -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
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
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama klub / kode invoice..."
                   class="px-3.5 py-2 text-xs font-bold border border-slate-200 bg-slate-50 rounded-xl w-full sm:w-64 focus:bg-white focus:border-blue-500 outline-none">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-xl transition cursor-pointer">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah Invoice Ditampilkan</p>
            <p class="text-xl font-black text-slate-900 mt-1"><?= count($transactions) ?> <span class="text-xs font-bold text-slate-500">Klub / Invoice</span></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Atlet Tercakup</p>
            <p class="text-xl font-black text-slate-900 mt-1"><?= number_format($filteredSkaters) ?> <span class="text-xs font-bold text-slate-500">Atlet</span></p>
        </div>
        <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 shadow-sm text-white">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Nominal (Sesuai Filter)</p>
            <p class="text-xl font-black text-emerald-400 mt-1">Rp <?= number_format($filteredSum, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- List Kartu Ringkasan Invoice per Klub -->
    <?php if (empty($transactions)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400 font-bold italic">
            Tidak ada invoice klub yang sesuai dengan filter.
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($transactions as $t): ?>
                <?php
                    $amt = (float)($t['payment_amount'] ?? 0);
                    $storedAmt = (float)($t['stored_amount'] ?? 0);
                    $st = (string)($t['status'] ?? 'Unpaid');
                    $stLower = strtolower($st);
                    $statusColor = match ($stLower) {
                        'paid'     => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
                        'rejected' => 'bg-red-100 text-red-700 border-red-200',
                        default    => 'bg-slate-100 text-slate-600 border-slate-200',
                    };
                    $hasStoredApproved = in_array($stLower, ['paid', 'pending'], true) && $storedAmt > 0;
                    $selisih = $amt - $storedAmt;
                    $pkgSummary = $t['package_summary'] ?? [];
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Header Invoice Klub & Tombol Aksi -->
                    <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if (($t['type'] ?? '') === 'Klub'): ?>
                                    <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">KLUB REGULER</span>
                                <?php else: ?>
                                    <span class="bg-violet-100 text-violet-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">TOKEN / MANUAL</span>
                                <?php endif; ?>
                                <span class="text-[11px] font-mono font-bold text-slate-500"><?= htmlspecialchars($t['identifier'] ?? '-') ?></span>
                                <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest rounded-full border <?= $statusColor ?>">
                                    <?= htmlspecialchars($st) ?>
                                </span>
                                <?php if (!empty($t['created_at'])): ?>
                                    <span class="text-[11px] text-slate-400 font-medium">• <?= date('d M Y H:i', strtotime($t['created_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <h2 class="text-base font-black text-slate-900 uppercase tracking-tight">
                                <?= htmlspecialchars($t['club_name'] ?? '-') ?>
                            </h2>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 font-medium">
                                <span><strong><?= (int)($t['skater_count'] ?? 0) ?></strong> Atlet • <strong><?= (int)($t['entry_count'] ?? 0) ?></strong> Entri</span>
                                <?php if (!empty($t['email'])): ?>
                                    <span class="text-slate-400">• <?= htmlspecialchars($t['email']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 lg:justify-end">
                            <div class="sm:text-right">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tagihan</div>
                                <div class="text-lg font-black text-slate-900 whitespace-nowrap">Rp <?= number_format($amt, 0, ',', '.') ?></div>
                                <?php if ($hasStoredApproved && abs($selisih) > 0.01): ?>
                                    <div class="text-[10px] font-bold <?= $selisih > 0 ? 'text-rose-600' : 'text-blue-600' ?>">
                                        Approved: Rp <?= number_format($storedAmt, 0, ',', '.') ?>
                                        (<?= $selisih > 0 ? '+ Rp ' . number_format($selisih, 0, ',', '.') : '- Rp ' . number_format(abs($selisih), 0, ',', '.') ?>)
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <?php if (!empty($t['payment_proof'])): ?>
                                    <a href="<?= getenv('APP_URL') ?>/uploads/payments/<?= htmlspecialchars($t['payment_proof']) ?>" target="_blank"
                                       class="px-3 py-2 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 text-[11px] font-bold uppercase transition">
                                        Bukti
                                    </a>
                                <?php endif; ?>
                                <a href="<?= getenv('APP_URL') . ($t['detail_url'] ?? '/roll/admin/entries') ?>"
                                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-black uppercase transition shadow-sm">
                                    Lihat Detail
                                </a>
                                <a href="<?= getenv('APP_URL') . ($t['print_invoice_url'] ?? ($t['detail_url'] ?? '/roll/admin/entries')) ?>" target="_blank"
                                   class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-black uppercase transition shadow-sm flex items-center gap-1.5">
                                    <span>🖨️</span> Cetak Invoice / Tanda Terima
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Paket Invoice Klub -->
                    <div class="p-4">
                        <?php if (empty($pkgSummary)): ?>
                            <div class="text-xs text-slate-400 italic font-medium">
                                Belum ada rincian entri lomba aktif pada invoice ini<?= $amt > 0 ? ' (tagihan tersimpan: Rp ' . number_format($amt, 0, ',', '.') . ').' : '.' ?>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2">Kategori</th>
                                            <th class="px-3 py-2">Ringkasan Paket Pendaftaran</th>
                                            <th class="px-3 py-2 text-center w-28">Jml Atlet</th>
                                            <th class="px-3 py-2 text-right w-40">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach ($pkgSummary as $ps): ?>
                                            <?php
                                            $catBadge = match ($ps['cat_group'] ?? 'Lainnya') {
                                                'Pemula'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'Standart' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'Speed'    => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
                                                default    => 'bg-slate-100 text-slate-700 border-slate-200',
                                            };
                                            ?>
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <span class="px-2 py-0.5 rounded border text-[9px] font-black uppercase <?= $catBadge ?>">
                                                        <?= htmlspecialchars($ps['cat_group'] ?? 'Lainnya') ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 font-bold text-slate-800"><?= htmlspecialchars($ps['package_label'] ?? '-') ?></td>
                                                <td class="px-3 py-2 text-center font-bold"><?= number_format((int)($ps['skaters'] ?? 0)) ?> atlet</td>
                                                <td class="px-3 py-2 text-right font-black text-slate-900 whitespace-nowrap">Rp <?= number_format((float)($ps['total_amount'] ?? 0), 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Progress Download ZIP Invoice -->
<div id="zipInvoiceModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Menyiapkan ZIP Invoice PDF</h3>
            <span id="zipInvoiceCounter" class="text-xs font-black text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">0 / 0</span>
        </div>
        <p id="zipInvoiceStatus" class="text-xs font-bold text-slate-600 truncate">Memulai proses...</p>
        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
            <div id="zipInvoiceProgressBar" class="h-full bg-indigo-600 transition-all duration-200" style="width: 0%;"></div>
        </div>
        <div class="flex justify-end pt-1">
            <button type="button" id="btnCancelZipInvoice"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold uppercase cursor-pointer">
                Batal
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnDownload = document.getElementById('btnDownloadAllInvoicesZip');
    const modal = document.getElementById('zipInvoiceModal');
    const counterEl = document.getElementById('zipInvoiceCounter');
    const statusEl = document.getElementById('zipInvoiceStatus');
    const progressBar = document.getElementById('zipInvoiceProgressBar');
    const btnCancel = document.getElementById('btnCancelZipInvoice');
    if (!btnDownload) return;

    const zipItems = <?= json_encode($zipItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const appBasePath = <?= json_encode($appBasePath, JSON_UNESCAPED_SLASHES) ?>;
    const appConfiguredOrigin = <?= json_encode((string)(parse_url((string)getenv('APP_URL'), PHP_URL_SCHEME) ? (parse_url((string)getenv('APP_URL'), PHP_URL_SCHEME) . '://' . parse_url((string)getenv('APP_URL'), PHP_URL_HOST) . (parse_url((string)getenv('APP_URL'), PHP_URL_PORT) ? ':' . parse_url((string)getenv('APP_URL'), PHP_URL_PORT) : '')) : ''), JSON_UNESCAPED_SLASHES) ?>;
    const zipFilename = <?= json_encode($zipFilename, JSON_UNESCAPED_UNICODE) ?>;

    let cancelled = false;
    btnCancel.addEventListener('click', function () {
        cancelled = true;
        statusEl.textContent = 'Membatalkan...';
    });

    function loadIntoIframe(iframe, url) {
        return new Promise((resolve, reject) => {
            let done = false;
            const timer = setTimeout(() => {
                if (!done) {
                    done = true;
                    reject(new Error('Timeout memuat halaman invoice'));
                }
            }, 15000);

            iframe.onload = () => {
                if (done) return;
                done = true;
                clearTimeout(timer);
                resolve();
            };
            iframe.onerror = () => {
                if (done) return;
                done = true;
                clearTimeout(timer);
                reject(new Error('Gagal memuat halaman invoice'));
            };
            iframe.src = url;
        });
    }

    async function waitDocumentReady(doc) {
        if (appConfiguredOrigin && appConfiguredOrigin !== window.location.origin) {
            doc.querySelectorAll('img[src]').forEach((img) => {
                if (img.src.startsWith(appConfiguredOrigin)) {
                    img.src = window.location.origin + img.src.slice(appConfiguredOrigin.length);
                }
            });
        }
        const imgs = Array.from(doc.images || []);
        const imgPromises = imgs.map((img) => {
            if (img.complete) return Promise.resolve();
            return new Promise((res) => {
                img.addEventListener('load', res, { once: true });
                img.addEventListener('error', res, { once: true });
            });
        });
        await Promise.race([
            Promise.all(imgPromises),
            new Promise((res) => setTimeout(res, 5000))
        ]);
        if (doc.fonts && doc.fonts.ready) {
            await Promise.race([doc.fonts.ready, new Promise((res) => setTimeout(res, 1500))]);
        }
        await new Promise((res) => requestAnimationFrame(() => requestAnimationFrame(res)));
    }

    function computePageSlices(sheet, sheetW, totalH) {
        const fullPagePx = sheetW * (297 / 210);
        if (totalH <= fullPagePx + 6) {
            return [{ start: 0, end: totalH, marginTopMm: 0 }];
        }

        const sheetTop = sheet.getBoundingClientRect().top;
        const blocks = [];
        sheet.querySelectorAll('tr, .invoice-box, .kop-surat, .sponsor-footer, p').forEach((el) => {
            const r = el.getBoundingClientRect();
            if (r.height > 0 && r.height < fullPagePx * 0.85) {
                blocks.push({ top: r.top - sheetTop, bottom: r.bottom - sheetTop });
            }
        });

        const firstPagePx = sheetW * (289 / 210);
        const nextPagePx = sheetW * (281 / 210);
        const slices = [];
        let cursor = 0;

        while (cursor < totalH - 2) {
            const isFirst = slices.length === 0;
            const maxSpan = isFirst ? firstPagePx : nextPagePx;
            let end = Math.min(totalH, cursor + maxSpan);

            if (end < totalH - 2) {
                for (let i = 0; i < blocks.length; i++) {
                    const b = blocks[i];
                    if (b.top < end && b.bottom > end && (b.top - cursor) >= 80) {
                        end = Math.min(end, b.top - 2);
                    }
                }
            }
            if (end <= cursor + 20) {
                end = Math.min(totalH, cursor + maxSpan);
            }
            slices.push({ start: cursor, end: end, marginTopMm: isFirst ? 0 : 8 });
            cursor = end;
        }
        return slices;
    }

    async function renderInvoicePdfBuffer(iframe, item) {
        const targetUrl = window.location.origin + appBasePath + item.print_url;
        await loadIntoIframe(iframe, targetUrl);
        const doc = iframe.contentDocument;
        if (!doc) throw new Error('Tidak dapat mengakses dokumen invoice');

        doc.querySelectorAll('.btn-print, .btn-close').forEach((el) => { el.style.display = 'none'; });
        const sheet = doc.querySelector('.sheet');
        if (!sheet) throw new Error('Template invoice tidak ditemukan');

        doc.body.style.background = '#ffffff';
        sheet.style.margin = '0';
        sheet.style.boxShadow = 'none';

        await waitDocumentReady(doc);

        const sheetW = sheet.offsetWidth || 794;
        const totalH = Math.max(sheet.scrollHeight, sheet.offsetHeight);
        const slices = computePageSlices(sheet, sheetW, totalH);
        const scale = totalH * 2 > 14000 ? 1.5 : 2;

        const fullCanvas = await window.html2canvas(sheet, {
            scale: scale,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            width: sheetW,
            height: totalH,
            windowWidth: sheetW
        });

        const pdf = new window.jspdf.jsPDF({ orientation: 'p', unit: 'mm', format: 'a4', compress: true });
        const sliceCanvas = document.createElement('canvas');
        const sliceCtx = sliceCanvas.getContext('2d');

        slices.forEach((sl, idx) => {
            if (idx > 0) pdf.addPage('a4', 'p');
            const srcY = Math.max(0, Math.round(sl.start * scale));
            const srcH = Math.max(1, Math.min(fullCanvas.height - srcY, Math.round((sl.end - sl.start) * scale)));
            sliceCanvas.width = fullCanvas.width;
            sliceCanvas.height = srcH;
            sliceCtx.fillStyle = '#ffffff';
            sliceCtx.fillRect(0, 0, sliceCanvas.width, sliceCanvas.height);
            sliceCtx.drawImage(fullCanvas, 0, srcY, fullCanvas.width, srcH, 0, 0, fullCanvas.width, srcH);

            const imgData = sliceCanvas.toDataURL('image/jpeg', 0.92);
            const hMm = Math.min(297 - sl.marginTopMm, 210 * ((sl.end - sl.start) / sheetW));
            pdf.addImage(imgData, 'JPEG', 0, sl.marginTopMm, 210, hMm, undefined, 'FAST');
        });

        fullCanvas.width = 0;
        fullCanvas.height = 0;
        sliceCanvas.width = 0;
        sliceCanvas.height = 0;

        return pdf.output('arraybuffer');
    }

    btnDownload.addEventListener('click', async function () {
        if (!zipItems.length) return;
        if (!window.JSZip || !window.html2canvas || !window.jspdf || !window.jspdf.jsPDF) {
            alert('Library PDF/ZIP belum termuat. Pastikan koneksi internet aktif lalu muat ulang halaman.');
            return;
        }

        cancelled = false;
        btnDownload.disabled = true;
        modal.classList.remove('hidden');

        const zip = new window.JSZip();
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;left:-99999px;top:0;width:850px;height:1200px;border:0;visibility:hidden;';
        document.body.appendChild(iframe);

        const failed = [];
        let added = 0;

        try {
            for (let i = 0; i < zipItems.length; i++) {
                if (cancelled) break;
                const item = zipItems[i];
                counterEl.textContent = (i + 1) + ' / ' + zipItems.length;
                statusEl.textContent = 'Memproses: ' + item.filename;
                progressBar.style.width = Math.round(((i) / zipItems.length) * 100) + '%';

                try {
                    const pdfBuffer = await renderInvoicePdfBuffer(iframe, item);
                    zip.file(item.filename, pdfBuffer);
                    added++;
                } catch (err) {
                    console.error('Gagal render invoice:', item.club_name, err);
                    failed.push(item.club_name);
                }
                progressBar.style.width = Math.round(((i + 1) / zipItems.length) * 100) + '%';
            }

            if (!cancelled && added > 0) {
                statusEl.textContent = 'Mengemas file ZIP (' + added + ' PDF)...';
                const zipBlob = await zip.generateAsync({ type: 'blob' });
                const dlUrl = URL.createObjectURL(zipBlob);
                const a = document.createElement('a');
                a.href = dlUrl;
                a.download = zipFilename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(dlUrl), 5000);
            }

            if (!cancelled && failed.length > 0) {
                alert('Sebagian invoice gagal diproses (' + failed.length + '): ' + failed.join(', '));
            }
        } finally {
            iframe.remove();
            modal.classList.add('hidden');
            btnDownload.disabled = false;
        }
    });
});
</script>
