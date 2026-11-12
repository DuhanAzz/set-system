<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="<?= getenv('APP_URL') ?>/swim/admin/medal_tally/best_swimmer?event_id=<?= (int)$eventId ?>&team_source=<?= htmlspecialchars($team_source) ?>" class="inline-flex items-center gap-2 text-blue-600 font-bold text-sm hover:underline mb-2">
                &larr; Kembali ke Leaderboard Perenang Terbaik
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-800 uppercase tracking-tight">⚖️ Perbandingan Tie-Breaker (Draw)</h1>
            <p class="text-slate-500 text-sm mt-1">Kejuaraan: <strong><?= htmlspecialchars($eventName) ?></strong></p>
        </div>
    </div>

    <?php if (empty($comparisonData)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 font-semibold">
            Data perenang untuk perbandingan tidak ditemukan pada event ini.
        </div>
    <?php else: ?>
        <?php foreach ($comparisonData as $index => $data): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <?php if ($index === 0 && count($comparisonData) > 1): ?>
                    <div class="bg-yellow-400 text-yellow-950 px-4 py-2 text-center font-black text-xs uppercase tracking-widest border-b border-yellow-500">
                        🏆 Pemenang Peringkat Tertinggi (Tie-Breaker Rekornas)
                    </div>
                <?php endif; ?>

                <div class="p-5 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-900 text-white text-xs font-black">#<?= $index + 1 ?></span>
                            <h2 class="text-lg font-black text-slate-900 uppercase"><?= htmlspecialchars($data['nama_atlet']) ?></h2>
                            <span class="px-2 py-0.5 rounded text-xs font-bold <?= $data['jenis_kelamin'] === 'P' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= $data['jenis_kelamin'] === 'P' ? 'Putri' : 'Putra' ?>
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-500 uppercase mt-1"><?= htmlspecialchars($data['team_name']) ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-black">🥇 <?= (int)$data['gold'] ?></span>
                        <span class="px-3 py-1 rounded-lg bg-slate-200 text-slate-700 text-xs font-black">🥈 <?= (int)$data['silver'] ?></span>
                        <span class="px-3 py-1 rounded-lg bg-orange-100 text-orange-800 text-xs font-black">🥉 <?= (int)$data['bronze'] ?></span>
                        <span class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-sm font-black shadow-sm">
                            ⚡ Skor Tertinggi: <?= number_format((float)$data['best_pct'], 2) ?>%
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-slate-600 font-bold text-xs tracking-wider uppercase border-b border-slate-200">
                                <th class="p-4">Acara</th>
                                <th class="p-4">Pemegang Rekor Nasional</th>
                                <th class="p-4 text-center">Waktu Rekor</th>
                                <th class="p-4 text-center bg-slate-100">Detik</th>
                                <th class="p-4 text-center text-emerald-700">Waktu Atlet</th>
                                <th class="p-4 text-center bg-emerald-50 text-emerald-700">Detik</th>
                                <th class="p-4 text-center text-amber-700">Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($data['races'])): ?>
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-400">Belum ada catatan nomor lomba bermedali.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['races'] as $row): ?>
                                    <?php
                                        $isBestRace = ($data['best_pct'] > 0 && abs(round((float)$row['pct_raw'], 2) - (float)$data['best_pct']) < 0.01);
                                        $medalBadge = '';
                                        if ((int)$row['rank_final'] === 1) {
                                            $medalBadge = '<span class="ml-2 text-[10px] bg-yellow-100 border border-yellow-400 text-yellow-800 px-2 py-0.5 rounded-md uppercase font-bold">Emas 🥇</span>';
                                        } elseif ((int)$row['rank_final'] === 2) {
                                            $medalBadge = '<span class="ml-2 text-[10px] bg-slate-100 border border-slate-400 text-slate-700 px-2 py-0.5 rounded-md uppercase font-bold">Perak 🥈</span>';
                                        } elseif ((int)$row['rank_final'] === 3) {
                                            $medalBadge = '<span class="ml-2 text-[10px] bg-orange-100 border border-orange-400 text-orange-800 px-2 py-0.5 rounded-md uppercase font-bold">Perunggu 🥉</span>';
                                        }
                                    ?>
                                    <tr class="transition <?= $isBestRace ? 'bg-indigo-50/60' : 'hover:bg-slate-50' ?>">
                                        <td class="p-4 font-bold text-slate-900">
                                            <?= htmlspecialchars($row['acara']) ?>
                                            <?= $medalBadge ?>
                                            <?php if ($isBestRace): ?>
                                                <span class="ml-1 text-[9px] bg-indigo-600 text-white px-2 py-0.5 rounded-md uppercase font-bold shadow-sm">Penentu 🚀</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-slate-600 text-[11px] uppercase"><?= htmlspecialchars($row['holder']) ?></td>
                                        <td class="p-4 text-center font-mono font-bold"><?= htmlspecialchars($row['rec_time']) ?></td>
                                        <td class="p-4 text-center font-mono bg-slate-50"><?= htmlspecialchars($row['rec_sec']) ?></td>
                                        <td class="p-4 text-center font-mono font-bold text-emerald-600"><?= htmlspecialchars($row['ath_time']) ?></td>
                                        <td class="p-4 text-center font-mono bg-emerald-50 text-emerald-700"><?= htmlspecialchars($row['ath_sec']) ?></td>
                                        <td class="p-4 text-center font-black <?= $isBestRace ? 'text-indigo-600 text-lg' : 'text-slate-500 text-base' ?>">
                                            <?= htmlspecialchars($row['pct']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-blue-800 text-sm">
        <strong>ℹ️ Mekanisme Tie-Breaker Rekornas:</strong> Rumus yang digunakan adalah <code>(Waktu Rekor Nasional Detik / Waktu Atlet Detik) × 100</code>. Pada kasus perolehan medali yang sama (Draw), atlet dengan <strong>Persentase Tunggal Tertinggi (dari salah satu nomor lombanya)</strong> yang paling mendekati Rekornas otomatis dinyatakan sebagai Perenang Terbaik.
    </div>
</div>
