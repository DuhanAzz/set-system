<div class="mb-8 font-sans">
    <h1 class="text-3xl font-black text-slate-800 uppercase italic">Tagihan Saya</h1>
    <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">Kelola pembayaran event klub Anda</p>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold shadow-sm <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> flex justify-between items-center">
        <div>
            <?= $_SESSION['flash_type'] === 'success' ? '✅' : '❌' ?> <?= $_SESSION['flash_message'] ?>
        </div>
        <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
    </div>
    <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden font-sans">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-900 text-slate-300 font-black uppercase text-[10px] tracking-widest">
            <tr>
                <th class="px-6 py-5 rounded-tl-3xl">Event</th>
                <th class="px-6 py-5 text-center">Status Lunas</th>
                <th class="px-6 py-5 text-right rounded-tr-3xl">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if(empty($events)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-bold italic">Belum ada riwayat pendaftaran/tagihan.</td>
                </tr>
            <?php endif; ?>

            <?php foreach($events as $comp): 
                $tgl = !empty($comp['event_date_start']) && $comp['event_date_start'] != '0000-00-00' ? date('d F Y', strtotime($comp['event_date_start'])) : 'TBA';
            ?>
            <tr class="hover:bg-slate-50 transition group">
                <td class="px-6 py-5 align-top">
                    <div class="font-black text-slate-800 text-base uppercase italic mb-1 group-hover:text-blue-600 transition">
                        <?= htmlspecialchars($comp['event_name']) ?>
                    </div>
                    <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        📅 Mulai: <b class="text-slate-700"><?= $tgl ?></b>
                    </div>
                </td>
                
                <td class="px-6 py-5 align-middle text-center">
                    <?php if($comp['unpaid_count'] > 0): ?>
                        <span class="bg-slate-100 text-slate-600 border border-slate-200 px-4 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase shadow-sm">Ada <?= $comp['unpaid_count'] ?> Tagihan</span>
                    <?php else: ?>
                        <span class="bg-emerald-100 text-emerald-700 border border-emerald-200 px-4 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase shadow-sm">Lunas ✅</span>
                    <?php endif; ?>
                </td>

                <td class="px-6 py-5 align-middle text-right flex justify-end gap-2">
                    <a href="<?= getenv('APP_URL') ?>/roll/user/checkout/detail/<?= $comp['id'] ?>" class="text-blue-600 bg-blue-50 px-6 py-3 rounded-xl font-black text-[10px] tracking-widest uppercase border border-blue-200 hover:bg-blue-600 hover:text-white transition shadow-sm">
                        Rincian Tagihan &rarr;
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
