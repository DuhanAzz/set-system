import re

with open('views/core/tools/pace_calculator.php', 'r') as f:
    original_code = f.read()

# 1. Add SheetJS to HEAD
head_end = original_code.find('</head>')
if head_end != -1:
    original_code = original_code[:head_end] + '    <!-- SheetJS for Excel -->\n    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>\n' + original_code[head_end:]

# 2. Modify Print CSS
print_css_start = original_code.find('@media print {')
print_css_end = original_code.find('}', original_code.find('.text-red { color: red !important; }')) + 1
old_print_css = original_code[print_css_start:print_css_end]

new_print_css = """@media print {
            @page {
                size: A4 portrait;
                margin: 0mm; 
            }
            body.is-landscape @page {
                size: A4 landscape;
            }
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            #print-container {
                padding: 10mm !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            body.is-landscape #print-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15mm;
                padding: 10mm 15mm !important;
            }
            .page-break { page-break-after: always; }
            .print-hidden { display: none !important; }
            .print-visible { display: block !important; }
            #navbar { display: none !important; }
            
            main { padding: 0 !important; margin: 0 !important; }
            .max-w-4xl { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .rounded-3xl { border: none !important; box-shadow: none !important; padding: 0 !important; }
            
            .excel-table { width: 100%; border-collapse: collapse; margin-top: 0; font-size: 13px; }
            body.is-landscape .excel-table { font-size: 11px; }
            .excel-table th, .excel-table td { border: 1px solid #000; padding: 4px; color: #000; }
            .excel-table .title-row th { font-size: 18px; border: none !important; padding-bottom: 15px; }
            .excel-table .thick-bottom { border-bottom: 2px solid #000 !important; }
            .excel-table .thick-top { border-top: 2px solid #000 !important; }
            .excel-table td[colspan="4"], .excel-table td[colspan="3"] { border: none !important; padding: 2px 4px; }
            .text-red { color: red !important; }
        }"""
original_code = original_code.replace(old_print_css, new_print_css)

# 3. Modify Header Title to add the toggle button
header_old = """                <div class="text-center mb-10 print-hidden">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-2 relative inline-block">
                        Pace Chart
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <p class="text-slate-500 font-bold mt-4 tracking-widest uppercase text-sm">Generator Pace Training</p>
                </div>"""
header_new = """                <div class="text-center mb-10 print-hidden flex flex-col items-center">
                    <h2 class="font-teko text-5xl font-black uppercase tracking-wide text-blue-900 mb-2 relative inline-block">
                        Pace Chart
                        <div class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </h2>
                    <div class="flex items-center gap-4 mt-4">
                        <p class="text-slate-500 font-bold tracking-widest uppercase text-sm mb-0">Generator Pace Training</p>
                        <button id="toggleModeBtn" onclick="toggleMode()" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black tracking-widest uppercase rounded shadow transition">Mode Matriks</button>
                    </div>
                </div>"""
original_code = original_code.replace(header_old, header_new)

# 4. Insert Matrix Section after input-section
input_sec_end_tag = '                </div>\n\n                <!-- STEP 2: OUTPUT SECTION -->'
matrix_html = """                </div>

                <!-- STEP 1B: BULK MATRIX SECTION -->
                <div id="input-matrix-section" class="print-hidden" style="display: none;">
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <button onclick="addMatrixRow()" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow transition">+ Tambah Baris</button>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <button onclick="exportExcel()" class="flex-1 sm:flex-none px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow transition">Export Template</button>
                            <label class="flex-1 sm:flex-none px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow transition cursor-pointer text-center">
                                Import Excel
                                <input type="file" id="excelUpload" accept=".xlsx, .xls" class="hidden" onchange="importExcel(event)">
                            </label>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm mb-8">
                        <table class="w-full text-sm text-center" id="matrixTable">
                            <thead class="bg-slate-800 text-white font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-2 w-10">No</th>
                                    <th class="py-3 px-2 min-w-[150px]">Nama Perenang</th>
                                    <th class="py-3 px-2 min-w-[120px]">Tgl Lahir (DD/MM/YYYY)</th>
                                    <th class="py-3 px-2 min-w-[100px]">Jarak Tes</th>
                                    <th class="py-3 px-2 min-w-[100px]">Waktu Tes<br><span class="text-[8px] font-normal">(MM.SS,MS)</span></th>
                                    <th class="py-3 px-2 min-w-[120px]">Gaya</th>
                                    <th class="py-3 px-2 min-w-[80px]">Offset (+3/+5)</th>
                                    <th class="py-3 px-2 w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="matrixBody" class="divide-y divide-slate-200 bg-white text-slate-700">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <button onclick="generateChart()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-sm tracking-widest uppercase py-4 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-1">
                        BUAT CHART MATRIKS
                    </button>
                </div>

                <!-- STEP 2: OUTPUT SECTION -->"""
original_code = original_code.replace(input_sec_end_tag, matrix_html)

# 5. Modify output-section screen layout
output_old = """                    <!-- Info Panel (Tailwind) -->
                    <div class="bg-blue-50/50 border-l-4 border-blue-500 rounded-r-xl p-6 mb-8 print-hidden" id="infoPanel">
                        <!-- Injected via JS -->
                    </div>

                    <!-- Table (Tailwind) -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm print-hidden mb-8">
                        <table class="w-full text-sm text-center" id="chartTable">
                            <thead class="bg-blue-900 text-white font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-4 px-3">Sistem Energi</th>
                                    <th class="py-4 px-3">HR (bpm)</th>
                                    <th class="py-4 px-3">% Speed</th>
                                    <th class="py-4 px-3">50m (110%)</th>
                                    <th class="py-4 px-3">100m (105%)</th>
                                    <th class="py-4 px-3">150m (100%)</th>
                                    <th class="py-4 px-3">200m (100%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 bg-white">
                                <!-- Rows generated by JS -->
                            </tbody>
                        </table>
                    </div>"""
