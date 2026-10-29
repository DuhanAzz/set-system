/**
 * Pace Training Calculator - Core Logic & Excel Handler
 * Mengelola kalkulasi pace training perenang (Single & Matrix Bulk Mode),
 * konversi sistem energi aerobik & anaerobik, ekspor/impor Excel (SheetJS),
 * serta formatting cetak A4 portrait / landscape.
 */

window.offset200 = 0;
window.offset150 = 0;
let isBulkMode = false;
let matrixRowCount = 0;

const intensities = [
    { name: "Recovery", percent: 75, type: "aerobic" },
    { name: "EN1", percent: 76, type: "aerobic" },
    { name: "EN1", percent: 80, type: "aerobic" },
    { name: "EN2", percent: 81, type: "aerobic" },
    { name: "EN2", percent: 85, type: "aerobic" },
    { name: "AT", percent: 86, type: "aerobic" },
    { name: "AT", percent: 89, type: "aerobic" },
    { name: "VO2MAX", percent: 90, type: "anaerobic" },
    { name: "VO2MAX", percent: 92, type: "anaerobic" },
    { name: "LP", percent: 93, type: "anaerobic" },
    { name: "LP", percent: 94, type: "anaerobic" },
    { name: "LT", percent: 95, type: "anaerobic" },
    { name: "LT", percent: 96, type: "anaerobic" },
    { name: "PL", percent: 97, type: "anaerobic" },
    { name: "PL", percent: 100, type: "anaerobic" }
];

function toggleOffset(val, dist) {
    if (dist === 200) {
        window.offset200 = window.offset200 === val ? 0 : val;
        const btn3 = document.getElementById('btn-offset-3-200');
        const btn5 = document.getElementById('btn-offset-5-200');
        if (btn3) btn3.className = window.offset200 === 3 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
        if (btn5) btn5.className = window.offset200 === 5 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
        
        const input = document.getElementById('time200');
        if (input) input.dispatchEvent(new Event('input'));
    } else {
        window.offset150 = window.offset150 === val ? 0 : val;
        const btn3 = document.getElementById('btn-offset-3-150');
        const btn5 = document.getElementById('btn-offset-5-150');
        if (btn3) btn3.className = window.offset150 === 3 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
        if (btn5) btn5.className = window.offset150 === 5 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
        
        const input = document.getElementById('time150');
        if (input) input.dispatchEvent(new Event('input'));
    }
}

function toggleMode() {
    isBulkMode = !isBulkMode;
    const inputSec = document.getElementById('input-section');
    const matrixSec = document.getElementById('input-matrix-section');
    const toggleBtn = document.getElementById('toggleModeBtn');

    if (inputSec) inputSec.style.display = isBulkMode ? 'none' : 'block';
    if (matrixSec) matrixSec.style.display = isBulkMode ? 'block' : 'none';
    if (toggleBtn) toggleBtn.innerText = isBulkMode ? 'Mode Tunggal' : 'Mode Matriks';

    if (isBulkMode && matrixRowCount === 0) {
        addMatrixRow();
    }
}

