/**
 * Standalone Report & Export Engine (PDF, High-Res PNG, Web Share)
 * Mengelola pembuatan kartu laporan 1 halaman A4 dengan dynamic scaling,
 * canvas image snapshot, html2canvas export, dan navigasi toolbar report.
 */

function printReport() {
    let reportWin = null;
    if (!window.location.search.includes('preview_print=1')) {
        try {
            reportWin = window.open('', '_blank');
        } catch (e) {
            reportWin = null;
        }
    }

    let rawName = document.getElementById('athleteName') ? document.getElementById('athleteName').value.trim() : '';
    if (!rawName) {
        try {
            rawName = (localStorage.getItem('race_analyzer_athlete_name') || '').trim();
        } catch (e) {}
    }
    const name = rawName || '-';
    const age = document.getElementById('athleteAge') ? (document.getElementById('athleteAge').value.trim() || '-') : '-';
    const gender = document.getElementById('athleteGender') ? document.getElementById('athleteGender').value : 'Putra';

    const getTodayFormatted = () => {
        const d = new Date();
        return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
    };
    const dateInput = document.getElementById('raceDate');
    const date = (dateInput && dateInput.value) ? dateInput.value : getTodayFormatted();

    const totalTime = document.getElementById('kpiTotalTime') ? document.getElementById('kpiTotalTime').innerText : '00.00.00';
    const avgSpeed = document.getElementById('kpiAvgSpeed') ? document.getElementById('kpiAvgSpeed').innerText : '0.00 m/s';

    const swolfVal = document.getElementById('valSwolf') ? document.getElementById('valSwolf').innerText : '0.0';
    const swolfNorm = document.getElementById('badgeSwolf') ? document.getElementById('badgeSwolf').innerText : '-';

    const dpsVal = document.getElementById('valDps') ? document.getElementById('valDps').innerText : '0.00 m';
    const dpsNorm = document.getElementById('badgeDps') ? document.getElementById('badgeDps').innerText : '-';

    const srVal = document.getElementById('valSr') ? document.getElementById('valSr').innerText : '0.0 SPM';
    const srNorm = document.getElementById('badgeSr') ? document.getElementById('badgeSr').innerText : '-';

    const pacing = document.getElementById('kpiPacingType') ? document.getElementById('kpiPacingType').innerText : '-';

    const timeInputs = document.querySelectorAll('.lap-time-input');
    const strokeInputs = document.querySelectorAll('.lap-stroke-input');
    const rows = document.querySelectorAll('#lapsTableBody tr');

    const lapCount = timeInputs.length;

    // Calibrated dynamic vertical scaling based on lap count to fit 1 page A4 perfectly (thin margins)
    let chartHeight = 290;
    let tablePad = '7px 6px';
    let cardPad = '9px 12px';
    let sectionGap = '8px';

    if (lapCount > 8) {
        chartHeight = 180;
        tablePad = '3.5px 4px';
        cardPad = '5px 6px';
        sectionGap = '4px';
    } else if (lapCount > 4) {
        chartHeight = 220;
        tablePad = '4.5px 5px';
        cardPad = '6px 8px';
        sectionGap = '6px';
    } else if (lapCount > 2) {
        chartHeight = 260;
        tablePad = '6px 6px';
        cardPad = '8px 10px';
        sectionGap = '7px';
    }

    let totalStrokesSum = 0;
    let lapRowsHtml = '';

    timeInputs.forEach((timeInp, idx) => {
        const lapNum = idx + 1;
        const startM = (lapNum - 1) * poolLength;
        const endM = lapNum * poolLength;
        const sec = parseTimeToSeconds(timeInp.value);
        const strokes = parseFloat(strokeInputs[idx]?.value) || 0;
        totalStrokesSum += strokes;

        const row = rows[idx];
        const vel = row?.querySelector('.lap-vel')?.innerText || '-';
        const sr = row?.querySelector('.lap-sr')?.innerText || '-';
        const dps = row?.querySelector('.lap-dps')?.innerText || '-';
        const swolf = row?.querySelector('.lap-swolf')?.innerText || '-';

        const bgRow = idx % 2 === 1 ? 'background-color:#f8fafc;' : 'background-color:#ffffff;';

        lapRowsHtml += `
            <tr style="${bgRow} border-bottom: 1px solid #cbd5e1;">
                <td style="padding: ${tablePad}; text-align:center; font-weight:bold; border-right: 1px solid #cbd5e1;">${lapNum}</td>
                <td style="padding: ${tablePad}; text-align:center; color:#475569; border-right: 1px solid #cbd5e1;">${startM} - ${endM}m</td>
                <td style="padding: ${tablePad}; text-align:center; font-weight:800; color:#0f172a; border-right: 1px solid #cbd5e1;">${formatSecondsToTime(sec)}</td>
                <td style="padding: ${tablePad}; text-align:center; font-weight:600; border-right: 1px solid #cbd5e1;">${strokes > 0 ? strokes : '-'}</td>
                <td style="padding: ${tablePad}; text-align:center; font-family:monospace; border-right: 1px solid #cbd5e1;">${vel}</td>
                <td style="padding: ${tablePad}; text-align:center; font-weight:700; color:#b45309; border-right: 1px solid #cbd5e1;">${sr}</td>
                <td style="padding: ${tablePad}; text-align:center; font-weight:700; color:#047857; border-right: 1px solid #cbd5e1;">${dps}</td>
                <td style="padding: ${tablePad}; text-align:center; font-weight:800; color:#1d4ed8; background-color:#eff6ff;">${swolf}</td>
            </tr>
        `;
    });

    const norms = getNormativeBenchmarks(age, gender, poolLength, strokeStyle);

    // Case-insensitive badge color helper
    const getBadgeColor = (text) => {
        if (!text || text === '-') return 'background:#f1f5f9; color:#475569;';
        const lower = text.toLowerCase();
        if (lower.includes('sangat baik')) return 'background:#059669; color:#ffffff;';
        if (lower.includes('baik') || lower.includes('optimal')) return 'background:#2563eb; color:#ffffff;';
        if (lower.includes('cukup') || lower.includes('sprint') || lower.includes('tinggi') || lower.includes('menengah')) return 'background:#d97706; color:#ffffff;';
        return 'background:#dc2626; color:#ffffff;';
    };

    // Format date DD/MM/YYYY
    let formattedDate = date;
    if (date && date.includes('-')) {
        const parts = date.split('-');
        if (parts.length === 3) {
            formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
    }

    // Chart Snapshot Image
    let chartImgHtml = '';
    if (chartInstance) {
        const chartImgData = chartInstance.toBase64Image();
        chartImgHtml = `
            <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; background: #ffffff; margin-top: ${sectionGap}; page-break-inside: avoid;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                    <span style="font-size: 10px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Grafik Tren Waktu Split & Kinematika per Jarak Putaran</span>
                    <span style="font-size: 9px; font-weight: 600; color: #64748b;">
                        <span style="color: #2563eb; font-weight: 900;">■</span> Waktu Split (s) &nbsp;&bull;&nbsp; 
                        <span style="color: #d97706; font-weight: 900;">■</span> SR (SPM) &nbsp;&bull;&nbsp; 
                        <span style="color: #7c3aed; font-weight: 900;">■</span> Laju (m/s) &nbsp;&bull;&nbsp; 
                        <span style="color: #059669; font-weight: 900;">■</span> DPS (m)
                    </span>
                </div>
                <div style="height: ${chartHeight}px; display:flex; align-items:center; justify-content:center; background: #ffffff; border-radius: 6px;">
                    <img src="${chartImgData}" style="max-height: ${chartHeight}px; width: 100%; object-fit: contain; margin: 0 auto; display:block;" alt="Grafik Pacing">
                </div>
            </div>
        `;
    }

    const printHtml = `
        <div class="print-sheet" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #0f172a;">
            
            <!-- 1. DATA ATLET & NOMOR LOMBA (2 BARIS TERSTRUKTUR - BEBAS OVERFLOW) -->
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; margin-bottom: 6px; page-break-inside: avoid; box-sizing: border-box;">
                <!-- Baris 1: Nama Atlet & Nomor Lomba -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px dashed #cbd5e1;">
                    <div style="display: flex; align-items: baseline; gap: 6px; overflow: hidden;">
                        <span style="color: #64748b; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">NAMA ATLET :</span>
                        <span style="font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">${escapeHtml(name)}</span>
                    </div>
                    <div style="display: flex; align-items: baseline; gap: 6px; text-align: right; flex-shrink: 0;">
                        <span style="color: #64748b; font-size: 9.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">NOMOR LOMBA :</span>
                        <span style="font-size: 13px; font-weight: 900; color: #1e3a8a;">${totalDistance}m ${escapeHtml(strokeStyle)}</span>
                        <span style="font-size: 10px; color: #64748b; font-weight: 700;">(Kolam ${poolLength}m)</span>
                    </div>
                </div>
                <!-- Baris 2: Umur / Gender / KU & Tanggal -->
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10px;">
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                        <span style="color: #64748b; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">UMUR / GENDER :</span>
                        <span style="font-weight: 700; color: #1e293b; white-space: nowrap;">${age !== '-' ? age + ' Tahun' : '-'} (${escapeHtml(gender)})</span>
                        <span style="color: #cbd5e1;">&bull;</span>
                        <span style="display: inline-block; color: #047857; font-weight: 800; background: #ecfdf5; padding: 2px 8px; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 9.5px; white-space: nowrap;">${escapeHtml(norms.tierName)}</span>
                    </div>
                    <div style="text-align: right; color: #64748b; font-size: 10px; flex-shrink: 0;">
                        <span style="color: #94a3b8; font-size: 9px; font-weight: 800; text-transform: uppercase;">TANGGAL :</span>
                        <span style="font-weight: 700; color: #334155; margin-left: 4px;">${formattedDate}</span>
                    </div>
                </div>
            </div>

            <!-- 2. TOTAL WAKTU, KECEPATAN & PACING -->
            <div style="background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 16px; margin-bottom: 8px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; page-break-inside: avoid;">
                <div>
                    <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">TOTAL WAKTU</span>
                    <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin-top: 2px;">${totalTime}</div>
                </div>
                <div>
                    <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">KECEPATAN RATA-RATA</span>
                    <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #0f172a; line-height: 1; margin-top: 2px;">${avgSpeed}</div>
                </div>
                <div>
                    <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block;">DISTRIBUSI PACING</span>
                    <div style="font-size: 11px; font-weight: 800; color: #1e293b; margin-top: 6px;">${pacing}</div>
                </div>
            </div>

            <!-- 3. TIGA KARTU NORMA EVALUASI -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: ${sectionGap}; page-break-inside: avoid;">
                
                <!-- SWOLF CARD -->
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">SWOLF Efisiensi</span>
                            <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(swolfNorm)}">${swolfNorm}</span>
                        </div>
                        <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${swolfVal}</div>
                    </div>
                    <div>
                        <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                        <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('sangat baik') ? 'background:#059669; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Sangat Baik</span> <span style="font-family: monospace;">&lt; ${norms.swolf.sangatBaik}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('baik') && !swolfNorm.toLowerCase().includes('sangat') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Baik (Optimal)</span> <span style="font-family: monospace;">${norms.swolf.sangatBaik} - ${norms.swolf.baik}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('cukup') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Cukup</span> <span style="font-family: monospace;">${norms.swolf.baik + 1} - ${norms.swolf.cukup}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${swolfNorm.toLowerCase().includes('kurang') ? 'background:#dc2626; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Kurang</span> <span style="font-family: monospace;">&gt; ${norms.swolf.cukup}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DPS CARD -->
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">DPS (Jarak/Kayuhan)</span>
                            <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(dpsNorm)}">${dpsNorm}</span>
                        </div>
                        <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${dpsVal}</div>
                    </div>
                    <div>
                        <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                        <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('sangat baik') ? 'background:#059669; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Sangat Baik</span> <span style="font-family: monospace;">&ge; ${norms.dps.sangatBaik.toFixed(2)} m</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('baik') && !dpsNorm.toLowerCase().includes('sangat') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Baik (Optimal)</span> <span style="font-family: monospace;">${norms.dps.baik.toFixed(2)} - ${(norms.dps.sangatBaik - 0.01).toFixed(2)} m</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('cukup') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Cukup</span> <span style="font-family: monospace;">${norms.dps.cukup.toFixed(2)} - ${(norms.dps.baik - 0.01).toFixed(2)} m</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${dpsNorm.toLowerCase().includes('kurang') ? 'background:#dc2626; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Kurang</span> <span style="font-family: monospace;">&lt; ${norms.dps.cukup.toFixed(2)} m</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STROKE RATE CARD -->
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-size: 9.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Irama Kayuhan (SR)</span>
                            <span style="padding: 1px 6px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; ${getBadgeColor(srNorm)}">${srNorm}</span>
                        </div>
                        <div style="font-family: 'Teko', sans-serif; font-size: 26px; font-weight: 900; color: #1e3a8a; line-height: 1; margin: 2px 0 4px;">${srVal}</div>
                    </div>
                    <div>
                        <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px;">SKALA NORMA STANDAR:</span>
                        <div style="display: flex; flex-direction: column; gap: 1.5px; font-size: 8.5px;">
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('sprint') || srNorm.toLowerCase().includes('tinggi') ? 'background:#d97706; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Sprint / Tinggi</span> <span style="font-family: monospace;">&gt; ${norms.sr.tinggi} SPM</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('optimal') || srNorm.toLowerCase().includes('menengah') ? 'background:#2563eb; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Optimal / Menengah</span> <span style="font-family: monospace;">${norms.sr.sedang} - ${norms.sr.tinggi} SPM</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1.5px 4px; border-radius: 3px; ${srNorm.toLowerCase().includes('tempo') || srNorm.toLowerCase().includes('rendah') ? 'background:#64748b; color:#ffffff; font-weight:800;' : 'color:#475569;'}">
                                <span>Tempo Rendah</span> <span style="font-family: monospace;">&lt; ${norms.sr.sedang} SPM</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 4. TABEL PUTARAN LAP -->
            <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; margin-top: ${sectionGap}; page-break-inside: avoid;">
                <thead>
                    <tr style="background-color: #0f172a; color: #ffffff;">
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 8%; border-right: 1px solid #334155;">Lap</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 14%; border-right: 1px solid #334155;">Jarak</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 16%; border-right: 1px solid #334155;">Waktu Split</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">Kayuhan</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">Laju (m/s)</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 13%; border-right: 1px solid #334155;">SR (SPM)</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 12%; border-right: 1px solid #334155;">DPS (m)</th>
                        <th style="padding: ${tablePad}; text-align: center; font-size: 9.5px; font-weight: 800; text-transform: uppercase; width: 13%;">SWOLF</th>
                    </tr>
                </thead>
                <tbody>
                    ${lapRowsHtml}
                    <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                        <td colspan="2" style="padding: ${tablePad}; text-align: center; font-weight: 800; font-size: 9.5px; text-transform: uppercase; border-right: 1px solid #cbd5e1;">TOTAL / RATA-RATA</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 900; color: #ea580c; font-size: 11px; border-right: 1px solid #cbd5e1;">${totalTime}</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 800; border-right: 1px solid #cbd5e1;">${totalStrokesSum > 0 ? totalStrokesSum : '-'}</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 800; border-right: 1px solid #cbd5e1;">${avgSpeed}</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 800; color: #b45309; border-right: 1px solid #cbd5e1;">${srVal}</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 800; color: #047857; border-right: 1px solid #cbd5e1;">${dpsVal}</td>
                        <td style="padding: ${tablePad}; text-align: center; font-weight: 900; color: #1d4ed8; font-size: 11px; background-color: #e0e7ff;">${swolfVal}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 5. EMBEDDED HIGH-RES GRAPH -->
            ${chartImgHtml}

            <!-- 6. FOOTER RESMI -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #cbd5e1; padding-top: 5px; margin-top: ${sectionGap}; font-size: 8px; color: #64748b; page-break-inside: avoid;">
                <div>Universal SET System &bull; Swimming Biomechanics & Race Performance Analysis &bull; www.setsystem.id</div>
                <div>Dicetak pada: ${formattedDate}</div>
            </div>

        </div>
    `;

    // Always update print-container in background as DOM reference
    const printContainer = document.getElementById('print-container');
    if (printContainer) printContainer.innerHTML = printHtml;

    // If preview_print=1 is in URL, render inline for automated testing/debugging
    if (window.location.search.includes('preview_print=1')) {
        document.querySelectorAll('.print-hidden').forEach(el => el.style.display = 'none');
        if (document.getElementById('navbar')) document.getElementById('navbar').style.display = 'none';
        if (document.querySelector('footer')) document.querySelector('footer').style.display = 'none';
        if (printContainer) {
            printContainer.classList.remove('hidden');
            printContainer.style.display = 'block';
        }
        return;
    }

    // STANDALONE NEW TAB / WINDOW GENERATION
    const rawAthleteName = (name && name !== '-') ? name.trim() : 'Atlet';
    const safeAthleteName = rawAthleteName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '').replace(/^_+|_+$/g, '');
    const safeDist = `${totalDistance}m`;
    const safeStroke = strokeStyle.trim().replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '').replace(/^_+|_+$/g, '');
    
    const dNow = new Date();
    let safeDate = String(dNow.getDate()).padStart(2, '0') + '-' + String(dNow.getMonth() + 1).padStart(2, '0') + '-' + dNow.getFullYear();
    if (date && date.includes('-')) {
        const dp = date.split('-');
        if (dp.length === 3) safeDate = `${dp[2]}-${dp[1]}-${dp[0]}`;
    } else if (date) {
        safeDate = date.replace(/[\/\\]/g, '-').replace(/[^a-zA-Z0-9_-]/g, '');
    }
    const fileBaseName = `${safeAthleteName}_${safeDist}_${safeStroke}_${safeDate}`;

    const fullPageHtml = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${fileBaseName}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"><\/script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0b1120;
            color: #0f172a;
            min-height: 100vh;
            padding: 16px 16px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .toolbar {
            position: sticky;
            top: 12px;
            z-index: 50;
            width: 100%;
            max-width: 820px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #f8fafc;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .toolbar-badge {
            background: #2563eb;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }
        .toolbar-title {
            font-size: 12px;
            font-weight: 700;
            color: #cbd5e1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-png {
            background: #059669;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        .btn-png:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        .btn-pdf {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-pdf:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-share {
            background: #7c3aed;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        .btn-share:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }
        .btn-close {
            background: #334155;
            color: #cbd5e1;
        }
        .btn-close:hover {
            background: #475569;
            color: #ffffff;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        #report-card {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
            border: 1px solid #cbd5e1;
        }
        @media (max-width: 640px) {
            body { padding: 8px 8px 24px; }
            .toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 10px; }
            .toolbar-actions { justify-content: space-between; overflow-x: auto; }
            .btn { padding: 6px 10px; font-size: 10px; }
            #report-card { padding: 10px 12px; border-radius: 8px; }
        }
        @media print {
            .no-print { display: none !important; }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            #report-card {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 4mm 6mm 4mm 6mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div class="toolbar-info">
            <span class="toolbar-badge">Race Analyzer</span>
            <span class="toolbar-title">${escapeHtml(name)} &bull; ${totalDistance}m ${escapeHtml(strokeStyle)}</span>
        </div>
        <div class="toolbar-actions">
            <button id="btn-png" class="btn btn-png" onclick="downloadPNG()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Simpan PNG</span>
            </button>
            <button class="btn btn-pdf" onclick="printPDF()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF</span>
            </button>
            <button id="btn-share" class="btn btn-share" onclick="shareReport()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Bagikan</span>
            </button>
            <button class="btn btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <div id="report-card">
        ${printHtml}
    </div>

    <script>
        const fileBaseName = "${fileBaseName}";

        function printPDF() {
            document.title = fileBaseName;
            window.print();
        }

        function downloadPNG() {
            const btn = document.getElementById('btn-png');
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<span>⏳ Memproses...</span>';
            btn.disabled = true;

            const element = document.getElementById('report-card');
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                windowWidth: element.scrollWidth
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = fileBaseName + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                btn.innerHTML = origHtml;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('Gagal mengekspor PNG. Silakan gunakan tangkapan layar (screenshot) browser.');
                btn.innerHTML = origHtml;
                btn.disabled = false;
            });
        }

        async function shareReport() {
            const element = document.getElementById('report-card');
            if (navigator.share) {
                const btn = document.getElementById('btn-share');
                const origHtml = btn.innerHTML;
                btn.innerHTML = '<span>⏳ Menyiapkan...</span>';
                btn.disabled = true;
                try {
                    const canvas = await html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff', scrollX: 0, scrollY: 0 });
                    canvas.toBlob(async (blob) => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                        if (!blob) {
                            downloadPNG();
                            return;
                        }
                        const file = new File([blob], fileBaseName + '.png', { type: 'image/png' });
                        if (navigator.canShare && navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                title: fileBaseName,
                                text: 'Laporan Analisis Performa Lomba Renang ' + fileBaseName,
                                files: [file]
                            });
                        } else {
                            await navigator.share({
                                title: fileBaseName,
                                text: 'Laporan Analisis Performa Lomba Renang ' + fileBaseName
                            });
                        }
                    }, 'image/png');
                } catch (err) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                    downloadPNG();
                }
            } else {
                downloadPNG();
            }
        }
    <\/script>
</body>
</html>`;

    if (reportWin) {
        reportWin.document.open();
        reportWin.document.write(fullPageHtml);
        reportWin.document.close();
        return;
    }

    // Fallback for popup blocked
    const originalTitle = document.title;
    document.title = fileBaseName;
    setTimeout(() => {
        window.print();
        setTimeout(() => {
            document.title = originalTitle;
        }, 1000);
    }, 150);
}

window.RaceReporter = {
    printReport
};
