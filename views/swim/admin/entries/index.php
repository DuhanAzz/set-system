<div class="max-w-[95%] mx-auto mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Verifikasi Pendaftaran</h1>
        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">
            Event: <span class="text-blue-600 font-black"><?= htmlspecialchars($eventName) ?></span> 
            <span class="text-slate-300 mx-2">|</span> ID: #<?= $targetEventId ?>
        </p>
    </div>
    
    <div class="flex flex-wrap items-center gap-3">
        <?php if (!empty($allEvents) && count($allEvents) > 1): ?>
        <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm">
            <label for="eventPicker" class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Pilih Event:</label>
            <select id="eventPicker" class="text-xs font-bold text-slate-800 bg-transparent border-none outline-none focus:ring-0 cursor-pointer" onchange="window.location.href='<?= getenv('APP_URL') ?>/swim/admin/entries/index?event_id=' + this.value">
                <?php foreach ($allEvents as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= ($ev['id'] == $targetEventId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['event_name']) ?> (#<?= $ev['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="px-5 py-2 bg-white rounded-xl shadow-sm border border-slate-200 text-right">
            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Pendaftar</span>
            <span class="block text-xl font-black text-slate-800"><?= count($listData) ?></span>
        </div>
    </div>
</div>

<div class="max-w-[95%] mx-auto bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden min-h-[500px]">
    
    <?php if($targetEventId == 0): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">⚠️</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Anda Belum Memiliki Event Aktif</h3>
        </div>
    <?php elseif(empty($listData)): ?>
        <div class="flex flex-col items-center justify-center py-32 text-center opacity-50">
            <div class="text-5xl mb-4 grayscale">📭</div>
            <h3 class="font-black text-slate-400 uppercase tracking-widest text-lg">Belum Ada Pendaftar Pada Event Ini</h3>
        </div>
    <?php else: ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest w-12">No</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Klub / Kontingen</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Ringkasan</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Bukti Bayar</th>
                        <th class="py-4 px-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="py-4 px-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($listData as $i => $row): 
                        $status = $row['payment_status'];
                    ?>
                    <tr class="group hover:bg-slate-50 transition <?= $status == 'Pending' ? 'bg-amber-50/40' : '' ?>">
                        
                        <td class="py-4 px-6 font-black text-slate-300 italic"><?= $i + 1 ?></td>
                        
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-lg shadow-sm border border-slate-200">
                                    🏊
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 uppercase text-xs">
                                        <?= htmlspecialchars($row['club_name'] ?? $row['nama_lengkap']) ?>
                                    </h4>
                                    <div class="text-[10px] font-bold text-slate-400">
                                        <?= htmlspecialchars($row['nama_lengkap']) ?> • <?= htmlspecialchars($row['email']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-600">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <b><?= $row['total_swimmers'] ?? 0 ?></b> Atlet • <b><?= $row['total_entries'] ?? 0 ?></b> Nomor
                                </span>
                                <span class="text-[10px] font-bold text-slate-400">
                                    Tagihan: <span class="text-emerald-600 font-black">Rp <?= number_format($row['amount'], 0, ',', '.') ?></span>
                                </span>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php if(!empty($row['file_path'])): ?>
                                <a href="<?= getenv('APP_URL') ?>/public/uploads/payments/<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 text-[9px] font-bold uppercase transition">
                                    👁️ Lihat Bukti
                                </a>
                            <?php else: ?>
                                <span class="text-[9px] font-bold text-slate-400 italic">Belum Upload</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php 
                            $badgeClass = match($status) {
                                'Paid', 'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200 shadow-sm',
                                'Pending', 'pending' => 'bg-amber-100 text-amber-700 border-amber-200 animate-pulse',
                                'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-slate-100 text-slate-500 border-slate-200'
                            };
                            $labelStatus = match($status) {
                                'completed' => 'Paid',
                                'pending' => 'Pending',
                                default => ucfirst(strtolower($status))
                            };
                            ?>
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border <?= $badgeClass ?>">
                                <?= $labelStatus ?>
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                
                                <a href="<?= getenv('APP_URL') ?>/swim/admin/entries/detail?id=<?= $row['club_id'] ?>&event_id=<?= $row['event_id'] ?>" 
                                   class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900 text-[10px] font-black uppercase transition shadow-lg shadow-slate-200">
                                    Rincian
                                </a>

                                <?php if($status == "Paid" || $status == "completed"): ?>
                                    <form method="POST" action="<?= getenv("APP_URL") ?>/swim/admin/entries/index" class="inline" onsubmit="return confirm('Batal Verifikasi Lunas? Status akan kembali Pending.');">
                                        <input type="hidden" name="rollback_payment_id" value="<?= $row["payment_id"] ?>">
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-500 hover:text-white text-[9px] font-black uppercase transition" title="Batal Verifikasi">
                                            ⏪ Batal
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if($status == 'Pending' || $status == 'pending'): ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/entries/index" class="inline" onsubmit="return confirm('Tolak Pembayaran ini?');">
                                        <input type="hidden" name="reject_payment_id" value="<?= $row['payment_id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-500 hover:text-white flex items-center justify-center transition" title="Tolak">
                                            ✕
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/entries/index" class="inline" onsubmit="return confirm('Verifikasi LUNAS?');">
                                        <input type="hidden" name="approve_payment_id" value="<?= $row['payment_id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition" title="Terima (Lunas)">
                                            ✓
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if($status == 'Unpaid' || $status == 'Rejected'): ?>
                                    <form method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/entries/index" class="inline" onsubmit="return confirm('Tandai pembayaran ini LUNAS secara manual?');">
                                        <input type="hidden" name="approve_payment_id" value="<?= $row['payment_id'] ?>">
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-[9px] font-black uppercase transition" title="Verifikasi Manual">
                                            ✓ Lunaskan
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>
