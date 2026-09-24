<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Atlet - <?= htmlspecialchars($event['event_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: white; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
        }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .print-container { max-width: 210mm; margin: 0 auto; background: white; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        @media print { .print-container { box-shadow: none; padding: 0; } }
    </style>
</head>
<body class="text-slate-800 text-sm">

    <!-- Floating Print Button -->
    <div class="no-print fixed bottom-6 right-6 flex gap-3 z-50">
        <button onclick="window.close()" class="px-6 py-3 bg-slate-800 text-white rounded-full font-bold shadow-lg hover:bg-slate-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            Tutup
        </button>
        <button onclick="window.print()" class="px-6 py-3 bg-blue-600 text-white rounded-full font-bold shadow-lg hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak PDF
        </button>
    </div>

    <div class="print-container">
        <!-- Cover Page (Optional, but good for Book) -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-black uppercase tracking-widest text-slate-800 mb-2">BUKU ATLET</h1>
            <h2 class="text-xl font-bold text-slate-600 mb-1"><?= htmlspecialchars($event['event_name']) ?></h2>
            <p class="text-sm text-slate-500 uppercase"><?= htmlspecialchars($event['event_city']) ?>, <?= date('d M Y', strtotime($event['event_date_start'])) ?></p>
        </div>

        <div class="w-full h-[2px] bg-slate-800 mb-8"></div>

        <?php if (empty($clubsData)): ?>
            <div class="text-center p-12 text-slate-500 italic border-2 border-dashed border-slate-300 rounded-xl">
                Belum ada data atlet yang terdaftar di kelas individu.
            </div>
        <?php else: ?>
            <?php foreach ($clubsData as $clubName => $athletes): ?>
                <div class="mb-10 avoid-break">
                    <!-- Header Klub -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-2 h-6 bg-blue-600 rounded"></div>
                        <h3 class="text-lg font-black uppercase text-slate-800 tracking-wide"><?= htmlspecialchars($clubName) ?></h3>
                        <div class="ml-auto text-xs font-bold bg-slate-100 text-slate-500 px-3 py-1 rounded-full border border-slate-200">
                            <?= count($athletes) ?> Atlet
                        </div>
                    </div>

                    <!-- Tabel Atlet -->
                    <table class="w-full text-left border-collapse border border-slate-300">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-300 text-xs uppercase tracking-wider text-slate-600">
                                <th class="p-2 border-r border-slate-300 w-12 text-center">No</th>
                                <th class="p-2 border-r border-slate-300 w-24 text-center">No BiB</th>
                                <th class="p-2 border-r border-slate-300">Nama Atlet</th>
                                <th class="p-2 w-32 text-center">KU</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($athletes as $ath): ?>
                                <tr class="border-b border-slate-200 hover:bg-slate-50">
                                    <td class="p-2 border-r border-slate-300 text-center font-medium"><?= $no++ ?></td>
                                    <td class="p-2 border-r border-slate-300 text-center font-black text-blue-700 text-base"><?= htmlspecialchars($ath['bib_number'] ?? '-') ?></td>
                                    <td class="p-2 border-r border-slate-300 font-bold text-slate-800 uppercase"><?= htmlspecialchars($ath['skater_name']) ?></td>
                                    <td class="p-2 text-center font-bold text-slate-600 uppercase text-xs"><?= htmlspecialchars($ath['ku']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="mt-12 text-center text-xs text-slate-400 italic">
            Dicetak oleh sistem pada <?= date('d M Y H:i:s') ?>
        </div>
    </div>
</body>
</html>
