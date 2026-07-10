<?php
// FILE: src/master/settings/hero_images.php
session_start();
require_once __DIR__ . '/../../../src/config/database.php';

// Proteksi Khusus Master
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php"); exit;
}

$uploadDir = __DIR__ . '/../../../public/uploads/sliders/';

// Pastikan folder upload ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 1. Proses Upload Gambar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['hero_image'])) {
    $file = $_FILES['hero_image'];
    
    // Validasi Ekstensi dan Mime
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);
    
    if (in_array($ext, $allowedExt) && in_array($mime, $allowedMime)) {
        // Buat nama file unik
        $newFilename = 'slider_' . time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $newFilename;
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Simpan path ke DB
            $dbPath = 'uploads/sliders/' . $newFilename;
            $stmt = $pdo->prepare("INSERT INTO roll_hero_images (image_path) VALUES (?)");
            $stmt->execute([$dbPath]);
            
            $_SESSION['flash_message'] = "Gambar slider berhasil diunggah!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Gagal memindahkan file yang diunggah.";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "Format file tidak didukung! Hanya JPG, PNG, WEBP.";
        $_SESSION['flash_type'] = "error";
    }
    header("Location: hero_images.php"); exit;
}

// 2. Proses Hapus Gambar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM roll_hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    
    if ($img) {
        $filePath = __DIR__ . '/../../../public/' . $img['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $pdo->prepare("DELETE FROM roll_hero_images WHERE id = ?")->execute([$id]);
        $_SESSION['flash_message'] = "Gambar slider berhasil dihapus!";
        $_SESSION['flash_type'] = "success";
    }
    header("Location: hero_images.php"); exit;
}

// Ambil Data Gambar Saat Ini
$stmt = $pdo->query("SELECT * FROM roll_hero_images ORDER BY id DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// FALLBACK LOGIC: Jika kosong, sediakan aset default bernuansa Sepatu Roda (Speed Skating)
if (empty($images)) {
    $images = [
        ['id' => 'Default-1', 'image_path' => 'https://images.unsplash.com/photo-1572016335905-1a890473a216?q=80&w=2000&auto=format&fit=crop'],
        ['id' => 'Default-2', 'image_path' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=2000&auto=format&fit=crop']
    ];
}

// Layout
include __DIR__ . '/../../../views/layout/topbar.php'; 
include __DIR__ . '/../../../views/layout/sidebar.php'; 
?>

<div class="p-6 sm:ml-64 pt-24 bg-slate-950 min-h-screen text-slate-300">
    <div class="max-w-6xl mx-auto">
        
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-red-900 text-red-500 rounded-2xl flex items-center justify-center text-2xl shadow-sm border border-red-800">
                🖼️
            </div>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Manajemen Gambar Slider</h1>
                <p class="text-sm text-slate-500 font-medium">Unggah atau hapus gambar _slideshow_ untuk halaman utama.</p>
            </div>
        </div>

        <!-- Form Upload -->
        <form action="" method="POST" enctype="multipart/form-data" class="bg-slate-900 rounded-3xl shadow-lg border border-slate-800 overflow-hidden mb-8">
            <div class="p-8">
                <label class="block text-sm font-bold text-slate-400 mb-2">Unggah Gambar Baru</label>
                <div class="flex flex-col sm:flex-row gap-4 items-center">
                    <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp" required 
                        class="block w-full text-sm text-slate-400
                        file:mr-4 file:py-3 file:px-6
                        file:rounded-xl file:border-0
                        file:text-sm file:font-bold
                        file:bg-slate-800 file:text-slate-300
                        hover:file:bg-slate-700 hover:file:text-white cursor-pointer bg-slate-950 rounded-xl border border-slate-800">
                    <button type="submit" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5 whitespace-nowrap w-full sm:w-auto">
                        📤 Unggah
                    </button>
                </div>
                <p class="text-[10px] text-slate-500 mt-3">Disarankan rasio 16:9 (Landscape) dengan ukuran maksimal 2MB. Format: JPG, PNG, WEBP.</p>
            </div>
        </form>

        <!-- Grid Gambar -->
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4 border-b border-slate-800 pb-2">Koleksi Gambar Aktif (<?= count($images) ?>)</h3>
        
        <?php if (count($images) === 0): ?>
            <div class="bg-slate-900 border-2 border-dashed border-slate-800 rounded-3xl p-12 text-center">
                <p class="text-slate-500 font-medium">Belum ada gambar yang diunggah.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($images as $img): 
                    // PATH RESOLVER: Beda aset lokal vs eksternal
                    $imgSrc = (strpos($img['image_path'], 'http') === 0) ? $img['image_path'] : BASE_URL . '/public/' . htmlspecialchars($img['image_path']);
                ?>
                <div class="bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 group relative shadow-lg">
                    <img src="<?= $imgSrc ?>" alt="Slider" class="w-full h-48 object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent opacity-80"></div>
                    
                    <div class="absolute bottom-4 left-4 right-4 flex justify-between items-center">
                        <span class="text-[10px] font-medium text-slate-400 truncate w-32 bg-slate-950/80 px-2 py-1 rounded">
                            ID: <?= $img['id'] ?>
                        </span>
                        <?php if(is_numeric($img['id'])): ?>
                        <a href="?delete=<?= $img['id'] ?>" onclick="return confirmAction(event, 'Hapus gambar ini secara permanen?', this.href)" 
                            class="px-4 py-2 bg-red-600/90 hover:bg-red-500 text-white text-xs font-bold rounded-lg shadow transition">
                            Hapus
                        </a>
                        <?php else: ?>
                        <span class="px-4 py-2 bg-slate-800 text-slate-500 text-xs font-bold rounded-lg border border-slate-700">Default</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../../../views/layout/footer.php'; ?>
