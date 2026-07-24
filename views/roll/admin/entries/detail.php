<style>
    /* CSS ini hanya agar tampilan di layar Admin enak dilihat */
    .card-atlet { 
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }
    /* Warna Gender */
    .bg-blue-500 { background-color: #3b82f6; color: white; }
    .bg-pink-500 { background-color: #ec4899; color: white; }
    .text-blue-600 { color: #2563eb; }
    .text-pink-600 { color: #db2777; }
</style>

<div class="max-w-7xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition shadow-sm">←</a>
        <div>
            <h1 class="text-2xl font-black uppercase italic text-slate-900 leading-none">Verifikasi & Tagihan</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">
                Klub/Kontingen: <span class="text-blue-600"><?= htmlspecialchars($clubName) ?></span>
            </p>
        </div>
    </div>
    
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries/print_invoice?id=<?= $targetUserId ?>&event_id=<?= $eventId ?>" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all flex items-center">
            <span class="mr-2">🖨️</span> Cetak Invoice / Tanda Terima
        </a>
    </div>
</div>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 pb-20 main-content">
    
    <div class="space-y-6 h-fit sticky top-24">
        <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl text-white relative overflow-hidden">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Tagihan Seharusnya</p>
            <h2 class="text-3xl font-black text-white tracking-tighter">Rp <?= number_format($totalTagihan,0,',','.') ?></h2>
            <div class="mt-4 pt-4 border-t border-slate-700 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400">Total Atlet</span>
                <span class="text-xs font-black bg-blue-600 px-2 py-1 rounded text-white"><?= count($groupedSkaters) ?></span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-xl">🛼</div>
                <div class="overflow-hidden">
                    <h2 class="text-sm font-black text-slate-800 uppercase leading-tight truncate"><?= htmlspecialchars($clubName) ?></h2>
                    <p class="text-[10px] font-bold text-slate-400 truncate"><?= htmlspecialchars($emailUser) ?></p>
                </div>
            </div>

            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bukti Upload</h3>
            <?php if(!empty($payData['payment_proof'])): ?>
                <a href="<?= getenv('APP_URL') ?>/uploads/payments/<?= htmlspecialchars($payData['payment_proof']) ?>" target="_blank" class="block group relative rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-video flex items-center justify-center cursor-pointer shadow-sm mb-4">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white font-bold text-[10px] uppercase backdrop-blur-sm">Lihat File</div>
                    <?php $ext = pathinfo($payData['payment_proof'], PATHINFO_EXTENSION); ?>
                    <?php if(in_array(strtolower($ext), ['jpg','jpeg','png'])): ?>
                        <img src="<?= getenv('APP_URL') ?>/uploads/payments/<?= htmlspecialchars($payData['payment_proof']) ?>" class="object-cover w-full h-full">
                    <?php else: ?>
                        <span class="text-4xl">📄</span>
                    <?php endif; ?>
                </a>
                
                <?php $statusPay = $payData['status'] ?? 'Unpaid'; if($statusPay == 'Pending' || $statusPay == 'Paid'): ?>
                    <div class="grid grid-cols-2 gap-2">
                        <?php if($statusPay == 'Pending'): ?>
                            <button onclick="openModal('approve')" class="col-span-2 bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl text-xs font-black uppercase shadow-md">✓ Terima</button>
                            <button onclick="openModal('reject')" class="col-span-2 bg-red-100 hover:bg-red-200 text-red-600 py-3 rounded-xl text-xs font-black uppercase">✕ Tolak</button>
                        <?php elseif($statusPay == 'Paid'): ?>
                            <button onclick="openModal('rollback')" class="col-span-2 bg-slate-100 hover:bg-orange-100 text-slate-500 hover:text-orange-600 border border-slate-200 py-2 rounded-lg text-[10px] font-bold uppercase">⏪ Batal Verifikasi (Rollback)</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-6 border border-dashed border-slate-300 rounded-xl bg-slate-50 text-[10px] text-slate-400 font-bold mb-4">Belum ada bukti</div>
                
                <?php $statusPay = $payData['status'] ?? 'Unpaid'; if($statusPay == 'Unpaid'): ?>
                    <button onclick="openModal('approve')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl text-xs font-black uppercase shadow-md">✓ Approve (Bypass Tanpa Bukti)</button>
                <?php elseif($statusPay == 'Paid'): ?>
                    <button onclick="openModal('rollback')" class="w-full bg-slate-100 hover:bg-orange-100 text-slate-500 hover:text-orange-600 border border-slate-200 py-2 rounded-lg text-[10px] font-bold uppercase">⏪ Batal Verifikasi (Rollback)</button>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2">
        <?php if(empty($groupedSkaters)): ?>
            <div class="bg-white rounded-[2.5rem] p-10 text-center border border-slate-200">
                <p class="font-bold text-slate-400 text-sm">Belum ada atlet yang didaftarkan.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
            
            <?php foreach($groupedSkaters as $skaterId => $data): 
                $info = $data['info'];
                $events = $data['items'];
                $isMale = ($info['gender'] == 'Putra');
                $bgHeader = $isMale ? 'bg-blue-50' : 'bg-pink-50'; 
                $textColor = $isMale ? 'text-blue-600' : 'text-pink-600';
                $iconColor = $isMale ? 'bg-blue-500' : 'bg-pink-500';
                $subtotal = $data['subtotal'];
            ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm card-atlet">
                    <div class="p-4 flex justify-between items-center border-b border-slate-100 <?= $bgHeader ?> card-header">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full <?= $iconColor ?> text-white flex items-center justify-center text-[10px] font-black shadow-sm">
                                <?= $isMale ? 'P' : 'W' ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase italic leading-tight">
                                    <?= htmlspecialchars($info['nama']) ?>
                                </h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <?= $info['gender'] ?> • Lahir: <?= date('Y', strtotime($info['lahir'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-bold text-sm <?= $textColor ?>">
                                Rp <?= number_format($subtotal, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <?php foreach($events as $ev): ?>
                            <div class="px-4 py-3 flex justify-between items-center hover:bg-slate-50 transition-colors card-row">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 uppercase">
                                        <?= $ev['distance'] ?> - <?= strtoupper($ev['stroke']) ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium mt-0.5 uppercase">
                                        KU <?= $ev['age_group'] ?>
                                    </span>
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

<form id="actionForm" method="POST" action="<?= getenv('APP_URL') ?>/roll/admin/entries/detail?id=<?= $targetUserId ?>&event_id=<?= $eventId ?>" class="hidden">
    <input type="hidden" name="payment_id" value="<?= $payData['id'] ?? '' ?>">
    <input type="hidden" name="action_type" id="modalActionInput">
</form>

<script>
function openModal(action) {
    if(confirm(action === 'approve' ? 'Terima & Kunci Data?' : 'Batal/Tolak Verifikasi?')) {
        document.getElementById('modalActionInput').value = action;
        document.getElementById('actionForm').submit();
    }
}
</script>
