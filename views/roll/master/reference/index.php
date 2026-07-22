    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Kamus Standar</h1>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Referensi Master Sepatu Roda</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 px-4 py-3 rounded-lg <?= ($_SESSION['flash_type'] == 'error') ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200' ?> font-bold shadow-sm flex items-center justify-between">
            <span><?= $_SESSION['flash_message'] ?></span>
            <button onclick="this.parentElement.style.display='none'" class="text-slate-500 hover:text-slate-800">✕</button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel 1: Jarak Tempuh -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                    <span class="text-xl">🏁</span> Jarak Tempuh
                </h3>
                <button onclick="document.getElementById('modalDistance').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">
                    + Tambah Jarak
                </button>
            </div>
            <div class="overflow-x-auto flex-1 p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black border-b border-slate-200">
                            <th class="p-4 pl-6 w-12 text-center">No</th>
                            <th class="p-4">Nama Jarak</th>
                            <th class="p-4 pr-6 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php $no = 1; foreach ($distances as $d): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="p-4 pl-6 text-center font-bold text-slate-400"><?= $no++ ?></td>
                            <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($d['distance_name']) ?></td>
                            <td class="p-4 pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editDistance(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['distance_name'])) ?>')" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-2 py-1 rounded font-bold text-xs" title="Edit">
                                        ✏️
                                    </button>
                                    <form action="<?= getenv('APP_URL') ?>/roll/master/reference/deleteDistance/<?= $d['id'] ?>" method="POST" onsubmit="return confirm('Hapus Jarak Tempuh ini?');">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-2 py-1 rounded font-bold text-xs" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($distances)): ?>
                        <tr>
                            <td colspan="3" class="p-6 text-center text-slate-400 font-bold text-sm">Belum ada data jarak tempuh.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel 2: Kelompok Umur -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                    <span class="text-xl">🎂</span> Kelompok Umur
                </h3>
                <button onclick="document.getElementById('modalAgeGroup').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">
                    + Tambah KU
                </button>
            </div>
            <div class="overflow-x-auto flex-1 p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black border-b border-slate-200">
                            <th class="p-4 pl-6 w-12 text-center">No</th>
                            <th class="p-4">Nama KU</th>
                            <th class="p-4 text-center">Tahun (Min - Max)</th>
                            <th class="p-4 pr-6 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php $no = 1; foreach ($ageGroups as $a): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="p-4 pl-6 text-center font-bold text-slate-400"><?= $no++ ?></td>
                            <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($a['group_name']) ?></td>
                            <td class="p-4 text-center text-slate-600 font-mono text-xs">
                                <span class="bg-slate-100 px-2 py-0.5 rounded font-bold"><?= $a['min_year'] ?></span> 
                                - 
                                <span class="bg-slate-100 px-2 py-0.5 rounded font-bold"><?= $a['max_year'] ?></span>
                            </td>
                            <td class="p-4 pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editAgeGroup(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['group_name'])) ?>', <?= $a['min_year'] ?>, <?= $a['max_year'] ?>)" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-2 py-1 rounded font-bold text-xs" title="Edit">
                                        ✏️
                                    </button>
                                    <form action="<?= getenv('APP_URL') ?>/roll/master/reference/deleteAgeGroup/<?= $a['id'] ?>" method="POST" onsubmit="return confirm('Hapus Kelompok Umur ini?');">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-2 py-1 rounded font-bold text-xs" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($ageGroups)): ?>
                        <tr>
                            <td colspan="4" class="p-6 text-center text-slate-400 font-bold text-sm">Belum ada data kelompok umur.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Panel 3: Kategori Roller (Skate Class) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                    <span class="text-xl">🛼</span> Kategori Roller
                </h3>
                <button onclick="document.getElementById('modalSkateClass').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">
                    + Tambah Roller
                </button>
            </div>
            <div class="overflow-x-auto flex-1 p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-widest font-black border-b border-slate-200">
                            <th class="p-4 pl-6 w-12 text-center">No</th>
                            <th class="p-4">Nama Roller</th>
                            <th class="p-4 pr-6 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php $no = 1; foreach ($skateClasses as $sc): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="p-4 pl-6 text-center font-bold text-slate-400"><?= $no++ ?></td>
                            <td class="p-4 font-bold text-slate-700"><?= htmlspecialchars($sc['class_name']) ?></td>
                            <td class="p-4 pr-6">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editSkateClass(<?= $sc['id'] ?>, '<?= htmlspecialchars(addslashes($sc['class_name'])) ?>')" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-2 py-1 rounded font-bold text-xs" title="Edit">
                                        ✏️
                                    </button>
                                    <form action="<?= getenv('APP_URL') ?>/roll/master/reference/deleteSkateClass/<?= $sc['id'] ?>" method="POST" onsubmit="return confirm('Hapus Kategori Roller ini?');">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 px-2 py-1 rounded font-bold text-xs" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($skateClasses)): ?>
                        <tr>
                            <td colspan="3" class="p-6 text-center text-slate-400 font-bold text-sm">Belum ada data Kategori Roller.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Jarak Tempuh -->
