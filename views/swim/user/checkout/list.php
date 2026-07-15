<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-800 uppercase italic">Tagihan Saya</h1>
    <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">Kelola pembayaran event Anda</p>
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

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-900 text-slate-300 font-black uppercase text-[10px] tracking-widest">
            <tr>
                <th class="px-6 py-5 rounded-tl-3xl">Event</th>
                <th class="px-6 py-5">Total Tagihan</th>
                <th class="px-6 py-5 text-center">Status</th>
                <th class="px-6 py-5 text-right rounded-tr-3xl">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if(empty($bills)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold italic">Belum ada riwayat tagihan.</td>
                </tr>
            <?php endif; ?>

            <?php foreach($bills as $b): ?>
            <tr class="hover:bg-slate-50 transition group">
                <td class="px-6 py-5 align-top">
                    <div class="font-black text-slate-800 text-base uppercase italic mb-1 group-hover:text-blue-600 transition">
                        <?= htmlspecialchars($b['event_name']) ?>
                    </div>
                    <span class="text-[10px] bg-slate-100 px-2 py-1 rounded text-slate-500 font-mono font-bold tracking-wider">
                        #INV-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?>
                    </span>
                    <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Atlet Terdaftar: <b class="text-slate-700"><?= $b['entries'] ?></b>
                    </div>
                </td>
                
                <td class="px-6 py-5 align-middle">
                    <div class="font-black text-xl text-slate-700">
                        Rp <?= number_format($b['amount'], 0, ',', '.') ?>
                    </div>
                </td>

                <td class="px-6 py-5 align-middle text-center">
                    <?php if($b['status'] == 'Paid' || $b['status'] == 'completed'): ?>
                        <span class="bg-emerald-100 text-emerald-700 border border-emerald-200 px-4 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase shadow-sm">Lunas</span>
                    <?php elseif($b['status'] == 'Pending' || $b['status'] == 'pending'): ?>
                        <span class="bg-amber-100 text-amber-700 border border-amber-200 px-4 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase shadow-sm">Verifikasi</span>
                    <?php else: ?>
                        <span class="bg-slate-100 text-slate-600 border border-slate-200 px-4 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase shadow-sm">Unpaid</span>
                    <?php endif; ?>
                </td>

                <td class="px-6 py-5 align-middle text-right flex justify-end gap-2">
                    <?php if($b['status'] != 'Paid' && $b['status'] != 'completed' && $b['status'] != 'Pending' && $b['status'] != 'pending'): ?>
                        <button onclick="bukaModal('<?= $b['event_id'] ?>', '<?= htmlspecialchars(addslashes($b['event_name'])) ?>', '<?= $b['amount'] ?>')" 
                            class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] tracking-widest uppercase shadow-lg hover:bg-blue-600 transition hover:-translate-y-0.5">
                            Upload Bukti
                        </button>
                    <?php endif; ?>
                    <a href="<?= getenv('APP_URL') ?>/swim/checkout/detail/<?= $b['event_id'] ?>" class="text-blue-600 bg-blue-50 px-6 py-3 rounded-xl font-black text-[10px] tracking-widest uppercase border border-blue-200 hover:bg-blue-600 hover:text-white transition shadow-sm">
                        Rincian
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="uploadModal" class="hidden fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
    <div class="bg-white w-full max-w-sm rounded-3xl p-8 shadow-2xl transform transition-all relative">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="font-black text-slate-800 uppercase italic text-xl">Upload Bukti</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Transfer Pembayaran</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-2xl text-slate-300 hover:text-red-500 transition outline-none">&times;</button>
        </div>

        <form method="POST" id="uploadForm" action="<?= getenv('APP_URL') ?>/swim/checkout/upload_proof/" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="from_list" value="1">
            
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Event Tujuan</p>
                <p class="font-black text-slate-700 text-sm leading-tight" id="modalEventName"></p>
            </div>
            
            <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest mb-1">Total Bayar</p>
                <p class="font-black text-emerald-700 text-2xl tracking-tighter" id="modalAmount"></p>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Pilih File (JPG/PNG/PDF)</label>
                <input type="file" name="bukti_transfer" required accept="image/*,application/pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-slate-900 file:text-white hover:file:bg-blue-600 file:transition border border-slate-200 rounded-xl p-1 bg-slate-50 cursor-pointer">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl uppercase text-[10px] tracking-widest hover:bg-slate-900 shadow-lg shadow-blue-200 transition active:scale-95 mt-2 outline-none">
                Kirim Bukti Sekarang
            </button>
        </form>
    </div>
</div>

<script>
function bukaModal(eventId, nama, amount) {
    document.getElementById('uploadForm').action = "<?= getenv('APP_URL') ?>/swim/checkout/upload_proof/" + eventId;
    document.getElementById('modalEventName').innerText = nama;
    document.getElementById('modalAmount').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(amount);
    document.getElementById('uploadModal').classList.remove('hidden');
}
</script>
