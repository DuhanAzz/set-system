    <main class="flex-grow container mx-auto px-6 py-12 max-w-5xl">
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl font-semibold mb-8 shadow-sm">
                ✅ Pengaturan Halaman Depan berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 mb-2">Public Page</h1>
            <p class="text-slate-500 font-medium">Atur Watermark Logo Event & Preview Sistem untuk Halaman Publik.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200">
            <form action="<?= getenv('APP_URL') ?>/core/settings/process" method="POST" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="action" value="update_landing">
                
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">📸 Gambar Preview Sistem & Logo Event</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- SWIM SYSTEM -->
                    <div class="space-y-4">
                        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Swim System Preview</label>
                            <?php if (!empty($settings['swim_system_image'])): ?>
                                <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['swim_system_image'], '/'))) ?>" class="w-full h-32 object-cover rounded-lg mb-2 border" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['swim_system_image'], '/')) ?>'">
                            <?php endif; ?>
                            <input type="file" name="swim_system_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-100 file:text-blue-700 mb-4">
                            
                            <label class="block text-sm font-semibold text-slate-700 mb-1 border-t border-blue-200 pt-4">Swim Event Logo (Watermark)</label>
                            <?php if (!empty($settings['swim_event_logo'])): ?>
                                <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['swim_event_logo'], '/'))) ?>" class="w-16 h-16 object-contain bg-slate-800 rounded-lg mb-2 border" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['swim_event_logo'], '/')) ?>'">
                            <?php endif; ?>
                            <input type="file" name="swim_event_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-100 file:text-blue-700">
                        </div>
                    </div>
                    
                    <!-- ROLL SYSTEM -->
                    <div class="space-y-4">
                        <div class="bg-orange-50/50 p-4 rounded-2xl border border-orange-100">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Roll System Preview</label>
                            <?php if (!empty($settings['roll_system_image'])): ?>
                                <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['roll_system_image'], '/'))) ?>" class="w-full h-32 object-cover rounded-lg mb-2 border" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['roll_system_image'], '/')) ?>'">
                            <?php endif; ?>
                            <input type="file" name="roll_system_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-orange-100 file:text-orange-700 mb-4">
                            
                            <label class="block text-sm font-semibold text-slate-700 mb-1 border-t border-orange-200 pt-4">Roll Event Logo (Watermark)</label>
                            <?php if (!empty($settings['roll_event_logo'])): ?>
                                <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['roll_event_logo'], '/'))) ?>" class="w-16 h-16 object-contain bg-slate-800 rounded-lg mb-2 border" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['roll_event_logo'], '/')) ?>'">
                            <?php endif; ?>
                            <input type="file" name="roll_event_logo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-orange-100 file:text-orange-700">
                        </div>
                    </div>
                </div>
                
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2 pt-6 border-t border-slate-100">⚡ Theme Features (4 Kotak)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Feature 1 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 1</label>
                        <?php if (!empty($settings['feature_1_icon'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['feature_1_icon'], '/'))) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['feature_1_icon'], '/')) ?>'">
                        <?php endif; ?>
                        <input type="file" name="feature_1_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                        <input type="text" name="feature_1_title" value="<?= htmlspecialchars($settings['feature_1_title'] ?? 'Live Timing') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                        <input type="text" name="feature_1_desc" value="<?= htmlspecialchars($settings['feature_1_desc'] ?? 'Hasil waktu nyata yang langsung disiarkan untuk semua penonton.') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 2</label>
                        <?php if (!empty($settings['feature_2_icon'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['feature_2_icon'], '/'))) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['feature_2_icon'], '/')) ?>'">
                        <?php endif; ?>
                        <input type="file" name="feature_2_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                        <input type="text" name="feature_2_title" value="<?= htmlspecialchars($settings['feature_2_title'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                        <input type="text" name="feature_2_desc" value="<?= htmlspecialchars($settings['feature_2_desc'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 3</label>
                        <?php if (!empty($settings['feature_3_icon'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['feature_3_icon'], '/'))) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['feature_3_icon'], '/')) ?>'">
                        <?php endif; ?>
                        <input type="file" name="feature_3_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                        <input type="text" name="feature_3_title" value="<?= htmlspecialchars($settings['feature_3_title'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                        <input type="text" name="feature_3_desc" value="<?= htmlspecialchars($settings['feature_3_desc'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                    </div>
                    
                    <!-- Feature 4 -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Kotak 4</label>
                        <?php if (!empty($settings['feature_4_icon'])): ?>
                            <img src="<?= getenv('APP_URL') ?>/<?= htmlspecialchars(str_replace('set-swim-system/public/', '', ltrim($settings['feature_4_icon'], '/'))) ?>" class="w-10 h-10 object-contain bg-white rounded border p-1 mb-2" onerror="this.src='<?= getenv('APP_URL') ?>/<?= htmlspecialchars(ltrim($settings['feature_4_icon'], '/')) ?>'">
                        <?php endif; ?>
                        <input type="file" name="feature_4_icon" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 mb-2">
                        <input type="text" name="feature_4_title" value="<?= htmlspecialchars($settings['feature_4_title'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Judul">
                        <input type="text" name="feature_4_desc" value="<?= htmlspecialchars($settings['feature_4_desc'] ?? '') ?>" class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Deskripsi">
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition-colors mt-8">Simpan Perubahan Landing Page</button>
            </form>
        </div>
    </main>