function addMatrixRow(data = {}) {
    matrixRowCount++;
    const tbody = document.getElementById('matrixBody');
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.id = `mRow_${matrixRowCount}`;
    
    const name = data.name || '';
    const dob = data.dob || '';
    const distance = data.distance || '200';
    const time = data.time || '';
    const style = data.style || '-';
    const offset = data.offset || '0';

    tr.innerHTML = `
        <td class="py-2 px-2 text-xs font-bold">${matrixRowCount}</td>
        <td class="py-2 px-2"><input type="text" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mName" placeholder="Nama" value="${name}"></td>
        <td class="py-2 px-2"><input type="text" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mDob" placeholder="DD/MM/YYYY" maxlength="10" value="${dob}" oninput="formatMatrixDob(this)"></td>
        <td class="py-2 px-2">
            <select class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mDist">
                <option value="200" ${distance == '200' ? 'selected' : ''}>200m</option>
                <option value="150" ${distance == '150' ? 'selected' : ''}>150m</option>
            </select>
        </td>
        <td class="py-2 px-2"><input type="text" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mTime" placeholder="00.00,00" value="${time}" oninput="formatMatrixTime(this)"></td>
        <td class="py-2 px-2">
            <select class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mStyle">
                <option value="-" ${style == '-' ? 'selected' : ''}>-</option>
                <option value="Bebas" ${style == 'Bebas' ? 'selected' : ''}>Bebas</option>
                <option value="Kupu-Kupu" ${style == 'Kupu-Kupu' ? 'selected' : ''}>Kupu-Kupu</option>
                <option value="Punggung" ${style == 'Punggung' ? 'selected' : ''}>Punggung</option>
                <option value="Dada" ${style == 'Dada' ? 'selected' : ''}>Dada</option>
            </select>
        </td>
        <td class="py-2 px-2">
            <select class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 mOffset">
                <option value="0" ${offset == '0' ? 'selected' : ''}>0</option>
                <option value="3" ${offset == '3' ? 'selected' : ''}>+3</option>
                <option value="5" ${offset == '5' ? 'selected' : ''}>+5</option>
            </select>
        </td>
        <td class="py-2 px-2"><button onclick="removeMatrixRow('${tr.id}')" class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs font-bold transition">X</button></td>
    `;
    tbody.appendChild(tr);
}

function removeMatrixRow(rowId) {
    const tr = document.getElementById(rowId);
    if (tr) tr.remove();
}

function formatMatrixDob(input) {
    let val = input.value.replace(/\D/g, ''); 
    if (val.length > 8) val = val.substring(0, 8); 
    let formatted = '';
    if (val.length > 0) formatted += val.substring(0, 2);
    if (val.length > 2) formatted += '/' + val.substring(2, 4);
    if (val.length > 4) formatted += '/' + val.substring(4, 8);
    input.value = formatted;
}

function formatMatrixTime(input) {
    let val = input.value.replace(/\D/g, ''); 
    if (val.length > 6) val = val.substring(0, 6); 
    let formatted = '';
    if (val.length > 0) formatted += val.substring(0, 2);
    if (val.length > 2) formatted += '.' + val.substring(2, 4);
    if (val.length > 4) formatted += ',' + val.substring(4, 6);
    input.value = formatted;
}

function exportExcel() {
    const ws_data = [
        ["Nama Perenang", "Tgl Lahir (DD/MM/YYYY)", "Jarak Tes (200/150)", "Waktu Tes (MM.SS,MS)", "Gaya", "Offset (+3/+5)"],
        ["Atlet Contoh 1", "01/01/2010", "200", "02.10,50", "Bebas", "0"],
        ["Atlet Contoh 2", "15/08/2008", "150", "01.45,00", "Dada", "3"]
    ];
    const ws = XLSX.utils.aoa_to_sheet(ws_data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Template");
    XLSX.writeFile(wb, "Template_Matrix_Pace.xlsx");
}

function importExcel(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {type: 'array'});
        const firstSheet = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheet];
        const json = XLSX.utils.sheet_to_json(worksheet, {header: 1});
        
        document.getElementById('matrixBody').innerHTML = '';
        matrixRowCount = 0;
        
        for (let i = 1; i < json.length; i++) {
            const row = json[i];
            if (row.length === 0 || !row[0]) continue;
            
            addMatrixRow({
                name: row[0] || '',
                dob: row[1] || '',
                distance: row[2] ? row[2].toString().replace(/m/g, '') : '200',
                time: row[3] || '',
                style: row[4] || '-',
                offset: row[5] ? row[5].toString() : '0'
            });
        }
    };
    reader.readAsArrayBuffer(file);
    e.target.value = '';
}

