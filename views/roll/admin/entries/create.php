<div class="max-w-5xl mx-auto font-sans mb-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= getenv('APP_URL') ?>/roll/admin/entries" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 hover:text-slate-800 hover:bg-slate-50 transition shadow-sm border border-slate-200">
            ←
        </a>
        <div>
            <h1 class="text-3xl font-black uppercase italic text-slate-900 leading-none">Pendaftaran Manual</h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-2">Bypass Admin Mode</p>
        </div>
    </div>

    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="mb-6 p-4 rounded-xl text-sm font-bold shadow-sm flex justify-between items-center <?= $_SESSION['flash_type'] == 'error' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' ?>">
            <div><?= $_SESSION['flash_message'] ?></div>
            <button onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100">&times;</button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- TABS -->
    <div class="flex gap-2 mb-6">
        <button onclick="switchTab('individu')" id="tab_btn_individu" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-xs shadow-lg shadow-blue-200 hover:bg-blue-700 transition uppercase tracking-widest">
            + DAFTAR INDIVIDU
        </button>
        <button onclick="switchTab('team')" id="tab_btn_team" class="px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest">
            + DAFTAR TIM / RELAY
        </button>
    </div>

    <!-- FORM INDIVIDU -->
    <div id="form_individu" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8">
        <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_add" method="POST">
            <input type="hidden" name="entry_type" value="individu">
            <input type="hidden" name="event_id" value="<?= $targetEventId ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Kiri: Pilih Klub & Atlet -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Data Atlet</h3>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Klub <span class="text-red-500">*</span></label>
                        <select name="club_id" id="indv_club_select" onchange="loadAthletes(this.value, 'indv_skater_select')" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">-- Pilih Klub --</option>
                            <?php foreach($clubs as $club): ?>
                                <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['club_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Atlet <span class="text-red-500">*</span></label>
                        <select name="skater_id" id="indv_skater_select" onchange="onSkaterSelect(this)" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">-- Pilih Atlet --</option>
                        </select>
                        <div id="indv_athlete_info" class="mt-2 text-[10px] text-blue-600 font-bold hidden bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                            Lahir: <span id="indv_skater_dob"></span> | Umur: <span id="indv_skater_age"></span> Thn | Gender: <span id="indv_skater_gender"></span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Pilih Nomor Lomba -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Nomor Lomba</h3>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Kategori <span class="text-red-500">*</span></label>
                        <select id="indv_cat_select" onchange="filterIndvClasses()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">-- Pilih Kategori --</option>
                            <?php 
                            $skateCats = [];
                            foreach($classes as $c) {
                                if(!isset($skateCats[$c['class_cat_id']])) {
                                    $skateCats[$c['class_cat_id']] = $c['class_name'];
                                }
                            }
                            foreach($skateCats as $catId => $catName): ?>
                                <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Nomor Lomba <span class="text-red-500">*</span></label>
                        <div id="indv_class_container" class="space-y-2 bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 max-h-48 overflow-y-auto">
                            <div class="text-xs text-slate-400 italic text-center p-2">- Pilih Kategori Terlebih Dahulu -</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" id="btn_submit_indv" disabled class="px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed">
                    Simpan Individu
                </button>
            </div>
        </form>
    </div>

    <!-- FORM TIM / RELAY -->
    <div id="form_team" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8 hidden">
        <form action="<?= getenv('APP_URL') ?>/roll/admin/entries/manual_add" method="POST">
            <input type="hidden" name="entry_type" value="team">
            <input type="hidden" name="event_id" value="<?= $targetEventId ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <!-- Kiri: Info Tim & Kelas Lomba -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Identitas Tim</h3>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Tim <span class="text-red-500">*</span></label>
                        <input type="text" name="team_name" required placeholder="- Masukkan Nama Tim -" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select id="team_cat_select" onchange="filterTeamKU()" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">- Kategori -</option>
                                <?php foreach($skateCats as $catId => $catName): ?>
                                    <option value="<?= $catId ?>"><?= htmlspecialchars($catName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Kelompok Umur <span class="text-red-500">*</span></label>
                            <select id="team_ku_select" onchange="filterTeamClasses()" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="">- Pilih KU -</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nomor Lomba Relay <span class="text-red-500">*</span></label>
                        <select name="race_class_id[]" id="team_class_select" onchange="updateTeamGenderRule()" required disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                            <option value="">- Pilih Nomor Lomba -</option>
                        </select>
                        <div id="team_class_info" class="mt-2 text-[10px] text-indigo-600 font-bold hidden bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-100">
                            Gender yang diizinkan: <span id="team_rule_gender"></span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Anggota Tim (Mix-Club Support) -->
                <div class="space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-800 border-b border-slate-100 pb-2">Anggota Tim (Mendukung Mix-Club)</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Pilih klub asal atlet, lalu pilih atletnya.</p>
                    
                    <div id="team_members_container" class="space-y-4">
                        <?php for($i=1; $i<=4; $i++): ?>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 relative team-slot">
                            <span class="absolute -left-2 -top-2 w-5 h-5 bg-indigo-600 text-white rounded-full flex items-center justify-center text-[10px] font-black"><?= $i ?></span>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <select id="team_club_select_<?= $i ?>" onchange="loadAthletes(this.value, 'team_skater_select_<?= $i ?>')" class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                        <option value="">- Pilih Klub -</option>
                                        <?php foreach($clubs as $club): ?>
                                            <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['club_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <select name="skater_id[]" id="team_skater_select_<?= $i ?>" onchange="validateTeamMembers()" disabled <?= $i <= 2 ? 'required' : '' ?> class="team-skater-select w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition disabled:opacity-50">
                                        <option value="">- Pilih Atlet -</option>
                                    </select>
                                </div>
                            </div>
                            <?php if($i > 2): ?>
                                <div id="team_req_label_<?= $i ?>" class="text-[9px] text-slate-400 mt-1 italic text-right">Opsional</div>
                            <?php else: ?>
                                <div id="team_req_label_<?= $i ?>" class="text-[9px] text-red-400 mt-1 italic text-right">Wajib Diisi</div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" id="btn_submit_team" disabled class="px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed">
                    Simpan Tim / Relay
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const eventYear = parseInt('<?= date('Y', strtotime($event['event_date_start'])) ?>');
const allClasses = <?= json_encode($classes) ?>;
const ageGroups = <?php
    $agList = [];
    foreach($classes as $c) {
        if(!isset($agList[$c['age_group_id']])) {
            $agList[$c['age_group_id']] = [
                'id' => $c['age_group_id'], 
                'name' => $c['group_name'],
                'min' => $c['min_year'] ?? 0,
                'max' => $c['max_year'] ?? 99
            ];
        }
    }
    echo json_encode(array_values($agList));
?>;

let athletesCache = {}; // { club_id: [ athletes array ] }

function switchTab(tab) {
    if (tab === 'individu') {
        document.getElementById('form_individu').classList.remove('hidden');
        document.getElementById('form_team').classList.add('hidden');
        
        document.getElementById('tab_btn_individu').className = 'px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-xs shadow-lg shadow-blue-200 hover:bg-blue-700 transition uppercase tracking-widest';
        document.getElementById('tab_btn_team').className = 'px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest';
    } else {
        document.getElementById('form_individu').classList.add('hidden');
        document.getElementById('form_team').classList.remove('hidden');
        
        document.getElementById('tab_btn_team').className = 'px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition uppercase tracking-widest';
        document.getElementById('tab_btn_individu').className = 'px-6 py-3 bg-white text-slate-500 border border-slate-200 rounded-xl font-black text-xs hover:bg-slate-50 transition uppercase tracking-widest';
    }
}

function loadAthletes(clubId, targetSelectId) {
    const select = document.getElementById(targetSelectId);
    select.innerHTML = '<option value="">- Memuat... -</option>';
    select.disabled = true;

    if (!clubId) {
        select.innerHTML = '<option value="">- Pilih Atlet -</option>';
        if (targetSelectId === 'indv_skater_select') onSkaterSelect(select);
        if (targetSelectId.startsWith('team_skater')) validateTeamMembers();
        return;
    }

    if (athletesCache[clubId]) {
        populateAthleteSelect(targetSelectId, athletesCache[clubId]);
        return;
    }

    fetch(`<?= getenv('APP_URL') ?>/roll/admin/entries/get_athletes_by_club?club_id=${clubId}`)
        .then(res => res.json())
        .then(data => {
            athletesCache[clubId] = data;
            populateAthleteSelect(targetSelectId, data);
        })
        .catch(err => {
            console.error('Fetch error:', err);
            select.innerHTML = '<option value="">- Gagal Memuat -</option>';
        });
}

function populateAthleteSelect(targetSelectId, data) {
    const select = document.getElementById(targetSelectId);
    select.innerHTML = '<option value="">- Pilih Atlet -</option>';
    
    // Jika mode Tim, filter umur berdasarkan KU yang terpilih (jika ada)
    let minAge = 0, maxAge = 99, targetGender = '';
    const isTeam = targetSelectId.startsWith('team_');
    if (isTeam) {
        const teamKuSelect = document.getElementById('team_ku_select');
        if (teamKuSelect.value) {
            const ag = ageGroups.find(a => a.id == teamKuSelect.value);
            if (ag) { minAge = ag.min; maxAge = ag.max; }
        }
        const teamClassSelect = document.getElementById('team_class_select');
        if (teamClassSelect.value) {
            const cls = allClasses.find(c => c.id == teamClassSelect.value);
            if (cls && cls.gender) targetGender = cls.gender.toLowerCase();
        }
    }

    data.forEach(a => {
        const dobYear = parseInt(a.birth_date.split('-')[0]);
        const age = eventYear - dobYear;
        const genderText = a.gender === 'M' ? 'Putra' : 'Putri';
        
        const opt = document.createElement('option');
        opt.value = a.id;
        opt.dataset.dob = a.birth_date;
        opt.dataset.age = age;
        opt.dataset.gender = a.gender;
        opt.textContent = `${a.skater_name} (${age} Thn, ${genderText})`;
        
        // Logika disable untuk Tim berdasarkan umur/gender
        if (isTeam) {
            // Kita gunakan validasi usia jika KU sudah dipilih (maxAge < 99)
            if (maxAge !== 99 && (age < minAge || age > maxAge)) {
                opt.disabled = true;
                opt.textContent += ' [Umur tidak sesuai]';
            } else if (targetGender === 'putra' && a.gender === 'F') {
                opt.disabled = true;
                opt.textContent += ' [Khusus Putra]';
            } else if (targetGender === 'putri' && a.gender === 'M') {
                opt.disabled = true;
                opt.textContent += ' [Khusus Putri]';
            }
        }

        select.appendChild(opt);
    });
    
    select.disabled = false;
    if (!isTeam) onSkaterSelect(select);
    else validateTeamMembers();
}

// --- LOGIKA INDIVIDU ---
let currentSkaterEntries = [];
let currentLockedCat = null;

function onSkaterSelect(sel) {
    const info = document.getElementById('indv_athlete_info');
    const container = document.getElementById('indv_class_container');
    const catSel = document.getElementById('indv_cat_select');
    
    currentSkaterEntries = [];
    currentLockedCat = null;
    catSel.disabled = false;
    catSel.value = "";
    container.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Lengkapi Kategori Terlebih Dahulu -</div>';
    checkIndvForm();

    if (sel.value) {
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('indv_skater_dob').innerText = opt.dataset.dob;
        document.getElementById('indv_skater_age').innerText = opt.dataset.age;
        document.getElementById('indv_skater_gender').innerText = opt.dataset.gender === 'M' ? 'Putra' : 'Putri';
        info.classList.remove('hidden');

        // Panggil history atlet
        fetch(`<?= getenv('APP_URL') ?>/roll/admin/entries/get_athlete_entries?skater_id=${sel.value}&event_id=${<?= $targetEventId ?>}`)
            .then(res => res.json())
            .then(data => {
                if (data.race_class_ids && data.race_class_ids.length > 0) {
                    currentSkaterEntries = data.race_class_ids;
                    currentLockedCat = data.locked_cat_id;
                    
                    if (currentLockedCat) {
                        catSel.value = currentLockedCat;
                        catSel.disabled = true; // Kunci kategori agar tidak bisa diubah jika sudah punya lomba
                        filterIndvClasses();
                    }
                }
            })
            .catch(err => console.error("Error fetching athlete history:", err));

    } else {
        info.classList.add('hidden');
    }
}

function filterIndvClasses() {
    const skaterSel = document.getElementById('indv_skater_select');
    const catSel = document.getElementById('indv_cat_select');
    const container = document.getElementById('indv_class_container');
    const btn = document.getElementById('btn_submit_indv');
    
    container.innerHTML = '';
    btn.disabled = true;
    btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';

    if (!skaterSel.value || !catSel.value) {
        container.innerHTML = '<div class="text-xs text-slate-400 italic text-center p-2">- Lengkapi Atlet & Kategori -</div>';
        return;
    }

    // Jika ada form submit, pastikan hidden input cat_id dikirim kalau selectnya disabled
    if (catSel.disabled) {
        let hiddenInput = document.getElementById('hidden_indv_cat_id');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'hidden_indv_cat_id';
            hiddenInput.name = 'indv_cat_id'; // Nama name bebas karena tak ditangkap backend
            document.getElementById('form_individu').querySelector('form').appendChild(hiddenInput);
        }
        hiddenInput.value = catSel.value;
    }

    const opt = skaterSel.options[skaterSel.selectedIndex];
    const age = parseInt(opt.dataset.age);
    const gender = opt.dataset.gender;
    const catId = catSel.value;
    let validCount = 0;

    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (dName.includes('relay') || dName.includes('team') || dName.includes('pair')) return; // Abaikan relay

        if (c.class_cat_id == catId) {
            const isChecked = currentSkaterEntries.some(id => String(id) === String(c.id));
            
            const minYear = c.min_year ? parseInt(c.min_year) : 0;
            const maxYear = c.max_year ? parseInt(c.max_year) : 99;
            let matchesAge = (age >= minYear && age <= maxYear);
            const catGender = (c.gender || '').toLowerCase();
            let matchesGender = ((catGender === 'putra' && gender === 'M') || (catGender === 'putri' && gender === 'F') || catGender === 'campuran');
            
            if ((matchesAge && matchesGender) || isChecked) {
                const isEks = (c.category_name === 'EKSEBISI');
                const eksLabel = isEks ? ' <span class="ml-2 bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-black text-[9px] uppercase tracking-widest border border-red-200">EKSEBISI</span>' : '';
                const labelText = (c.race_number ? c.race_number + ' - ' : '') + c.distance_name + ' (' + c.group_name + ')' + (!matchesAge || !matchesGender ? ' ⚠️ [Diluar Umur/Gender]' : '') + eksLabel;
                const bgClass = isChecked ? 'bg-blue-50 border-blue-200' : (isEks ? 'hover:bg-red-50 border-transparent border-l-4 border-l-red-500' : 'hover:bg-slate-100 border-transparent');
                
                const label = document.createElement('label');
                label.className = `flex items-center justify-between p-3 rounded-lg cursor-pointer transition border ${bgClass}`;
                    label.innerHTML = `
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="race_class_id[]" value="${c.id}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 indv-cb" onchange="checkIndvForm(this)">
                            <span class="text-xs font-bold text-slate-700 uppercase">${labelText}</span>
                        </div>
                        ${isChecked ? '<span class="text-[9px] font-black uppercase text-blue-600 tracking-widest bg-blue-100 px-2 py-1 rounded">TERDAFTAR</span>' : ''}
                    `;
                    container.appendChild(label);
                    validCount++;
            }
        }
    });

    if (validCount === 0) {
        container.innerHTML = '<div class="text-xs text-red-400 italic text-center p-2 font-bold">- Tidak ada kelas lomba untuk umur/gender atlet ini pada kategori terpilih -</div>';
    } else {
        // Cek awal tombol simpan
        checkIndvForm(null);
    }
}

