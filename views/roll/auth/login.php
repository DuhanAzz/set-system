<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Roll System</title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-96 border border-slate-200">
        <h2 class="text-3xl font-black text-center mb-6 text-slate-800">Roll Login</h2>
        
        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <form action="<?= getenv('APP_URL') ?>/roll/login/submit" method="POST">
            <div class="mb-4">
                <label class="block text-slate-700 font-bold mb-2 text-sm">Username / Email</label>
                <input type="text" name="username" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-slate-50" required>
            </div>
            <div class="mb-6">
                <label class="block text-slate-700 font-bold mb-2 text-sm">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 bg-slate-50" required>
            </div>
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition-colors">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
