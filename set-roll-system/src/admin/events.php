<?php
// FILE: src/admin/events.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// --- LOGIKA INSERT/UPDATE/DELETE ---
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['event_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $format = $_POST['race_format'] ?? 'DTT';
        $status = $_POST['status'] ?? 'Draft';

        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO roll_events (event_name, location, race_format, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $loc, $format, $status]);
                $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Kejuaraan berhasil ditambahkan!</div>";
            } catch (PDOException $e) {
                $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: " . $e->getMessage() . "</div>";
            }
        } else {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: Nama Kejuaraan tidak boleh kosong!</div>";
        }
    }
}

// AMBIL DATA EVENT
$stmt = $pdo->query("SELECT * FROM roll_events ORDER BY id DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kejuaraan - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Include Sidebar -->
    <?php include __DIR__ . '/../../views/layout/sidebar.php'; ?>

    <!-- Main Canvas -->
    <div class="ml-64 p-8 min-h-screen">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Kejuaraan</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola event sepatu roda dan format perlombaan.</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                + Tambah Event Baru
            </button>
        </div>

        <?= $msg ?>

        <!-- Tabel Data -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Nama Kejuaraan</th>
                        <th class="px-6 py-4 font-bold">Lokasi</th>
                        <th class="px-6 py-4 font-bold text-center">Format Lomba</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($events)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400 font-medium">Belum ada data kejuaraan.</td></tr>
                    <?php endif; ?>
                    <?php foreach($events as $e): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($e['event_name']) ?></td>
                        <td class="px-6 py-4 text-slate-600 text-sm"><?= htmlspecialchars($e['location'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 rounded-lg text-xs font-bold tracking-wider">
                                <?= htmlspecialchars($e['race_format']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php 
                            $bg = 'bg-slate-100 text-slate-600';
                            if($e['status'] == 'Published') $bg = 'bg-green-100 text-green-700';
                            if($e['status'] == 'Completed') $bg = 'bg-blue-100 text-blue-700';
                            ?>
                            <span class="<?= $bg ?> px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                                <?= htmlspecialchars($e['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="text-orange-500 hover:text-orange-700 font-bold text-sm transition">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Tambah Event -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Tambah Kejuaraan Baru</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add">
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kejuaraan</label>
                    <input type="text" name="event_name" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Contoh: Piala Gubernur 2026">
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi</label>
                    <input type="text" name="location" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" placeholder="Contoh: Velodrome JIS">
                </div>
                
                <div class="grid grid-cols-2 gap-5 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Format Lomba</label>
                        <select name="race_format" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="DTT">DTT (Dual Time Trial)</option>
                            <option value="SPRINT">SPRINT</option>
                            <option value="PTP">PTP (Point to Point)</option>
                            <option value="ELIMINATION">ELIMINATION</option>
                            <option value="TIME_TRIAL">TIME TRIAL (Standar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Status Awal</label>
                        <select name="status" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="Draft">Draft</option>
                            <option value="Published">Published</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
