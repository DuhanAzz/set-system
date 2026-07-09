<?php
// FILE: src/user/skaters.php
require_once __DIR__ . '/../config/database.php';

// Proteksi Akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$club_id = $_SESSION['club_id'];
$msg = '';

// --- LOGIKA INSERT ATLET (ISOLASI) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $skater_name = trim($_POST['skater_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $age_group = $_POST['age_group'] ?? '';

    if (!empty($skater_name) && !empty($gender) && !empty($birth_date) && !empty($age_group)) {
        try {
            // club_id HARUS dari SESSION! (Mencegah manipulasi HTML DOM)
            $stmt = $pdo->prepare("INSERT INTO roll_skaters (club_id, skater_name, gender, birth_date, age_group) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$club_id, $skater_name, $gender, $birth_date, $age_group]);
            $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Data Atlet berhasil ditambahkan ke tim Anda.</div>";
        } catch (PDOException $e) {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
}

// --- LOGIKA HAPUS (Pastikan atlet milik club_id) ---
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM roll_skaters WHERE id = ? AND club_id = ?");
        $stmtDel->execute([$del_id, $club_id]);
        $msg = "<div class='bg-orange-100 text-orange-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-orange-200'>🗑️ Atlet berhasil dihapus dari tim.</div>";
    } catch (PDOException $e) {
        $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Gagal menghapus: Mungkin atlet sudah terdaftar di sebuah nomor lomba.</div>";
    }
}

// --- AMBIL DATA ATLET KLUB INI SAJA ---
$stmt = $pdo->prepare("SELECT * FROM roll_skaters WHERE club_id = ? ORDER BY skater_name ASC");
$stmt->execute([$club_id]);
$skaters = $stmt->fetchAll();
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Daftar Atlet Tim</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola anggota tim Anda sebelum mendaftarkan ke kejuaraan.</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                + Daftarkan Atlet Baru
            </button>
        </div>

        <?= $msg ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Nama Lengkap Atlet</th>
                        <th class="px-6 py-4 font-bold text-center">Kelamin</th>
                        <th class="px-6 py-4 font-bold text-center">Tanggal Lahir</th>
                        <th class="px-6 py-4 font-bold text-center">Kelompok Usia</th>
                        <th class="px-6 py-4 font-bold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($skaters)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400 font-medium">Belum ada atlet di tim Anda.</td></tr>
                    <?php endif; ?>
                    <?php foreach($skaters as $s): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-black text-slate-800"><?= htmlspecialchars($s['skater_name']) ?></td>
                        <td class="px-6 py-4 text-center font-bold text-slate-500"><?= $s['gender'] == 'L' ? 'Laki-Laki' : 'Perempuan' ?></td>
                        <td class="px-6 py-4 text-center font-medium text-slate-600 text-sm"><?= htmlspecialchars($s['birth_date']) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded border border-orange-200 font-bold text-xs">
                                <?= htmlspecialchars($s['age_group']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center space-x-3 text-sm font-bold">
                            <a href="?delete=<?= $s['id'] ?>" onclick="return confirm('Hapus atlet ini?')" class="text-red-500 hover:text-red-700 transition">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Tanpa Dropdown Klub) -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Tambah Profil Atlet</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add">
                
                <div class="grid gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Atlet</label>
                        <input type="text" name="skater_name" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold" placeholder="Budi Santoso">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Kelamin</label>
                        <select name="gender" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="L">Laki-laki (M)</option>
                            <option value="P">Perempuan (F)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="birth_date" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kelompok Usia (KU)</label>
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
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
