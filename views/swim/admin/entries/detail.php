<style>
    /* Styling Card Atlet */
    .card-atlet { 
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        border-radius: 1rem;
        margin-bottom: 1rem;
    }
    .bg-blue-500 { background-color: #3b82f6; color: white; }
    .bg-pink-500 { background-color: #ec4899; color: white; }
    .text-blue-600 { color: #2563eb; }
    .text-pink-600 { color: #db2777; }
</style>

<div class="max-w-7xl mx-auto mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="<?= getenv('APP_URL') ?>/swim/admin/entries/index?event_id=<?= $eventId ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition shadow-sm font-black">←</a>
        <div>
            <h1 class="text-2xl font-black uppercase italic text-slate-900 leading-none">Verifikasi & Tagihan</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">
                Klub: <span class="text-blue-600 font-black"><?= htmlspecialchars($clubName) ?></span>
                <span class="text-slate-300 mx-1.5">|</span> Event: <span class="text-slate-700"><?= htmlspecialchars($eventData['event_name'] ?? 'Event Swimming') ?> (#<?= $eventId ?>)</span>
            </p>
        </div>
    </div>
    
    <a href="<?= getenv('APP_URL') ?>/swim/admin/entries/print?id=<?= $targetUserId ?>&event_id=<?= $eventId ?>" target="_blank" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl text-xs font-bold uppercase shadow-lg hover:bg-slate-800 flex items-center gap-2 hover:translate-y-[-2px] transition-transform">
        🖨️ Cetak PDF A4
    </a>
</div>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 pb-20 main-content">
    
    <!-- PANEL KIRI (STICKY): TAGIHAN & PEMBAYARAN -->
    <div class="space-y-6 h-fit sticky top-24">
        
        <!-- CARD TOTAL TAGIHAN -->
        <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl text-white relative overflow-hidden">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Total Tagihan Seharusnya</p>
            <h2 class="text-3xl font-black text-white tracking-tighter">Rp <?= number_format($totalTagihan, 0, ',', '.') ?></h2>
            
            <div class="mt-3 pt-3 border-t border-slate-800 text-[10px] text-slate-300">
                <?php if (($eventData['pricing_mode'] ?? 'per_item') === 'package'): ?>
                    <span class="text-emerald-400 font-bold">📦 Skema Paket:</span> 
                    Rp <?= number_format((float)($eventData['package_price'] ?? 0), 0, ',', '.') ?> (maks <?= (int)($eventData['package_limit'] ?? 0) ?> nomor)
                    <?php if ((float)($eventData['extra_price'] ?? 0) > 0): ?>
                        + Ekstra Rp <?= number_format((float)($eventData['extra_price'] ?? 0), 0, ',', '.') ?>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-blue-400 font-bold">🏷️ Skema Satuan:</span> 
                    Rp <?= number_format((float)($eventData['price_per_item'] ?? ($eventData['package_price'] ?? 0)), 0, ',', '.') ?> / nomor lomba
                <?php endif; ?>
                <?php 
                $adminRelayRate = (float)($eventData['price_relay'] ?? ($calc['defaultRelayPrice'] ?? 0));
                if ($adminRelayRate > 0 || !empty($relayEntries)): 
                ?>
                    <div class="mt-1 text-indigo-300 font-medium">
                        <span class="text-indigo-400 font-bold">👥 Tarif Estafet:</span>
                        Rp <?= number_format($adminRelayRate, 0, ',', '.') ?> / tim
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-800 flex justify-between items-center text-xs">
                <span class="font-bold text-slate-400">Total Atlet & Nomor</span>
                <span class="font-black bg-blue-600 px-2 py-1 rounded text-white text-[11px]">
                    <?= count($groupedSwimmers) ?> Atlet • <?= $calc['totalIndividual'] ?? count($groupedSwimmers) ?> No <?php if(!empty($relayEntries)): ?> • <?= count($relayEntries) ?> Estafet<?php endif; ?>
                </span>
            </div>
        </div>

        <!-- CARD STATUS & BUKTI BAYAR -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-xl">👤</div>
                <div class="overflow-hidden">
                    <h2 class="text-sm font-black text-slate-800 uppercase leading-tight truncate"><?= htmlspecialchars($namaUser) ?></h2>
                    <p class="text-[10px] font-bold text-slate-400 truncate"><?= htmlspecialchars($emailUser) ?></p>
                </div>
            </div>

            <!-- STATUS BADGE -->
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Status Pembayaran</span>
                <?php 
                    $displayStatus = $payData['status'] ?? 'Unpaid';
                    if ($displayStatus === 'completed') $displayStatus = 'Paid';
                    elseif ($displayStatus === 'pending') $displayStatus = 'Pending';
                    
                    $statusColors = [
                        'Unpaid'   => 'bg-slate-100 text-slate-600 border-slate-200',
                        'Pending'  => 'bg-amber-100 text-amber-700 border-amber-200 animate-pulse',
                        'Paid'     => 'bg-emerald-100 text-emerald-700 border-emerald-200 shadow-sm',
                        'Rejected' => 'bg-red-100 text-red-700 border-red-200'
                    ];
                    $c = $statusColors[$displayStatus] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                ?>
                <div class="w-full <?= $c ?> py-2.5 rounded-xl text-center font-black text-xs uppercase tracking-widest border">
                    <?= $displayStatus ?>
                </div>
            </div>

            <!-- BUKTI UPLOAD -->
            <div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bukti Transfer</h3>
                <?php if(!empty($payData['file_path'])): ?>
                    <a href="<?= getenv('APP_URL') ?>/public/uploads/payments/<?= htmlspecialchars($payData['file_path']) ?>" target="_blank" class="block group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video flex items-center justify-center cursor-pointer shadow-sm mb-3">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-[10px] uppercase backdrop-blur-sm">Lihat Ukuran Penuh ↗</div>
                        <?php $ext = pathinfo($payData['file_path'], PATHINFO_EXTENSION); ?>
                        <?php if(in_array(strtolower($ext), ['jpg','jpeg','png'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/public/uploads/payments/<?= htmlspecialchars($payData['file_path']) ?>" class="object-contain w-full h-full" alt="Bukti Transfer">
                        <?php else: ?>
                            <div class="text-center p-2">
                                <span class="text-3xl block mb-1">📄</span>
                                <span class="text-[10px] font-bold text-slate-600">Dokumen PDF</span>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <div class="text-center py-5 border border-dashed border-slate-200 rounded-xl bg-slate-50 text-[10px] text-slate-400 font-bold mb-3">
                        Belum ada bukti transfer diunggah
                    </div>
                <?php endif; ?>
            </div>

            <!-- TOMBOL AKSI VERIFIKASI -->
            <div class="pt-2 border-t border-slate-100">
                <?php if($displayStatus === 'Pending'): ?>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="openModal('approve')" class="col-span-1 bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl text-xs font-black uppercase shadow-md transition active:scale-95">✓ Terima</button>
                        <button onclick="openModal('reject')" class="col-span-1 bg-red-100 hover:bg-red-200 text-red-600 py-3 rounded-xl text-xs font-black uppercase transition active:scale-95">✕ Tolak</button>
                    </div>
                <?php elseif($displayStatus === 'Paid'): ?>
                    <button onclick="openModal('rollback')" class="w-full bg-slate-100 hover:bg-orange-100 text-slate-600 hover:text-orange-600 border border-slate-200 py-2.5 rounded-xl text-[10px] font-black uppercase transition">
                        ⏪ Batal Verifikasi (Rollback ke Pending)
                    </button>
                <?php else: ?>
                    <button onclick="openModal('approve')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl text-xs font-black uppercase shadow-md transition active:scale-95">
                        ✓ Tandai Lunas (Verifikasi Manual)
                    </button>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- PANEL KANAN: RINCIAN ATLET & ESTAFET -->
    <div class="lg:col-span-2 space-y-6">
        
        <?php if(empty($groupedSwimmers) && empty($relayEntries)): ?>
            <div class="bg-white rounded-[2.5rem] p-12 text-center border border-slate-200">
                <span class="text-4xl block mb-2">🏊</span>
                <p class="font-bold text-slate-400 text-sm">Belum ada atlet yang didaftarkan oleh klub ini.</p>
            </div>
        <?php else: ?>

            <!-- KARTU NOMOR ESTAFET (JIKA ADA) -->
            <?php if(!empty($relayEntries)): ?>
                <div class="bg-white rounded-2xl border border-indigo-200 overflow-hidden shadow-sm card-atlet mb-6">
                    <div class="p-4 flex justify-between items-center border-b border-indigo-100 bg-indigo-50/70">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                EST
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-indigo-950 uppercase italic leading-tight">Tim Estafet (Relay)</h3>
                                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider mt-0.5"><?= count($relayEntries) ?> Tim Terdaftar</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-black text-sm text-indigo-700">
                                Rp <?= number_format($calc['totalRelay'] ?? 0, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <?php foreach($relayEntries as $re): 
                            $relayDistStr = \App\Helpers\SwimEventHelper::formatDistance($re["distance"] ?? 0, true);
                        ?>
                            <div class="px-4 py-3 flex justify-between items-center hover:bg-slate-50 transition-colors">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-800 uppercase"><?= htmlspecialchars($re["team_name"] ?? 'Tim Estafet') ?></span>
                                    <span class="text-[10px] text-slate-400 font-medium mt-0.5 uppercase">
                                        <?= $relayDistStr ?> <?= strtoupper($re["stroke"]) ?> (<?= $re["jenis_kelamin"] ?>)
                                        <?php if(!empty($re["age_group"])): ?> • KU <?= $re["age_group"] ?><?php endif; ?>
                                    </span>
                                </div>
                                <div class="text-right flex items-center gap-3">
                                    <span class="font-mono text-xs font-bold text-slate-400">
                                        Seed: <b class="text-slate-600"><?= $re["seed_time"] ? htmlspecialchars($re["seed_time"]) : "NT" ?></b>
                                    </span>
                                    <span class="font-mono text-xs font-black text-slate-700">
                                        Rp <?= number_format($re['effective_price'] ?? ($re['item_price'] ?? 0), 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- KARTU ATLET PERORANGAN -->
            <div class="space-y-4">
            <?php foreach($groupedSwimmers as $swimmerId => $data): 
                $info = $data['info'];
                $events = $data['items'];
                $gender = strtoupper($info['gender'] ?? ($info['jenis_kelamin'] ?? ''));
                $isMale = ($gender === 'L' || $gender === 'PA');
                $bgHeader = $isMale ? 'bg-blue-50/70 border-blue-100' : 'bg-pink-50/70 border-pink-100'; 
                $textColor = $isMale ? 'text-blue-700' : 'text-pink-700';
                $iconColor = $isMale ? 'bg-blue-500' : 'bg-pink-500';
                $subtotal = $data['subtotal'];
                $isPackage = (($eventData['pricing_mode'] ?? 'per_item') === 'package');
                $pkgLimit = (int)($eventData['package_limit'] ?? 0);
            ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm card-atlet">
                    
                    <!-- Header Atlet -->
                    <div class="p-4 flex justify-between items-center border-b <?= $bgHeader ?>">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full <?= $iconColor ?> text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                <?= $isMale ? 'PA' : 'PI' ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase italic leading-tight">
                                    <?= htmlspecialchars($info['nama'] ?? ($info['nama_atlet'] ?? '')) ?>
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                                    <?= $isMale ? 'Putra' : 'Putri' ?> <?= !empty($info['lahir']) ? '• Thn ' . date('Y', strtotime($info['lahir'])) : '' ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-black text-sm <?= $textColor ?>">
                                Rp <?= number_format($subtotal, 0, ',', '.') ?>
                            </span>
                            <span class="inline-block text-[9px] font-bold text-slate-600 bg-white/90 px-2 py-0.5 rounded-md border border-slate-200 mt-0.5">
                                <?= htmlspecialchars($data['breakdown_note']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Nomor yang Diikuti Atlet -->
                    <div class="divide-y divide-slate-50">
                        <?php foreach($events as $idx => $ev): 
                            $itemNum = $idx + 1;
                            $isExtra = ($isPackage && $pkgLimit > 0 && $itemNum > $pkgLimit);
                        ?>
                            <div class="px-4 py-3 flex justify-between items-center hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-500 font-mono text-[10px] font-bold flex items-center justify-center">
                                        <?= $itemNum ?>
                                    </span>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 uppercase">
                                            <?= $ev['distance'] ?>M <?= strtoupper($ev['stroke']) ?>
                                        </span>
                                        <?php if(!empty($ev['age_group'])): ?>
                                            <span class="text-[10px] text-slate-400 font-medium uppercase">
                                                KU <?= $ev['age_group'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-3">
                                    <span class="font-mono text-xs font-bold text-slate-400">
                                        Seed: <b class="text-slate-600"><?= $ev['entry_time'] ? htmlspecialchars($ev['entry_time']) : 'NT' ?></b>
                                    </span>
                                    <?php if($isPackage): ?>
                                        <?php if(!$isExtra): ?>
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Termasuk Paket</span>
                                        <?php else: ?>
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">+ Ekstra Rp <?= number_format((float)($eventData['extra_price'] ?? 0), 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="font-mono text-xs font-black text-slate-700">
                                            Rp <?= number_format($ev['effective_price'] ?? 0, 0, ',', '.') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<!-- FORM AKSI VERIFIKASI POST -->
<form id="actionForm" method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/entries/detail?id=<?= $targetUserId ?>&event_id=<?= $eventId ?>" class="hidden">
    <input type="hidden" name="payment_id" value="<?= $payData['id'] ?? '' ?>">
    <input type="hidden" name="action_type" id="modalActionInput">
</form>

<script>
function openModal(action) {
    let msg = 'Apakah Anda yakin ingin memproses aksi ini?';
    if (action === 'approve') msg = 'Verifikasi LUNAS pendaftaran ini? Klub akan dapat mencetak ID Card.';
    else if (action === 'reject') msg = 'Tolak pembayaran ini?';
    else if (action === 'rollback') msg = 'Batalkan verifikasi lunas? Status akan kembali Pending.';

    if (typeof showCustomConfirm === 'function') {
        showCustomConfirm(msg, function() {
            document.getElementById('modalActionInput').value = action;
            document.getElementById('actionForm').submit();
        });
    } else {
        if(confirm(msg)) {
            document.getElementById('modalActionInput').value = action;
            document.getElementById('actionForm').submit();
        }
    }
}
</script>
