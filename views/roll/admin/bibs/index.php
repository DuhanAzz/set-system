<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">🧾</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Manajemen Pendaftaran dan BIB</h1>
                    <p class="text-slate-500 mt-2 font-medium">Monitoring atlet per klub dan pembuatan nomor punggung</p>
                </div>
                <?php if($eventId > 0): ?>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/bibs/print" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all flex items-center">
                        <span class="mr-2">🖨️</span> Cetak (PDF)
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/bibs/export_csv" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-emerald-500/25 transition-all flex items-center">
                        <span class="mr-2">📊</span> Export CSV
                    </a>
                    <button type="button" onclick="confirmGenerateBib()" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-amber-500/25 transition-all flex items-center">
                        <span class="mr-2">🔢</span> Generate Nomor BIB
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($eventId == 0): ?>
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php else: ?>

            <!-- TABEL DAFTAR KLUB -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm p-6 w-full">
                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest mb-4 border-b border-slate-200 pb-2">Daftar Klub Pendaftar</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b-2 border-slate-200">
                                <th class="p-4 font-bold">No</th>
                                <th class="p-4 font-bold">Klub</th>
                                <th class="p-4 font-bold">Asal Kota</th>
                                <th class="p-4 font-bold text-center">Total Atlet</th>
                                <th class="p-4 font-bold text-center">Status Pembayaran</th>
                                <th class="p-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (empty($clubs)): ?>
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium italic">Belum ada klub yang mendaftar pada event ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($clubs as $c): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-500 font-bold"><?= $no++ ?></td>
                                        <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($c['club_name']) ?></td>
                                        <td class="p-4 text-slate-600"><?= htmlspecialchars($c['city_province'] ?? '-') ?></td>
                                        <td class="p-4 text-center">
                                            <span class="bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded-full text-xs">
                                                <?= $c['total_athletes'] ?> Atlet
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php if ($c['payment_status'] == 'Paid'): ?>
                                                <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-md text-xs uppercase tracking-widest">Lunas</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 font-bold px-3 py-1 rounded-md text-xs uppercase tracking-widest"><?= $c['payment_status'] ?? 'Unpaid' ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="<?= getenv('APP_URL') ?>/roll/admin/bibs/detail?club_id=<?= $c['id'] ?>" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg shadow hover:shadow-blue-500/25 transition-all text-xs uppercase tracking-widest inline-flex items-center">
                                                <span>Lihat Detail</span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmGenerateBib() {
    Swal.fire({
        title: 'Generate Nomor BIB?',
        text: "Pastikan pendaftaran sudah ditutup sebelum melakukan Generate BIB. Nomor atlet akan diatur ulang berdasarkan urutan abjad klub secara massal. Lanjutkan?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Generate!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= getenv('APP_URL') ?>/roll/admin/bibs/generate';
        }
    })
}
</script>
