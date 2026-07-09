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
    $curr = $pdo->query("SELECT * FROM universal_settings WHERE id=1")->fetch();

    $appName = isset($_POST['app_name']) ? $_POST['app_name'] : $curr['app_name'];
    $heroTitle = isset($_POST['hero_title']) ? $_POST['hero_title'] : $curr['hero_title'];
    $siteDesc = isset($_POST['site_description']) ? $_POST['site_description'] : $curr['site_description'];
    $contactEmail = isset($_POST['contact_email']) ? $_POST['contact_email'] : $curr['contact_email'];
    $contactWa = isset($_POST['contact_wa']) ? $_POST['contact_wa'] : $curr['contact_wa'];
    $linkIg = isset($_POST['link_instagram']) ? $_POST['link_instagram'] : $curr['link_instagram'];
    $promoTitle = isset($_POST['promo_title']) ? $_POST['promo_title'] : $curr['promo_title'];
    
    $f1Title = isset($_POST['feature_1_title']) ? $_POST['feature_1_title'] : $curr['feature_1_title'];
    $f1Desc = isset($_POST['feature_1_desc']) ? $_POST['feature_1_desc'] : $curr['feature_1_desc'];
    $f2Title = isset($_POST['feature_2_title']) ? $_POST['feature_2_title'] : $curr['feature_2_title'];
    $f2Desc = isset($_POST['feature_2_desc']) ? $_POST['feature_2_desc'] : $curr['feature_2_desc'];
    $f3Title = isset($_POST['feature_3_title']) ? $_POST['feature_3_title'] : $curr['feature_3_title'];
    $f3Desc = isset($_POST['feature_3_desc']) ? $_POST['feature_3_desc'] : $curr['feature_3_desc'];
    $f4Title = isset($_POST['feature_4_title']) ? $_POST['feature_4_title'] : $curr['feature_4_title'];
    $f4Desc = isset($_POST['feature_4_desc']) ? $_POST['feature_4_desc'] : $curr['feature_4_desc'];

    $stmt = $pdo->prepare("UPDATE universal_settings SET app_name=?, hero_title=?, site_description=?, contact_email=?, contact_wa=?, link_instagram=?, promo_title=?, feature_1_title=?, feature_1_desc=?, feature_2_title=?, feature_2_desc=?, feature_3_title=?, feature_3_desc=?, feature_4_title=?, feature_4_desc=? WHERE id=1");
    $stmt->execute([$appName, $heroTitle, $siteDesc, $contactEmail, $contactWa, $linkIg, $promoTitle, $f1Title, $f1Desc, $f2Title, $f2Desc, $f3Title, $f3Desc, $f4Title, $f4Desc]);

    
    // Handle specific system images
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $swimImg = $_FILES['swim_system_image'] ?? null;
    if ($swimImg && $swimImg['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($swimImg['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'swim_preview_' . time() . '.' . $ext;
            if (move_uploaded_file($swimImg['tmp_name'], $uploadDir . $filename)) {
                $dbPath = 'uploads/' . $filename;
                $pdo->prepare("UPDATE universal_settings SET swim_system_image=? WHERE id=1")->execute([$dbPath]);
            }
        }
    }
    
    $rollImg = $_FILES['roll_system_image'] ?? null;
    if ($rollImg && $rollImg['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($rollImg['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'roll_preview_' . time() . '.' . $ext;
            if (move_uploaded_file($rollImg['tmp_name'], $uploadDir . $filename)) {
                $dbPath = 'uploads/' . $filename;
                $pdo->prepare("UPDATE universal_settings SET roll_system_image=? WHERE id=1")->execute([$dbPath]);
            }
        }
    }
    
    // Event Logos
    $swimLogo = $_FILES['swim_event_logo'] ?? null;
    if ($swimLogo && $swimLogo['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($swimLogo['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'swim_logo_' . time() . '.' . $ext;
            if (move_uploaded_file($swimLogo['tmp_name'], $uploadDir . $filename)) {
                $dbPath = 'uploads/' . $filename;
                $pdo->prepare("UPDATE universal_settings SET swim_event_logo=? WHERE id=1")->execute([$dbPath]);
            }
        }
    }
    
    $rollLogo = $_FILES['roll_event_logo'] ?? null;
    if ($rollLogo && $rollLogo['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($rollLogo['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'roll_logo_' . time() . '.' . $ext;
            if (move_uploaded_file($rollLogo['tmp_name'], $uploadDir . $filename)) {
                $dbPath = 'uploads/' . $filename;
                $pdo->prepare("UPDATE universal_settings SET roll_event_logo=? WHERE id=1")->execute([$dbPath]);
            }
        }
    }
    
    // Feature Icons
    for ($i = 1; $i <= 4; $i++) {
        $featIcon = $_FILES["feature_{$i}_icon"] ?? null;
        if ($featIcon && $featIcon['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($featIcon['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                $filename = "feature_{$i}_" . time() . '.' . $ext;
                if (move_uploaded_file($featIcon['tmp_name'], $uploadDir . $filename)) {
                    $dbPath = 'uploads/' . $filename;
                    $pdo->prepare("UPDATE universal_settings SET feature_{$i}_icon=? WHERE id=1")->execute([$dbPath]);
                }
            }
        }
    }

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

        <div class="space-y-8">
            <!-- 1. PENGATURAN TEKS & NAVBAR -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">📝 Pengaturan Navbar & Teks Utama</h2>
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
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-4">Simpan Perubahan</button>
                </form>
            </div>

            <!-- 2. PENGATURAN GAMBAR SLIDER (HERO) -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">🖼️ Gambar Slider Utama (Hero)</h2>
                
                <form method="POST" enctype="multipart/form-data" class="mb-8 flex gap-2">
                    <input type="file" name="hero_image" accept="image/*" required class="flex-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                    <button type="submit" class="bg-blue-600 text-white font-bold px-6 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">Upload</button>
                </form>

                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if (empty($sliders)): ?>
                        <p class="text-slate-500 text-sm text-center py-4 bg-slate-50 rounded-xl col-span-full">Belum ada gambar yang diunggah.</p>
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

            <!-- 3. GAMBAR PREVIEW & LOGO EVENT -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">📸 Gambar Preview Sistem & Logo Event</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="update_settings" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Swim System Preview</label>
                                <?php if (!empty($settings['swim_system_image'])): ?>
                                    <img src="<?= htmlspecialchars($settings['swim_system_image']) ?>" class="w-full h-32 object-cover rounded-lg mb-2 border">
                                <?php endif; ?>
                                <input type="file" name="swim_system_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-100 file:text-blue-700 mb-4">
                                
                                <label class="block text-sm font-semibold text-slate-700 mb-1 border-t border-blue-200 pt-4">Swim Event Logo (Watermark)</label>
                                <?php if (!empty($settings['swim_event_logo'])): ?>
                                    <img src="<?= htmlspecialchars($settings['swim_event_logo']) ?>" class="w-16 h-16 object-contain bg-slate-800 rounded-lg mb-2 border">
                                <?php endif; ?>
                                <input type="file" name="swim_event_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-100 file:text-blue-700">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-orange-50/50 p-4 rounded-2xl border border-orange-100">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Roll System Preview</label>
                                <?php if (!empty($settings['roll_system_image'])): ?>
                                    <img src="<?= htmlspecialchars($settings['roll_system_image']) ?>" class="w-full h-32 object-cover rounded-lg mb-2 border">
                                <?php endif; ?>
                                <input type="file" name="roll_system_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-orange-100 file:text-orange-700 mb-4">
                                
                                <label class="block text-sm font-semibold text-slate-700 mb-1 border-t border-orange-200 pt-4">Roll Event Logo (Watermark)</label>
                                <?php if (!empty($settings['roll_event_logo'])): ?>
                                    <img src="<?= htmlspecialchars($settings['roll_event_logo']) ?>" class="w-16 h-16 object-contain bg-slate-800 rounded-lg mb-2 border">
                                <?php endif; ?>
                                <input type="file" name="roll_event_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-orange-100 file:text-orange-700">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-4">Simpan Perubahan</button>
                </form>
            </div>

            <!-- 4. THEME FEATURES -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">⚡ Theme Features (4 Kotak)</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="update_settings" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 1</label>
                            <?php if (!empty($settings['feature_1_icon'])): ?>
                                <img src="<?= htmlspecialchars($settings['feature_1_icon']) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2">
                            <?php endif; ?>
                            <input type="file" name="feature_1_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                            <input type="text" name="feature_1_title" value="<?= htmlspecialchars($settings['feature_1_title'] ?? 'Live Timing') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                            <input type="text" name="feature_1_desc" value="<?= htmlspecialchars($settings['feature_1_desc'] ?? 'Hasil waktu nyata yang langsung disiarkan untuk semua penonton.') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 2</label>
                            <?php if (!empty($settings['feature_2_icon'])): ?>
                                <img src="<?= htmlspecialchars($settings['feature_2_icon']) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2">
                            <?php endif; ?>
                            <input type="file" name="feature_2_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                            <input type="text" name="feature_2_title" value="<?= htmlspecialchars($settings['feature_2_title'] ?? 'Heat Seeding') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                            <input type="text" name="feature_2_desc" value="<?= htmlspecialchars($settings['feature_2_desc'] ?? 'Penyusunan lintasan otomatis berbasis waktu terbaik atlet.') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 3</label>
                            <?php if (!empty($settings['feature_3_icon'])): ?>
                                <img src="<?= htmlspecialchars($settings['feature_3_icon']) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2">
                            <?php endif; ?>
                            <input type="file" name="feature_3_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                            <input type="text" name="feature_3_title" value="<?= htmlspecialchars($settings['feature_3_title'] ?? 'Buku Acara') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                            <input type="text" name="feature_3_desc" value="<?= htmlspecialchars($settings['feature_3_desc'] ?? 'Cetak otomatis buku acara (start list) hanya dengan 1 klik.') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 4</label>
                            <?php if (!empty($settings['feature_4_icon'])): ?>
                                <img src="<?= htmlspecialchars($settings['feature_4_icon']) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2">
                            <?php endif; ?>
                            <input type="file" name="feature_4_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                            <input type="text" name="feature_4_title" value="<?= htmlspecialchars($settings['feature_4_title'] ?? 'Analitik Hasil') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                            <input type="text" name="feature_4_desc" value="<?= htmlspecialchars($settings['feature_4_desc'] ?? 'Perhitungan poin klasemen dan medali secara otomatis.') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-4">Simpan Perubahan</button>
                </form>
            </div>

            <!-- 5. SEKSI PROMO & KONTAK FOOTER -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">📣 Seksi Promo & Kontak Footer</h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="update_settings" value="1">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Promo Utama (Call to Action)</label>
                        <input type="text" name="promo_title" value="<?= htmlspecialchars($settings['promo_title'] ?? '') ?>" placeholder="PERCAYAKAN MANAJEMEN KOMPETISI ANDA BERSAMA KAMI" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <hr class="border-slate-100 my-4">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Kontak Footer</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" placeholder="contoh@gmail.com" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">No. WhatsApp</label>
                            <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa'] ?? '') ?>" placeholder="62812345678" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Link Instagram</label>
                        <input type="url" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram'] ?? '') ?>" placeholder="https://instagram.com/..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-6">Simpan Perubahan</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