function calculateAge(dobString) {
    if (!dobString || dobString.length !== 10) return { y: 0, m: 0, d: 0, totalYears: 0 };
    const parts = dobString.split('/');
    if (parts.length !== 3) return { y: 0, m: 0, d: 0, totalYears: 0 };

    const dStr = parseInt(parts[0], 10);
    const mStr = parseInt(parts[1], 10) - 1; 
    const yStr = parseInt(parts[2], 10);

    const dob = new Date(yStr, mStr, dStr);
    const today = new Date();

    let y = today.getFullYear() - dob.getFullYear();
    let m = today.getMonth() - dob.getMonth();
    let d = today.getDate() - dob.getDate();

    if (d < 0) {
        m--;
        d += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
    }
    if (m < 0) {
        y--;
        m += 12;
    }

    const diff_ms = Date.now() - dob.getTime();
    const age_dt = new Date(diff_ms);
    const totalYears = Math.abs(age_dt.getUTCFullYear() - 1970);

    return { y, m, d, totalYears };
}

function formatTime(totalSeconds) {
    let mins = Math.floor(totalSeconds / 60);
    let secs = Math.floor(totalSeconds % 60);
    let ms = Math.round((totalSeconds % 1) * 100);

    if (ms === 100) { ms = 0; secs++; }
    if (secs === 60) { secs = 0; mins++; }

    let minStr = mins.toString().padStart(2, '0');
    let secStr = secs.toString().padStart(2, '0');
    let msStr = ms.toString().padStart(2, '0');

    return `${minStr}.${secStr},${msStr}`;
}

