<?php
// FILE: src/admin/events.php
require_once __DIR__ . '/../config/database.php';

// --- LOGIKA INSERT/UPDATE/DELETE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = trim($_POST['event_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $format = $_POST['race_format'] ?? 'DTT';
        $status = $_POST['status'] ?? 'Draft';
        $date_start = !empty($_POST['event_date_start']) ? $_POST['event_date_start'] : null;
        $date_end = !empty($_POST['event_date_end']) ? $_POST['event_date_end'] : null;

        // Proses Upload Poster
        $posterPath = null;
        if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == 0) {
            $file = $_FILES['poster_image'];
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);
            
            if (in_array($ext, $allowedExt) && strpos($mime, 'image/') === 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/posters/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $newFilename = 'poster_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                    $posterPath = 'uploads/posters/' . $newFilename;
                }
            }
        }

        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO roll_events (event_name, location, race_format, status, event_date_start, event_date_end, poster_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $loc, $format, $status, $date_start, $date_end, $posterPath]);
                $_SESSION['flash_message'] = '✅ Kejuaraan berhasil ditambahkan!';
                $_SESSION['flash_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "❌ Error: " . $e->getMessage();
                $_SESSION['flash_type'] = 'error';
            }
        } else {
            $_SESSION['flash_message'] = "❌ Error: Nama Kejuaraan tidak boleh kosong!";
            $_SESSION['flash_type'] = 'error';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['event_name'] ?? '');
        $loc = trim($_POST['location'] ?? '');
        $format = $_POST['race_format'] ?? 'DTT';
        $status = $_POST['status'] ?? 'Draft';
        $date_start = !empty($_POST['event_date_start']) ? $_POST['event_date_start'] : null;
        $date_end = !empty($_POST['event_date_end']) ? $_POST['event_date_end'] : null;

        // Proses Upload Poster (Hanya jika diunggah baru)
        $posterPath = null;
        if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == 0) {
            $file = $_FILES['poster_image'];
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);
            
            if (in_array($ext, $allowedExt) && strpos($mime, 'image/') === 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/posters/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $newFilename = 'poster_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                    $posterPath = 'uploads/posters/' . $newFilename;
                    
                    // Hapus poster lama
                    $stmtOld = $pdo->prepare("SELECT poster_image FROM roll_events WHERE id = ?");
                    $stmtOld->execute([$id]);
                    $oldPoster = $stmtOld->fetchColumn();
                    if ($oldPoster && file_exists(__DIR__ . '/../../public/' . $oldPoster)) {
                        unlink(__DIR__ . '/../../public/' . $oldPoster);
                    }
                }
            }
        }

        if (!empty($name)) {
            try {
                if ($posterPath) {
                    $stmt = $pdo->prepare("UPDATE roll_events SET event_name=?, location=?, race_format=?, status=?, event_date_start=?, event_date_end=?, poster_image=? WHERE id=?");
                    $stmt->execute([$name, $loc, $format, $status, $date_start, $date_end, $posterPath, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE roll_events SET event_name=?, location=?, race_format=?, status=?, event_date_start=?, event_date_end=? WHERE id=?");
                    $stmt->execute([$name, $loc, $format, $status, $date_start, $date_end, $id]);
                }
                $_SESSION['flash_message'] = '✅ Kejuaraan berhasil diperbarui!';
                $_SESSION['flash_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "❌ Error: " . $e->getMessage();
                $_SESSION['flash_type'] = 'error';
            }
        }
    }
}

// AMBIL DATA EVENT
$stmt = $pdo->query("SELECT * FROM roll_events ORDER BY id DESC");
$events = $stmt->fetchAll();

// INCLUDE LAYOUT
include __DIR__ . '/../../views/layout/topbar.php';
include __DIR__ . '/../../views/layout/sidebar.php';
?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Kejuaraan</h2>
                <p class="text-slate-500 mt-1 font-medium">Kelola event sepatu roda dan format perlombaan.</p>
            </div>
            <button type="button" onclick="showAddModal()" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">
                + Tambah Event Baru
            </button>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-200">
                        <th class="px-6 py-4 font-bold">Nama Kejuaraan</th>
                        <th class="px-6 py-4 font-bold">Lokasi</th>
                        <th class="px-6 py-4 font-bold text-center">Format Lomba</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(empty($events)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400 font-medium">Belum ada data kejuaraan.</td></tr>
                    <?php endif; ?>
                    <?php foreach($events as $e): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($e['event_name']) ?></td>
                        <td class="px-6 py-4 text-slate-600 text-sm"><?= htmlspecialchars($e['location'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1 rounded-lg text-xs font-bold tracking-wider">
                                <?= htmlspecialchars($e['race_format']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php 
                            $bg = 'bg-slate-100 text-slate-600';
                            if($e['status'] == 'Published') $bg = 'bg-green-100 text-green-700';
                            if($e['status'] == 'Completed') $bg = 'bg-blue-100 text-blue-700';
                            ?>
                            <span class="<?= $bg ?> px-3 py-1 rounded-full text-xs font-bold tracking-wide">
                                <?= htmlspecialchars($e['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick='editEvent(<?= htmlspecialchars(json_encode($e), ENT_QUOTES, 'UTF-8') ?>)' class="text-orange-500 hover:text-orange-700 font-bold text-sm transition">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Tambah/Edit Event -->
    <div id="modalAdd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden">
        <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all">
            <div class="bg-slate-900 p-6 flex justify-between items-center border-b border-slate-800">
                <h3 id="modalTitle" class="text-xl font-black text-white">Tambah Kejuaraan Baru</h3>
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
            </div>
            <form id="eventForm" action="" method="POST" enctype="multipart/form-data" class="p-8">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="eventId" value="">
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kejuaraan</label>
                    <input type="text" name="event_name" id="eventName" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Contoh: Piala Gubernur 2026">
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi</label>
                    <input type="text" name="location" id="eventLocation" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition" placeholder="Contoh: Velodrome JIS">
                </div>
                
                <div class="grid grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="event_date_start" id="eventDateStart" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="event_date_end" id="eventDateEnd" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload Poster Lomba</label>
                    <input type="file" name="poster_image" accept=".jpg,.jpeg,.png,.webp" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, WEBP (Max 2MB). Biarkan kosong jika tidak ingin mengubah.</p>
                </div>
                
                <div class="grid grid-cols-2 gap-5 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Format Lomba</label>
                        <select name="race_format" id="eventFormat" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="DTT">DTT (Dual Time Trial)</option>
                            <option value="SPRINT">SPRINT</option>
                            <option value="PTP">PTP (Point to Point)</option>
                            <option value="ELIMINATION">ELIMINATION</option>
                            <option value="TIME_TRIAL">TIME TRIAL (Standar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Status Awal</label>
                        <select name="status" id="eventStatus" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                            <option value="Draft">Draft</option>
                            <option value="Published">Published</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-6 py-2.5 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-orange-500/30 transition-all transform hover:-translate-y-0.5">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

<script>
function editEvent(data) {
    document.getElementById('modalTitle').innerText = 'Edit Kejuaraan';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('eventId').value = data.id;
    document.getElementById('eventName').value = data.event_name;
    document.getElementById('eventLocation').value = data.location;
    document.getElementById('eventDateStart').value = data.event_date_start || '';
    document.getElementById('eventDateEnd').value = data.event_date_end || '';
    document.getElementById('eventFormat').value = data.race_format;
    document.getElementById('eventStatus').value = data.status;
    
    document.getElementById('modalAdd').classList.remove('hidden');
}

function showAddModal() {
    document.getElementById('modalTitle').innerText = 'Tambah Kejuaraan Baru';
    document.getElementById('eventForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('eventId').value = '';
    
    document.getElementById('modalAdd').classList.remove('hidden');
}
</script>

<?php include __DIR__ . '/../../views/layout/footer.php'; ?>