<div id="modalDistance" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeDistanceModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 id="distanceModalTitle" class="font-black text-slate-800 uppercase tracking-tight">Tambah Jarak Tempuh</h3>
                <button onclick="closeDistanceModal()" class="text-slate-400 hover:text-red-500 font-bold">✕</button>
            </div>
            <form id="distanceForm" action="<?= getenv('APP_URL') ?>/roll/master/reference/storeDistance" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Jarak</label>
                        <input type="text" id="distance_name" name="distance_name" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 100m, 200m DTT" required>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition uppercase tracking-widest text-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kelompok Umur -->
<div id="modalAgeGroup" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAgeGroupModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 id="ageGroupModalTitle" class="font-black text-slate-800 uppercase tracking-tight">Tambah Kelompok Umur</h3>
                <button onclick="closeAgeGroupModal()" class="text-slate-400 hover:text-red-500 font-bold">✕</button>
            </div>
            <form id="ageGroupForm" action="<?= getenv('APP_URL') ?>/roll/master/reference/storeAgeGroup" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama KU</label>
                        <input type="text" id="group_name" name="group_name" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: KU A, Junior" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-1">Tahun Max</label>
                            <input type="number" id="max_year" name="max_year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Termuda" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-widest mb-1">Tahun Min</label>
                            <input type="number" id="min_year" name="min_year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Tertua" required>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-widest leading-relaxed">Max Year = Tahun kelahiran paling muda (misal 2017).<br>Min Year = Tahun kelahiran paling tua (misal 2015).</p>
                </div>
                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition uppercase tracking-widest text-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kategori Roller -->
<div id="modalSkateClass" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeSkateClassModal()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 id="skateClassModalTitle" class="font-black text-slate-800 uppercase tracking-tight">Tambah Kategori Roller</h3>
                <button onclick="closeSkateClassModal()" class="text-slate-400 hover:text-red-500 font-bold">✕</button>
            </div>
            <form id="skateClassForm" action="<?= getenv('APP_URL') ?>/roll/master/reference/storeSkateClass" method="POST" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Nama Roller</label>
                        <input type="text" id="class_name" name="class_name" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Pemula, Speed" required>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-sm transition uppercase tracking-widest text-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editDistance(id, name) {
    document.getElementById('distanceModalTitle').innerText = 'Edit Jarak Tempuh';
    document.getElementById('distanceForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/updateDistance/' + id;
    document.getElementById('distance_name').value = name;
    document.getElementById('modalDistance').classList.remove('hidden');
}

function closeDistanceModal() {
    document.getElementById('distanceModalTitle').innerText = 'Tambah Jarak Tempuh';
    document.getElementById('distanceForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/storeDistance';
    document.getElementById('distance_name').value = '';
    document.getElementById('modalDistance').classList.add('hidden');
}

function editAgeGroup(id, name, min, max) {
    document.getElementById('ageGroupModalTitle').innerText = 'Edit Kelompok Umur';
    document.getElementById('ageGroupForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/updateAgeGroup/' + id;
    document.getElementById('group_name').value = name;
    document.getElementById('min_year').value = min;
    document.getElementById('max_year').value = max;
    document.getElementById('modalAgeGroup').classList.remove('hidden');
}

function closeAgeGroupModal() {
    document.getElementById('ageGroupModalTitle').innerText = 'Tambah Kelompok Umur';
    document.getElementById('ageGroupForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/storeAgeGroup';
    document.getElementById('group_name').value = '';
    document.getElementById('min_year').value = '';
    document.getElementById('max_year').value = '';
    document.getElementById('modalAgeGroup').classList.add('hidden');
}

function editSkateClass(id, name) {
    document.getElementById('skateClassModalTitle').innerText = 'Edit Kategori Roller';
    document.getElementById('skateClassForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/updateSkateClass/' + id;
    document.getElementById('class_name').value = name;
    document.getElementById('modalSkateClass').classList.remove('hidden');
}

function closeSkateClassModal() {
    document.getElementById('skateClassModalTitle').innerText = 'Tambah Kategori Roller';
    document.getElementById('skateClassForm').action = '<?= getenv("APP_URL") ?>/roll/master/reference/storeSkateClass';
    document.getElementById('class_name').value = '';
    document.getElementById('modalSkateClass').classList.add('hidden');
}
</script>
