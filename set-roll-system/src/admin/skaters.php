<?php
// FILE: src/admin/skaters.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

// --- AMBIL DATA KLUB UNTUK DROPDOWN ---
$stmtClubs = $pdo->query("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC");
$clubsList = $stmtClubs->fetchAll();

// --- LOGIKA INSERT/UPDATE/DELETE ---
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $club_id = $_POST['club_id'] ?? 0;
        $name = trim($_POST['skater_name'] ?? '');
        $gender = $_POST['gender'] ?? 'M';
        $dob = $_POST['birth_date'] ?? '';
        $age_group = $_POST['age_group'] ?? '';

        if (!empty($name) && !empty($club_id) && !empty($dob)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$club_id, $name, $gender, $dob, $age_group]);
                $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Atlet berhasil ditambahkan!</div>";
            } catch (PDOException $e) {
                $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: " . $e->getMessage() . "</div>";
            }
        } else {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: Lengkapi semua form wajib!</div>";
        }
    }
}

// --- AMBIL DATA ATLET BESERTA NAMA KLUB (SQL JOIN) ---
$stmt = $pdo->query("
    SELECT s.*, c.club_name 
    FROM roll_skaters s 
    LEFT JOIN roll_clubs c ON s.club_id = c.id 
    ORDER BY s.id DESC
");
$skaters = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Atlet - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <?php include __DIR__ . '/../../views/layout/sidebar.php'; ?>

    <div class="ml-64 p-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="clubs.php" class="text-orange-500 hover:underline font-bold text-sm mb-2 block">&larr; Kembali ke Klub</a>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Atlet (Skaters)</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola data pendaftaran profil atlet sepatu roda.</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                + Tambah Atlet Baru
            </button>
        </div>

        <?= $msg ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Nama Atlet</th>
                        <th class="px-6 py-4 font-bold">Asal Klub</th>
                        <th class="px-6 py-4 font-bold text-center">L/P</th>
                        <th class="px-6 py-4 font-bold">Usia / Kategori</th>
                        <th class="px-6 py-4 font-bold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($skaters)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400 font-medium">Belum ada data atlet.</td></tr>
                    <?php endif; ?>
                    <?php foreach($skaters as $s): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($s['skater_name']) ?></td>
                        <td class="px-6 py-4 text-slate-600 font-medium"><?= htmlspecialchars($s['club_name'] ?? 'Klub Dihapus') ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 border border-slate-200 px-2 py-1 rounded font-bold text-xs text-slate-600">
                                <?= $s['gender'] == 'M' ? 'L' : 'P' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-slate-700"><?= htmlspecialchars($s['age_group']) ?></div>
                            <div class="text-xs text-slate-500">Lahir: <?= date('d M Y', strtotime($s['birth_date'])) ?></div>
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

    <!-- Modal Tambah Atlet -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Registrasi Atlet Baru</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-2 gap-5 mb-5">
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Atlet</label>
                        <input type="text" name="skater_name" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" placeholder="Contoh: Budi Santoso">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Asal Klub</label>
                        <select name="club_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="">-- Pilih Klub --</option>
                            <?php foreach($clubsList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['club_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Kelamin</label>
                        <select name="gender" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="M">Laki-laki (M)</option>
                            <option value="F">Perempuan (F)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="birth_date" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kategori / Kelompok Umur (KU)</label>
                        <select name="age_group" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="Pemula 1">Pemula 1</option>
                            <option value="Pemula 2">Pemula 2</option>
                            <option value="Pemula 3">Pemula 3</option>
                            <option value="Pemula 4">Pemula 4</option>
                            <option value="KU A">KU A</option>
                            <option value="KU B">KU B</option>
                            <option value="KU C">KU C</option>
                            <option value="KU D">KU D</option>
                            <option value="Speed/Elite">Speed/Elite</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 mt-4">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
