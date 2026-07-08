<?php
// FILE: login.php (Universal Super Admin Login)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/set-swim-system/src/config/database.php';

// Redirect if already logged in
if (isset($_SESSION['super_admin_id'])) {
    header("Location: admin.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM universal_admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['super_admin_id'] = $admin['id'];
        $_SESSION['super_admin_username'] = $admin['username'];
        header("Location: admin.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Super Admin - Universal SET System</title>
    <link rel="icon" type="image/png" href="set-swim-system/public/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
        <div class="bg-slate-900 p-8 text-center">
            <img src="set-swim-system/public/img/logo.png" alt="Logo" class="h-16 mx-auto mb-4 invert brightness-0">
            <h2 class="text-2xl font-bold text-white">Super Admin Portal</h2>
            <p class="text-slate-400 text-sm mt-2">Login untuk mengelola halaman depan Universal SET System.</p>
        </div>
        
        <div class="p-8">
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 font-semibold border border-red-100">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" placeholder="Masukkan username">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none" placeholder="Masukkan password">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/30">
                    Masuk ke Sistem
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="index.php" class="text-sm text-slate-500 hover:text-slate-700 font-medium">&larr; Kembali ke Halaman Depan</a>
            </div>
        </div>
    </div>

</body>
</html>
