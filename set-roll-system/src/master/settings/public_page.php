<?php
// FILE: src/master/settings/public_page.php
require_once __DIR__ . '/../../../src/config/database.php';

// Proteksi Khusus Master
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'master') {
    header("Location: " . BASE_URL . "/public/login.php"); exit;
}

// Ambil Data Saat Ini
$stmt = $pdo->query("SELECT * FROM roll_site_settings ORDER BY id ASC LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    die("Data pengaturan tidak ditemukan. Silakan jalankan setup database.");
}

// Layout
include __DIR__ . '/../../../views/layout/topbar.php'; 
include __DIR__ . '/../../../views/layout/sidebar.php'; 
?>

<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                🌍
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Konfigurasi Landing Page</h1>
                <p class="text-sm text-slate-500 font-medium">Atur teks utama, pengumuman berjalan, dan instruksi publik (Role: Master)</p>
            </div>
        </div>

        <form action="process_cms.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <input type="hidden" name="action" value="update_landing">
            <div class="p-8 space-y-8">
                
                <!-- Section: Hero Utama -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Bagian 1: Hero Banner (Paling Atas)</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Judul Utama (Hero Title)</label>
                            <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title']) ?>" required 
                                class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition px-4 py-3 font-medium text-slate-800">
                            <p class="text-[10px] text-slate-400 mt-1">Muncul besar di tengah layar pertama kali web dibuka.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Sub-Judul (Hero Subtitle)</label>
                            <input type="text" name="hero_subtitle" value="<?= htmlspecialchars($settings['hero_subtitle']) ?>" required 
                                class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition px-4 py-3 font-medium text-slate-800">
                        </div>
                    </div>
                </div>

                <!-- Section: Pengumuman Berjalan -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Bagian 2: Teks Berjalan (News Ticker)</h3>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Konten Running Text</label>
                        <input type="text" name="running_text" value="<?= htmlspecialchars($settings['running_text']) ?>" required 
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition px-4 py-3 font-medium text-slate-800">
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan karakter pemisah seperti | untuk membedakan kalimat.</p>
                    </div>
                </div>

                <!-- Section: Instruksi Pendaftaran -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Bagian 3: Instruksi & Panduan (Bawah)</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Judul Kotak Panduan</label>
                            <input type="text" name="info_title" value="<?= htmlspecialchars($settings['info_title']) ?>" required 
                                class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition px-4 py-3 font-medium text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Isi Panduan</label>
                            <textarea name="info_text" rows="4" required 
                                class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition px-4 py-3 font-medium text-slate-800"><?= htmlspecialchars($settings['info_text']) ?></textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Gunakan tag HTML sederhana (seperti &lt;br&gt; atau &lt;b&gt;) jika diperlukan.</p>
                        </div>
                    </div>
                </div>

                <!-- Section: Gambar Lainnya -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Bagian 4: Gambar Pendukung Publik</h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Gambar Section About (Pattern)</label>
                            <input type="file" name="about_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 bg-slate-50 rounded-xl border border-slate-200">
                            <?php if(!empty($settings['about_image'])): ?>
                                <p class="text-xs text-emerald-600 mt-2 font-bold">✓ Sudah ada gambar. Kosongkan jika tidak ingin mengubah.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Gambar Background Footer</label>
                            <input type="file" name="footer_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 bg-slate-50 rounded-xl border border-slate-200">
                            <?php if(!empty($settings['footer_image'])): ?>
                                <p class="text-xs text-emerald-600 mt-2 font-bold">✓ Sudah ada gambar. Kosongkan jika tidak ingin mengubah.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Gambar Poster Placeholder Event</label>
                            <input type="file" name="event_fallback_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 bg-slate-50 rounded-xl border border-slate-200">
                            <?php if(!empty($settings['event_fallback_image'])): ?>
                                <p class="text-xs text-emerald-600 mt-2 font-bold">✓ Sudah ada gambar. Kosongkan jika tidak ingin mengubah.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="bg-slate-50 p-6 border-t border-slate-200 flex justify-end">
                <button type="submit" name="update_public_page" onclick="return confirmAction(event, 'Simpan perubahan konfigurasi Landing Page?', 'form')" 
                    class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transition transform hover:-translate-y-0.5">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../../../views/layout/footer.php'; ?>
