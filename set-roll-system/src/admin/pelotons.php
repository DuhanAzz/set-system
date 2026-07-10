<?php
// FILE: src/admin/pelotons.php
require_once __DIR__ . '/../config/database.php';

// --- LOGIKA SIMPAN GANDA (TRANSACTION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_pelotons') {
    $event_id = $_POST['event_id'] ?? 0;
    $skater_ids = $_POST['skater_id'] ?? [];
    $heat_names = $_POST['heat_name'] ?? [];
    $start_grids = $_POST['start_grid'] ?? [];

    if ($event_id) {
        try {
            $pdo->beginTransaction();
            
            // Loop data form bulk
            $count = 0;
            foreach ($skater_ids as $index => $s_id) {
                $h_name = trim($heat_names[$index] ?? '');
                $s_grid = trim($start_grids[$index] ?? '');
                
                // Hanya simpan jika heat_name dan start_grid diisi
                if (!empty($h_name) && $s_grid !== '') {
                    
                    // Aksi 1: INSERT INTO roll_pelotons
                    $stmtPeloton = $pdo->prepare("INSERT INTO roll_pelotons (event_id, skater_id, heat_name, start_grid) VALUES (?, ?, ?, ?)");
                    $stmtPeloton->execute([$event_id, $s_id, $h_name, $s_grid]);
                    
                    // Aksi 2: INSERT INTO roll_event_results (Siapkan slot kosong)
                    $stmtResult = $pdo->prepare("INSERT INTO roll_event_results (event_id, skater_id, heat_name) VALUES (?, ?, ?)");
                    $stmtResult->execute([$event_id, $s_id, $h_name]);
                    
                    $count++;
                }
            }
            
            $pdo->commit();
            $_SESSION['flash_message'] = '✅ Berhasil menyimpan {$count} susunan peloton dan mem-booking slot hasil!';
                $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_message'] = "❌ Terjadi Kesalahan (Rollback dijalankan): \" . $e->getMessage() . \"";
                $_SESSION['flash_type'] = 'error';
        }
    }
}

// --- AMBIL DATA FILTER (GET) ---
$filter_event_id = $_GET['event_id'] ?? '';
$filter_distance = $_GET['race_distance'] ?? '';

// Ambil list event untuk dropdown filter
$events = $pdo->query("SELECT id, event_name, race_format FROM roll_events ORDER BY id DESC")->fetchAll();

// Ambil list jarak unik dari pendaftaran untuk dropdown filter
$distances = $pdo->query("SELECT DISTINCT race_distance FROM roll_entries ORDER BY race_distance ASC")->fetchAll();

// Ambil data pendaftaran jika filter aktif
$entries = [];
if (!empty($filter_event_id) && !empty($filter_distance)) {
    $stmtEntries = $pdo->prepare("
        SELECT e.skater_id, s.skater_name, s.age_group, c.club_name, e.race_distance
        FROM roll_entries e
        JOIN roll_skaters s ON e.skater_id = s.id
        LEFT JOIN roll_clubs c ON s.club_id = c.id
        WHERE e.event_id = ? AND e.race_distance = ?
        ORDER BY s.age_group ASC, s.skater_name ASC
    ");
    $stmtEntries->execute([$filter_event_id, $filter_distance]);
    $entries = $stmtEntries->fetchAll();
}
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Peloton & Start Grid</h2>
            <p class="text-slate-500 mt-1 font-medium">Atur grup keberangkatan (Heat) secara dinamis tanpa batasan lintasan.</p>
        </div>

        <!-- FILTER FORM -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
            <form method="GET" action="" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kejuaraan</label>
                    <select name="event_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                        <option value="">-- Kejuaraan --</option>
                        <?php foreach($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= ($filter_event_id == $ev['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['event_name']) ?> (<?= htmlspecialchars($ev['race_format']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Jarak Lomba</label>
                    <select name="race_distance" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                        <option value="">-- Jarak Lomba --</option>
                        <?php foreach($distances as $dst): ?>
                            <option value="<?= htmlspecialchars($dst['race_distance']) ?>" <?= ($filter_distance == $dst['race_distance']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dst['race_distance']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-3 rounded-xl font-bold shadow-md transition-all">
                        Tampilkan Peserta
                    </button>
                </div>
            </form>
        </div>

        <!-- FORM BULK ASSIGNMENT (PELOTON & GRID) -->
        <?php if(!empty($filter_event_id) && !empty($filter_distance)): ?>
            <?php if(empty($entries)): ?>
                <div class="text-center py-10 bg-white rounded-2xl border border-slate-200 text-slate-500 font-bold">
                    Tidak ada pendaftar di nomor/jarak ini.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_pelotons">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($filter_event_id) ?>">

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                                    <th class="px-6 py-4 font-bold">Nama Atlet</th>
                                    <th class="px-6 py-4 font-bold">Asal Klub</th>
                                    <th class="px-6 py-4 font-bold">Kategori Usia</th>
                                    <th class="px-6 py-4 font-bold text-center">Nama Grup / Heat</th>
                                    <th class="px-6 py-4 font-bold text-center w-32">Posisi Grid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($entries as $index => $e): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        <?= htmlspecialchars($e['skater_name']) ?>
                                        <input type="hidden" name="skater_id[]" value="<?= $e['skater_id'] ?>">
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium text-sm">
                                        <?= htmlspecialchars($e['club_name'] ?? '-') ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 text-sm">
                                        <?= htmlspecialchars($e['age_group']) ?>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="text" name="heat_name[]" placeholder="Contoh: Heat 1 / Final" class="w-full max-w-[200px] text-center bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold transition">
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="number" name="start_grid[]" placeholder="Grid" class="w-full text-center bg-slate-50 border border-slate-200 text-slate-800 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 font-bold transition font-mono">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Simpan Raksasa -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-10 py-4 rounded-xl font-black text-lg shadow-xl shadow-orange-500/30 transition-all transform hover:-translate-y-1">
                            Simpan Susunan Peloton & Generate Start List
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>

    </div>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
