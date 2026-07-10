<?php
// FILE: src/master/settings/global_config.php
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
                ⚙️
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Pengaturan Global Sistem</h1>
                <p class="text-sm text-slate-500 font-medium">Manajemen mode perbaikan, profil sosial, dan kontak pusat.</p>
            </div>
        </div>

        <form action="process_cms.php" method="POST" id="global-config-form" class="space-y-6">
            <input type="hidden" name="action" value="update_global">
            
            <!-- SECTION 1: SYSTEM CONTROLS (CRITICAL) -->
            <div class="bg-white rounded-3xl shadow-sm border <?= $settings['maintenance_mode'] ? 'border-red-500' : 'border-slate-200' ?> overflow-hidden relative">
                
                <?php if($settings['maintenance_mode']): ?>
                    <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1 rounded-bl-xl z-10 animate-pulse">
                        Sistem Sedang Dikunci
                    </div>
                <?php endif; ?>

                <div class="p-8">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Kontrol Utama Sistem</h3>
                    
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="pt-1">
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                                  <div class="w-14 h-7 bg-red-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-600"></div>
                                </label>
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-red-800">Aktifkan Maintenance Mode (Mode Perbaikan)</h4>
                                <p class="text-sm text-red-600 mt-1">Jika saklar ini dinyalakan, semua pengguna biasa (Publik, User Klub, Admin) tidak akan bisa mengakses sistem. Halaman depan akan berubah menjadi layar perbaikan. <b>Hanya Master yang diizinkan masuk.</b></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: KONTAK & SOSIAL MEDIA -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Informasi Kontak & Tautan</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">WhatsApp Pusat</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">📞</div>
                                <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa']) ?>" 
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition pl-11 pr-4 py-3 font-medium text-slate-800">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Format: 628xxx (tanpa + atau 0)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Email Pusat</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">✉️</div>
                                <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email']) ?>" 
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition pl-11 pr-4 py-3 font-medium text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Link Instagram</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">📸</div>
                                <input type="text" name="link_instagram" value="<?= htmlspecialchars($settings['link_instagram']) ?>" 
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition pl-11 pr-4 py-3 font-medium text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Link Facebook</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">📘</div>
                                <input type="text" name="link_facebook" value="<?= htmlspecialchars($settings['link_facebook']) ?>" 
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-red-500 transition pl-11 pr-4 py-3 font-medium text-slate-800">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 border-t border-slate-200 flex justify-end">
                    <button type="submit" name="update_global_config" onclick="return confirmAction(event, 'Simpan pengaturan global ini?', 'global-config-form')" 
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transition transform hover:-translate-y-0.5">
                        💾 Simpan Pengaturan
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<?php include __DIR__ . '/../../../views/layout/footer.php'; ?>
