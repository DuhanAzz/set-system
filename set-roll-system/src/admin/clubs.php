<?php
// FILE: src/admin/clubs.php
require_once __DIR__ . '/../config/database.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['club_name'] ?? '');
        $city = trim($_POST['city_province'] ?? '');

        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO roll_clubs (club_name, city_province) VALUES (?, ?)");
                $stmt->execute([$name, $city]);
                $msg = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-green-200'>✅ Klub/Tim berhasil ditambahkan!</div>";
            } catch (PDOException $e) {
                $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: " . $e->getMessage() . "</div>";
            }
        } else {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-semibold shadow-sm border border-red-200'>❌ Error: Nama Klub tidak boleh kosong!</div>";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM roll_clubs ORDER BY id DESC");
$clubs = $stmt->fetchAll();
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Klub & Tim</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola data klub sepatu roda yang berpartisipasi.</p>
            </div>
            <div class="flex gap-4">
                <a href="skaters.php" class="bg-white border border-slate-200 text-slate-600 hover:text-orange-500 hover:border-orange-500 px-6 py-2.5 rounded-xl font-bold shadow-sm transition-all">
                    Lihat Data Atlet &rarr;
                </a>
                <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                    + Tambah Klub Baru
                </button>
            </div>
        </div>

        <?= $msg ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Nama Klub / Tim</th>
                        <th class="px-6 py-4 font-bold">Kota / Provinsi</th>
                        <th class="px-6 py-4 font-bold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($clubs)): ?>
                        <tr><td colspan="3" class="text-center py-10 text-slate-400 font-medium">Belum ada data klub.</td></tr>
                    <?php endif; ?>
                    <?php foreach($clubs as $c): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($c['club_name']) ?></td>
                        <td class="px-6 py-4 text-slate-600 text-sm"><?= htmlspecialchars($c['city_province'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-center">
                            <button class="text-orange-500 hover:text-orange-700 font-bold text-sm transition">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Klub -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Tambah Klub Baru</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add">
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Klub / Tim</label>
                    <input type="text" name="club_name" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" placeholder="Contoh: INLINE SKATE CLUB">
                </div>
                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kota / Provinsi Asal</label>
                    <input type="text" name="city_province" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" placeholder="Contoh: Jakarta">
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