function checkIndvForm(changedCheckbox = null) {
    if (changedCheckbox) {
        const lbl = changedCheckbox.closest('label');
        if (changedCheckbox.checked) {
            lbl.classList.add('bg-blue-50', 'border-blue-200');
            lbl.classList.remove('hover:bg-slate-100', 'border-transparent');
        } else {
            lbl.classList.remove('bg-blue-50', 'border-blue-200');
            lbl.classList.add('hover:bg-slate-100', 'border-transparent');
        }
    }

    const checked = document.querySelectorAll('.indv-cb:checked').length;
    const btn = document.getElementById('btn_submit_indv');
    
    // Walaupun checked = 0, kita izinkan save jika sebelumnya ada entry (artinya Hapus Semua)
    if (checked > 0 || currentSkaterEntries.length > 0) {
        btn.disabled = false;
        btn.className = 'px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-blue-200 transition cursor-pointer';
        if (checked === 0) {
            btn.innerText = "Simpan (Hapus Semua Lomba)";
            btn.className = 'px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-red-200 transition cursor-pointer';
        } else {
            btn.innerText = "Simpan Individu";
        }
    } else {
        btn.disabled = true;
        btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';
    }
}

// --- LOGIKA TIM / RELAY ---
function filterTeamKU() {
    const catId = document.getElementById('team_cat_select').value;
    const kuSelect = document.getElementById('team_ku_select');
    const classSelect = document.getElementById('team_class_select');
    
    kuSelect.innerHTML = '<option value="">- Pilih KU -</option>';
    classSelect.innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    kuSelect.disabled = true;
    classSelect.disabled = true;
    document.getElementById('team_class_info').classList.add('hidden');
    
    // Refresh club lists to apply constraints if needed (or reset them)
    document.querySelectorAll('.team-skater-select').forEach(sel => {
        sel.innerHTML = '<option value="">- Pilih Atlet -</option>';
    });

    if (!catId) return;

    let validKUs = new Set();
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            validKUs.add(c.age_group_id);
        }
    });

    if (validKUs.size > 0) {
        kuSelect.disabled = false;
        ageGroups.forEach(ag => {
            if (validKUs.has(ag.id)) {
                const opt = document.createElement('option');
                opt.value = ag.id;
                opt.textContent = ag.name + ` (${ag.min} - ${ag.max} Thn)`;
                kuSelect.appendChild(opt);
            }
        });
    } else {
        kuSelect.innerHTML = '<option value="">- Tidak ada Relay di Kategori ini -</option>';
    }
}

