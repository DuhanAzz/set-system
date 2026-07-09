<?php
// FILE: src/user/entries.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$club_id = $_SESSION['club_id'];
$msg = '';

// --- LOGIKA DAFTAR LOMBA (ISOLASI) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $event_id = $_POST['event_id'] ?? 0;
    $skater_id = $_POST['skater_id'] ?? 0;
    $race_distance = trim($_POST['race_distance'] ?? '');

    // Validasi Ekstra: Pastikan skater_id yang dikirim BENAR-BENAR milik club_id dari SESSION
    $checkSkater = $pdo->prepare("SELECT id FROM roll_skaters WHERE id = ? AND club_id = ?");
    $checkSkater->execute([$skater_id, $club_id]);
    $isValidSkater = $checkSkater->fetchColumn();

    if ($isValidSkater && !empty($event_id) && !empty($race_distance)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO roll_entries (event_id, skater_id, race_distance) VALUES (?, ?, ?)");
            $stmt->execute([$event_id, $skater_id, $race_distance]);
            $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Atlet berhasil didaftarkan ke perlombaan!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: Data sudah ada atau bermasalah.</div>";
        }
    } else {
        $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: Akses Ditolak atau Form Tidak Lengkap. Dilarang mendaftarkan atlet klub lain!</div>";
    }
}

// --- DATA UNTUK DROPDOWN ---
// Event: Hanya status Published
$stmtEvents = $pdo->query("SELECT id, event_name, race_format FROM roll_events WHERE status = 'Published' ORDER BY id DESC");
$eventsList = $stmtEvents->fetchAll();

// Skater: Hanya milik club_id ini
$stmtSkaters = $pdo->prepare("SELECT id, skater_name, age_group FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
$stmtSkaters->execute([$club_id]);
$skatersList = $stmtSkaters->fetchAll();

// --- DATA RIWAYAT PENDAFTARAN KLUB INI ---
$stmtEntries = $pdo->prepare("
    SELECT e.id, e.race_distance, 
           ev.event_name, ev.status,
           s.skater_name, s.age_group
    FROM roll_entries e
    JOIN roll_events ev ON e.event_id = ev.id
    JOIN roll_skaters s ON e.skater_id = s.id
    WHERE s.club_id = ?
    ORDER BY e.id DESC
");
$stmtEntries->execute([$club_id]);
$entries = $stmtEntries->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Lomba Klub - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../../views/layout/sidebar_user.php'; ?>

    <div class="ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Riwayat Pendaftaran Tim</h2>
                <p class="text-slate-500 mt-1 font-medium">Daftarkan atlet tim Anda ke nomor lomba di kejuaraan publik.</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                + Daftarkan ke Kejuaraan
            </button>
        </div>

        <?= $msg ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Kejuaraan (Publik)</th>
                        <th class="px-6 py-4 font-bold">Nama Atlet Tim</th>
                        <th class="px-6 py-4 font-bold text-center">KU</th>
                        <th class="px-6 py-4 font-bold text-center">Jarak Lomba</th>
                        <th class="px-6 py-4 font-bold text-center w-32">Status Lomba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($entries)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400 font-medium">Tim belum mendaftar di kejuaraan mana pun.</td></tr>
                    <?php endif; ?>
                    <?php foreach($entries as $e): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-700 text-sm"><?= htmlspecialchars($e['event_name']) ?></td>
                        <td class="px-6 py-4 font-black text-slate-900"><?= htmlspecialchars($e['skater_name']) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 border border-slate-200 px-3 py-1 rounded font-bold text-xs text-slate-600">
                                <?= htmlspecialchars($e['age_group']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-orange-100 border border-orange-200 text-orange-700 px-3 py-1 rounded-lg font-bold text-xs">
                                <?= htmlspecialchars($e['race_distance']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-500">
                            <?= htmlspecialchars($e['status']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Dropdown Filtered) -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Daftarkan Atlet Tim Anda</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Kejuaraan Terbuka</label>
                        <select name="event_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="">-- Hanya event 'Published' --</option>
                            <?php foreach($eventsList as $ev): ?>
                                <option value="<?= $ev['id'] ?>"><?= htmlspecialchars($ev['event_name']) ?> (<?= htmlspecialchars($ev['race_format']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Atlet Tim Anda</label>
                        <select name="skater_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="">-- Nama Atlet Anda --</option>
                            <?php foreach($skatersList as $sk): ?>
                                <option value="<?= $sk['id'] ?>">
                                    <?= htmlspecialchars($sk['skater_name']) ?> - <?= htmlspecialchars($sk['age_group']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jarak / Nomor Lomba</label>
                        <select name="race_distance" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="">-- Tentukan Jarak Lomba --</option>
                            <option value="100m">100m (Sprint / DTT)</option>
                            <option value="200m">200m</option>
                            <option value="500m">500m</option>
                            <option value="1000m">1000m</option>
                            <option value="5000m">5000m (PTP / Elimination)</option>
                            <option value="10000m">10000m (PTP / Elimination)</option>
                            <option value="15000m">15000m (Marathon)</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 mt-4">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">Daftarkan Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
