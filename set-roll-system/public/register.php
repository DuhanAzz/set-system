<?php
require_once __DIR__ . '/../src/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama_klub = trim($_POST['nama_klub'] ?? '');
    $pass = $_POST['password'] ?? '';
    $userType = 'user'; // Default daftar sebagai user klub

    // Cek Username
    $stmt = $pdo->prepare("SELECT id FROM roll_users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $error = "Username sudah terdaftar. Silakan pilih username lain.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        try {
            $pdo->beginTransaction();
            
            // Insert Club first
            $insClub = $pdo->prepare("INSERT INTO roll_clubs (club_name) VALUES (?)");
            $insClub->execute([$nama_klub]);
            $newClubId = $pdo->lastInsertId();
            
            // Insert User
            $ins = $pdo->prepare("INSERT INTO roll_users (username, password, role, club_id) VALUES (?, ?, ?, ?)");
            if($ins->execute([$username, $hash, $userType, $newClubId])) {
                $pdo->commit();
                $waNumber = '6281234567890';
                $success = true;
            } else {
                $pdo->rollBack();
                $error = "Gagal mendaftar.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Klub - SET Roll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center font-[Inter]">

    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-2xl border border-slate-200">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-slate-900 tracking-tighter">SET<span class="text-orange-500">ROLL</span></h1>
            <p class="text-slate-500 text-sm mt-2 font-bold uppercase tracking-widest">Registrasi Klub Baru</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold text-center border border-red-200"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="bg-orange-50 text-orange-800 p-6 rounded-2xl mb-6 text-center shadow-md border border-orange-200">
                <div class="text-4xl mb-3">✅</div>
                <h3 class="font-black text-xl mb-1 uppercase tracking-tight">Pendaftaran Berhasil!</h3>
                <p class="text-sm font-medium mb-5 text-slate-600">Akun Anda telah dibuat. Silakan hubungi Super Admin jika memerlukan verifikasi lanjutan, atau langsung masuk ke Dashboard.</p>
                <a href="login.php" class="block w-full bg-slate-900 hover:bg-orange-600 text-white font-black py-4 px-4 rounded-xl transition shadow-lg mb-3 uppercase tracking-widest text-xs">
                    Masuk ke Sistem
                </a>
            </div>
            <style> form { display: none; } </style>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Nama Klub Sepatu Roda</label>
                <input type="text" name="nama_klub" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-600 outline-none font-semibold text-slate-800" required placeholder="Contoh: Garuda Speed Club">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Username Login</label>
                <input type="text" name="username" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-600 outline-none font-semibold text-slate-800" required placeholder="Gunakan format tanpa spasi">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-600 outline-none font-semibold text-slate-800" required placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-orange-600 text-white font-black py-4 rounded-xl hover:bg-orange-700 transition transform hover:-translate-y-0.5 shadow-[0_0_20px_rgba(234,88,12,0.4)] mt-4 uppercase tracking-widest text-xs">Daftar Sekarang</button>
        </form>

        <p class="mt-8 text-center text-sm font-bold text-slate-500">
            Sudah memiliki akun? <a href="login.php" class="text-orange-600 hover:underline uppercase tracking-widest">Login</a>
        </p>
    </div>

</body>
</html>
