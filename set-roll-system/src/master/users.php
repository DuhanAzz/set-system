<?php
// FILE: src/master/users.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$msg = '';

// --- LOGIKA TAMBAH AKUN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $club_id = !empty($_POST['club_id']) ? $_POST['club_id'] : null;

    if ($role === 'admin' || $role === 'master') {
        $club_id = null; // Admin/Master tidak punya klub
    }

    if (!empty($username) && !empty($password)) {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO roll_users (username, password, role, club_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed, $role, $club_id]);
            $msg = "<div class='bg-green-900/40 text-green-400 p-4 rounded-xl mb-6 font-semibold border border-green-800/50'>✅ Akun pengguna berhasil diciptakan.</div>";
        } catch (PDOException $e) {
            $msg = "<div class='bg-red-900/40 text-red-400 p-4 rounded-xl mb-6 font-semibold border border-red-800/50'>❌ Gagal: Username mungkin sudah digunakan.</div>";
        }
    }
}

// --- AMBIL DATA KLUB UNTUK DROPDOWN ---
$clubs = $pdo->query("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC")->fetchAll();

// --- AMBIL DAFTAR PENGGUNA (JOIN KLUB) ---
$users = $pdo->query("
    SELECT u.id, u.username, u.role, c.club_name 
    FROM roll_users u
    LEFT JOIN roll_clubs c ON u.club_id = c.id
    ORDER BY u.role ASC, u.username ASC
")->fetchAll();
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight">Registrasi & Hak Akses</h2>
                <p class="text-slate-400 mt-1 font-medium">Buat akun Panitia (Admin) atau Manajer Klub (User).</p>
            </div>
            <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-red-600 hover:bg-red-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5">
                + Ciptakan Akun Baru
            </button>
        </div>

        <?= $msg ?>

        <div class="bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-800">
                        <th class="px-6 py-4 font-bold">Username Login</th>
                        <th class="px-6 py-4 font-bold text-center">Tingkat Akses (Role)</th>
                        <th class="px-6 py-4 font-bold">Afiliasi Klub</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    <?php foreach($users as $u): ?>
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-white"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="px-6 py-4 text-center">
                            <?php 
                                $badgeClass = 'bg-slate-800 text-slate-400';
                                if($u['role'] === 'master') $badgeClass = 'bg-red-900/50 text-red-400 border border-red-800/50';
                                if($u['role'] === 'admin') $badgeClass = 'bg-orange-900/50 text-orange-400 border border-orange-800/50';
                                if($u['role'] === 'user') $badgeClass = 'bg-blue-900/50 text-blue-400 border border-blue-800/50';
                            ?>
                            <span class="<?= $badgeClass ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                <?= htmlspecialchars($u['role']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-400">
                            <?= ($u['role'] === 'user') ? htmlspecialchars($u['club_name'] ?? 'Klub Dihapus/Tidak Ada') : '<em>Sistem Pusat (Akses Penuh)</em>' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH AKUN -->
    <div id="modalAdd" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-slate-900 w-full max-w-lg rounded-[2rem] shadow-2xl border border-slate-700 overflow-hidden transform transition-all">
            <div class="bg-slate-950 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 class="text-xl font-black text-white">Suntikkan Akun Baru</h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-500 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form action="" method="POST" class="p-8">
                <input type="hidden" name="action" value="add_user">
                
                <div class="grid gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-400 mb-2">Username</label>
                        <input type="text" name="username" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold" placeholder="Bebas spasi (contoh: klub_garuda)">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-400 mb-2">Password</label>
                        <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold" placeholder="Kata sandi rahasia">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-400 mb-2">Hak Akses (Role)</label>
                        <select id="roleSelect" name="role" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold cursor-pointer">
                            <option value="user">Manajer Klub (User)</option>
                            <option value="admin">Panitia Kejuaraan (Admin)</option>
                        </select>
                    </div>
                    <div id="clubWrapper">
                        <label class="block text-sm font-bold text-slate-400 mb-2">Afiliasi Klub</label>
                        <select id="clubSelect" name="club_id" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold cursor-pointer">
                            <option value="">-- Pilih Klub Milik Manajer --</option>
                            <?php foreach($clubs as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['club_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-800 mt-4">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-6 py-2.5 text-slate-400 font-bold hover:bg-slate-800 rounded-xl transition">Batal</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5">Ciptakan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vanilla JS Interactivity -->
    <script>
        const roleSelect = document.getElementById('roleSelect');
        const clubWrapper = document.getElementById('clubWrapper');
        const clubSelect = document.getElementById('clubSelect');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'admin' || this.value === 'master') {
                clubWrapper.style.display = 'none';
                clubSelect.required = false;
                clubSelect.value = '';
            } else {
                clubWrapper.style.display = 'block';
                clubSelect.required = true;
            }
        });
    </script>
</body>
</html>
