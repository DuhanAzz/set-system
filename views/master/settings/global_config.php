<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Config - Universal SET System</title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- NAVBAR MASTER -->
    <header class="bg-slate-900 text-white shadow-xl sticky top-0 z-50">
        <div class="container mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= getenv('APP_URL') ?>/img/logo.png" alt="Logo" class="h-8 invert brightness-0">
                <span class="text-lg font-black tracking-widest uppercase text-blue-400">Master Portal</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="<?= getenv('APP_URL') ?>/master/dashboard" class="text-sm font-semibold hover:text-blue-400 transition-colors">&larr; Kembali ke Dasbor</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-6 py-12 max-w-4xl">
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-semibold mb-8 shadow-sm">
                ✅ Pengaturan berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Konfigurasi Global</h1>
            <p class="text-slate-500 font-medium">Ubah pengaturan teks utama, deskripsi SEO, dan informasi kontak.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <form action="<?= getenv('APP_URL') ?>/master/settings/process" method="POST" class="space-y-5">
                <input type="hidden" name="action" value="update_global">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Aplikasi (Title Bar)</label>
                    <input type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Hero Title (Teks Besar)</label>
                    <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Pendek</label>
                    <textarea name="site_description" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Informasi Kontak & Sosial Media</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">WhatsApp</label>
                            <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Link Instagram</label>
                            <input type="text" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-8">Simpan Konfigurasi</button>
            </form>
        </div>
    </main>
</body>
</html>