function filterTeamClasses() {
    const catId = document.getElementById('team_cat_select').value;
    const kuId = document.getElementById('team_ku_select').value;
    const classSelect = document.getElementById('team_class_select');
    
    classSelect.innerHTML = '<option value="">- Pilih Nomor Lomba -</option>';
    classSelect.disabled = true;
    document.getElementById('team_class_info').classList.add('hidden');
    
    // Re-populate skaters to apply age limits
    for(let i=1; i<=4; i++) {
        const clubId = document.getElementById('team_club_select_'+i).value;
        if(clubId) loadAthletes(clubId, 'team_skater_select_'+i);
    }

    if (!catId || !kuId) return;

    let hasClasses = false;
    allClasses.forEach(c => {
        const dName = (c.distance_name || '').toLowerCase();
        if (c.class_cat_id == catId && c.age_group_id == kuId && (dName.includes('relay') || dName.includes('team') || dName.includes('pair'))) {
            hasClasses = true;
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = (c.race_number ? c.race_number + ' - ' : '') + c.distance_name + ' (' + c.gender + ')';
            classSelect.appendChild(opt);
        }
    });

    if (hasClasses) classSelect.disabled = false;
}

function updateTeamGenderRule() {
    const classId = document.getElementById('team_class_select').value;
    const info = document.getElementById('team_class_info');
    const span = document.getElementById('team_rule_gender');
    
    if (classId) {
        const c = allClasses.find(x => x.id == classId);
        if (c) {
            span.innerText = c.gender.toUpperCase();
            info.classList.remove('hidden');
            
            // Atur label wajib/opsional
            const isPair = (c.distance_name || '').toLowerCase().includes('pair');
            for(let i=1; i<=4; i++) {
                const labelEl = document.getElementById('team_req_label_' + i);
                const selectEl = document.getElementById('team_skater_select_' + i);
                const clubEl = document.getElementById('team_club_select_' + i);
                
                if (isPair) {
                    if (i <= 2) {
                        labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                        labelEl.innerText = 'Wajib Diisi';
                        clubEl.disabled = false;
                    } else {
                        labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                        labelEl.innerText = 'Tidak Tersedia';
                        clubEl.value = '';
                        selectEl.value = '';
                        clubEl.disabled = true;
                        selectEl.disabled = true;
                    }
                } else {
                    // Relay
                    clubEl.disabled = false;
                    if (i <= 3) {
                        labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                        labelEl.innerText = 'Wajib Diisi';
                    } else {
                        labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                        labelEl.innerText = 'Opsional';
                    }
                }
            }
        }
    } else {
        info.classList.add('hidden');
        // Reset
        for(let i=1; i<=4; i++) {
            const labelEl = document.getElementById('team_req_label_' + i);
            const clubEl = document.getElementById('team_club_select_' + i);
            clubEl.disabled = false;
            if (i <= 2) {
                labelEl.className = 'text-[9px] text-red-400 mt-1 italic text-right';
                labelEl.innerText = 'Wajib Diisi';
            } else {
                labelEl.className = 'text-[9px] text-slate-400 mt-1 italic text-right';
                labelEl.innerText = 'Opsional';
            }
        }
    }
    
    // Re-populate skaters to apply gender limits
    for(let i=1; i<=4; i++) {
        const clubId = document.getElementById('team_club_select_'+i).value;
        if(clubId) loadAthletes(clubId, 'team_skater_select_'+i);
    }
    validateTeamMembers();
}

function validateTeamMembers() {
    const btn = document.getElementById('btn_submit_team');
    const classId = document.getElementById('team_class_select').value;
    let selectedCount = 0;
    
    document.querySelectorAll('.team-skater-select').forEach(sel => {
        if (sel.value) selectedCount++;
    });

    let isValid = false;
    let requiredCount = 2; // Default
    let isPair = false;

    if (classId) {
        const c = allClasses.find(x => x.id == classId);
        if (c) {
            const dName = (c.distance_name || '').toLowerCase();
            if (dName.includes('pair')) {
                isPair = true;
                requiredCount = 2;
                isValid = (selectedCount === 2);
            } else {
                // Relay / Team minimal 3 orang
                requiredCount = 3;
                isValid = (selectedCount >= 3);
            }
        }
    }

    if (classId && isValid) {
        btn.disabled = false;
        btn.className = 'px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-200 transition cursor-pointer';
    } else {
        btn.disabled = true;
        btn.className = 'px-8 py-3 bg-slate-300 text-white rounded-xl font-black uppercase tracking-widest text-xs transition cursor-not-allowed';
    }
}
</script>
