<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Tagihan Manual</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Daftar Pendaftaran via Admin</p>
    </div>
    
    <div class="flex gap-3 items-center">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="px-5 py-2 bg-white hover:bg-slate-50 text-slate-700 rounded-xl shadow-sm border border-slate-200 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full">
            Kembali ke Verifikasi
        </a>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_add" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-sm border border-blue-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full">
            <span class="text-lg leading-none">+</span> Tambah Pendaftar Manual
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="max-w-[95%] mx-auto mb-6 p-4 rounded-xl text-sm font-bold <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px]">
    
    <?php if($targetEventId == 0): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">⚠️</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Anda Belum Memilih Event Aktif</h3>
        </div>
    <?php elseif(empty($invoices)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Tagihan Manual</h3>
        </div>
    <?php else: ?>

        <div class="p-6 grid gap-6">
            <?php foreach($invoices as $inv): 
                $code = $inv['invoice_code'];
                $details = $invoiceDetails[$code] ?? [];
                $status = $inv['status'];
                
                $badgeClass = match($status) {
                    'Paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'Pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                    default => 'bg-slate-100 text-slate-500 border-slate-200'
                };
            ?>
            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                <!-- Header -->
                <div class="bg-slate-50 border-b border-slate-200 p-4 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-black text-slate-800 uppercase"><?= htmlspecialchars($code) ?></h3>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border <?= $badgeClass ?>">
                                <?= $status ?>
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-500 mt-1">
                            Dibuat: <?= date('d M Y H:i', strtotime($inv['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Tagihan</span>
                            <span class="block text-xl font-black text-emerald-600">Rp <?= number_format($inv['total_amount'], 0, ',', '.') ?></span>
                        </div>
                        
                        <?php if($status !== 'Paid'): ?>
                        <div class="flex gap-2">
                            <?php if(!empty($inv['payment_proof'])): ?>
                                <a href="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($inv['payment_proof']) ?>" target="_blank" class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-xl flex items-center justify-center transition border border-blue-200" title="Lihat Bukti Transfer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            <?php endif; ?>
                            <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" method="POST" onsubmit="return confirm('Setujui (Lunas) tagihan ini?');">
                                <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="w-10 h-10 bg-emerald-100 hover:bg-emerald-200 text-emerald-600 rounded-xl flex items-center justify-center transition border border-emerald-200" title="Setujui (Paid)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </form>
                            <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" method="POST" onsubmit="return confirm('Tolak tagihan ini?');">
                                <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-xl flex items-center justify-center transition border border-red-200" title="Tolak (Reject)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Body (List Entries) -->
                <div class="p-4 bg-white">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Rincian Atlet (<?= count($details) ?> Entri)</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach($details as $d): ?>
                            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex flex-col justify-center">
                                <div class="text-xs font-black text-slate-800 uppercase mb-1 flex justify-between items-start gap-2">
                                    <span><?= htmlspecialchars($d['skater_name']) ?></span>
                                    <?php if($d['team_name']): ?>
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-[9px]"><?= htmlspecialchars($d['team_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[10px] font-bold text-slate-500 mb-1">
                                    Klub: <?= htmlspecialchars($d['club_name'] ?? '-') ?>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Lomba: <?= htmlspecialchars($d['distance_name'] . ' - ' . $d['group_name']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>
