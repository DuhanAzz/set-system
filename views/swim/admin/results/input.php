<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700;900&family=Courier+Prime:wght@400;700&display=swap');
    
    .toggle-checkbox { display: none; }
    .toggle-label { width: 44px; height: 24px; background-color: #cbd5e1; border-radius: 9999px; position: relative; cursor: pointer; transition: background-color 0.3s ease; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); }
    .toggle-label::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
    .toggle-checkbox:checked + .toggle-label { background-color: #3b82f6; }
    .toggle-checkbox:checked + .toggle-label::after { transform: translateX(20px); }
    
    .input-time { width: 100%; border: 1px solid #ccc; background: #f9f9f9; padding: 2px; font-family: 'Courier Prime', monospace; font-weight: bold; text-align: right; font-size: 10pt; color: blue; outline: none; border-radius: 4px; }
    .input-status { width: 100%; border: none; background: transparent; font-size: 8pt; font-weight: bold; text-align: center; cursor: pointer; }
    
    @media print {
        body { background: white !important; font-family: 'Arial Narrow', sans-serif !important; margin: 0 !important; padding: 0 !important; }
        .input-time { border: none !important; background: transparent !important; color: black !important; padding: 0 !important; margin: 0 !important; box-shadow: none !important; text-align: right !important; font-size: 9pt !important; font-family: 'Courier New', monospace !important; }
        .input-status { border: none !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important; color: black !important; background: transparent !important; box-shadow: none !important; padding: 0 !important; font-size: 9pt !important; text-align: center !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        /* Sembunyikan Elemen Master Layout */
        nav, aside, #logo-sidebar, .no-print, .alert-box { display: none !important; }
        #main-wrapper { margin-left: 0 !important; padding-top: 0 !important; padding: 0 !important; background: white !important; min-height: 0 !important; }
        
        /* Format Legacy (Sama dengan view_startlist.php) */
        .print-header { position: relative !important; display: flex !important; justify-content: space-between !important; align-items: flex-end !important; border-bottom: 2px solid #000 !important; padding: 2px 0 !important; margin-top: 10px !important; margin-bottom: 0px !important; background: #fff !important; min-height: 35px !important; border-top: none !important; }
        .print-header-left { display: flex !important; flex-direction: column !important; justify-content: center !important; width: 180px !important; line-height: 1.1 !important; z-index: 2 !important; position: relative !important; background: white !important; }
        .print-header-left h1 { font-size: 14pt !important; font-weight: 900 !important; margin-bottom: 2px !important; color: #000 !important; text-transform: uppercase !important; font-style: normal !important; }
        .print-header-center { position: absolute !important; left: 50% !important; bottom: 3px !important; transform: translateX(-50%) !important; text-align: center !important; width: 60% !important; z-index: 1 !important; }
        .print-header-center p { font-size: 11pt !important; font-weight: 800 !important; text-transform: uppercase !important; color: #000 !important; }
        .print-header-right { width: 80px !important; text-align: right !important; z-index: 2 !important; position: relative !important; background: white !important; color: #000 !important; }
        
        .print-heat-title { text-align: right !important; font-size: 9pt !important; font-weight: bold !important; text-transform: uppercase !important; margin-top: 12px !important; margin-bottom: 2px !important; color: #000 !important; border: none !important; padding: 0 !important; }
        
        .print-table { width: 100% !important; border-collapse: collapse !important; table-layout: fixed !important; margin-bottom: 2px !important; font-size: 8pt !important; border: none !important; }
        .print-table th { background-color: #e5e7eb !important; color: #000 !important; font-weight: bold !important; font-size: 8pt !important; text-transform: uppercase !important; padding: 2px 2px !important; border-top: 1px solid #000 !important; border-bottom: 2px solid #000 !important; border-left: none !important; border-right: none !important; text-align: center !important; }
        .print-table td { padding: 4px 4px !important; border-bottom: 1px solid #ccc !important; border-top: none !important; border-left: none !important; border-right: none !important; vertical-align: middle !important; font-weight: bold !important; color: #000 !important; } 
        
        .print-table th:first-child, .print-table td:first-child { width: 5% !important; text-align: center !important; } /* LN */
        .print-table th:nth-child(2), .print-table td:nth-child(2) { text-align: left !important; padding-left: 5px !important; } /* NAMA */
        .print-table th:nth-child(3), .print-table td:nth-child(3) { width: 10% !important; text-align: center !important; } /* KU */
        .print-table th:nth-child(4), .print-table td:nth-child(4) { width: 22% !important; text-align: left !important; padding-left: 5px !important; } /* TIM */
        .print-table th:nth-child(5), .print-table td:nth-child(5) { width: 12% !important; text-align: right !important; padding-right: 5px !important; } /* WAKTU */
        .print-table th:nth-child(6), .print-table td:nth-child(6) { width: 12% !important; text-align: center !important; } /* STATUS */
    }
</style>

<?php 
$partType = strtolower($eventProfile['participation_type'] ?? 'club');
$isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
$eventYear = date('Y', strtotime($eventProfile['event_date_start'] ?? date('Y')));

if (!function_exists('getKULabelInput')) {
    function getKULabelInput($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return "DILUAR KATEGORI ($age TH)";
    }
}
?>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert-box max-w-3xl mx-auto mb-4 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm sticky top-20 z-50 print:hidden">
        <span>✅</span> <strong><?= $_SESSION['success'] ?></strong>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert-box max-w-3xl mx-auto mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm sticky top-20 z-50 print:hidden">
        <span>❌</span> <strong><?= $_SESSION['error'] ?></strong>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="max-w-4xl mx-auto mb-6 flex flex-col items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm sticky top-20 z-40 gap-4 print:hidden">
    <div class="flex flex-col md:flex-row justify-between w-full items-center gap-4">
        <div>
            <h2 class="text-lg font-black text-slate-800 italic">INPUT HASIL LOMBA</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-[10px] font-bold uppercase text-slate-500">Mode Ranking:</span>
                <span class="text-xs font-bold text-blue-600">
                    <?= ($raceInfo['rank_mode'] ?? 'split') === 'overall' ? 'GABUNGAN (OVERALL)' : 'PER KELOMPOK UMUR (SPLIT)' ?>
                </span>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="<?= $prevUrl ?? '#' ?>" class="h-10 px-4 flex items-center justify-center rounded-l-lg font-bold text-xs uppercase transition border-r border-slate-600 <?= $prevClass ?? '' ?>">&laquo; PREV</a>
            <div class="flex bg-slate-100 rounded-none p-1 gap-1">
                <a href="<?= getenv('APP_URL') ?>/swim/admin/results" class="h-8 px-3 flex items-center bg-white border border-slate-300 rounded text-slate-600 font-bold text-[10px] uppercase hover:bg-slate-50">Menu</a>
                <a href="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $cat_id ?>&export_txt=1" class="h-8 px-3 flex items-center bg-teal-500 text-white rounded font-bold text-[10px] uppercase hover:bg-teal-600 gap-1" title="Download Data ke TXT Format Stopwatch">📤 EXPORT</a>
                <button type="button" onclick="document.getElementById('txtUploadForm').classList.toggle('hidden')" class="h-8 px-3 flex items-center bg-emerald-500 text-white rounded font-bold text-[10px] uppercase hover:bg-emerald-600 gap-1" title="Import TXT Backup dari Stopwatch">📝 IMPORT</button>
                <button type="button" onclick="window.print()" class="h-8 px-3 flex items-center bg-orange-500 text-white rounded font-bold text-[10px] uppercase hover:bg-orange-600 gap-1">🖨️ PDF</button>
                <button type="submit" form="formResult" class="h-8 px-4 flex items-center bg-blue-600 text-white rounded font-bold text-[10px] uppercase hover:bg-blue-700 gap-1 shadow-sm">💾 SIMPAN</button>
            </div>
            <a href="<?= $nextUrl ?? '#' ?>" class="h-10 px-4 flex items-center justify-center rounded-r-lg font-bold text-xs uppercase transition border-l border-slate-600 <?= $nextClass ?? '' ?>">NEXT &raquo;</a>
        </div>
    </div>

    <!-- Form Upload TXT Hidden -->
    <div id="txtUploadForm" class="hidden w-full border-t pt-3 mt-1 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
        <label class="block text-xs font-bold text-emerald-700 mb-2">Import Hasil Lomba dari File .TXT Stopwatch (Fallback)</label>
        <form method="POST" enctype="multipart/form-data" class="flex gap-2 items-center">
            <input type="file" name="txt_backup" accept=".txt" required class="text-xs w-full p-1 bg-white border border-emerald-200 rounded">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded font-bold text-xs whitespace-nowrap shadow-sm">Upload & Sinkron</button>
        </form>
    </div>

    <div class="mt-4 border-t pt-4 text-center w-full print:hidden">
        <label class="text-sm font-semibold text-blue-600">🔗 Link GDrive / Web (Untuk QR Code di PDF):</label>
        <input type="text" name="gdrive_link" id="qrLinkInput" class="w-full mt-2 p-2 border border-blue-200 rounded text-center text-sm bg-blue-50/30 border-dashed" placeholder="Tempel link file hasil di sini... (Auto Save)">
    </div>
</div>

<form id="formResult" method="POST" action="<?= getenv('APP_URL') ?>/swim/admin/results/input?category_id=<?= $cat_id ?>">
    <input type="hidden" name="rank_mode_input" value="<?= $raceInfo['rank_mode'] ?? 'split' ?>">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-sm border border-slate-200 print:shadow-none print:border-none print:p-0 print:max-w-full">
        <div class="flex justify-between items-start mb-6 pb-4 border-b border-slate-300 print-header">
            <div class="text-left print-header-left">
                <h1 class="text-2xl font-black text-slate-800 uppercase italic">ACARA #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></h1>
            </div>
            
            <div class="print-header-center hidden print:block">
                <p class="font-bold text-slate-600"><?= htmlspecialchars($raceInfo['event_name'] ?? '') ?></p>
            </div>
            
            <div class="hidden print:flex flex-col items-center justify-center print-header-right">
                <img id="printQrCode" src="" alt="QR" class="w-16 h-16 object-contain mb-0.5">
                <span class="text-[7px] font-bold mt-0 text-slate-600 uppercase tracking-widest text-center leading-none">LIVE<br>RESULT</span>
            </div>
            
            <div class="text-left print:hidden">
                <p class="font-bold text-slate-600"><?= htmlspecialchars($raceInfo['event_name'] ?? '') ?></p>
            </div>
        </div>
        
        <?php if(empty($heats)): ?>
            <div class="text-center py-12"><p class="italic text-slate-400">Belum ada peserta di nomor acara ini.</p></div>
        <?php else: ?>
            <?php foreach($heats as $heatNo => $lanesData): ?>
            <div class="mb-8 print:mb-2">
                <div class="text-right font-bold text-sm border-b-2 border-slate-800 mb-2 print-heat-title">SERI <?= str_pad($heatNo, 2, '0', STR_PAD_LEFT) ?></div>
                <table class="w-full text-sm text-left print-table">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">LN</th>
                            <th class="px-4 py-3">NAMA ATLET</th>
                            <th class="px-4 py-3 text-center">KU</th>
                            <th class="px-4 py-3">TIM</th>
                            <th class="px-4 py-3 text-right w-32">WAKTU</th>
                            <th class="px-4 py-3 text-center w-24">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Loop 0 hingga 9 (Sesuai Aturan Mutlak)
                        for($ln = 0; $ln <= 9; $ln++): 
                            $s = $lanesData[$ln] ?? null; 
                            
                            // Sembunyikan baris jika kosong DAN bukan bagian dari lintasan yang aktif (digunakan)
                            if (!$s && isset($usedLanes) && !in_array($ln, $usedLanes)) continue;
                        ?>
                        <tr class="border-b hover:bg-slate-50">
                            <td class="px-4 py-3 text-center font-bold whitespace-nowrap"><?= $ln ?></td>
                            <?php if($s): 
                                $is_real_dq = (($s['is_dq']??0) == 1 && !in_array($s['dq_reason'], ['DNF', 'DNS', '']));
                                $dq_text = $is_real_dq ? ($s['dq_reason'] ?? '') : '';
                                
                                $kuLabel = getKULabelInput($s['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
                                if (isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1) {
                                    $teamName = $s['club_name'] ?? '';
                                } else {
                                    $teamName = $isSchoolEvent ? (!empty($s['asal_sekolah']) ? $s['asal_sekolah'] : ($s['club_name'] ?? '')) : ($s['club_name'] ?? '');
                                }
                            ?>
                                <td class="px-4 py-3 font-bold"><?= htmlspecialchars($s['nama_atlet'] ?? '') ?></td>
                                <td class="px-4 py-3 text-center text-xs text-slate-700 font-bold print:text-[9pt] print:text-black"><?= htmlspecialchars($kuLabel) ?></td>
                                <td class="px-4 py-3 text-xs print:text-[9pt] print:font-normal"><?= htmlspecialchars($teamName) ?></td>
                                <td class="px-4 py-3 text-right">
                                    <input type="text" name="entries[<?= $s['id'] ?>][time]" value="<?= htmlspecialchars($s['final_time'] ?? '') ?>" class="input-time" autocomplete="off" <?= (($s['is_dq']??0) == 1) ? 'disabled style="background:#eee;color:#ccc;"' : '' ?>>
                                </td>
                                <td class="px-4 py-3 text-center relative">
                                    <select name="entries[<?= $s['id'] ?>][status]" class="input-status" onchange="handleStatusChange(this, '<?= $s['id'] ?>')">
                                        <option value="" <?= empty($s['dq_reason']) ? 'selected' : '' ?>></option>
                                        <option value="DQ" class="text-red-600 font-black" <?= $is_real_dq ? 'selected' : '' ?>>DQ</option>
                                        <option value="DNF" class="text-orange-600 font-black" <?= ($s['dq_reason']=='DNF') ? 'selected' : '' ?>>DNF</option>
                                        <option value="DNS" class="text-gray-500 font-black" <?= ($s['dq_reason']=='DNS') ? 'selected' : '' ?>>DNS</option>
                                    </select>
                                    <input type="hidden" name="entries[<?= $s['id'] ?>][dq_reason]" id="dq_reason_<?= $s['id'] ?>" value="<?= htmlspecialchars($dq_text) ?>">
                                    
                                    <div id="dq_display_<?= $s['id'] ?>" class="text-[9px] text-red-600 font-bold mt-0.5 text-center truncate w-full" title="<?= htmlspecialchars($dq_text) ?>">
                                        <?= htmlspecialchars($dq_text) ?>
                                    </div>
                                </td>
                            <?php else: ?>
                                <td colspan="5" class="px-4 py-3 text-slate-300 italic text-xs">&lt; KOSONG &gt;</td>
                            <?php endif; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</form>

<div id="dqModal" class="fixed inset-0 z-[1000] hidden bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="font-black text-slate-800 text-lg uppercase italic">Pilih Regulasi DQ</h3>
                <p class="text-xs font-bold text-slate-500">Pilih pasal atau tekan tombol "Lewati".</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="selectDqRule('DQ')" class="text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-lg border border-blue-300 transition">Lewati (Tanpa Pasal)</button>
                <button type="button" onclick="closeDqModal()" class="text-slate-400 hover:text-red-500 transition"><span class="text-2xl">&times;</span></button>
            </div>
        </div>
        
        <div class="p-4 border-b bg-white">
             <input type="text" id="searchDq" class="w-full border-slate-300 rounded-lg text-sm bg-slate-50 font-medium focus:ring-blue-500 focus:border-blue-500" placeholder="🔍 Cari pasal atau deskripsi pelanggaran..." onkeyup="filterDq()">
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-slate-50">
            <div class="grid gap-2" id="dqList">
                
                <!-- OPSI DQ KOSONG / UMUM -->
                <button type="button" onclick="selectDqRule('DQ')" class="dq-item text-left w-full bg-blue-50 border border-blue-200 hover:border-blue-500 hover:shadow-md p-3 rounded-xl transition flex gap-3 group">
                    <span class="bg-blue-100 border border-blue-300 text-blue-800 font-black px-2 py-1 rounded text-xs h-fit whitespace-nowrap group-hover:bg-blue-500 group-hover:text-white transition">
                        DQ
                    </span>
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">UMUM</div>
                        <div class="text-xs font-bold text-slate-800 leading-snug dq-desc">Disqualified (Tanpa alasan spesifik / Cepat)</div>
                    </div>
                </button>

                <?php foreach($dq_rules_list as $rule): ?>
                <button type="button" onclick="selectDqRule('<?= htmlspecialchars($rule['pasal']) ?>')" class="dq-item text-left w-full bg-white border border-slate-200 hover:border-blue-500 hover:shadow-md p-3 rounded-xl transition flex gap-3 group">
                    <span class="bg-red-50 border border-red-200 text-red-700 font-black px-2 py-1 rounded text-xs h-fit whitespace-nowrap group-hover:bg-blue-100 group-hover:text-blue-700 transition">
                        <?= htmlspecialchars($rule['pasal']) ?>
                    </span>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5"><?= htmlspecialchars($rule['kategori_gaya']) ?></div>
                        <div class="text-xs font-medium text-slate-700 leading-snug dq-desc"><?= htmlspecialchars($rule['deskripsi']) ?></div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
let currentDqTarget = null;

function handleStatusChange(selectObj, id) {
    const val = selectObj.value;
    const timeInput = document.querySelector(`input[name="entries[${id}][time]"]`);
    const dqReasonHidden = document.getElementById(`dq_reason_${id}`);
    const dqDisplay = document.getElementById(`dq_display_${id}`);
    
    if(val === 'DQ') {
        timeInput.disabled = true;
        timeInput.style.background = '#eee';
        timeInput.style.color = '#ccc';
        
        // Tetapkan default 'DQ' jika user langsung menutup modal
        if(dqReasonHidden.value === '') {
            dqReasonHidden.value = 'DQ';
            dqDisplay.innerText = 'DQ';
        }
        
        currentDqTarget = id;
        document.getElementById('dqModal').classList.remove('hidden');
        document.getElementById('dqModal').classList.add('flex');
    } else if(val === 'DNF' || val === 'DNS') {
        timeInput.disabled = true;
        timeInput.style.background = '#eee';
        timeInput.style.color = '#ccc';
        dqReasonHidden.value = val;
        dqDisplay.innerText = '';
    } else {
        timeInput.disabled = false;
        timeInput.style.background = '#f9f9f9';
        timeInput.style.color = 'blue';
        dqReasonHidden.value = '';
        dqDisplay.innerText = '';
    }
}

function closeDqModal() {
    document.getElementById('dqModal').classList.add('hidden');
    document.getElementById('dqModal').classList.remove('flex');
}

function selectDqRule(pasal) {
    if(currentDqTarget) {
        document.getElementById(`dq_reason_${currentDqTarget}`).value = pasal;
        document.getElementById(`dq_display_${currentDqTarget}`).innerText = pasal;
    }
    closeDqModal();
}

function filterDq() {
    let input = document.getElementById('searchDq').value.toLowerCase();
    let items = document.querySelectorAll('.dq-item');
    items.forEach(item => {
        let text = item.innerText.toLowerCase();
        item.style.display = text.includes(input) ? 'flex' : 'none';
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const inputLink = document.getElementById('qrLinkInput');
    const qrImage = document.getElementById('printQrCode');
    const storageKey = "qr_link_cat_<?= $cat_id ?>"; 
    const defaultUrl = "<?= getenv('APP_URL') ?>/swim/results";
    
    function updateQrCode(url) {
        if (qrImage) {
            qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(url);
        }
    }
    
    if (inputLink) {
        const savedLink = localStorage.getItem(storageKey);
        if (savedLink) { 
            inputLink.value = savedLink;
        } else {
            inputLink.value = defaultUrl;
        }
        updateQrCode(inputLink.value);
        
        inputLink.addEventListener("input", function() { 
            localStorage.setItem(storageKey, this.value);
            updateQrCode(this.value);
        });
    }
});
</script>