output_new = """                    <!-- Screen Output Container for Single/Bulk Mode -->
                    <div id="screen-output-container" class="print-hidden mb-8 space-y-12">
                        <!-- Chart blocks will be injected here via JS -->
                    </div>"""
original_code = original_code.replace(output_old, output_new)

# 6. Append new Javascript logic
js_insert = """
        let isBulkMode = false;
        let matrixRowCount = 0;

        function toggleMode() {
            isBulkMode = !isBulkMode;
            document.getElementById('input-section').style.display = isBulkMode ? 'none' : 'block';
            document.getElementById('input-matrix-section').style.display = isBulkMode ? 'block' : 'none';
            document.getElementById('toggleModeBtn').innerText = isBulkMode ? 'Mode Tunggal' : 'Mode Matriks';
            if (isBulkMode && matrixRowCount === 0) {
                addMatrixRow();
            }
        }

        function addMatrixRow(data = {}) {
            matrixRowCount++;
            const tbody = document.getElementById('matrixBody');
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
                
                // Skip header row (index 0)
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
            e.target.value = ''; // reset
        }

        // We will override generateChart and printChart to support Bulk Mode
        const originalGenerateChart = generateChart;
        function generateChart() {
            if (!isBulkMode) {
                document.body.classList.remove('is-landscape');
                originalGenerateChart();
                return;
            }
            
            document.body.classList.add('is-landscape');
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

            let printHtml = '';

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
                    <div class="${(index % 2 === 1) ? 'page-break' : ''}" style="width: 100%;">
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
                printHtml += p_html;
            });

            printContainer.innerHTML = printHtml;
            document.getElementById('input-section').style.display = 'none';
            document.getElementById('input-matrix-section').style.display = 'none';
            document.getElementById('output-section').style.display = 'block';
            window.scrollTo(0,0);
        }

        const originalPrintChart = printChart;
        function printChart() {
            if (!isBulkMode) {
                originalPrintChart();
                return;
            }
            
            const originalTitle = document.title;
            const dateObj = new Date();
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            
            document.title = `Bulk_Training_Chart_${dateStr}`;
            window.print();
            
            setTimeout(() => {
                document.title = originalTitle;
            }, 1000);
        }

        const originalResetForm = resetForm;
        function resetForm() {
            if (!isBulkMode) {
                originalResetForm();
                return;
            }
            document.getElementById('input-section').style.display = 'none';
            document.getElementById('input-matrix-section').style.display = 'block';
            document.getElementById('output-section').style.display = 'none';
            document.getElementById('screen-output-container').innerHTML = '';
        }
"""
original_code = original_code.replace('const intensities = [', js_insert + '\n        const intensities = [')

# Also modify generateChart to clear screen-output-container for single mode too if it was appended
gen_single_modify = """            const screenContainer = document.getElementById('screen-output-container');
            if(screenContainer) screenContainer.innerHTML = '';
            
            // Tailwind Info Panel"""
original_code = original_code.replace('            // Tailwind Info Panel', gen_single_modify)

# To properly wrap screen output for Single mode
# We replaced the table and infopanel with screen-output-container. 
# But wait! I removed them! So I need to reinject them in single mode's generateChart!
# Ah! I should undo the replacement of output-section, and just add screen-output-container inside output-section.
# Let's fix output_new:
output_new_fix = """                    <!-- Screen Output Container for Bulk Mode -->
                    <div id="screen-output-container" class="print-hidden mb-8 space-y-8"></div>

                    <!-- Single Mode Elements -->
                    <div id="single-mode-wrapper">
                        <!-- Info Panel (Tailwind) -->
                        <div class="bg-blue-50/50 border-l-4 border-blue-500 rounded-r-xl p-6 mb-8 print-hidden" id="infoPanel">
                            <!-- Injected via JS -->
                        </div>

                        <!-- Table (Tailwind) -->
                        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm print-hidden mb-8">
                            <table class="w-full text-sm text-center" id="chartTable">
                                <thead class="bg-blue-900 text-white font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-4 px-3">Sistem Energi</th>
                                        <th class="py-4 px-3">HR (bpm)</th>
                                        <th class="py-4 px-3">% Speed</th>
                                        <th class="py-4 px-3">50m (110%)</th>
                                        <th class="py-4 px-3">100m (105%)</th>
                                        <th class="py-4 px-3">150m (100%)</th>
                                        <th class="py-4 px-3">200m (100%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 font-semibold text-slate-700 bg-white">
                                    <!-- Rows generated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>"""
original_code = original_code.replace(output_new, output_new_fix)

# Then in JS single mode:
gen_single_mod_2 = """            if(document.getElementById('single-mode-wrapper')) document.getElementById('single-mode-wrapper').style.display = 'block';
            if(document.getElementById('screen-output-container')) document.getElementById('screen-output-container').style.display = 'none';

            // Tailwind Info Panel"""
original_code = original_code.replace(gen_single_modify, gen_single_mod_2)

# And in JS bulk mode:
gen_bulk_mod = """            if(document.getElementById('single-mode-wrapper')) document.getElementById('single-mode-wrapper').style.display = 'none';
            if(document.getElementById('screen-output-container')) document.getElementById('screen-output-container').style.display = 'block';

            let printHtml = '';"""
original_code = original_code.replace("            let printHtml = '';", gen_bulk_mod)


with open('views/core/tools/pace_calculator_new.php', 'w') as f:
    f.write(original_code)

