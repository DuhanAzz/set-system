<div class="-m-6 p-6 min-h-[calc(100vh-4rem)] bg-white text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Flash Messages (Universal Toast) -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div id="toast-message" class="fixed bottom-10 right-10 z-[9999] min-w-[300px] p-4 rounded-xl border <?= $_SESSION['flash_type'] === 'success' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-red-600 border-red-500 text-white' ?> flex items-center justify-between shadow-2xl transition-all duration-500 transform translate-y-0 opacity-100">
                <div class="flex items-center space-x-3">
                    <span class="text-xl"><?= $_SESSION['flash_type'] === 'success' ? '✅' : '⚠️' ?></span>
                    <span class="font-bold tracking-wide"><?= $_SESSION['flash_message'] ?></span>
                </div>
                <button onclick="this.parentElement.style.opacity='0'; setTimeout(()=>this.parentElement.remove(), 500)" class="text-2xl ml-6 hover:text-slate-200 transition-colors">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    let toast = document.getElementById('toast-message');
                    if(toast) {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateY(20px)';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 4000);
            </script>
            <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-200/50 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <span class="text-9xl">🏛️</span>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300 tracking-tight uppercase">Kelas Lomba</h1>
                    <p class="text-slate-500 mt-2 font-medium">Manajemen Matriks dan Jadwal Tergenerate</p>
                </div>
                <?php if(!empty($row)): ?>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-2 md:gap-4">
                    <a href="<?= getenv('APP_URL') ?>/roll/admin/events/print_schedule" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-indigo-500/25 transition-all flex items-center">
                        <span class="mr-2">🖨️</span> Cetak Jadwal & Kelas
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(empty($row)): ?>
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl p-12 text-center backdrop-blur-sm">
                <span class="text-6xl mb-4 block">⚠️</span>
                <h3 class="text-xl font-bold text-slate-600 mb-2">Tidak Ada Event Aktif</h3>
                <p class="text-slate-500">Silakan buat atau pilih event aktif melalui Dashboard terlebih dahulu.</p>
            </div>
        <?php else: ?>

        <div class="space-y-8">
            <!-- Kelas Lomba -->
            <div class="bg-slate-50/50 rounded-2xl border border-slate-200/50 shadow-xl backdrop-blur-sm w-full flex flex-col">
                <div class="p-6 border-b border-slate-200/50 flex justify-between items-center bg-white rounded-t-2xl">
                    <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Matriks Kelas Lomba</h3>
                    <span class="text-[10px] text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200 font-bold uppercase tracking-widest">Isi Nomor Lomba (Misal: 104)</span>
                </div>
                
                <?php
                // Logic to prepare matrix data
                $speedKuNames = ['Ku A (< 7 Thn)', 'Ku B (8-9 Thn)', 'Ku C (10-11 Thn)', 'Ku D (12-14 Thn)', 'Junior (15-18 Thn)', 'Senior (>= 19 Thn)'];
                $speedDistNames = ['ITT 100m', 'ITT 200m', 'DTT 200m', '500m +D', '1000m', '3000m Eliminasi', '5000m Eliminasi', '10.000m Eliminasi', '3000m PTP', '5000m PTP', 'Pair 500m', 'Relay 3000m', 'TTT'];

                $stdKuNames = ['Ku A (< 7 Thn)', 'Ku B (8-9 Thn)', 'Ku C (10-11 Thn)', 'Ku D (12-14 Thn)'];
                $stdDistNames = ['300m', '500m', '1000m', 'Relay 1000m', 'Relay 1800m'];
                
                $pemulaKuNames = ['Ku I (< 7 Thn)', 'Ku II (7-9 Thn)', 'Ku III (> 9 Thn)'];
                $pemulaDistNames = ['100m', '200m'];

                // Tampilkan semua KU dari master referensi agar dinamis jika ada perubahan/penambahan
                $speedKUs = $ageGroups;
                $stdKUs = $ageGroups;
                $pemulaKUs = $ageGroups;

                $speedDists = array_filter($distances, fn($d) => in_array($d['distance_name'], $speedDistNames));
                usort($speedDists, fn($a, $b) => array_search($a['distance_name'], $speedDistNames) - array_search($b['distance_name'], $speedDistNames));

                $stdDists = array_filter($distances, fn($d) => in_array($d['distance_name'], $stdDistNames));
                usort($stdDists, fn($a, $b) => array_search($a['distance_name'], $stdDistNames) - array_search($b['distance_name'], $stdDistNames));

                $pemulaDists = array_filter($distances, fn($d) => in_array($d['distance_name'], $pemulaDistNames));
                usort($pemulaDists, fn($a, $b) => array_search($a['distance_name'], $pemulaDistNames) - array_search($b['distance_name'], $pemulaDistNames));

                // Existing matrix: $matrixData[skate_class_id][age_group_id][distance_id] = race_number
                $matrixData = [];
                foreach ($classes as $c) {
                    $matrixData[$c['skate_class_id']][$c['age_group_id']][$c['distance_id']] = $c['race_number'];
                }

                // Get skate_class_id
                $scSpeed = 3; $scStd = 2; $scPemula = 1;
                foreach($skateClasses as $sc) {
                    if(strtolower($sc['class_name']) == 'speed') $scSpeed = $sc['id'];
                    if(strtolower($sc['class_name']) == 'standart') $scStd = $sc['id'];
                    if(strtolower($sc['class_name']) == 'pemula') $scPemula = $sc['id'];
                }
                ?>

                <div class="p-6 bg-white rounded-b-2xl">
                    <!-- TABS -->
                    <div class="flex space-x-2 border-b border-slate-200 mb-6" id="matrix-tabs">
                        <button type="button" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-fuchsia-600 border-b-2 border-fuchsia-500 tab-btn transition-colors" onclick="switchTab('speed', this)">Speed</button>
                        <button type="button" class="px-6 py-3 text-xs font-bold uppercase tracking-widest text-slate-400 border-b-2 border-transparent hover:text-slate-600 tab-btn transition-colors" onclick="switchTab('standar', this)">Standar</button>
                        <button type="button" class="px-6 py-3 text-xs font-bold uppercase tracking-widest text-slate-400 border-b-2 border-transparent hover:text-slate-600 tab-btn transition-colors" onclick="switchTab('pemula', this)">Pemula</button>
                    </div>

                    <form action="<?= getenv('APP_URL') ?>/roll/admin/events/saveMatrix" method="POST">
                        <input type="hidden" name="event_id" value="<?= $eventId ?>">

                        <!-- TAB SPEED -->
                        <div id="tab-speed" class="tab-content overflow-x-auto bg-white border border-slate-200 rounded-xl shadow-sm">
                            <table class="w-full text-left border-collapse min-w-max">
                                <thead>
                                    <tr class="bg-slate-50 text-[10px] uppercase font-black tracking-widest text-fuchsia-700 border-b border-slate-200">
                                        <th class="p-4 sticky left-0 bg-slate-100 z-10 w-32 border-r border-slate-200">KU \ Jarak</th>
                                        <?php foreach($speedDists as $d): ?>
                                            <th class="p-3 text-center border-l border-slate-200" title="<?= $d['distance_name'] ?>">
                                                <?= str_replace(' Eliminasi', ' Elim', $d['distance_name']) ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <?php foreach($speedKUs as $ku): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-800 font-bold sticky left-0 bg-white z-10 border-r border-slate-200 text-xs shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <?= htmlspecialchars($ku['group_name']) ?>
                                        </td>
                                        <?php foreach($speedDists as $d): 
                                            $val = $matrixData[$scSpeed][$ku['id']][$d['id']] ?? '';
                                        ?>
                                            <td class="p-2 border-l border-slate-200 text-center align-middle">
                                                <input type="text" name="matrix[<?= $scSpeed ?>][<?= $ku['id'] ?>][<?= $d['id'] ?>]" value="<?= htmlspecialchars($val) ?>" class="w-14 mx-auto bg-slate-50 border border-slate-200 rounded px-2 py-2 text-center text-slate-800 focus:ring-2 focus:ring-fuchsia-500 font-bold transition-all text-xs" placeholder="-">
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB STANDAR -->
                        <div id="tab-standar" class="tab-content overflow-x-auto bg-white border border-slate-200 rounded-xl shadow-sm hidden">
                            <table class="w-full text-left border-collapse min-w-max">
                                <thead>
                                    <tr class="bg-slate-50 text-[10px] uppercase font-black tracking-widest text-amber-600 border-b border-slate-200">
                                        <th class="p-4 sticky left-0 bg-slate-100 z-10 w-32 border-r border-slate-200">KU \ Jarak</th>
                                        <?php foreach($stdDists as $d): ?>
                                            <th class="p-3 text-center border-l border-slate-200"><?= $d['distance_name'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <?php foreach($stdKUs as $ku): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-800 font-bold sticky left-0 bg-white z-10 border-r border-slate-200 text-xs shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <?= htmlspecialchars($ku['group_name']) ?>
                                        </td>
                                        <?php foreach($stdDists as $d): 
                                            $val = $matrixData[$scStd][$ku['id']][$d['id']] ?? '';
                                        ?>
                                            <td class="p-2 border-l border-slate-200 text-center align-middle">
                                                <input type="text" name="matrix[<?= $scStd ?>][<?= $ku['id'] ?>][<?= $d['id'] ?>]" value="<?= htmlspecialchars($val) ?>" class="w-14 mx-auto bg-slate-50 border border-slate-200 rounded px-2 py-2 text-center text-slate-800 focus:ring-2 focus:ring-amber-500 font-bold transition-all text-xs" placeholder="-">
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB PEMULA -->
                        <div id="tab-pemula" class="tab-content overflow-x-auto bg-white border border-slate-200 rounded-xl shadow-sm hidden">
                            <table class="w-full text-left border-collapse min-w-max">
                                <thead>
                                    <tr class="bg-slate-50 text-[10px] uppercase font-black tracking-widest text-emerald-600 border-b border-slate-200">
                                        <th class="p-4 sticky left-0 bg-slate-100 z-10 w-32 border-r border-slate-200">KU \ Jarak</th>
                                        <?php foreach($pemulaDists as $d): ?>
                                            <th class="p-3 text-center border-l border-slate-200"><?= $d['distance_name'] ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <?php foreach($pemulaKUs as $ku): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="p-4 text-slate-800 font-bold sticky left-0 bg-white z-10 border-r border-slate-200 text-xs shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <?= htmlspecialchars($ku['group_name']) ?>
                                        </td>
                                        <?php foreach($pemulaDists as $d): 
                                            $val = $matrixData[$scPemula][$ku['id']][$d['id']] ?? '';
                                        ?>
                                            <td class="p-2 border-l border-slate-200 text-center align-middle">
                                                <input type="text" name="matrix[<?= $scPemula ?>][<?= $ku['id'] ?>][<?= $d['id'] ?>]" value="<?= htmlspecialchars($val) ?>" class="w-14 mx-auto bg-slate-50 border border-slate-200 rounded px-2 py-2 text-center text-slate-800 focus:ring-2 focus:ring-emerald-500 font-bold transition-all text-xs" placeholder="-">
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-8 flex justify-end pt-4 border-t border-slate-200">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-10 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all uppercase tracking-widest text-sm flex items-center gap-2">
                                💾 Simpan Matriks
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function switchTab(tabId, btn) {
                    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                    document.getElementById('tab-' + tabId).classList.remove('hidden');
                    
                    document.querySelectorAll('.tab-btn').forEach(el => {
                        el.classList.remove('text-fuchsia-600', 'border-fuchsia-500', 'text-amber-600', 'border-amber-500', 'text-emerald-600', 'border-emerald-500', 'font-black');
                        el.classList.add('text-slate-400', 'border-transparent', 'font-bold');
                    });
                    
                    btn.classList.remove('text-slate-400', 'border-transparent', 'font-bold');
                    btn.classList.add('font-black');
                    if (tabId === 'speed') btn.classList.add('text-fuchsia-600', 'border-fuchsia-500');
                    if (tabId === 'standar') btn.classList.add('text-amber-600', 'border-amber-500');
                    if (tabId === 'pemula') btn.classList.add('text-emerald-600', 'border-emerald-500');
                }
            </script>

                <?php endif; ?>
    </div>
</div>


<?php
// Group classes by day
$scheduleByDay = [];
foreach ($classes as $c) {
    if (empty($c['race_number'])) continue;
    $dayDigit = (int)substr($c['race_number'], 0, 1);
    if ($dayDigit === 0) $dayDigit = 1; // Fallback
    
    if (!isset($scheduleByDay[$dayDigit])) {
        $scheduleByDay[$dayDigit] = [];
    }
    $scheduleByDay[$dayDigit][] = $c;
}
ksort($scheduleByDay);

?>

<?php if (!empty($scheduleByDay)): ?>
<div class="mt-8 bg-white rounded-2xl border border-slate-200/50 shadow-xl overflow-hidden">
    <div class="p-6 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">⚙️ Pengaturan Durasi & Generate Jadwal</h3>
    </div>
    <div class="p-6">
        <form id="formGenTime" onsubmit="generateScheduleTime(event)" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($scheduleByDay as $day => $dayClasses): 
                    $dateStr = '';
                    if (!empty($row['event_date_start'])) {
                        try {
                            $dt = new DateTime($row['event_date_start']);
                            if ($day > 1) {
                                $dt->modify("+" . ($day - 1) . " days");
                            }
                            $dateStr = $dt->format('d M Y');
                        } catch(Exception $e) {}
                    }
                ?>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 border-b border-slate-200 pb-2">Hari Ke-<?= $day ?> <?= $dateStr ? '('.$dateStr.')' : '' ?></label>
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold mb-1">Waktu Mulai Acara</p>
                        <input type="time" name="start_times[<?= $day ?>]" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" value="07:30" required>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 border-dashed">
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold mb-1">Mulai Istirahat</p>
                            <input type="time" name="break_start_times[<?= $day ?>]" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" value="11:30">
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold mb-1">Selesai Istirahat</p>
                            <input type="time" name="break_end_times[<?= $day ?>]" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" value="13:00">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-bold text-slate-700">Total Durasi Pemula (Jam):</label>
                    <input type="number" step="0.1" min="0" name="pemula_duration" placeholder="Otomatis" class="rounded-lg border-slate-300 w-28 text-sm focus:border-indigo-500 focus:ring-indigo-500" value="">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all uppercase tracking-widest text-sm flex items-center gap-2">
                    ⚡ Generate Waktu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeInputs = document.querySelectorAll('#formGenTime input[type="time"], #formGenTime input[type="number"]');
    timeInputs.forEach(input => {
        const saved = localStorage.getItem('roll_gen_time_' + input.name);
        if (saved) {
            input.value = saved;
        }
        input.addEventListener('change', function() {
            localStorage.setItem('roll_gen_time_' + input.name, this.value);
        });
    });
});

