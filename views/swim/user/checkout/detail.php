<div class="max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row gap-6">
        
        <div class="flex-1 space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                <h2 class="text-xl font-black text-slate-800 uppercase italic mb-1">Ringkasan Pendaftaran</h2>
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

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if(empty($details) && empty($relayDetails)): ?>
                        <p class="text-center py-10 text-slate-400 text-xs italic font-bold">Belum ada peserta yang didaftarkan.</p>
                    <?php else: ?>
                        <?php foreach($details as $d): ?>
                        <div class="flex justify-between items-center p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-blue-200 transition">
                            <div>
                                <p class="text-[10px] font-black text-blue-600 uppercase mb-0.5"><?= htmlspecialchars($d['nama_atlet'] ?? '') ?></p>
                                <p class="text-xs font-bold text-slate-700 uppercase italic"><?= $d['distance'] ?>m <?= $d['stroke'] ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-slate-800">Rp <?= number_format($d['price'], 0, ',', '.') ?></p>
                                <p class="text-[9px] font-bold text-slate-400">Time: <span class="font-mono"><?= $d['entry_time'] ?: 'NT' ?></span></p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php foreach($relayDetails as $r): ?>
                        <div class="flex justify-between items-center p-4 bg-indigo-50 rounded-2xl border border-indigo-100 hover:border-indigo-300 transition">
                            <div>
                                <p class="text-[10px] font-black text-indigo-600 uppercase mb-0.5">[ESTAFET] <?= htmlspecialchars($r['team_name'] ?? '') ?></p>
                                <p class="text-xs font-bold text-slate-700 uppercase italic"><?= $r['distance'] ?>m <?= $r['stroke'] ?></p>
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
                    <form action="<?= getenv('APP_URL') ?>/swim/checkout/upload_proof/<?= $event['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
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

                        <button type="submit" class="<?= $displayStatus == 'Rejected' ? 'bg-red-600 shadow-red-200 hover:bg-red-700' : 'bg-blue-600 shadow-blue-200 hover:bg-blue-700' ?> text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none <?= $totalTagihan == 0 ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $totalTagihan == 0 ? 'disabled' : '' ?>>
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
                        <?php if(!empty($proofFile)): ?>
                            <a href="<?= getenv('APP_URL') ?>/public/uploads/payments/<?= htmlspecialchars($proofFile) ?>" target="_blank" class="inline-block mt-4 px-6 py-2 bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-black text-slate-600 uppercase tracking-widest hover:bg-slate-900 hover:text-white transition">Lihat Bukti Saya</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/swim/registration/index/<?= $event['id'] ?>" class="block w-full py-4 bg-white border border-slate-200 rounded-2xl text-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-600 transition-all outline-none">
                &larr; Kembali
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
