<div class="max-w-4xl mx-auto font-sans">
    <div class="flex flex-col md:flex-row gap-6">
        
        <div class="flex-1 space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                <h2 class="text-xl font-black text-slate-800 uppercase italic mb-1">Ringkasan Tagihan</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Event: <?= $event ? htmlspecialchars($event['event_name']) : 'TIDAK ADA EVENT AKTIF' ?></p>

                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
                        <div>
                            <?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?>
                        </div>
                        <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
                    </div>
                    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
                <?php endif; ?>

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if(empty($unpaidEntries) && empty($historyEntries)): ?>
                        <p class="text-center py-10 text-slate-400 text-xs italic font-bold">Belum ada peserta yang memiliki tagihan.</p>
                    <?php else: ?>
                        
                        <?php if(!empty($unpaidEntries)): ?>
                            <h3 class="text-[10px] font-black text-red-500 uppercase tracking-widest mt-2 mb-2 border-b border-slate-100 pb-1">Belum Dibayar</h3>
                            <?php foreach($unpaidEntries as $ue): ?>
                            <div class="flex justify-between items-center p-4 bg-red-50/50 rounded-2xl border border-red-100 hover:border-red-200 transition">
                                <div>
                                    <p class="text-[10px] font-black text-slate-800 uppercase mb-0.5"><?= htmlspecialchars($ue['skater_name'] ?? '') ?></p>
                                    <p class="text-xs font-bold text-blue-600 uppercase italic"><?= htmlspecialchars(($ue['group_name'] ?? '') . ' - ' . ($ue['distance_name'] ?? '')) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black text-slate-800">Rp <?= number_format($ue['payment_amount'] ?? 0, 0, ',', '.') ?></p>
                                    <p class="text-[9px] font-bold text-red-500 uppercase"><?= htmlspecialchars($paymentStatus) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if(!empty($historyEntries)): ?>
                            <h3 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-6 mb-2 border-b border-slate-100 pb-1">Riwayat (Pending/Paid)</h3>
                            <?php foreach($historyEntries as $he): ?>
                            <div class="flex justify-between items-center p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 hover:border-emerald-200 transition opacity-80">
                                <div>
                                    <p class="text-[10px] font-black text-slate-800 uppercase mb-0.5"><?= htmlspecialchars($he['skater_name'] ?? '') ?></p>
                                    <p class="text-xs font-bold text-slate-500 uppercase italic"><?= htmlspecialchars(($he['group_name'] ?? '') . ' - ' . ($he['distance_name'] ?? '')) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black text-slate-800">Rp <?= number_format($he['payment_amount'] ?? 0, 0, ',', '.') ?></p>
                                    <p class="text-[9px] font-bold <?= $paymentStatus == 'Paid' ? 'text-emerald-600' : 'text-amber-500' ?> uppercase"><?= htmlspecialchars($paymentStatus) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t-2 border-dashed border-slate-200 flex justify-between items-center">
                    <p class="text-sm font-black text-slate-800 uppercase italic">Total Tagihan (Belum Lunas)</p>
                    <p class="text-3xl font-black text-blue-600 tracking-tighter">Rp <?= number_format($totalFee, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="w-full md:w-80 space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 sticky top-24">
                <h3 class="text-sm font-black text-slate-800 uppercase italic mb-4">Pembayaran</h3>

                <form action="<?= getenv('APP_URL') ?>/roll/user/checkout/pay/<?= $event['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    
                    <?php if(!empty($unpaidEntries)): ?>
                        <?php foreach($unpaidEntries as $ue): ?>
                            <input type="hidden" name="entry_ids[]" value="<?= $ue['id'] ?>">
                        <?php endforeach; ?>
                        
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl">
                            <p class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2">Instruksi Pembayaran</p>
                            <p class="text-[10px] text-blue-600 leading-relaxed font-medium italic mb-2">Silakan transfer sesuai total tagihan ke rekening berikut:</p>
                            <p class="font-black text-blue-900 text-sm">BCA</p>
                            <p class="font-mono text-lg font-bold text-blue-700 tracking-wider my-1">08762514</p>
                            <p class="text-xs font-bold text-blue-600">a.n Panitia Pendaftaran</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="payment_proof" required accept="image/jpeg,image/png,application/pdf" class="w-full text-xs font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:uppercase file:tracking-widest file:font-black file:bg-slate-900 file:text-white hover:file:bg-blue-600 file:transition border border-slate-200 rounded-xl p-1 bg-slate-50 cursor-pointer outline-none focus:border-blue-500 transition">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 shadow-blue-200 text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none">
                            Kirim Bukti Bayar ➜
                        </button>
                    <?php else: ?>
                        <div class="text-center space-y-3 py-6">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner border border-slate-100">
                                💸
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 leading-relaxed px-4 uppercase tracking-widest">
                                Tidak ada tagihan yang belum lunas.
                            </p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <a href="<?= getenv('APP_URL') ?>/roll/user/registration/index/<?= $event['id'] ?>" class="block w-full py-4 bg-white border border-slate-200 rounded-2xl text-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-600 transition-all outline-none">
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
