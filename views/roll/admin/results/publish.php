<?php
// FILE: views/roll/admin/results/publish.php
$title = "Publikasi Hasil";
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Publikasi Hasil</h2>
    <p class="text-slate-500 text-sm">Kelola visibilitas hasil lomba pada portal publik.</p>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="p-4 mb-6 rounded shadow <?= $_SESSION['flash_type'] == 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700' ?>">
        <?= $_SESSION['flash_message'] ?>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<!-- Global Publish Toggle -->
<div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-8 flex justify-between items-center">
    <div>
        <h3 class="font-bold text-slate-800 text-lg">Publikasi Seluruh Hasil Event</h3>
        <p class="text-sm text-slate-500">Buka atau tutup seluruh akses hasil pada portal secara global.</p>
    </div>
    <div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" class="sr-only peer global-toggle" data-event-id="<?= $eventId ?>" <?= (isset($eventInfo['is_result_published']) && $eventInfo['is_result_published'] == 1) ? 'checked' : '' ?>>
            <div class="w-14 h-7 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
        </label>
    </div>
</div>

<!-- Table by Classes -->
<div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b">
                <tr>
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Kategori Lomba</th>
                    <th class="px-6 py-4">Status Publikasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($classes)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-slate-500">Belum ada data kelas lomba.</td>
                </tr>
                <?php else: ?>
                    <?php foreach($classes as $index => $c): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900"><?= $index + 1 ?></td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($c['distance_name'] ?? '') ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($c['category_name'] ?? '') ?> - <?= htmlspecialchars($c['group_name'] ?? '') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer class-toggle" data-class-id="<?= $c['id'] ?>" <?= ($c['result_status'] == 'Published') ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                            <span class="ml-3 text-xs font-semibold class-status-text <?= ($c['result_status'] == 'Published') ? 'text-green-600' : 'text-slate-500' ?>">
                                <?= ($c['result_status'] == 'Published') ? 'PUBLISHED' : 'DRAFT' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Global Event Toggle
    const globalToggle = document.querySelector('.global-toggle');
    if (globalToggle) {
        globalToggle.addEventListener('change', function() {
            const evId = this.getAttribute('data-event-id');
            const isPub = this.checked ? 1 : 0;
            
            fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'action': 'toggle_event_publish',
                    'event_id': evId,
                    'is_result_published': isPub
                })
            })
            .then(res => res.json())
            .then(data => {
                if(!data.success) {
                    alert('Gagal: ' + data.message);
                    this.checked = !this.checked;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan.');
                this.checked = !this.checked;
            });
        });
    }

    // Class Toggle
    document.querySelectorAll('.class-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const classId = this.getAttribute('data-class-id');
            const statusStr = this.checked ? 'Published' : 'Draft';
            const textSpan = this.parentElement.nextElementSibling;
            
            fetch('<?= getenv('APP_URL') ?>/roll/admin/results/publish', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'action': 'toggle_publish',
                    'class_id': classId,
                    'is_published': statusStr
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    textSpan.textContent = statusStr.toUpperCase();
                    if(statusStr === 'Published') {
                        textSpan.classList.remove('text-slate-500');
                        textSpan.classList.add('text-green-600');
                    } else {
                        textSpan.classList.remove('text-green-600');
                        textSpan.classList.add('text-slate-500');
                    }
                } else {
                    alert('Gagal: ' + data.message);
                    this.checked = !this.checked;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan jaringan.');
                this.checked = !this.checked;
            });
        });
    });

});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../layout/master_layout.php';
?>