async function generateScheduleTime(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.innerHTML = 'Memproses...';
    btn.disabled = true;
    
    try {
        const fd = new FormData(e.target);
        const res = await fetch(`<?= getenv('APP_URL') ?>/roll/admin/events/generate_schedule_time`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        
        if (data.success) {
            alert('Sukses: ' + data.message);
            window.location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch(err) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.innerHTML = '⚡ Generate Waktu';
        btn.disabled = false;
    }
}
</script>

<div class="mt-8 bg-white rounded-2xl border border-slate-200/50 shadow-xl overflow-hidden">
    <div class="p-6 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-800 uppercase tracking-widest">Jadwal Lomba Tergenerate</h3>
    </div>
    
    <div class="p-6">
        <?php foreach ($scheduleByDay as $day => $dayClasses): 
            $dateStr = '';
            if (!empty($row['event_date_start'])) {
                try {
                    $dt = new DateTime($row['event_date_start']);
                    if ($day > 1) {
                        $dt->modify("+" . ($day - 1) . " days");
                    }
                    $dateStr = $dt->format('d M Y');
                } catch(Exception $e) {}
            }
        ?>
        <div class="mb-8">
            <h4 class="text-md font-bold text-blue-600 uppercase tracking-widest border-b-2 border-blue-100 pb-2 mb-4">
                Hari Ke-<?= $day ?> <?= $dateStr ? '<span class="text-slate-400 font-normal">('.$dateStr.')</span>' : '' ?>
            </h4>
            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-black tracking-wider text-slate-400">
                        <tr>
                            <th class="p-3">Waktu (Pukul)</th>
                            <th class="p-3 text-center">Heat</th>
                            <th class="p-3">No. Lomba</th>
                            <th class="p-3">Jarak</th>
                            <th class="p-3">Kelompok Umur</th>
                            <th class="p-3">Kategori</th>
                            <th class="p-3">Gender</th>
                            <th class="p-3 text-center">Peserta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        usort($dayClasses, function($a, $b) {
                            $cmp = strnatcmp($a['race_number'], $b['race_number']);
                            if ($cmp === 0) {
                                return strcmp($a['gender'] ?? '', $b['gender'] ?? '');
                            }
                            return $cmp;
                        });

                        $pemulaGroup = [];
                        $renderPemulaGroup = function($group) {
                            if (empty($group)) return;
                            $time = $group[0]['race_time'] ?? '-';
                            $heats = 0; $athletes = 0;
                            $races = []; $dists = []; $kus = []; $genders = [];
                            foreach ($group as $g) {
                                $heats += (int)($g['total_heats'] ?? 0);
                                $athletes += (int)($g['total_athletes'] ?? 0);
                                $races[] = $g['race_number'];
                                $dist = $g['distance_name'] ?? $g['distance'];
                                if ($dist) $dists[] = $dist;
                                if ($g['group_name']) $kus[] = $g['group_name'];
                                $gn = $g['gender'] === 'Putra' ? '🔵 Putra' : ($g['gender'] === 'Putri' ? '🔴 Putri' : $g['gender']);
                                if ($gn) $genders[] = $gn;
                            }
                            $racesStr = implode(' & ', array_unique($races));
                            $distStr = implode(' & ', array_unique($dists));
                            $kuStr = implode(', ', array_unique($kus));
                            $genderStr = implode(' & ', array_unique($genders));
                            ?>
                            <tr class="hover:bg-slate-50 bg-blue-50/30">
                                <td class="p-3 font-black text-indigo-600"><?= htmlspecialchars($time) ?></td>
                                <td class="p-3 text-center font-bold text-slate-700">
                                    <span class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-xs"><?= $heats ?></span>
                                </td>
                                <td class="p-3 font-bold text-slate-800"><?= htmlspecialchars($racesStr) ?></td>
                                <td class="p-3 text-blue-600 font-bold uppercase"><?= htmlspecialchars($distStr) ?></td>
                                <td class="p-3 font-bold text-slate-700 uppercase"><?= htmlspecialchars($kuStr) ?></td>
                                <td class="p-3 text-xs uppercase font-bold tracking-widest text-slate-500">PEMULA</td>
                                <td class="p-3 text-xs font-bold"><?= $genderStr ?></td>
                                <td class="p-3 text-center font-bold text-slate-700">
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded text-xs"><?= $athletes ?></span>
                                </td>
                            </tr>
                            <?php
                        };

                        foreach ($dayClasses as $c) {
                            $rName = strtolower($c['roller_name'] ?? '');
                            $groupName = strtolower($c['group_name'] ?? '');
                            $isPemula = (strpos($rName, 'pemula') !== false || strpos($groupName, 'pemula') !== false);

                            if ($isPemula) {
                                $pemulaGroup[] = $c;
                            } else {
                                if (!empty($pemulaGroup)) {
                                    $renderPemulaGroup($pemulaGroup);
                                    $pemulaGroup = [];
                                }
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-3 font-black text-indigo-600"><?= htmlspecialchars($c['race_time'] ?? '-') ?></td>
                                    <td class="p-3 text-center font-bold text-slate-700">
                                        <span class="bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-xs"><?= (int)($c['total_heats'] ?? 0) ?></span>
                                    </td>
                                    <td class="p-3 font-bold text-slate-800"><?= htmlspecialchars($c['race_number']) ?></td>
                                    <td class="p-3 text-blue-600 font-bold uppercase"><?= htmlspecialchars($c['distance_name'] ?? $c['distance']) ?></td>
                                    <td class="p-3 font-bold text-slate-700 uppercase"><?= htmlspecialchars($c['group_name'] ?? '-') ?></td>
                                    <td class="p-3 text-xs uppercase font-bold tracking-widest text-slate-500"><?= htmlspecialchars($c['roller_name'] ?? '-') ?></td>
                                    <td class="p-3 text-xs font-bold">
                                        <?php if ($c['gender'] === 'Putra'): ?>
                                            <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded">🔵 Putra</span>
                                        <?php elseif ($c['gender'] === 'Putri'): ?>
                                            <span class="text-pink-600 bg-pink-50 px-2 py-1 rounded">🔴 Putri</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($c['gender']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-center font-bold text-slate-700">
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded text-xs"><?= (int)($c['total_athletes'] ?? 0) ?></span>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        if (!empty($pemulaGroup)) {
                            $renderPemulaGroup($pemulaGroup);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

