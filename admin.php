<?php
// FILE: admin.php (Universal Super Admin Dashboard)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/set-swim-system/src/config/database.php';

// Proteksi Halaman
if (!isset($_SESSION['super_admin_id'])) {
    header("Location: login.php");
    exit;
}

$msg = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle Update Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $appName = $_POST['app_name'] ?? '';
    $heroTitle = $_POST['hero_title'] ?? '';
    $siteDesc = $_POST['site_description'] ?? '';
    $contactEmail = $_POST['contact_email'] ?? '';
    $contactWa = $_POST['contact_wa'] ?? '';
    $linkIg = $_POST['link_instagram'] ?? '';

    $stmt = $pdo->prepare("UPDATE universal_settings SET app_name=?, hero_title=?, site_description=?, contact_email=?, contact_wa=?, link_instagram=? WHERE id=1");
    $stmt->execute([$appName, $heroTitle, $siteDesc, $contactEmail, $contactWa, $linkIg]);
    $msg = "Pengaturan berhasil diperbarui!";
}

// Handle Upload Hero Image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['hero_image'])) {
    $file = $_FILES['hero_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'hero_' . time() . '.' . $ext;
            $uploadPath = __DIR__ . '/uploads/hero/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $dbPath = 'uploads/hero/' . $filename;
                $stmt = $pdo->prepare("INSERT INTO universal_hero_images (image_path) VALUES (?)");
                $stmt->execute([$dbPath]);
                $msg = "Gambar berhasil diunggah!";
            } else {
                $msg = "Gagal memindahkan file ke folder uploads. (Periksa hak akses folder)";
            }
        } else {
            $msg = "Format file tidak didukung (Hanya JPG, PNG, WEBP).";
        }
    }
}

// Handle Delete Hero Image
if (isset($_GET['delete_image'])) {
    $id = (int)$_GET['delete_image'];
    $stmt = $pdo->prepare("SELECT image_path FROM universal_hero_images WHERE id=?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    if ($img) {
        $fullPath = __DIR__ . '/' . ltrim($img['image_path'], '/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $pdo->prepare("DELETE FROM universal_hero_images WHERE id=?")->execute([$id]);
        $msg = "Gambar berhasil dihapus!";
    }
}

// Fetch current data
$settings = $pdo->query("SELECT * FROM universal_settings WHERE id=1")->fetch();
$sliders = $pdo->query("SELECT * FROM universal_hero_images ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Super Admin - Universal SET System</title>
    <link rel="icon" type="image/png" href="set-swim-system/public/favicon.png?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

    <!-- Navbar -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-xl font-black tracking-widest uppercase text-blue-400">Universal CMS</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400 font-medium hidden sm:block">Halo, <?= htmlspecialchars($_SESSION['super_admin_username']) ?></span>
                <a href="index.php" target="_blank" class="text-sm font-semibold bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg transition-colors">Lihat Web</a>
                <a href="?action=logout" class="text-sm font-semibold bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition-colors">Logout</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-10 max-w-5xl">
        
        <?php if ($msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-semibold mb-8 shadow-sm">
                ✅ <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- PENGATURAN TEKS & KONTAK -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">📝 Pengaturan Teks & Kontak</h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="update_settings" value="1">
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

                    <hr class="border-slate-100 my-4">
                    <h3 class="text-lg font-bold text-slate-800">Kontak Footer</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" placeholder="contoh@gmail.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" placeholder="62812345678" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link Instagram</label>
                        <input type="url" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" placeholder="https://instagram.com/..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-4">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- PENGATURAN GAMBAR SLIDER -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 self-start">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">🖼️ Gambar Slider Utama</h2>
                
                <form method="POST" enctype="multipart/form-data" class="mb-8 flex gap-2">
                    <input type="file" name="hero_image" accept="image/*" required class="flex-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                    <button type="submit" class="bg-blue-600 text-white font-bold px-6 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">Upload</button>
                </form>

                <div class="space-y-4 max-h-[450px] overflow-y-auto pr-2">
                    <?php if (empty($sliders)): ?>
                        <p class="text-slate-500 text-sm text-center py-4 bg-slate-50 rounded-xl">Belum ada gambar yang diunggah.</p>
                    <?php endif; ?>
                    
                    <?php foreach ($sliders as $slide): ?>
                        <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 group">
                            <img src="<?= htmlspecialchars($slide['image_path']) ?>" class="w-24 h-16 object-cover rounded-lg shadow-sm">
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-slate-500">Diunggah: <br><span class="text-slate-700 font-bold"><?= date('d M Y', strtotime($slide['created_at'])) ?></span></p>
                            </div>
                            <a href="?delete_image=<?= $slide['id'] ?>" onclick="return confirm('Hapus gambar ini?')" class="text-red-500 bg-red-50 hover:bg-red-500 hover:text-white p-3 rounded-xl transition-colors shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
