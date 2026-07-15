<div class="mb-6 flex gap-3 items-center">
    <a href="<?= getenv('APP_URL') ?>/swim/checkout" class="w-10 h-10 bg-slate-200 hover:bg-slate-300 rounded-full flex items-center justify-center text-slate-600 transition shrink-0">⬅</a>
    <div>
        <h1 class="text-2xl font-black uppercase italic text-slate-900">Checkout & Tagihan</h1>
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Event: <?= $event ? htmlspecialchars($event['event_name']) : 'TIDAK ADA EVENT AKTIF' ?></p>
    </div>
</div>

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

<?php if(!$event): ?>
    <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center">
        <div class="text-6xl mb-4 opacity-50">🏆</div>
        <h3 class="text-xl font-black text-slate-800 uppercase italic">Belum Ada Event Aktif</h3>
    </div>
<?php else: ?>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Kiri: Rincian Tagihan -->
    <div class="flex-1 space-y-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
            <h2 class="text-xl font-black text-slate-800 uppercase italic mb-6">Ringkasan Pendaftaran</h2>

            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                <?php if(empty($details) && empty($relayDetails)): ?>
                    <p class="text-center py-10 text-slate-400 text-xs italic font-bold">Belum ada peserta yang didaftarkan.</p>
                <?php else: ?>
                    <!-- Individu -->
                    <?php foreach($details as $d): ?>
                    <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-blue-600 uppercase mb-0.5"><?= htmlspecialchars($d['nama_atlet'] ?? '') ?></p>
                            <p class="text-xs font-bold text-slate-700 uppercase italic"><?= $d['distance'] ?>M <?= $d['stroke'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-800">Rp <?= number_format($d['price'], 0, ',', '.') ?></p>
                            <p class="text-[9px] font-bold text-slate-400">Time: <span class="font-mono"><?= $d['entry_time'] ?: 'NT' ?></span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Estafet -->
                    <?php foreach($relayDetails as $r): ?>
                    <div class="flex justify-between items-center p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                        <div>
                            <p class="text-[10px] font-black text-indigo-600 uppercase mb-0.5">[ESTAFET] <?= htmlspecialchars($r['team_name'] ?? '') ?></p>
                            <p class="text-xs font-bold text-slate-700 uppercase italic"><?= $r['distance'] ?>M <?= $r['stroke'] ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-800">Rp <?= number_format($r['price'], 0, ',', '.') ?></p>
                            <p class="text-[9px] font-bold text-slate-400">Time: <span class="font-mono"><?= $r['seed_time'] ?: 'NT' ?></span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="mt-6 pt-6 border-t-2 border-dashed border-slate-200 flex justify-between items-center">
                <p class="text-sm font-black text-slate-800 uppercase italic">Total Tagihan</p>
                <p class="text-3xl font-black text-blue-600 tracking-tighter">Rp <?= number_format($totalTagihan, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <!-- Kanan: Status & Pembayaran -->
    <div class="w-full md:w-80 space-y-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 sticky top-24">
            <h3 class="text-sm font-black text-slate-800 uppercase italic mb-4">Status Pembayaran</h3>
            
            <?php 
                $statusColors = [
                    'Unpaid'   => 'bg-slate-100 text-slate-600 border border-slate-200',
                    'Pending'  => 'bg-amber-100 text-amber-700 border border-amber-200',
                    'Paid'     => 'bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm',
                    'Rejected' => 'bg-red-100 text-red-700 border border-red-200',
                ];
                // Handle case differences
                $displayStatus = ucfirst(strtolower($paymentStatus));
                if ($displayStatus === 'Completed') $displayStatus = 'Paid';
                
                $colorClass = $statusColors[$displayStatus] ?? $statusColors['Unpaid'];
            ?>
            <div class="w-full p-4 rounded-2xl text-center <?= $colorClass ?> transition mb-6">
                <span class="block text-[10px] uppercase tracking-widest font-bold opacity-70 mb-1">Status Saat Ini</span>
                <span class="block text-xl font-black uppercase tracking-widest"><?= $displayStatus ?></span>
            </div>

            <?php if($displayStatus === 'Unpaid' || $displayStatus === 'Rejected'): ?>
                <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl mb-6">
                    <p class="text-[10px] font-bold text-blue-800 uppercase mb-2">Transfer Pembayaran Ke:</p>
                    <p class="font-black text-blue-900 text-sm"><?= htmlspecialchars($event['bank_name'] ?? 'BCA') ?></p>
                    <p class="font-mono text-lg font-bold text-blue-700 tracking-wider my-1"><?= htmlspecialchars($event['bank_account_number'] ?? '-') ?></p>
                    <p class="text-xs font-bold text-blue-600">a.n <?= htmlspecialchars($event['bank_account_name'] ?? '-') ?></p>
                </div>

                <form action="<?= getenv('APP_URL') ?>/swim/checkout/upload_proof/<?= $event['id'] ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="from_list" value="0">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Upload Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer mb-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xs py-3 rounded-xl uppercase tracking-widest transition shadow-lg <?= $totalTagihan == 0 ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $totalTagihan == 0 ? 'disabled' : '' ?>>Kirim Bukti Bayar</button>
                </form>
            <?php else: ?>
                <div class="text-center pt-4 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-500">Tagihan telah diproses atau sudah lunas. Anda tidak dapat mengunggah bukti bayar lagi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?>
