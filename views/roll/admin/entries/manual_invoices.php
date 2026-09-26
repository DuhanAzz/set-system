<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Manual Invoices</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Daftar tagihan dari pendaftaran manual atau penggunaan token.</p>
    </div>
    
    <div class="flex gap-3 items-center">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="px-5 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-xl shadow-sm border border-slate-700 text-[11px] font-black uppercase tracking-widest transition flex items-center gap-2 h-full">
            <span class="text-lg leading-none">🔙</span> Kembali ke Reguler
        </a>
    </div>
</div>

<?php if(isset($_SESSION['flash_message'])): ?>
    <div class="max-w-[95%] mx-auto mb-6 p-4 rounded-xl <?= $_SESSION['flash_type'] == 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?> flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm"><?= $_SESSION['flash_message'] ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" class="opacity-50 hover:opacity-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="max-w-[95%] mx-auto">
    <?php if(empty($invoices)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50 bg-white rounded-3xl border border-slate-200 shadow-sm">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Tagihan Manual</h3>
        </div>
    <?php else: ?>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="py-4 px-6 w-16">#</th>
                        <th class="py-4 px-4">Invoice / Kode Token</th>
                        <th class="py-4 px-4">Tagihan & Entri</th>
                        <th class="py-4 px-4 text-center">Bukti Transfer</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($invoices as $i => $inv): 
                        $code = $inv['invoice_code'];
                        $details = $invoiceDetails[$code] ?? [];
                        $status = $inv['status'];
                    ?>
                    <tr class="group hover:bg-slate-50 transition <?= $status == 'Pending' ? 'bg-amber-50/40' : '' ?>">
                        
                        <td class="py-4 px-6 font-black text-slate-300 italic"><?= $i + 1 ?></td>
                        
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-lg shadow-sm border border-slate-200">
                                    🎫
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs">
                                        <?= htmlspecialchars($code) ?>
                                    </h4>
                                    <div class="text-[10px] font-bold text-slate-400">
                                        <?= date('d M Y H:i', strtotime($inv['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <?= count($details) ?> Entri Atlet
                                </span>
                                <?php if($inv['total_amount'] > 0): ?>
                                <span class="text-[10px] font-bold text-slate-400">
                                    Tagihan: <span class="text-emerald-600">Rp <?= number_format($inv['total_amount'], 0, ',', '.') ?></span>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php if(!empty($inv['payment_proof'])): ?>
                                <a href="<?= getenv('APP_URL') ?>/uploads/payments/<?= htmlspecialchars($inv['payment_proof']) ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 text-[9px] font-bold uppercase transition">
                                    👁️ Lihat Bukti
                                </a>
                            <?php else: ?>
                                <span class="text-[9px] font-bold text-slate-400 italic">Belum Upload</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php 
                            $badgeClass = match($status) {
                                'Paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'Pending' => 'bg-amber-100 text-amber-700 border-amber-200 animate-pulse',
                                'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-slate-100 text-slate-500 border-slate-200'
                            };
                            ?>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border <?= $badgeClass ?>">
                                <?= $status ?>
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                
                                <button type="button" onclick="document.getElementById('details_<?= $i ?>').classList.toggle('hidden')" class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 text-[10px] font-black uppercase transition shadow-lg shadow-slate-200">
                                    Lihat Detail
                                </button>

                                <?php if($status === 'Paid'): ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" class="inline" onsubmit="return confirm('Batal Verifikasi Lunas? Status akan kembali Pending.');">
                                        <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                        <input type="hidden" name="action" value="rollback">
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-500 hover:text-white text-[9px] font-black uppercase transition" title="Batal Verifikasi">
                                            ⏪ Batal
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" class="inline" onsubmit="return confirm('Tolak Pembayaran ini?');">
                                        <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-500 hover:text-white flex items-center justify-center transition" title="Tolak">
                                            ✕
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" class="inline" onsubmit="return confirm('Verifikasi LUNAS?');">
                                        <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition" title="Terima">
                                            ✓
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_invoice_action" class="inline ml-2" onsubmit="return confirm('Hapus permanen tagihan dan SELURUH entri terkait? Tindakan ini tidak bisa dibatalkan.');">
                                    <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-500 hover:text-white flex items-center justify-center transition" title="Hapus Permanen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    
                    <!-- Rincian Atlet Row (Hidden by default) -->
                    <tr id="details_<?= $i ?>" class="hidden bg-slate-50/50">
                        <td colspan="6" class="p-6 border-b border-slate-200">
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">Rincian Atlet (<?= count($details) ?> Entri)</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <?php foreach($details as $d): ?>
                                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex flex-col justify-center hover:border-blue-200 transition">
                                            <div class="text-xs font-black text-slate-800 uppercase mb-1 flex justify-between items-start gap-2">
                                                <span><?= htmlspecialchars($d['skater_name']) ?></span>
                                                <?php if($d['team_name']): ?>
                                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-[9px] truncate max-w-[80px]" title="<?= htmlspecialchars($d['team_name']) ?>"><?= htmlspecialchars($d['team_name']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[10px] font-bold text-slate-500 mb-1">
                                                Klub Asal: <?= htmlspecialchars($d['club_name'] ?? '-') ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400 leading-tight">
                                                Lomba: <?= htmlspecialchars($d['distance_name'] . ' - ' . $d['group_name']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>
