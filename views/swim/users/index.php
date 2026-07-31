<div class="font-sans">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <nav class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">
                <a href="<?= getenv('APP_URL') ?>/swim/dashboard/master" class="hover:text-blue-600">← Control Center</a>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic leading-none">
                Manajemen <?= $targetRole == 'admin' ? 'Event Organizer' : 'User Klub' ?>
            </h1>
            <p class="text-slate-500 text-xs font-medium mt-2">Total Data: <?= count($users) ?></p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <form method="GET" class="relative">
                <input type="hidden" name="role" value="<?= $targetRole ?>">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari..." 
                       class="pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold w-full md:w-64 focus:outline-none focus:border-blue-500 shadow-sm">
                <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
            </form>

            <div class="flex gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200">
                <a href="?role=user" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition <?= $targetRole == 'user' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' ?>">Klub</a>
                <a href="?role=admin" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition <?= $targetRole == 'admin' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-50' ?>">EO / Event</a>
            </div>
        </div>
    </div>

    <div class="flex justify-end mb-6">
        <button onclick="openModal()" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-[0.1em] shadow-xl hover:bg-blue-600 transition flex items-center gap-2 hover:-translate-y-1 transform duration-200">
            <span>+</span> Tambah <?= strtoupper($targetRole) ?>
        </button>
    </div>

    <div class="bg-white rounded-[2rem] shadow-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest w-1/4">User Account</th>
                        <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest">Kontak</th>
                        <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest w-1/3">
                            <?= $targetRole == 'admin' ? 'Detail Event' : 'Detail Klub' ?>
                        </th>
                        <?php if($targetRole == 'user'): ?>
                            <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest text-center">Atlet</th>
                        <?php endif; ?>
                        <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 font-black uppercase text-[9px] text-slate-400 tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($users)): ?>
                        <tr><td colspan="6" class="px-8 py-20 text-center text-slate-300 font-bold italic uppercase text-xs">Belum ada data.</td></tr>
                    <?php else: foreach($users as $u): ?>
                    <tr class="hover:bg-blue-50/30 transition group">
                        
                        <td class="px-6 py-5 align-top">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-black text-xs border border-slate-200 shadow-sm shrink-0">
                                    <?= strtoupper(substr($u['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 uppercase italic leading-tight text-xs"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                                    <div class="text-[10px] font-mono text-slate-400 mt-0.5">@<?= htmlspecialchars($u['username']) ?></div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="flex flex-col gap-1.5">
                                <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-[10px] font-bold text-blue-500 hover:underline flex items-center gap-1">
                                    📧 <?= htmlspecialchars($u['email']) ?>
                                </a>
                                <?php if(!empty($u['phone'])): 
                                    $waNum = preg_replace('/[^0-9]/', '', $u['phone']);
                                    if(substr($waNum, 0, 1) == '0') $waNum = '62' . substr($waNum, 1);
                                ?>
                                    <a href="https://wa.me/<?= $waNum ?>" target="_blank" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded w-fit hover:bg-emerald-100 transition flex items-center gap-1">
                                        📱 WhatsApp
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="px-6 py-5 align-top">
                            <?php if($targetRole == 'admin'): ?>
                                <div class="space-y-1">
                                    <div class="text-xs font-black text-slate-700 uppercase">
                                        <?= htmlspecialchars($u['event_name'] ?? '-') ?>
                                    </div>
                                    <div class="flex flex-wrap gap-1 items-center">
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[9px] font-bold text-slate-500 border border-slate-200">
                                            📍 <?= htmlspecialchars($u['event_location'] ?? '-') ?> <?= !empty($u['event_city']) ? ' - ' . htmlspecialchars($u['event_city']) : '' ?>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-medium">
                                            📅 <?= !empty($u['event_date_start']) ? date('d M Y', strtotime($u['event_date_start'])) : '-' ?>
                                        </span>
                                    </div>
                                    <span class="text-[9px] italic text-slate-400"><?= htmlspecialchars($u['competition_system'] ?? '-') ?></span>
                                </div>
                            <?php else: ?>
                                <div class="space-y-1">
                                    <div class="text-xs font-black text-slate-700 uppercase">
                                        <?= htmlspecialchars($u['nama_klub'] ?? '-') ?>
                                    </div>
                                    <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[9px] font-bold text-slate-500 border border-slate-200 inline-block">
                                        🏠 <?= htmlspecialchars($u['kota'] ?? '-') ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <?php if($targetRole == 'user'): ?>
                            <td class="px-6 py-5 align-top text-center">
                                <a href="<?= getenv('APP_URL') ?>/swim/swimmers/index?search=<?= urlencode($u['nama_klub']) ?>" class="inline-block bg-slate-50 border border-slate-200 rounded-lg px-3 py-1 hover:bg-blue-50 hover:border-blue-200 hover:scale-105 transition cursor-pointer group/card">
                                    <span class="block text-lg font-black text-blue-600 leading-none group-hover/card:text-blue-700"><?= $u['total_atlet'] ?></span>
                                    <span class="text-[8px] uppercase font-bold text-slate-400">Atlet &rarr;</span>
                                </a>
                            </td>
                        <?php endif; ?>

                        <td class="px-6 py-5 align-top text-center">
                            <?php 
                                $status = $u['account_status'] ?? 'pending';
                                $statusClass = match($status) {
                                    'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'suspended' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-slate-100 text-slate-500'
                                };
                            ?>
                            <div class="flex flex-col items-center gap-2">
                                <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-wider border <?= $statusClass ?>">
                                    <?= $status ?>
                                </span>
                                <div class="flex gap-1 opacity-100 lg:opacity-30 lg:group-hover:opacity-100 transition">
                                    <?php if($status != 'active'): ?>
                                        <a href="<?= getenv('APP_URL') ?>/swim/master/users/status?uid=<?= $u['id'] ?>&status=active&role=<?= $targetRole ?>" 
                                           class="w-5 h-5 rounded bg-emerald-500 text-white flex items-center justify-center hover:bg-emerald-600 shadow-sm text-[10px]" title="Aktifkan">✓</a>
                                    <?php endif; ?>
                                    <?php if($status != 'suspended'): ?>
                                        <a href="<?= getenv('APP_URL') ?>/swim/master/users/status?uid=<?= $u['id'] ?>&status=suspended&role=<?= $targetRole ?>" 
                                           class="w-5 h-5 rounded bg-red-500 text-white flex items-center justify-center hover:bg-red-600 shadow-sm text-[10px]" title="Blokir" onclick="return confirm('Blokir user ini?')">✕</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top text-right">
                            <div class="flex justify-end gap-2">
                                <?php if($status === 'pending'): ?>
                                    <a href="verify_user.php?id=<?= $u['id'] ?>" class="flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition shadow-sm">
                                        Verifikasi Akun
                                    </a>
                                <?php else: ?>
                                    <button 
                                        type="button"
                                        data-user="<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="editAdmin(this)"
                                        class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg hover:border-blue-500 hover:text-blue-600 transition text-slate-400 shadow-sm">
                                        ✏️
                                    </button>
                                <?php endif; ?>
                                <a href="<?= getenv('APP_URL') ?>/swim/master/users/delete?id=<?= $u['id'] ?>&role=<?= $targetRole ?>" onclick="return confirm('Hapus permanen? Data event/klub (termasuk data atlet mereka) akan hilang permanen.')" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg hover:border-red-500 hover:text-red-600 transition text-slate-400 shadow-sm">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-admin" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="bg-slate-900 p-6 text-white flex justify-between items-center sticky top-0 z-10">
            <div><h3 id="modal-title" class="font-black uppercase tracking-widest italic text-lg leading-none">Tambah Akun</h3></div>
            <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-red-500 transition">✕</button>
        </div>
        <form method="POST" class="p-8 space-y-6">
            <input type="hidden" name="save_user" value="1">
            <input type="hidden" name="user_id" id="form-id">
            <input type="hidden" name="role_type" value="<?= $targetRole ?>">
            
            <div class="space-y-3">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-1">Info Login (Akun)</h4>
                
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Username Login</label>
                    <input type="text" name="username" id="form-username" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required placeholder="Username" autocomplete="off" oninput="this.value = this.value.toLowerCase().replace(/\s+/g, '')">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Email</label>
                        <input type="email" name="email" id="form-email" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required autocomplete="off" oninput="this.value = this.value.toLowerCase()">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">No. WhatsApp</label>
                        <input type="text" name="phone" id="form-phone" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" placeholder="08...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Password</label>
                        <input type="password" name="password" id="form-pass" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" placeholder="Kosongi jika edit user" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Nama Pemilik Akun</label>
                        <input type="text" name="nama_lengkap" id="form-nama" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required placeholder="Nama Admin / Ketua Klub">
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-1">
                    <?= $targetRole == 'admin' ? 'Detail Event (Tabel Events)' : 'Detail Klub (Tabel Clubs)' ?>
                </h4>
                
                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase">
                        <?= $targetRole == 'admin' ? 'Nama Event (Kejuaraan)' : 'Nama Klub Renang' ?>
                    </label>
                    <input type="text" name="nama_detail" id="form-nama-detail" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" required placeholder="<?= $targetRole == 'admin' ? 'Contoh: O2SN 2026' : 'Contoh: Pari Sakti SC' ?>">
                </div>

                <?php if($targetRole == 'admin'): ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Sistem Lomba</label>
                            <select name="competition_system" id="form-mode" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-xs font-bold uppercase outline-none">
                                <option value="Langsung Final">Langsung Final</option>
                                <option value="Babak Penyisihan">Babak Penyisihan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Tanggal Mulai</label>
                            <input type="date" name="event_date_start" id="form-date" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Lokasi (Nama Kolam)</label>
                            <input type="text" name="event_location" id="form-location" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Kab/Kota</label>
                            <input type="text" name="event_city" id="form-city" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none">
                        </div>
                    </div>
                <?php else: ?>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Kota Asal Klub</label>
                        <input type="text" name="kota" id="form-kota" class="w-full px-4 py-3 border border-slate-200 bg-slate-50 rounded-xl text-sm font-bold focus:bg-white focus:border-blue-500 outline-none" placeholder="Cth: Yogyakarta (Boleh Kosong)">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full bg-slate-900 hover:bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg transition uppercase tracking-widest text-xs mt-4">
                Simpan Data
            </button>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modal-admin').classList.remove('hidden');
        document.getElementById('modal-title').innerText = 'Tambah <?= strtoupper($targetRole) ?>';
        document.getElementById('form-id').value = ""; 
        
        document.getElementById('form-username').value = "";
        document.getElementById('form-nama').value = "";
        document.getElementById('form-email').value = "";
        document.getElementById('form-phone').value = "";
        document.getElementById('form-pass').required = true; 
        document.getElementById('form-pass').value = ""; 
        
        document.getElementById('form-nama-detail').value = "";
        <?php if($targetRole == 'admin'): ?>
            document.getElementById('form-mode').value = "Langsung Final";
            document.getElementById('form-location').value = "";
            document.getElementById('form-city').value = "";
            document.getElementById('form-date').value = "";
        <?php else: ?>
            document.getElementById('form-kota').value = "";
        <?php endif; ?>
    }

    function editAdmin(buttonElement) {
        const data = JSON.parse(buttonElement.getAttribute('data-user'));
        editUser(data);
    }

    function editUser(data) {
        document.getElementById('modal-admin').classList.remove('hidden');
        document.getElementById('modal-title').innerText = 'Edit <?= strtoupper($targetRole) ?>';
        
        document.getElementById('form-id').value = data.id; 
        document.getElementById('form-username').value = data.username || '';
        document.getElementById('form-nama').value = data.nama_lengkap; 
        document.getElementById('form-email').value = data.email || '';
        document.getElementById('form-phone').value = data.phone || '';
        document.getElementById('form-pass').required = false; 
        document.getElementById('form-pass').value = ""; 

        const detailName = data.nama_klub || data.event_name || data.nama_lengkap;
        document.getElementById('form-nama-detail').value = detailName;

        if(document.getElementById('form-mode')) {
            document.getElementById('form-mode').value = data.competition_system || 'Langsung Final';
        }
        if(document.getElementById('form-location')) {
            document.getElementById('form-location').value = data.event_location || '';
        }
        if(document.getElementById('form-city')) {
            document.getElementById('form-city').value = data.event_city || '';
        }
        if(document.getElementById('form-date')) {
            document.getElementById('form-date').value = data.event_date_start || ''; 
        }
        
        if(document.getElementById('form-kota')) {
            document.getElementById('form-kota').value = data.kota || '';
        }

        modal.classList.remove('hidden');
    } catch (e) {
        console.error("Gagal parse data user:", e);
        alert("Terjadi kesalahan saat mengambil data. Cek console.");
    }
}

function closeModal() { 
    modal.classList.add('hidden'); 
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(isset($_SESSION['swal_type'])): ?>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false, 
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: '<?= $_SESSION['swal_type'] ?>',
            title: '<?= $_SESSION['swal_msg'] ?>'
        });

        <?php unset($_SESSION['swal_type']); unset($_SESSION['swal_msg']); ?>
    <?php endif; ?>
</script>