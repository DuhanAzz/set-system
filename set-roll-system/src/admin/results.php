<?php
// FILE: src/admin/results.php
require_once __DIR__ . '/../config/database.php';

$msg = '';

// --- LOGIKA BULK UPDATE (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_results') {
    $result_ids = $_POST['result_id'] ?? [];
    $format = $_POST['race_format_post'] ?? '';
    
    // Data Dinamis
    $times = $_POST['time_ms'] ?? [];
    $advancements = $_POST['advancement'] ?? [];
    $points = $_POST['total_points'] ?? [];
    $finish_positions = $_POST['finish_position'] ?? [];
    $eliminations = $_POST['is_eliminated'] ?? [];

    if (!empty($result_ids)) {
        try {
            $pdo->beginTransaction();
            $count = 0;
            
            foreach ($result_ids as $index => $r_id) {
                if (in_array($format, ['TIME_TRIAL', 'SPRINT', 'DTT'])) {
                    $t = (isset($times[$index]) && $times[$index] !== '') ? (int)$times[$index] : null;
                    $adv = $advancements[$index] ?? 'Menunggu';
                    $stmt = $pdo->prepare("UPDATE roll_event_results SET finish_time_ms = ?, advancement_status = ? WHERE id = ?");
                    $stmt->execute([$t, $adv, $r_id]);
                } 
                elseif ($format === 'PTP') {
                    $pt = (isset($points[$index]) && $points[$index] !== '') ? (int)$points[$index] : 0;
                    $fp = (isset($finish_positions[$index]) && $finish_positions[$index] !== '') ? (int)$finish_positions[$index] : null;
                    $stmt = $pdo->prepare("UPDATE roll_event_results SET total_points = ?, finish_position = ? WHERE id = ?");
                    $stmt->execute([$pt, $fp, $r_id]);
                } 
                elseif ($format === 'ELIMINATION') {
                    $elim = (isset($eliminations[$index]) && $eliminations[$index] !== '') ? (int)$eliminations[$index] : 0;
                    $fp = (isset($finish_positions[$index]) && $finish_positions[$index] !== '') ? (int)$finish_positions[$index] : null;
                    $stmt = $pdo->prepare("UPDATE roll_event_results SET is_eliminated = ?, finish_position = ? WHERE id = ?");
                    $stmt->execute([$elim, $fp, $r_id]);
                }
                $count++;
            }
            
            $pdo->commit();
            $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Berhasil menyimpan {$count} data hasil lomba secara serentak!</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Terjadi Kesalahan (Rollback): " . $e->getMessage() . "</div>";
        }
    }
}

// --- FILTER AMBIL DATA EVENT & HEAT (GET) ---
$filter_event_id = $_GET['event_id'] ?? '';
$filter_heat = $_GET['heat_name'] ?? '';

// Ambil list event
$events = $pdo->query("SELECT id, event_name, race_format FROM roll_events ORDER BY id DESC")->fetchAll();

// Ambil list heat unik jika event_id terpilih (Auto-Submit script)
$heats = [];
if ($filter_event_id) {
    $stmtHeats = $pdo->prepare("SELECT DISTINCT heat_name FROM roll_event_results WHERE event_id = ? ORDER BY heat_name ASC");
    $stmtHeats->execute([$filter_event_id]);
    $heats = $stmtHeats->fetchAll(PDO::FETCH_COLUMN);
}

// --- AMBIL DATA HASIL LOMBA ---
$resultsData = [];
$activeFormat = '';

