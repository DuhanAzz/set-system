<?php
// FILE: src/master/users.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

// --- LOGIKA FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 1. TAMBAH AKUN BARU
    if ($_POST['action'] == 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $club_id = !empty($_POST['club_id']) ? $_POST['club_id'] : null;

        if ($role === 'admin' || $role === 'master') {
            $club_id = null; 
        }

        if (!empty($username) && !empty($password)) {
            // Validasi Duplikat
            $chk = $pdo->prepare("SELECT COUNT(*) FROM roll_users WHERE username = ?");
            $chk->execute([$username]);
            if($chk->fetchColumn() > 0) {
                $_SESSION['flash_message'] = "❌ Gagal: Username sudah digunakan.";
                $_SESSION['flash_type'] = 'error';
            } else {
                try {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO roll_users (username, password, role, club_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $hashed, $role, $club_id]);
                    $_SESSION['flash_message'] = '✅ Akun pengguna berhasil diciptakan.';
                    $_SESSION['flash_type'] = 'success';
                } catch (PDOException $e) {
                    $_SESSION['flash_message'] = "❌ Terjadi Kesalahan Sistem.";
                    $_SESSION['flash_type'] = 'error';
                }
            }
        }
    }

    // 2. UBAH STATUS (SUSPEND/ACTIVE)
    if ($_POST['action'] == 'change_status') {
        $id = $_POST['user_id'];
        $status = $_POST['status'];
        if(in_array($status, ['active', 'suspended'])) {
            $stmt = $pdo->prepare("UPDATE roll_users SET account_status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $_SESSION['flash_message'] = '✅ Status akun berhasil diubah.';
            $_SESSION['flash_type'] = 'success';
        }
    }

    // 3. RESET SANDI BYPASS
    if ($_POST['action'] == 'reset_password') {
        $id = $_POST['user_id'];
        $new_pass = $_POST['new_password'];
        if(!empty($new_pass)){
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE roll_users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $id]);
            $_SESSION['flash_message'] = '✅ Password berhasil di-reset.';
            $_SESSION['flash_type'] = 'success';
        }
    }
    
    // 4. EDIT PROFIL OLEH MASTER
    if ($_POST['action'] == 'edit_profile') {
        $id = $_POST['user_id'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $stmt = $pdo->prepare("UPDATE roll_users SET nama_lengkap = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$nama_lengkap, $email, $phone, $id]);
        $_SESSION['flash_message'] = '✅ Profil akun berhasil diperbarui.';
        $_SESSION['flash_type'] = 'success';
    }

    header("Location: users.php");
    exit;
}

$clubs = $pdo->query("SELECT id, club_name FROM roll_clubs ORDER BY club_name ASC")->fetchAll();

$users = $pdo->query("
    SELECT u.*, c.club_name 
    FROM roll_users u
    LEFT JOIN roll_clubs c ON u.club_id = c.id
    ORDER BY u.role ASC, u.username ASC
")->fetchAll();

include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-950 min-h-screen font-sans">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-6 py-4 rounded-xl font-bold text-sm shadow-lg animate-pulse 
            <?= $_SESSION['flash_type'] === 'error' ? 'bg-red-500/10 text-red-500 border border-red-500/20' : 'bg-green-500/10 text-green-500 border border-green-500/20' ?>">
            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-black text-white tracking-tight">Registrasi & Hak Akses</h2>
            <p class="text-slate-400 mt-1 font-medium">Lifecycle Management, Isolasi Akun, & Kontrol Master.</p>
        </div>
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-red-600 hover:bg-red-500 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition-all transform hover:-translate-y-0.5">
            + Ciptakan Akun Baru
        </button>
    </div>

    <div class="bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-950 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-800">
                    <th class="px-6 py-4 font-bold">Username Login</th>
                    <th class="px-6 py-4 font-bold text-center">Tingkat Akses</th>
                    <th class="px-6 py-4 font-bold">Status</th>
                    <th class="px-6 py-4 font-bold">Afiliasi Klub</th>
                    <th class="px-6 py-4 font-bold text-right">Otoritas Master</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                <?php foreach($users as $u): ?>
                <tr class="hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 font-bold text-white">
                        <?= htmlspecialchars($u['username']) ?>
                        <div class="text-[10px] text-slate-500 font-normal mt-1"><?= htmlspecialchars($u['email'] ?? '-') ?></div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php 
                            $badgeClass = 'bg-slate-800 text-slate-400';
                            if($u['role'] === 'master') $badgeClass = 'bg-red-900/50 text-red-400 border border-red-800/50';
                            if($u['role'] === 'admin') $badgeClass = 'bg-orange-900/50 text-orange-400 border border-orange-800/50';
                            if($u['role'] === 'user') $badgeClass = 'bg-blue-900/50 text-blue-400 border border-blue-800/50';
                        ?>
                        <span class="<?= $badgeClass ?> px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold">
                        <?php if($u['account_status'] == 'active'): ?>
                            <span class="text-green-500 text-xs uppercase tracking-widest">Active</span>
                        <?php elseif($u['account_status'] == 'pending'): ?>
                            <span class="text-yellow-500 text-xs uppercase tracking-widest">Pending</span>
                        <?php else: ?>
                            <span class="text-red-500 text-xs uppercase tracking-widest">Suspended</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-400 text-sm">
                        <?= ($u['role'] === 'user') ? htmlspecialchars($u['club_name'] ?? 'Klub Dihapus/Tidak Ada') : '<em>Sistem Pusat</em>' ?>
                    </td>
                    <td class="px-6 py-4 text-right flex justify-end gap-2">
                        <?php if($u['role'] !== 'master'): ?>
                            <!-- Edit Profil -->
                            <button onclick="openEditModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama_lengkap']) ?>', '<?= htmlspecialchars($u['email']) ?>', '<?= htmlspecialchars($u['phone']) ?>')" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs font-bold transition">Edit Profil</button>
                            
                            <!-- Reset Password -->
                            <button onclick="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')" class="px-3 py-1 bg-yellow-900/50 hover:bg-yellow-800 text-yellow-500 rounded text-xs font-bold transition border border-yellow-700/50">Reset Sandi</button>
                            
                            <!-- Status Toggle -->
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="action" value="change_status">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <?php if(in_array($u['account_status'], ['pending', 'suspended'])): ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="px-3 py-1 bg-green-900/50 hover:bg-green-800 text-green-500 rounded text-xs font-bold transition border border-green-700/50">Aktifkan</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" onclick="return confirm('Suspend akun ini?')" class="px-3 py-1 bg-red-900/50 hover:bg-red-800 text-red-500 rounded text-xs font-bold transition border border-red-700/50">Suspend</button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODALS -->
<!-- 1. ADD USER MODAL -->
<div id="modalAdd" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
    <div class="bg-slate-900 w-full max-w-lg rounded-[2rem] shadow-2xl border border-slate-700 overflow-hidden">
        <div class="bg-slate-950 p-6 flex justify-between items-center border-b border-slate-800">
            <h3 class="text-xl font-black text-white">Suntikkan Akun Baru</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-500 hover:text-white text-2xl font-bold">&times;</button>
        </div>
        <form action="" method="POST" class="p-8">
            <input type="hidden" name="action" value="add_user">
            <div class="grid gap-5 mb-5">
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2">Username</label>
                    <input type="text" name="username" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold" placeholder="Bebas spasi">
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
                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition-all">Ciptakan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. EDIT PROFILE MODAL -->
<div id="modalEdit" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
    <div class="bg-slate-900 w-full max-w-lg rounded-[2rem] shadow-2xl border border-slate-700 overflow-hidden">
        <div class="bg-slate-950 p-6 flex justify-between items-center border-b border-slate-800">
            <h3 class="text-xl font-black text-white">Edit Profil Akun</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-500 hover:text-white text-2xl font-bold">&times;</button>
        </div>
        <form action="" method="POST" class="p-8">
            <input type="hidden" name="action" value="edit_profile">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="grid gap-5 mb-5">
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit_nama" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2">No. WhatsApp</label>
                    <input type="text" name="phone" id="edit_phone" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-800 mt-4">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-6 py-2.5 text-slate-400 font-bold hover:bg-slate-800 rounded-xl transition">Batal</button>
                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. RESET PASSWORD MODAL -->
<div id="modalReset" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
    <div class="bg-slate-900 w-full max-w-lg rounded-[2rem] shadow-2xl border border-slate-700 overflow-hidden">
        <div class="bg-slate-950 p-6 flex justify-between items-center border-b border-slate-800">
            <h3 class="text-xl font-black text-white">Reset Sandi Paksa</h3>
            <button onclick="document.getElementById('modalReset').classList.add('hidden')" class="text-slate-500 hover:text-white text-2xl font-bold">&times;</button>
        </div>
        <form action="" method="POST" class="p-8">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="reset_user_id">
            <div class="mb-5">
                <p class="text-slate-400 text-sm mb-4">Reset sandi untuk: <strong id="reset_username" class="text-white"></strong></p>
                <label class="block text-sm font-bold text-slate-400 mb-2">Sandi Baru</label>
                <input type="password" name="new_password" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold" placeholder="Masukkan sandi baru">
            </div>
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-800 mt-4">
                <button type="button" onclick="document.getElementById('modalReset').classList.add('hidden')" class="px-6 py-2.5 text-slate-400 font-bold hover:bg-slate-800 rounded-xl transition">Batal</button>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-500 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-yellow-600/30 transition-all">Terapkan Sandi Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Add logic
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

    // Edit logic
    function openEditModal(id, nama, email, phone) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    // Reset logic
    function openResetModal(id, username) {
        document.getElementById('reset_user_id').value = id;
        document.getElementById('reset_username').innerText = username;
        document.getElementById('modalReset').classList.remove('hidden');
    }
</script>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
