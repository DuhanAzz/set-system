<div class="max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row gap-6">
        
        <div class="flex-1 space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-xl font-black text-slate-800 uppercase italic">Ringkasan Pendaftaran</h2>
                    <?php if (($event['pricing_mode'] ?? 'per_item') === 'package'): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            📦 Skema Paket Aktif
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                            🏷️ Skema Satuan Aktif
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Event: <?= $event ? htmlspecialchars($event['event_name']) : 'TIDAK ADA EVENT AKTIF' ?></p>

                <?php if (isset($success)): ?>
                    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-green-100 text-green-700">
                        ✅ <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm bg-red-100 text-red-700">
                        ❌ <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if(empty($groupedSwimmers) && empty($relayDetails)): ?>
                        <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50">
                            <span class="text-3xl block mb-2">🏊</span>
                            <p class="text-slate-400 text-xs italic font-bold">Belum ada peserta yang didaftarkan pada event ini.</p>
                            <a href="<?= getenv('APP_URL') ?>/swim/user/registration/index/<?= $event['id'] ?>" class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold uppercase shadow">Daftarkan Atlet Sekarang</a>
                        </div>
                    <?php else: ?>
                        <?php 
                        $pricingMode = $event['pricing_mode'] ?? 'per_item';
                        $limit = (int)($event['package_limit'] ?? 0);
                        $basePrice = (float)($event['package_price'] ?? 0);
                        $extraPrice = (float)($event['extra_price'] ?? 0);
                        $defaultItemPrice = (float)($event['price_per_item'] ?? ($basePrice > 0 ? $basePrice : 0));
                        ?>

                        <!-- RINCIAN TIM ESTAFET (JIKA ADA) -->
                        <?php if(!empty($relayDetails)): ?>
                        <div class="bg-indigo-50/50 rounded-2xl border border-indigo-200 overflow-hidden shadow-sm">
                            <div class="p-4 flex items-center justify-between border-b border-indigo-100 bg-indigo-100/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                        EST
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-indigo-950 uppercase italic leading-tight">Nomor Estafet (Relay)</h4>
                                        <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mt-0.5"><?= count($relayDetails) ?> Tim Terdaftar</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-mono font-black text-sm text-indigo-900">
                                        Rp <?= number_format($calc['totalRelay'] ?? 0, 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="divide-y divide-indigo-100/80 bg-white">
                                <?php foreach($relayDetails as $r): ?>
                                <div class="px-4 py-3 flex items-center justify-between hover:bg-indigo-50/30 transition text-xs">
                                    <div>
                                        <p class="font-black text-indigo-900 uppercase leading-snug"><?= htmlspecialchars($r['team_name'] ?? 'Tim Estafet') ?></p>
                                        <p class="text-[10px] font-semibold text-slate-500 uppercase mt-0.5">
                                            <?= $r['distance'] ?>m <?= htmlspecialchars($r['stroke']) ?>
                                            <?php if(!empty($r['age_group'])): ?> • KU <?= htmlspecialchars($r['age_group']) ?><?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-mono text-xs font-black text-slate-800">Rp <?= number_format($r['effective_price'] ?? ($r['item_price'] ?? 0), 0, ',', '.') ?></p>
                                        <p class="text-[9px] font-bold text-slate-400">Seed: <span class="font-mono"><?= $r['seed_time'] ?: 'NT' ?></span></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- RINCIAN PER ATLET -->
                        <?php foreach($groupedSwimmers as $swimmerId => $data): 
                            $info = $data['info'];
                            $items = $data['items'];
                            $gender = strtoupper($info['gender'] ?? '');
                            $isMale = ($gender === 'L' || $gender === 'PA');
                            $headerBg = $isMale ? 'bg-blue-50/70 border-blue-100' : 'bg-pink-50/70 border-pink-100';
                            $badgeColor = $isMale ? 'bg-blue-500' : 'bg-pink-500';
                            $subtotalColor = $isMale ? 'text-blue-700' : 'text-pink-700';
                            $itemCount = count($items);
                        ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                            <!-- Card Header Atlet -->
                            <div class="p-4 flex items-center justify-between border-b <?= $headerBg ?>">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full <?= $badgeColor ?> text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                        <?= $isMale ? 'PA' : 'PI' ?>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black text-slate-800 uppercase italic leading-tight">
                                            <?= htmlspecialchars($info['nama']) ?>
                                        </h3>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                                            <?= $isMale ? 'Putra' : 'Putri' ?> <?= !empty($info['lahir']) ? '• Thn ' . date('Y', strtotime($info['lahir'])) : '' ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block font-mono font-black text-sm <?= $subtotalColor ?>">
                                        Rp <?= number_format($data['subtotal'], 0, ',', '.') ?>
                                    </span>
                                    <span class="inline-block text-[9px] font-bold text-slate-600 bg-white/90 px-2 py-0.5 rounded-md border border-slate-200 mt-0.5">
                                        <?= htmlspecialchars($data['breakdown_note']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- List Nomor Lomba Atlet -->
                            <div class="divide-y divide-slate-100">
                                <?php foreach($items as $idx => $ev): 
                                    $itemNum = $idx + 1;
                                    $isExtra = ($pricingMode === 'package' && $limit > 0 && $itemNum > $limit);
                                ?>
                                <div class="px-4 py-2.5 flex items-center justify-between hover:bg-slate-50 transition text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-500 font-mono text-[10px] font-bold flex items-center justify-center">
                                            <?= $itemNum ?>
                                        </span>
                                        <div>
                                            <span class="font-bold text-slate-800 uppercase italic">
                                                <?= $ev['distance'] ?>m <?= htmlspecialchars($ev['stroke']) ?>
                                            </span>
                                            <?php if(!empty($ev['age_group'])): ?>
                                                <span class="text-[10px] font-semibold text-slate-400 ml-1.5 uppercase">KU <?= htmlspecialchars($ev['age_group']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-right flex items-center gap-3">
                                        <span class="font-mono text-[10px] text-slate-400">
                                            Seed: <b class="text-slate-600"><?= !empty($ev['entry_time']) ? htmlspecialchars($ev['entry_time']) : 'NT' ?></b>
                                        </span>
                                        <?php if($pricingMode === 'package'): ?>
                                            <?php if(!$isExtra): ?>
                                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Termasuk Paket</span>
                                            <?php else: ?>
                                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">+ Ekstra Rp <?= number_format($extraPrice, 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="font-mono text-[11px] font-black text-slate-800">
                                                Rp <?= number_format($ev['effective_price'] ?? 0, 0, ',', '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>

                <!-- TOTAL & PENJELASAN ATURAN BIAYA -->
                <div class="mt-6 pt-6 border-t-2 border-dashed border-slate-200">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Tagihan Seharusnya</p>
                            <?php if (($event['pricing_mode'] ?? 'per_item') === 'package'): ?>
                                <p class="text-[11px] text-emerald-700 font-bold mt-1">
                                    📦 Paket: Rp <?= number_format((float)($event['package_price'] ?? 0), 0, ',', '.') ?> / atlet (maks <?= (int)($event['package_limit'] ?? 0) ?> nomor)
                                    <?php if ((float)($event['extra_price'] ?? 0) > 0): ?>
                                        <br><span class="text-slate-500 font-normal">Nomor tambahan setelah kuota: + Rp <?= number_format((float)($event['extra_price'] ?? 0), 0, ',', '.') ?> / nomor</span>
                                    <?php endif; ?>
                                </p>
                            <?php else: ?>
                                <p class="text-[11px] text-blue-700 font-bold mt-1">
                                    🏷️ Satuan: Tarif Rp <?= number_format((float)($event['price_per_item'] ?? ($event['package_price'] ?? 0)), 0, ',', '.') ?> / nomor lomba
                                </p>
                            <?php endif; ?>
                            <?php 
                            $effectiveRelayRate = (float)($event['price_relay'] ?? ($calc['defaultRelayPrice'] ?? 0));
                            if ($effectiveRelayRate > 0 || !empty($relayDetails)): 
                            ?>
                                <p class="text-[11px] text-indigo-700 font-bold mt-1">
                                    👥 Estafet: Tarif Rp <?= number_format($effectiveRelayRate, 0, ',', '.') ?> / tim
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center gap-2 mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <span>Total Atlet: <b class="text-slate-700"><?= count($groupedSwimmers ?? []) ?></b></span>
                                <span>•</span>
                                <span>Total Nomor: <b class="text-slate-700"><?= $calc['totalEntries'] ?? 0 ?></b></span>
                                <?php if(!empty($relayDetails)): ?>
                                    <span>•</span>
                                    <span>Tim Estafet: <b class="text-slate-700"><?= count($relayDetails) ?></b></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Bayar</span>
                            <p class="text-3xl font-black text-blue-600 tracking-tighter">Rp <?= number_format($totalTagihan, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL STATUS & INSTRUKSI PEMBAYARAN -->
        <div class="w-full md:w-80 space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 sticky top-24">
                <h3 class="text-sm font-black text-slate-800 uppercase italic mb-4">Status Pembayaran</h3>
                
                <?php 
                    $displayStatus = ucfirst(strtolower($paymentStatus));
                    if ($displayStatus === 'Completed') $displayStatus = 'Paid';
                    
                    $statusColors = [
                        'Unpaid'   => 'bg-slate-100 text-slate-600 border border-slate-200',
                        'Pending'  => 'bg-amber-100 text-amber-700 border border-amber-200',
                        'Paid'     => 'bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm',
                        'Rejected' => 'bg-red-100 text-red-700 border border-red-200'
                    ];
                    $c = $statusColors[$displayStatus] ?? 'bg-slate-100 text-slate-600';
                ?>
                <div class="w-full <?= $c ?> py-3 rounded-xl text-center font-black text-xs uppercase tracking-widest mb-6">
                    <?= $displayStatus ?>
                </div>

                <?php if ($displayStatus === 'Unpaid' || $displayStatus === 'Rejected'): ?>
                    <form action="<?= getenv('APP_URL') ?>/swim/user/checkout/upload_proof/<?= $event['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="from_list" value="0">
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl">
                            <p class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2">Instruksi Pembayaran</p>
                            <p class="text-[10px] text-blue-600 leading-relaxed font-medium italic mb-2">Silakan transfer sesuai total tagihan ke rekening berikut:</p>
                            <p class="font-black text-blue-900 text-sm"><?= htmlspecialchars($event['bank_name'] ?? 'BCA') ?></p>
                            <p class="font-mono text-lg font-bold text-blue-700 tracking-wider my-1"><?= htmlspecialchars($event['bank_account_number'] ?? '-') ?></p>
                            <p class="text-xs font-bold text-blue-600">a.n <?= htmlspecialchars($event['bank_account_name'] ?? '-') ?></p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="bukti_transfer" required accept="image/jpeg,image/png,application/pdf" class="w-full text-xs font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:uppercase file:tracking-widest file:font-black file:bg-slate-900 file:text-white hover:file:bg-blue-600 file:transition border border-slate-200 rounded-xl p-1 bg-slate-50 cursor-pointer outline-none focus:border-blue-500 transition">
                        </div>

                        <button type="submit" class="<?= $displayStatus == 'Rejected' ? 'bg-red-600 shadow-red-200 hover:bg-red-700' : 'bg-blue-600 shadow-blue-200 hover:bg-blue-700' ?> text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none">
                            <?= $displayStatus == 'Rejected' ? 'Upload Ulang Bukti' : 'Kirim Bukti Bayar ➜' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center space-y-3">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner border border-slate-100">
                            <?= ($displayStatus == 'Pending') ? '⏳' : '✅' ?>
                        </div>
                        <p class="text-[10px] font-bold text-slate-500 leading-relaxed px-4 uppercase tracking-widest">
                            <?= ($displayStatus == 'Pending') ? 'Menunggu Verifikasi Panitia' : 'Pembayaran Lunas!' ?>
                        </p>
                        <?php if(!empty($pay['file_path'])): ?>
                            <a href="<?= getenv('APP_URL') ?>/public/uploads/payments/<?= htmlspecialchars($pay['file_path']) ?>" target="_blank" class="inline-block mt-4 px-6 py-2 bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-black text-slate-600 uppercase tracking-widest hover:bg-slate-900 hover:text-white transition">Lihat Bukti Saya</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/swim/user/registration/index/<?= $event['id'] ?>" class="block w-full py-4 bg-white border border-slate-200 rounded-2xl text-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-600 transition-all outline-none">
                &larr; Kembali ke Pendaftaran
            </a>
        </div>

    </div>
</div>

<style>
/* Custom Scrollbar untuk kotak tagihan */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>