function generateChartSingle() {
    document.body.classList.remove('is-landscape');
    
    let printStyle = document.getElementById('dynamic-print-style');
    if (!printStyle) {
        printStyle = document.createElement('style');
        printStyle.id = 'dynamic-print-style';
        document.head.appendChild(printStyle);
    }
    printStyle.innerHTML = '@page { size: A4 portrait !important; margin: 0mm !important; }';

    const name = document.getElementById('swimmerName').value || "Tanpa Nama";
    const dobInput = document.getElementById('dob').value;
    const time200Val = document.getElementById('time200').value.replace(/\D/g, '');
    const time150Val = document.getElementById('time150').value.replace(/\D/g, '');
    const style200 = document.getElementById('style200').value;
    const style150 = document.getElementById('style150').value;

    const parseVal = (val) => {
        let m = 0, s = 0, ms = 0;
        if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
        if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
        if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);
        return (m * 60) + s + (ms / 100);
    };

    const totalSeconds200 = parseVal(time200Val);
    const totalSeconds150 = parseVal(time150Val);

    if (totalSeconds200 === 0 && totalSeconds150 === 0) {
        alert("Harap masukkan waktu tes 200m atau 150m!");
        return;
    }

    const ageObj = calculateAge(dobInput);
    const age = ageObj.totalYears;
    const maxHR = 220 - age; 

    let vBase = 0;
    let selectedStyle = "";
    let finalTimeSecs = 0;
    let activeOffset = 0;
    let totalDistance = 0;

    if (totalSeconds200 > 0) {
        activeOffset = window.offset200 || 0;
        finalTimeSecs = totalSeconds200 + activeOffset;
        vBase = 200 / finalTimeSecs; 
        selectedStyle = style200;
        totalDistance = 200;
    } else {
        activeOffset = window.offset150 || 0;
        finalTimeSecs = totalSeconds150 + activeOffset;
        vBase = 150 / finalTimeSecs; 
        selectedStyle = style150;
        totalDistance = 150;
    }

    const todayStr = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const formattedTime = formatTime(finalTimeSecs);
    const offsetLabel = activeOffset > 0 ? ` +${activeOffset}` : '';

    if(document.getElementById('single-mode-wrapper')) document.getElementById('single-mode-wrapper').style.display = 'block';
    if(document.getElementById('screen-output-container')) document.getElementById('screen-output-container').style.display = 'none';

    // Tailwind Info Panel
    const infoHtml = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 font-semibold text-sm text-slate-800">
            <div class="space-y-2">
                <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Nama Perenang</div><div class="w-4">:</div><div class="flex-1">${name}</div></div>
                <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Tanggal Lahir</div><div class="w-4">:</div><div class="flex-1">${dobInput || '-'}</div></div>
                <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Umur</div><div class="w-4">:</div><div class="flex-1">${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln ' + ageObj.d + ' Hr' : '0 Thn 0 Bln 0 Hr'}</div></div>
                <div class="flex"><div class="w-32 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Tanggal Dibuat</div><div class="w-4">:</div><div class="flex-1">${todayStr}</div></div>
            </div>
            <div class="space-y-2">
                <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Gaya Renang</div><div class="w-4">:</div><div class="flex-1">${selectedStyle !== '-' ? selectedStyle : '-'}</div></div>
                <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Jarak (meter)</div><div class="w-4">:</div><div class="flex-1">${totalDistance}</div></div>
                <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Waktu (detik)${offsetLabel}</div><div class="w-4">:</div><div class="flex-1 text-red-600 font-bold">${finalTimeSecs.toFixed(2).replace('.', ',')} &nbsp;&nbsp; ${formattedTime.replace('.', ':')}</div></div>
                <div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Kecepatan (m/s)</div><div class="w-4">:</div><div class="flex-1">${vBase.toFixed(9).replace('.', ',')}</div></div>
            </div>
        </div>
    `;
    document.getElementById('infoPanel').innerHTML = infoHtml;

    const tbody = document.querySelector('#chartTable tbody');
    tbody.innerHTML = ''; 

    intensities.forEach(zone => {
        const tr = document.createElement('tr');
        tr.className = "hover:bg-slate-50 transition";
        const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
        const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
        const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
        const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
        const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));

        tr.innerHTML = `
        <td class="py-3 px-3">${zone.name}</td>
        <td class="py-3 px-3">${estimatedHR}</td>
        <td class="py-3 px-3">${zone.percent}%</td>
        <td class="py-3 px-3">${formatTime(t50)}</td>
        <td class="py-3 px-3">${formatTime(t100)}</td>
        <td class="py-3 px-3">${formatTime(t150)}</td>
        <td class="py-3 px-3">${formatTime(t200)}</td>
        `;
        tbody.appendChild(tr);
    });

    // Format PDF (Print view)
    let printHtml = `
        <table class="excel-table">
            <tr class="title-row">
                <th colspan="7" style="text-align:center; font-weight:bold; font-size: 20px; padding-bottom: 20px;">TRAINING PACE CHART</th>
            </tr>
            <tr>
                <td colspan="4" style="font-weight:bold; text-align:left;">Nama Perenang : ${name}</td>
                <td colspan="3" style="font-weight:bold; text-align:left;">Gaya Renang : ${selectedStyle !== '-' ? selectedStyle : '-'}</td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight:bold; text-align:left;">Tanggal Lahir : ${dobInput || '-'}</td>
                <td colspan="3" style="font-weight:bold; text-align:left;">Total Jarak (meter) : ${totalDistance}</td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight:bold; text-align:left;">Umur : ${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln ' + ageObj.d + ' Hr' : '0 Thn 0 Bln 0 Hr'}</td>
                <td colspan="3" style="font-weight:bold; text-align:left;">Total Waktu (detik)${offsetLabel} : <span class="text-red">${finalTimeSecs.toFixed(2).replace('.', ',')} &nbsp;&nbsp; ${formattedTime.replace('.', ':')}</span></td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight:bold; text-align:left;">Tanggal Dibuat : ${todayStr}</td>
                <td colspan="3" style="font-weight:bold; text-align:left;">Kecepatan (m/s) : ${vBase.toFixed(9).replace('.', ',')}</td>
            </tr>
            
            <tr class="thick-top">
                <th rowspan="2" style="text-align:center;">Sistem Energi</th>
                <th rowspan="2" style="text-align:center;">Estimasi Denyut Nadi<br>(per menit)</th>
                <th rowspan="2" style="text-align:center;">% Kecepatan Test</th>
                <th style="text-align:center; font-weight:bold;">50 meter</th>
                <th style="text-align:center; font-weight:bold;">100 meter</th>
                <th style="text-align:center; font-weight:bold;">150 meter</th>
                <th style="text-align:center; font-weight:bold;">200 meter</th>
            </tr>
            <tr class="thick-bottom">
                <th style="text-align:center;">110%</th>
                <th style="text-align:center;">105%</th>
                <th style="text-align:center;">100%</th>
                <th style="text-align:center;">100%</th>
            </tr>
            
            <tr>
                <th colspan="7" style="text-align:center; font-weight:bold; padding-top: 10px;">Zona Latihan Aerobik</th>
            </tr>
            <tr>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">AEROBIC</td>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">0-170</td>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">80%-89%</td>
                <td colspan="4" style="font-weight:bold; text-align:center; background:#f0f0f0;">Detik</td>
            </tr>
    `;
    
    intensities.filter(z => z.type === 'aerobic').forEach(zone => {
        const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
        const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
        const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
        const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
        const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
        
        printHtml += `
            <tr>
                <td style="text-align:center;">${zone.name}</td>
                <td style="text-align:center;">${estimatedHR}</td>
                <td style="text-align:center;">${zone.percent}%</td>
                <td style="text-align:center;">${formatTime(t50)}</td>
                <td style="text-align:center;">${formatTime(t100)}</td>
                <td style="text-align:center;">${formatTime(t150)}</td>
                <td style="text-align:center;">${formatTime(t200)}</td>
            </tr>
        `;
    });
    
    printHtml += `
            <tr class="thick-top">
                <th colspan="7" style="text-align:center; font-weight:bold; padding-top: 10px;">Zona Latihan Anaerobik</th>
            </tr>
            <tr>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">ANAEROBIC</td>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">&gt; 170</td>
                <td style="font-weight:bold; text-align:center; background:#f0f0f0;">90%-100%</td>
                <td colspan="4" style="font-weight:bold; text-align:center; background:#f0f0f0;">Detik</td>
            </tr>
    `;

    intensities.filter(z => z.type === 'anaerobic').forEach(zone => {
        const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
        const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
        const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
        const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
        const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
        
        printHtml += `
            <tr>
                <td style="text-align:center;">${zone.name}</td>
                <td style="text-align:center;">${estimatedHR}</td>
                <td style="text-align:center;">${zone.percent}%</td>
                <td style="text-align:center;">${formatTime(t50)}</td>
                <td style="text-align:center;">${formatTime(t100)}</td>
                <td style="text-align:center;">${formatTime(t150)}</td>
                <td style="text-align:center;">${formatTime(t200)}</td>
            </tr>
        `;
    });

    printHtml += `</table>`;
    document.getElementById('print-container').innerHTML = printHtml;

    document.getElementById('input-section').style.display = 'none';
    document.getElementById('output-section').style.display = 'block';
    window.scrollTo(0,0);
}

function resetFormSingle() {
    document.getElementById('input-section').style.display = 'block';
    document.getElementById('output-section').style.display = 'none';
    document.querySelector('#chartTable tbody').innerHTML = '';
}

function printChartSingle() {
    const originalTitle = document.title;
    const nameInput = document.getElementById('swimmerName');
    let name = "Tanpa_Nama";
    if (nameInput && nameInput.value.trim() !== "") {
        name = nameInput.value.trim();
    }
    
    const dateObj = new Date();
    const dateStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    
    document.title = `${name} _Training Chart_${dateStr}`;
    window.print();
    
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}

function generateChartBulk() {
    document.body.classList.add('is-landscape');
    
    let printStyle = document.getElementById('dynamic-print-style');
    if (!printStyle) {
        printStyle = document.createElement('style');
        printStyle.id = 'dynamic-print-style';
        document.head.appendChild(printStyle);
    }
    printStyle.innerHTML = '@page { size: A4 landscape !important; margin: 0mm !important; }';

    const tbody = document.getElementById('matrixBody');
    const rows = tbody.querySelectorAll('tr');
    if (rows.length === 0) {
        alert("Harap masukkan minimal 1 atlet di tabel matriks!");
        return;
    }

    const screenContainer = document.getElementById('screen-output-container');
    const printContainer = document.getElementById('print-container');
    screenContainer.innerHTML = '';
    printContainer.innerHTML = '';

    if(document.getElementById('single-mode-wrapper')) document.getElementById('single-mode-wrapper').style.display = 'none';
    if(document.getElementById('screen-output-container')) document.getElementById('screen-output-container').style.display = 'block';

    let printBlocks = [];

    rows.forEach((tr, index) => {
        const name = tr.querySelector('.mName').value || "Tanpa Nama";
        const dobInput = tr.querySelector('.mDob').value;
        const distance = parseInt(tr.querySelector('.mDist').value);
        const timeVal = tr.querySelector('.mTime').value.replace(/\D/g, '');
        const style = tr.querySelector('.mStyle').value;
        const offset = parseInt(tr.querySelector('.mOffset').value) || 0;

        const parseVal = (val) => {
            let m = 0, s = 0, ms = 0;
            if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
            if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
            if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);
            return (m * 60) + s + (ms / 100);
        };

        const totalSeconds = parseVal(timeVal);
        if (totalSeconds === 0) return;

        const ageObj = calculateAge(dobInput);
        const age = ageObj.totalYears;
        const maxHR = 220 - age; 

        const finalTimeSecs = totalSeconds + offset;
        const vBase = distance / finalTimeSecs; 

        const todayStr = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const formattedTime = formatTime(finalTimeSecs);
        const offsetLabel = offset > 0 ? ` +${offset}` : '';
        
        // Screen HTML
        let screenTbody = '';
        intensities.forEach(zone => {
            const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
            const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
            const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
            const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
            const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
            screenTbody += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="py-2 px-2">${zone.name}</td>
                    <td class="py-2 px-2">${estimatedHR}</td>
                    <td class="py-2 px-2">${zone.percent}%</td>
                    <td class="py-2 px-2">${formatTime(t50)}</td>
                    <td class="py-2 px-2">${formatTime(t100)}</td>
                    <td class="py-2 px-2">${formatTime(t150)}</td>
                    <td class="py-2 px-2">${formatTime(t200)}</td>
                </tr>
            `;
        });

        const blockHtml = `
            <div class="border-2 border-slate-200 rounded-xl overflow-hidden shadow-sm">
                <div class="bg-blue-50 border-b border-slate-200 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-semibold text-slate-800">
                        <div><span class="text-slate-500">Nama:</span> ${name}</div>
                        <div><span class="text-slate-500">Gaya:</span> ${style}</div>
                        <div><span class="text-slate-500">Umur:</span> ${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln' : '-'}</div>
                        <div><span class="text-slate-500">Jarak:</span> ${distance}m</div>
                        <div><span class="text-slate-500">Waktu${offsetLabel}:</span> <span class="text-red-600">${finalTimeSecs.toFixed(2).replace('.', ',')}s (${formattedTime.replace('.', ':')})</span></div>
                        <div><span class="text-slate-500">Speed:</span> ${vBase.toFixed(4)} m/s</div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-center">
                        <thead class="bg-slate-800 text-white font-bold uppercase tracking-wider text-[9px]">
                            <tr>
                                <th class="py-2 px-2">Energi</th>
                                <th class="py-2 px-2">HR</th>
                                <th class="py-2 px-2">% Speed</th>
                                <th class="py-2 px-2">50m</th>
                                <th class="py-2 px-2">100m</th>
                                <th class="py-2 px-2">150m</th>
                                <th class="py-2 px-2">200m</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 bg-white">
                            ${screenTbody}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        screenContainer.innerHTML += blockHtml;

        // Print HTML
        let p_html = `
            <div class="print-col">
                <table class="excel-table">
                    <tr class="title-row">
                        <th colspan="7" style="text-align:center; font-weight:bold; font-size: 16px; padding-bottom: 15px;">TRAINING PACE CHART</th>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Nama : ${name}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Gaya : ${style}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Tgl Lahir : ${dobInput || '-'}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Jarak Tes : ${distance}m</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Umur : ${age > 0 ? ageObj.y + ' Thn ' + ageObj.m + ' Bln' : '-'}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Waktu${offsetLabel} : <span class="text-red">${finalTimeSecs.toFixed(2).replace('.', ',')} &nbsp; ${formattedTime.replace('.', ':')}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-weight:bold; text-align:left;">Tgl Dibuat : ${todayStr}</td>
                        <td colspan="3" style="font-weight:bold; text-align:left;">Kecepatan : ${vBase.toFixed(6).replace('.', ',')} m/s</td>
                    </tr>
                    
                    <tr class="thick-top">
                        <th rowspan="2" style="text-align:center;">Sistem Energi</th>
                        <th rowspan="2" style="text-align:center;">HR (bpm)</th>
                        <th rowspan="2" style="text-align:center;">% Speed</th>
                        <th style="text-align:center; font-weight:bold;">50 meter</th>
                        <th style="text-align:center; font-weight:bold;">100 meter</th>
                        <th style="text-align:center; font-weight:bold;">150 meter</th>
                        <th style="text-align:center; font-weight:bold;">200 meter</th>
                    </tr>
                    <tr class="thick-bottom">
                        <th style="text-align:center;">110%</th>
                        <th style="text-align:center;">105%</th>
                        <th style="text-align:center;">100%</th>
                        <th style="text-align:center;">100%</th>
                    </tr>
                    <tr><th colspan="7" style="text-align:center; font-weight:bold; padding-top: 5px;">Zona Latihan Aerobik</th></tr>
        `;
        
        intensities.filter(z => z.type === 'aerobic').forEach(zone => {
            const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
            const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
            const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
            const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
            const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
            p_html += `
                <tr>
                    <td style="text-align:center;">${zone.name}</td>
                    <td style="text-align:center;">${estimatedHR}</td>
                    <td style="text-align:center;">${zone.percent}%</td>
                    <td style="text-align:center;">${formatTime(t50)}</td>
                    <td style="text-align:center;">${formatTime(t100)}</td>
                    <td style="text-align:center;">${formatTime(t150)}</td>
                    <td style="text-align:center;">${formatTime(t200)}</td>
                </tr>
            `;
        });
        
        p_html += `<tr><th colspan="7" style="text-align:center; font-weight:bold; padding-top: 5px;" class="thick-top">Zona Latihan Anaerobik</th></tr>`;
        
        intensities.filter(z => z.type === 'anaerobic').forEach(zone => {
            const estimatedHR = age > 0 ? Math.round(maxHR * (zone.percent / 100)) : '-';
            const t50 = 50 / (vBase * 1.10 * (zone.percent / 100));
            const t100 = 100 / (vBase * 1.05 * (zone.percent / 100));
            const t150 = 150 / (vBase * 1.00 * (zone.percent / 100));
            const t200 = 200 / (vBase * 1.00 * (zone.percent / 100));
            p_html += `
                <tr>
                    <td style="text-align:center;">${zone.name}</td>
                    <td style="text-align:center;">${estimatedHR}</td>
                    <td style="text-align:center;">${zone.percent}%</td>
                    <td style="text-align:center;">${formatTime(t50)}</td>
                    <td style="text-align:center;">${formatTime(t100)}</td>
                    <td style="text-align:center;">${formatTime(t150)}</td>
                    <td style="text-align:center;">${formatTime(t200)}</td>
                </tr>
            `;
        });
        
        p_html += `</table></div>`;
        printBlocks.push(p_html);
    });

    let printHtml = '';
    for (let i = 0; i < printBlocks.length; i += 2) {
        printHtml += '<div class="print-page-row">';
        printHtml += printBlocks[i];
        if (printBlocks[i+1]) {
            printHtml += printBlocks[i+1];
        } else {
            printHtml += '<div class="print-col"></div>';
        }
        printHtml += '</div>';
    }

    printContainer.innerHTML = printHtml;
    document.getElementById('input-section').style.display = 'none';
    document.getElementById('input-matrix-section').style.display = 'none';
    document.getElementById('output-section').style.display = 'block';
    window.scrollTo(0,0);
}

function printChartBulk() {
    const originalTitle = document.title;
    const dateObj = new Date();
    const dateStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    
    document.title = `Bulk_Training_Chart_${dateStr}`;
    window.print();
    
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}

function resetFormBulk() {
    document.getElementById('input-section').style.display = 'none';
    document.getElementById('input-matrix-section').style.display = 'block';
    document.getElementById('output-section').style.display = 'none';
    document.getElementById('screen-output-container').innerHTML = '';
}

function generateChart() {
    if (isBulkMode) {
        generateChartBulk();
    } else {
        generateChartSingle();
    }
}

function resetForm() {
    if (isBulkMode) {
        resetFormBulk();
    } else {
        resetFormSingle();
    }
}

function printChart() {
    if (isBulkMode) {
        printChartBulk();
    } else {
        printChartSingle();
    }
}

// Inisialisasi DOM & Event Handlers
document.addEventListener('DOMContentLoaded', () => {
    if (typeof flatpickr === 'function') {
        flatpickr("#dob", {
            dateFormat: "d/m/Y",
            locale: "id",
            allowInput: true,
            onChange: function (selectedDates, dateStr) {
                if (dateStr.length === 10) {
                    const age = calculateAge(dateStr);
                    const ageDisp = document.getElementById('ageDisplay');
                    if (ageDisp) {
                        ageDisp.innerText = (age.totalYears > 0 || age.y > 0 || age.m > 0 || age.d > 0)
                            ? `${age.y}Thn ${age.m}Bln ${age.d}Hr`
                            : `0Thn 0Bln 0Hr`;
                    }
                } else {
                    const ageDisp = document.getElementById('ageDisplay');
                    if (ageDisp) ageDisp.innerText = `0Thn 0Bln 0Hr`;
                }
            }
        });
    }

    const dobEl = document.getElementById('dob');
    if (dobEl) {
        dobEl.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, ''); 
            if (val.length > 8) val = val.substring(0, 8); 

            let formatted = '';
            if (val.length > 0) formatted += val.substring(0, 2);
            if (val.length > 2) formatted += '/' + val.substring(2, 4);
            if (val.length > 4) formatted += '/' + val.substring(4, 8);

            e.target.value = formatted;

            const ageDisp = document.getElementById('ageDisplay');
            if (formatted.length === 10) {
                const age = calculateAge(formatted);
                if (ageDisp) {
                    ageDisp.innerText = (age.totalYears > 0 || age.y > 0 || age.m > 0 || age.d > 0)
                        ? `${age.y}Thn ${age.m}Bln ${age.d}Hr`
                        : `0Thn 0Bln 0Hr`;
                }
            } else {
                if (ageDisp) ageDisp.innerText = `0Thn 0Bln 0Hr`;
            }
        });
    }

    const formatInputAndCalcVel = (e, distance) => {
        let val = e.target.value.replace(/\D/g, ''); 
        if (val.length > 6) val = val.substring(0, 6); 

        let formatted = '';
        if (val.length > 0) formatted += val.substring(0, 2);
        if (val.length > 2) formatted += '.' + val.substring(2, 4);
        if (val.length > 4) formatted += '.' + val.substring(4, 6);

        e.target.value = formatted;

        let m = 0, s = 0, ms = 0;
        if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
        if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
        if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);

        let totalSeconds = (m * 60) + s + (ms / 100);
        const velSpan = document.getElementById(distance === 200 ? 'vel200' : 'vel150');

        if (totalSeconds > 0) {
            const activeOffset = distance === 200 ? window.offset200 : window.offset150;
            totalSeconds += (activeOffset || 0);
            const vBase = distance / totalSeconds;
            if (velSpan) velSpan.innerText = vBase.toFixed(3) + ' m/s';
        } else {
            if (velSpan) velSpan.innerText = '0.00 m/s';
        }
    };

    const t200 = document.getElementById('time200');
    if (t200) t200.addEventListener('input', (e) => formatInputAndCalcVel(e, 200));

    const t150 = document.getElementById('time150');
    if (t150) t150.addEventListener('input', (e) => formatInputAndCalcVel(e, 150));
});
