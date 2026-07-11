        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-semibold mb-8 shadow-sm">
                ✅ Gambar berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Gambar Slider Utama (Hero)</h1>
            <p class="text-slate-500 font-medium">Upload dan kelola gambar latar belakang yang bergulir di Halaman Depan.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <form action="<?= getenv('APP_URL') ?>/master/settings/process" method="POST" enctype="multipart/form-data" class="mb-8 flex gap-2">
                <input type="hidden" name="action" value="upload_slider">
                <input type="file" name="hero_image" accept="image/*" required class="flex-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                <button type="submit" class="bg-blue-600 text-white font-bold px-6 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">Upload</button>
            </form>

            <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (empty($sliders)): ?>
                    <p class="text-slate-500 text-sm text-center py-4 bg-slate-50 rounded-xl col-span-full">Belum ada gambar yang diunggah.</p>
                <?php endif; ?>
                
                <?php foreach ($sliders as $slide): ?>
                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 group">
                        <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($slide['image_path'], '/'))) ?>" class="w-24 h-16 object-cover rounded-lg shadow-sm" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($slide['image_path'], '/')) ?>'">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-500">Dibuat: <br><span class="text-slate-700 font-bold"><?= isset($slide['created_at']) ? date('d M Y', strtotime($slide['created_at'])) : '-' ?></span></p>
                        </div>
                        <form action="<?= getenv('APP_URL') ?>/master/settings/process" method="POST" class="inline">
                            <input type="hidden" name="action" value="delete_slider">
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <button type="submit" onclick="return confirm('Hapus gambar ini?')" class="text-red-500 bg-red-50 hover:bg-red-500 hover:text-white p-3 rounded-xl transition-colors shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PENGATURAN PROMO PARALLAX IMAGE -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 mt-8">
            <h2 class="text-2xl font-bold mb-2 flex items-center gap-2">🌄 Gambar Latar Promo (Parallax)</h2>
            <p class="text-slate-500 font-medium mb-6">Gambar ini akan digunakan sebagai efek latar belakang menawan (Parallax) di bagian bawah halaman.</p>
            
            <?php 
                // Cek gambar promo yang ada dari tabel settings
                $db = \App\Core\Database::getInstance()->getConnection();
                try {
                    $promo = $db->query("SELECT promo_image FROM universal_settings WHERE id=1")->fetchColumn();
                } catch (\Exception $e) { $promo = null; }
            ?>
            <div class="flex items-center gap-6">
                <?php if (!empty($promo)): ?>
                    <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($promo, '/'))) ?>" class="w-40 h-24 object-cover rounded-xl shadow border border-slate-200" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($promo, '/')) ?>'">
                <?php else: ?>
                    <div class="w-40 h-24 bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center text-xs text-slate-400 font-bold">Belum Ada Gambar</div>
                <?php endif; ?>
                
                <form action="<?= getenv('APP_URL') ?>/master/settings/process" method="POST" enctype="multipart/form-data" class="flex-1 flex gap-2">
                    <input type="hidden" name="action" value="upload_promo_image">
                    <input type="file" name="promo_image" accept="image/*" required class="flex-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                    <button type="submit" class="bg-blue-600 text-white font-bold px-6 rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/30">Simpan Promo Latar</button>
                </form>
            </div>
        </div>
    </main>
