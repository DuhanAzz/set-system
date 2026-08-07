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
                        
                        <?php 
                        $groupedUnpaid = [];
                        if(!empty($unpaidEntries)) {
                            foreach($unpaidEntries as $ue) {
                                $sId = $ue['skater_id'];
                                if(!isset($groupedUnpaid[$sId])) {
                                    $groupedUnpaid[$sId] = [
                                        'name' => $ue['skater_name'],
                                        'amount' => 0,
                                        'entries' => []
                                    ];
                                }
                                $groupedUnpaid[$sId]['amount'] += $ue['payment_amount'];
                                $groupedUnpaid[$sId]['entries'][] = $ue;
                            }
                        }
                        
                        $groupedHistory = [];
                        if(!empty($historyEntries)) {
                            foreach($historyEntries as $he) {
                                $sId = $he['skater_id'];
                                if(!isset($groupedHistory[$sId])) {
                                    $groupedHistory[$sId] = [
                                        'name' => $he['skater_name'],
                                        'amount' => 0,
                                        'entries' => []
                                    ];
                                }
                                $groupedHistory[$sId]['amount'] += $he['payment_amount'];
                                $groupedHistory[$sId]['entries'][] = $he;
                            }
                        }
                        ?>

                        <?php if(!empty($groupedUnpaid)): ?>
                            <h3 class="text-[10px] font-black text-red-500 uppercase tracking-widest mt-2 mb-2 border-b border-slate-100 pb-1">Belum Dibayar</h3>
                            <?php foreach($groupedUnpaid as $sId => $g): ?>
                            <div class="p-4 bg-red-50/50 rounded-2xl border border-red-100 hover:border-red-200 transition relative">
                                <div class="absolute top-4 right-4">
                                    <p class="text-[9px] font-bold text-red-500 uppercase"><?= htmlspecialchars($paymentStatus) ?></p>
                                </div>
                                <p class="text-[10px] font-black text-slate-800 uppercase mb-2"><?= htmlspecialchars($g['name']) ?></p>
                                <div class="mb-3 pl-2 border-l-2 border-red-200">
                                    <?php foreach($g['entries'] as $ent): ?>
                                    <p class="text-[11px] font-bold text-blue-600 uppercase italic leading-snug">
                                        <?= htmlspecialchars(($ent['group_name'] ?? '') . ' - ' . ($ent['distance_name'] ?? '')) ?>
                                    </p>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-xs font-black text-slate-800">Rp <?= number_format($g['amount'], 0, ',', '.') ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if(!empty($groupedHistory)): ?>
                            <h3 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-6 mb-2 border-b border-slate-100 pb-1">Riwayat (Pending/Paid)</h3>
                            <?php foreach($groupedHistory as $sId => $g): ?>
                            <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 hover:border-emerald-200 transition opacity-80 relative">
                                <div class="absolute top-4 right-4">
                                    <p class="text-[9px] font-bold <?= $paymentStatus == 'Paid' ? 'text-emerald-600' : 'text-amber-500' ?> uppercase"><?= htmlspecialchars($paymentStatus) ?></p>
                                </div>
                                <p class="text-[10px] font-black text-slate-800 uppercase mb-2"><?= htmlspecialchars($g['name']) ?></p>
                                <div class="mb-3 pl-2 border-l-2 border-emerald-200">
                                    <?php foreach($g['entries'] as $ent): ?>
                                    <p class="text-[11px] font-bold text-slate-500 uppercase italic leading-snug">
                                        <?= htmlspecialchars(($ent['group_name'] ?? '') . ' - ' . ($ent['distance_name'] ?? '')) ?>
                                    </p>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-xs font-black text-slate-800">Rp <?= number_format($g['amount'], 0, ',', '.') ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t-2 border-dashed border-slate-200">
                    <div class="mb-4">
                        <p class="text-xs font-black text-slate-800 uppercase italic mb-2">Ringkasan Tagihan</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <?php if (!empty($summaryCounts['Speed'])): ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Speed</p>
                                <p class="text-lg font-black text-fuchsia-600"><?= $summaryCounts['Speed'] ?> <span class="text-[10px]">Atlet</span></p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1"><?= $summaryCounts['Speed'] ?> x Rp <?= number_format($event['fee_speed'] ?? 0, 0, ',', '.') ?></p>
                                <p class="text-xs font-black text-fuchsia-700 mt-0.5">Rp <?= number_format($summaryCounts['Speed'] * ($event['fee_speed'] ?? 0), 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($summaryCounts['Standart'])): ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Standart</p>
                                <p class="text-lg font-black text-amber-500"><?= $summaryCounts['Standart'] ?> <span class="text-[10px]">Atlet</span></p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1"><?= $summaryCounts['Standart'] ?> x Rp <?= number_format($event['fee_standart'] ?? 0, 0, ',', '.') ?></p>
                                <p class="text-xs font-black text-amber-600 mt-0.5">Rp <?= number_format($summaryCounts['Standart'] * ($event['fee_standart'] ?? 0), 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($summaryCounts['Pemula'])): ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Pemula</p>
                                <p class="text-lg font-black text-emerald-500"><?= $summaryCounts['Pemula'] ?> <span class="text-[10px]">Atlet</span></p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1"><?= $summaryCounts['Pemula'] ?> x Rp <?= number_format($event['fee_pemula'] ?? 0, 0, ',', '.') ?></p>
                                <p class="text-xs font-black text-emerald-600 mt-0.5">Rp <?= number_format($summaryCounts['Pemula'] * ($event['fee_pemula'] ?? 0), 0, ',', '.') ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($summaryCounts['Team'])): ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-slate-500 uppercase font-bold">Team Relay / Pair</p>
                                <p class="text-lg font-black text-indigo-500"><?= $summaryCounts['Team'] ?> <span class="text-[10px]">Tim</span></p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1">Gratis (Termasuk)</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <p class="text-sm font-black text-slate-800 uppercase italic">Total Tagihan (Belum Lunas)</p>
                        <p class="text-3xl font-black text-blue-600 tracking-tighter">Rp <?= number_format($totalFee, 0, ',', '.') ?></p>
                    </div>
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
                            <p class="font-black text-blue-900 text-sm"><?= htmlspecialchars($event['bank_name'] ?? 'BCA') ?></p>
                            <p class="font-mono text-lg font-bold text-blue-700 tracking-wider my-1"><?= htmlspecialchars($event['bank_account'] ?? '08762514') ?></p>
                            <p class="text-xs font-bold text-blue-600">a.n <?= htmlspecialchars($event['bank_account_name'] ?? 'Panitia Pendaftaran') ?></p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1.5 uppercase tracking-widest">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="payment_proof" required accept="image/jpeg,image/png,application/pdf" class="w-full text-xs font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:uppercase file:tracking-widest file:font-black file:bg-slate-900 file:text-white hover:file:bg-blue-600 file:transition border border-slate-200 rounded-xl p-1 bg-slate-50 cursor-pointer outline-none focus:border-blue-500 transition">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 shadow-blue-200 text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none mb-3">
                            Kirim Bukti Bayar ➜
                        </button>
                        
                        <?php 
                        $waMsg = "Halo Admin SET SYSTEM,\n\nSaya dari Klub *" . ($clubName) . "* ingin mengonfirmasi pembayaran untuk event *" . ($event['event_name'] ?? 'Event') . "*.\n\nTotal Tagihan: *Rp " . number_format($totalFee, 0, ',', '.') . "*\n\nBukti transfer telah kami kirimkan melalui sistem. Mohon untuk segera diverifikasi. Terima kasih! ✨";
                        $adminWa = !empty($event['contact_phone']) ? preg_replace('/[^0-9]/', '', $event['contact_phone']) : '6281234567890';
                        if(substr($adminWa, 0, 1) == '0') $adminWa = '62' . substr($adminWa, 1);
                        $waLink = "https://wa.me/" . $adminWa . "?text=" . urlencode($waMsg);
                        ?>
                        <a href="<?= $waLink ?>" target="_blank" class="flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200 text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.52 3.449a11.967 11.967 0 00-8.498-3.447C5.43 0 .044 5.385.044 11.975c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.66 1.446h.005c6.59 0 11.975-5.385 11.975-11.976a11.964 11.964 0 00-3.482-8.367zM12.023 21.758a9.882 9.882 0 01-5.042-1.378l-.36-.214-3.748.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.445 4.432-9.877 9.882-9.877a9.87 9.87 0 016.985 2.894 9.865 9.865 0 012.892 6.98c-.001 5.446-4.434 9.895-9.862 9.895zM17.433 14.372c-.297-.148-1.757-.867-2.028-.966-.271-.1-.47-.148-.668.148-.198.297-.767.966-.94 1.164-.173.198-.346.223-.643.074a8.1 8.1 0 01-2.42-1.492c-.933-.86-1.562-1.92-1.745-2.217-.183-.297-.02-.458.129-.607.133-.133.297-.346.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.148-.668-1.609-.915-2.203-.242-.578-.487-.5-.668-.51h-.57c-.198 0-.52.074-.792.371-.272.297-1.04 1.015-1.04 2.476s1.064 2.871 1.213 3.069c.148.198 2.094 3.196 5.074 4.482.709.306 1.263.489 1.694.626.713.226 1.36.194 1.871.118.571-.085 1.757-.718 2.005-1.41.247-.693.247-1.287.173-1.41-.074-.124-.272-.198-.57-.346z"></path></svg>
                            Hubungi Admin via WA
                        </a>
                    <?php else: ?>
                        <div class="text-center space-y-3 py-6">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner border border-slate-100">
                                💸
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 leading-relaxed px-4 uppercase tracking-widest mb-4">
                                Tidak ada tagihan yang belum lunas (Atau sedang menunggu verifikasi).
                            </p>
                        </div>
                        <?php 
                        $waMsg = "Halo Admin SET SYSTEM,\n\nSaya dari Klub *" . ($clubName) . "* ingin menanyakan status verifikasi pembayaran untuk event *" . ($event['event_name'] ?? 'Event') . "*.\n\nBukti transfer telah kami kirimkan melalui sistem. Mohon bantuannya untuk segera diverifikasi agar kami bisa mendaftarkan atlet lainnya. Terima kasih! ";
                        $adminWa = !empty($event['contact_phone']) ? preg_replace('/[^0-9]/', '', $event['contact_phone']) : '6281234567890';
                        if(substr($adminWa, 0, 1) == '0') $adminWa = '62' . substr($adminWa, 1);
                        $waLink = "https://wa.me/" . $adminWa . "?text=" . urlencode($waMsg);
                        ?>
                        <a href="<?= $waLink ?>" target="_blank" class="flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200 text-white font-black py-4 w-full rounded-2xl shadow-lg transition-all uppercase text-[10px] tracking-widest active:scale-95 outline-none mt-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.52 3.449a11.967 11.967 0 00-8.498-3.447C5.43 0 .044 5.385.044 11.975c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.66 1.446h.005c6.59 0 11.975-5.385 11.975-11.976a11.964 11.964 0 00-3.482-8.367zM12.023 21.758a9.882 9.882 0 01-5.042-1.378l-.36-.214-3.748.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.445 4.432-9.877 9.882-9.877a9.87 9.87 0 016.985 2.894 9.865 9.865 0 012.892 6.98c-.001 5.446-4.434 9.895-9.862 9.895zM17.433 14.372c-.297-.148-1.757-.867-2.028-.966-.271-.1-.47-.148-.668.148-.198.297-.767.966-.94 1.164-.173.198-.346.223-.643.074a8.1 8.1 0 01-2.42-1.492c-.933-.86-1.562-1.92-1.745-2.217-.183-.297-.02-.458.129-.607.133-.133.297-.346.446-.52.148-.173.198-.297.297-.495.099-.198.05-.371-.025-.52-.074-.148-.668-1.609-.915-2.203-.242-.578-.487-.5-.668-.51h-.57c-.198 0-.52.074-.792.371-.272.297-1.04 1.015-1.04 2.476s1.064 2.871 1.213 3.069c.148.198 2.094 3.196 5.074 4.482.709.306 1.263.489 1.694.626.713.226 1.36.194 1.871.118.571-.085 1.757-.718 2.005-1.41.247-.693.247-1.287.173-1.41-.074-.124-.272-.198-.57-.346z"></path></svg>
                            Hubungi Admin via WA
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if(!empty($unpaidEntries)): ?>
                <button type="button" onclick="alert('Harap upload bukti pembayaran Anda terlebih dahulu sebelum dapat kembali / melanjutkan pendaftaran.')" class="block w-full py-4 bg-slate-100 border border-slate-200 rounded-2xl text-center text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-not-allowed outline-none">
                    &larr; Kembali ke Pendaftaran (Terkunci)
                </button>
            <?php else: ?>
                <a href="<?= getenv('APP_URL') ?>/roll/user/registration/index/<?= $event['id'] ?>" class="block w-full py-4 bg-white border border-slate-200 rounded-2xl text-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 hover:text-slate-600 transition-all outline-none">
                    &larr; Lanjut ke Pendaftaran
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
/* Custom Scrollbar untuk kotak tagihan */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>