if (!empty($filter_event_id) && !empty($filter_heat)) {
    // Cari format event ini
    $stmtFormat = $pdo->prepare("SELECT race_format FROM roll_events WHERE id = ?");
    $stmtFormat->execute([$filter_event_id]);
    $activeFormat = $stmtFormat->fetchColumn();

    // Query hasil lengkap
    $stmtRes = $pdo->prepare("
        SELECT r.*, s.skater_name, s.age_group, c.club_name, p.start_grid
        FROM roll_event_results r
        JOIN roll_skaters s ON r.skater_id = s.id
        LEFT JOIN roll_clubs c ON s.club_id = c.id
        LEFT JOIN roll_pelotons p ON r.event_id = p.event_id AND r.skater_id = p.skater_id AND r.heat_name = p.heat_name
        WHERE r.event_id = ? AND r.heat_name = ?
        ORDER BY p.start_grid ASC, s.skater_name ASC
    ");
    $stmtRes->execute([$filter_event_id, $filter_heat]);
    $resultsData = $stmtRes->fetchAll();
}
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Mesin Penjurian & Hasil</h2>
                <p class="text-slate-500 mt-1 font-medium">Panel adaptif pencatatan skor/waktu sesuai regulasi format lomba.</p>
            </div>
            <?php if($activeFormat): ?>
            <div class="bg-orange-100 text-orange-800 px-4 py-2 rounded-xl font-bold border border-orange-200">
                Mode Aktif: <?= htmlspecialchars($activeFormat) ?>
            </div>
            <?php endif; ?>
        </div>

        <?= $msg ?>

        <!-- FILTER FORM -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="" class="flex gap-4 items-end" id="filterForm">
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kejuaraan</label>
                    <select name="event_id" onchange="document.getElementById('filterForm').submit();" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                        <option value="">-- Tentukan Kejuaraan --</option>
                        <?php foreach($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= ($filter_event_id == $ev['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['event_name']) ?> (<?= htmlspecialchars($ev['race_format']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Heat / Peloton</label>
                    <select name="heat_name" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                        <option value="">-- Grup --</option>
                        <?php foreach($heats as $h): ?>
                            <option value="<?= htmlspecialchars($h) ?>" <?= ($filter_heat == $h) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-2.5 rounded-xl font-bold shadow-md transition-all">
                        Tarik Data Grid
                    </button>
                </div>
            </form>
        </div>

        <!-- FORM BULK UPDATE (ADAPTIVE UI) -->
        <?php if(!empty($filter_event_id) && !empty($filter_heat)): ?>
            <?php if(empty($resultsData)): ?>
                <div class="text-center py-10 bg-white rounded-2xl border border-slate-200 text-slate-500 font-bold">
                    Grup ini masih kosong / belum ada Grid yang tersimpan.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_results">
                    <input type="hidden" name="race_format_post" value="<?= htmlspecialchars($activeFormat) ?>">

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                        <table class="w-full text-left border-collapse dense-table">
                            <thead>
                                <tr class="bg-slate-900 text-white text-xs uppercase tracking-widest border-b border-slate-700">
                                    <th class="px-4 py-3 font-bold text-center w-16">Grid</th>
                                    <th class="px-4 py-3 font-bold">Atlet</th>
                                    <th class="px-4 py-3 font-bold">Kategori</th>
                                    
                                    <!-- KOLOM DINAMIS HEADER -->
                                    <?php if(in_array($activeFormat, ['TIME_TRIAL', 'SPRINT', 'DTT'])): ?>
                                        <th class="px-4 py-3 font-bold text-center w-40">Waktu (ms)</th>
                                        <th class="px-4 py-3 font-bold text-center w-40">Status Lolos</th>
                                    <?php elseif($activeFormat === 'PTP'): ?>
                                        <th class="px-4 py-3 font-bold text-center w-32">Total Poin</th>
                                        <th class="px-4 py-3 font-bold text-center w-32">Posisi Finis</th>
                                    <?php elseif($activeFormat === 'ELIMINATION'): ?>
                                        <th class="px-4 py-3 font-bold text-center w-32">Eliminasi</th>
                                        <th class="px-4 py-3 font-bold text-center w-32">Posisi Akhir</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($resultsData as $index => $r): ?>
                                <tr class="hover:bg-slate-50 transition-colors <?= ($r['is_eliminated']) ? 'bg-red-50/50' : '' ?>">
                                    
                                    <!-- KOLOM STATIS -->
                                    <td class="px-4 py-2 font-black text-slate-800 text-center text-lg">
                                        <?= htmlspecialchars($r['start_grid'] ?? '-') ?>
                                        <input type="hidden" name="result_id[]" value="<?= $r['id'] ?>">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="font-bold text-slate-900 leading-tight"><?= htmlspecialchars($r['skater_name']) ?></div>
                                        <div class="text-[10px] text-slate-500 font-medium tracking-wide uppercase"><?= htmlspecialchars($r['club_name'] ?? '-') ?></div>
                                    </td>
                                    <td class="px-4 py-2 text-xs font-bold text-slate-500">
                                        <?= htmlspecialchars($r['age_group']) ?>
                                    </td>
                                    
                                    <!-- KOLOM DINAMIS ISI -->
                                    <?php if(in_array($activeFormat, ['TIME_TRIAL', 'SPRINT', 'DTT'])): ?>
                                        <td class="px-4 py-2 text-center">
                                            <input type="number" name="time_ms[]" value="<?= htmlspecialchars($r['finish_time_ms'] ?? '') ?>" placeholder="Misal: 15400" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-800 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold transition font-mono text-sm">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <select name="advancement[]" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-700 rounded px-2 py-1 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                <option value="Menunggu" <?= ($r['advancement_status']=='Menunggu')?'selected':'' ?>>Menunggu</option>
                                                <option value="Lolos" <?= ($r['advancement_status']=='Lolos')?'selected':'' ?>>Lolos</option>
                                                <option value="Gugur" <?= ($r['advancement_status']=='Gugur')?'selected':'' ?>>Gugur</option>
                                            </select>
                                        </td>

                                    <?php elseif($activeFormat === 'PTP'): ?>
                                        <td class="px-4 py-2 text-center">
                                            <input type="number" name="total_points[]" value="<?= htmlspecialchars($r['total_points'] ?? '') ?>" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-800 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 font-black transition text-lg">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="number" name="finish_position[]" value="<?= htmlspecialchars($r['finish_position'] ?? '') ?>" placeholder="Finis Ke-" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-800 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold transition">
                                        </td>

                                    <?php elseif($activeFormat === 'ELIMINATION'): ?>
                                        <td class="px-4 py-2 text-center">
                                            <select name="is_eliminated[]" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-700 rounded px-2 py-1 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-orange-500 <?= ($r['is_eliminated']) ? 'text-red-600 bg-red-100 border-red-300' : '' ?>">
                                                <option value="0" <?= (!$r['is_eliminated'])?'selected':'' ?>>Bertahan (Active)</option>
                                                <option value="1" <?= ($r['is_eliminated'])?'selected':'' ?>>Gugur (Eliminated)</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="number" name="finish_position[]" value="<?= htmlspecialchars($r['finish_position'] ?? '') ?>" placeholder="Finis Ke-" class="w-full text-center bg-slate-50 border border-slate-300 text-slate-800 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold transition">
                                        </td>
                                    <?php endif; ?>

                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Papan Kontrol Eksekusi -->
                    <div class="bg-slate-900 rounded-2xl p-6 shadow-2xl border border-slate-700 flex justify-between items-center mt-2 sticky bottom-6 z-50">
                        <div class="text-slate-400 text-sm">
                            ⚠️ <span class="font-bold text-white">Pastikan data yang diinput benar.</span> Pembaruan data akan menimpa (overwrite) data sebelumnya secara langsung.
                        </div>
                        <button type="submit" class="bg-orange-500 hover:bg-orange-400 text-white px-10 py-3.5 rounded-xl font-black text-lg shadow-[0_0_20px_rgba(249,115,22,0.4)] transition-all transform hover:scale-105">
                            Simpan Hasil Penjurian
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    <script>
        // Optional JS helper: auto-refresh the heat list when event is changed
    </script>
</body>
</html>
