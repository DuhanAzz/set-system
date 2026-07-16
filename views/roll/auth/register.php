<?php
$waNumber = $_SESSION['wa_number'] ?? '6281993189787';
$email = $_SESSION['register_email'] ?? '';
$success = isset($_SESSION['success_register']);
unset($_SESSION['success_register']);
unset($_SESSION['register_email']);
unset($_SESSION['wa_number']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=3">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SET System Roll</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center py-10 px-4">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-200 relative">
        <a href="<?= getenv('APP_URL') ?>/roll/login" class="absolute top-4 left-4 text-slate-400 hover:text-orange-600 transition duration-300 font-bold text-xs uppercase tracking-widest">&larr; Kembali</a>
        
        <div class="text-center mb-8 mt-4">
            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Buat Akun</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Daftarkan klub sepatu roda Anda.</p>
        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 text-sm flex items-center gap-3">
                <span class="font-bold"><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-50 text-green-800 p-6 rounded-2xl mb-6 text-center shadow-sm border border-green-200">
                <div class="text-5xl mb-4">✅</div>
                <h3 class="font-black text-xl mb-2 uppercase tracking-tight">Pendaftaran Berhasil!</h3>
                <p class="text-sm font-medium mb-6 text-green-700">Akun Anda sedang dalam status <strong class="bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded text-xs uppercase tracking-wider">PENDING</strong>.<br>Silakan hubungi Admin via WhatsApp untuk verifikasi.</p>
                <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>?text=Halo%20Admin,%20saya%20baru%20saja%20mendaftar%20akun%20di%20Set%20System%20Roll%20dengan%20email:%20<?= urlencode($email) ?>.%20Mohon%20untuk%20di-approve." target="_blank" class="flex items-center justify-center gap-2 w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-black py-4 px-4 rounded-xl transition shadow-lg mb-4 uppercase text-sm tracking-wide">
                    <span>💬</span> Hubungi Admin via WA
                </a>
                <a href="<?= getenv('APP_URL') ?>/roll/login" class="inline-block text-xs font-bold text-slate-500 hover:text-slate-800 underline uppercase tracking-wider">Ke Halaman Login</a>
            </div>
            <style> form { display: none; } </style>
        <?php endif; ?>

        <form action="<?= getenv('APP_URL') ?>/roll/register/submit" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 tracking-wide">Nama Lengkap (Admin Klub)</label>
                <input type="text" name="nama" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition font-semibold" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 tracking-wide">Nama Klub Sepatu Roda</label>
                <input type="text" name="nama_klub" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition font-semibold" required placeholder="Contoh: Roller Speed Club">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 tracking-wide">No. WhatsApp</label>
                <input type="text" name="phone" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition font-semibold" required placeholder="08...">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 tracking-wide">Email</label>
                <input type="email" name="email" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition font-semibold" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 tracking-wide">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 focus:bg-white rounded-xl focus:ring-2 focus:ring-orange-500 outline-none transition font-semibold" required>
            </div>
            <button type="submit" class="w-full bg-[#0F172A] hover:bg-orange-600 text-white font-black py-4 rounded-xl transition shadow-lg mt-4 uppercase text-sm tracking-wide transform hover:-translate-y-0.5">Daftar Sekarang</button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500 font-medium">
            Sudah punya akun? <a href="<?= getenv('APP_URL') ?>/roll/login" class="text-orange-600 font-bold hover:underline">Login di sini</a>
        </p>
    </div>

</body>
</html>
