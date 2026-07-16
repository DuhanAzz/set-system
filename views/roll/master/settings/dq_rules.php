
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter">Aturan Diskualifikasi (DQ)</h2>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' ?> font-bold">
            <?= $_SESSION['flash_msg'] ?>
        </div>
        <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Form Add DQ Rule -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Tambah Aturan Baru</h3>
                </div>
                <div class="p-6">
                    <form action="<?= getenv('APP_URL') ?>/roll/masterSettings/dq_rules" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_rule">
                        
                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Kode DQ</label>
                            <input type="text" name="kode_dq" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-red-500 bg-slate-50 font-mono text-lg uppercase" placeholder="Contoh: FS">
                        </div>

                        <div>
                            <label class="block text-slate-600 font-bold mb-2 text-xs uppercase">Deskripsi Pelanggaran</label>
                            <textarea name="deskripsi" required rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-red-500 bg-slate-50" placeholder="False Start..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black uppercase text-xs tracking-widest py-3 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                            Simpan Aturan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table DQ Rules -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-black text-slate-800 uppercase tracking-wider text-sm">Daftar Kode Diskualifikasi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">KODE DQ</th>
                                <th class="px-6 py-4">DESKRIPSI</th>
                                <th class="px-6 py-4 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($rules)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">Belum ada aturan diskualifikasi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rules as $r): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4">
                                            <span class="bg-red-100 text-red-700 font-black uppercase px-3 py-1 rounded-md text-sm font-mono border border-red-200">
                                                <?= htmlspecialchars($r['kode_dq']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-slate-700">
                                            <?= htmlspecialchars($r['deskripsi']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="<?= getenv('APP_URL') ?>/roll/masterSettings/dq_rules" method="POST" onsubmit="return confirm('Yakin hapus kode DQ ini?');" class="inline-block">
                                                <input type="hidden" name="action" value="delete_rule">
                                                <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="text-slate-400 hover:text-red-600 transition">
                                                    🗑️
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
