<?php include __DIR__ . '/layout/header.php'; ?>

<div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 pt-10 px-4 sm:px-6 lg:px-8">
            <main class="mt-10 mx-auto max-w-7xl sm:mt-12 md:mt-16 lg:mt-20 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-slate-900 sm:text-5xl md:text-6xl">
                        <span class="block xl:inline">Portal Resmi</span>
                        <span class="block text-blue-600 xl:inline">Kejuaraan Sepatu Roda</span>
                    </h1>
                    <p class="mt-3 text-base text-slate-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Akses langsung ke seluruh informasi pertandingan, mulai dari daftar atlet, penyisihan, klasemen medali, hingga hasil akhir secara real-time dan transparan.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a href="<?= getenv('APP_URL') ?>/roll/live" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10 transition-all">
                                Lihat Hasil Langsung
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-slate-50 flex items-center justify-center p-12">
        <div class="text-9xl opacity-20">🛼🏁</div>
    </div>
</div>

<div class="bg-slate-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Kejuaraan Berlangsung</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Event Aktif Saat Ini
            </p>
        </div>

        <?php if (!empty($event)): ?>
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden flex flex-col md:flex-row items-center">
                <div class="w-full md:w-1/3 bg-slate-100 flex items-center justify-center p-8 h-64 md:h-auto">
                    <?php if(!empty($event['poster_image'])): ?>
                        <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars($event['poster_image']) ?>" class="max-h-48 object-contain">
                    <?php else: ?>
                        <span class="text-7xl">🏆</span>
                    <?php endif; ?>
                </div>
                <div class="w-full md:w-2/3 p-8 md:p-12">
                    <div class="uppercase tracking-wide text-sm text-blue-600 font-bold mb-1">
                        <?= htmlspecialchars($event['status']) ?>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-2">
                        <?= htmlspecialchars($event['event_name']) ?>
                    </h3>
                    <p class="text-slate-500 mb-6 flex flex-col gap-2">
                        <span class="flex items-center gap-2">📅 <?= htmlspecialchars($event['start_date']) ?> s/d <?= htmlspecialchars($event['end_date']) ?></span>
                        <span class="flex items-center gap-2">📍 <?= htmlspecialchars($event['event_location'] ?? '-') ?> <?= htmlspecialchars($event['event_city']) ?></span>
                    </p>
                    <a href="<?= getenv('APP_URL') ?>/roll/live" class="inline-block bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold py-2 px-6 rounded-lg transition-colors border border-blue-200">
                        Buka Halaman Event &rarr;
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
                <span class="text-4xl mb-4 block">⚠️</span>
                <h3 class="text-lg font-bold text-slate-600 mb-1">Data Belum Tersedia</h3>
                <p class="text-slate-500 text-sm">Belum ada kejuaraan aktif yang dipublikasikan saat ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
