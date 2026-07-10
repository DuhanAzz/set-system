<?php
// FILE: src/user/profile.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$club_id = $_SESSION['club_id'];

// PROSES FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // UPDATE PROFIL
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $nama = $_POST['nama_lengkap'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $stmt = $pdo->prepare("UPDATE roll_users SET nama_lengkap = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $phone, $user_id]);
        $_SESSION['nama_lengkap'] = $nama;
        $_SESSION['flash_msg'] = "Profil berhasil diperbarui.";
        $_SESSION['flash_type'] = "success";
        header("Location: profile.php");
        exit;
    }
    
    // UBAH SANDI
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $conf_pass = $_POST['confirm_password'];
        
        if ($new_pass !== $conf_pass) {
            $_SESSION['flash_msg'] = "Sandi baru dan konfirmasi tidak cocok.";
            $_SESSION['flash_type'] = "error";
        } else {
            $stmt = $pdo->prepare("SELECT password FROM roll_users WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_hash = $stmt->fetchColumn();
            
            if (password_verify($old_pass, $current_hash)) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE roll_users SET password = ? WHERE id = ?");
                $upd->execute([$new_hash, $user_id]);
                $_SESSION['flash_msg'] = "Kata sandi berhasil diubah.";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_msg'] = "Sandi lama yang Anda masukkan salah.";
                $_SESSION['flash_type'] = "error";
            }
        }
        header("Location: profile.php");
        exit;
    }
}

// AMBIL DATA TERKINI
$stmt = $pdo->prepare("
    SELECT u.*, c.club_name 
    FROM roll_users u 
    LEFT JOIN roll_clubs c ON u.club_id = c.id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    
    <div class="max-w-4xl mx-auto">
        <?php if(isset($_SESSION['flash_msg'])): ?>
            <div class="mb-6 px-6 py-4 rounded-xl font-bold text-sm shadow-lg animate-pulse 
                <?= $_SESSION['flash_type'] === 'error' ? 'bg-red-100 text-red-600 border border-red-200' : 'bg-green-100 text-green-600 border border-green-200' ?>">
                <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-4 mb-8">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-[1.5rem] flex items-center justify-center text-3xl shadow-lg shadow-blue-600/30">
                👤
            </div>
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Profil Manajer Klub</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola informasi pribadi dan keamanan akun Anda.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- FORM PROFIL -->
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-lg font-black text-slate-800">Informasi Pribadi</h3>
                </div>
                <form action="" method="POST" class="p-8">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Klub (Read Only)</label>
                            <input type="text" value="<?= htmlspecialchars($me['club_name'] ?? '-') ?>" readonly class="w-full bg-slate-100 border border-slate-200 text-slate-500 rounded-xl px-4 py-3 cursor-not-allowed font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($me['nama_lengkap'] ?? '') ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($me['email'] ?? '') ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">No. WhatsApp</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($me['phone'] ?? '') ?>" required class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-600 font-semibold transition">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">Simpan Profil</button>
                </form>
            </div>

            <!-- FORM KEAMANAN -->
            <div class="bg-slate-900 rounded-[2rem] shadow-xl border border-slate-800 overflow-hidden text-white relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-500/20 blur-3xl rounded-full"></div>
                <div class="p-8 border-b border-slate-800 relative z-10">
                    <h3 class="text-lg font-black text-white">Keamanan Sandi</h3>
                </div>
                <form action="" method="POST" class="p-8 relative z-10">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Sandi Saat Ini</label>
                            <input type="password" name="old_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Sandi Baru</label>
                            <input type="password" name="new_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Konfirmasi Sandi Baru</label>
                            <input type="password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold transition" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-8 w-full bg-red-600 hover:bg-red-500 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">Ubah Kata Sandi</button>
                </form>
            </div>

        </div>
    </div>
</div>
<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
