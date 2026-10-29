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
