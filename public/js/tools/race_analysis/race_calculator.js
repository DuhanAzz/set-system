/**
 * Race Analysis Core Calculator & State Manager
 * Mengelola state input atlet, tabel putaran lap, formatting waktu, navigasi keyboard,
 * kalkulasi kinematika renang (Laju, SR, DPS, SWOLF, Pacing), serta evaluasi norma.
 */

// State Global
let poolLength = 50;
let strokeStyle = 'Gaya Bebas';
let totalDistance = 100;
let chartInstance = null;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function saveAthleteData() {
    try {
        const nameInp = document.getElementById('athleteName');
        const ageInp = document.getElementById('athleteAge');
        const genderInp = document.getElementById('athleteGender');
        if (nameInp) localStorage.setItem('race_analyzer_athlete_name', nameInp.value);
        if (ageInp) localStorage.setItem('race_analyzer_athlete_age', ageInp.value);
        if (genderInp) localStorage.setItem('race_analyzer_athlete_gender', genderInp.value);
    } catch (e) {}
}

function restoreAthleteData() {
    try {
        const savedName = localStorage.getItem('race_analyzer_athlete_name');
        const nameInp = document.getElementById('athleteName');
        if (savedName && nameInp && !nameInp.value) {
            nameInp.value = savedName;
        }
        const savedAge = localStorage.getItem('race_analyzer_athlete_age');
        const ageInp = document.getElementById('athleteAge');
        if (savedAge && ageInp) {
            ageInp.value = savedAge;
        }
        const savedGender = localStorage.getItem('race_analyzer_athlete_gender');
        const genderInp = document.getElementById('athleteGender');
        if (savedGender && genderInp) {
            genderInp.value = savedGender;
        }
    } catch (e) {}
}

// 1. CONFIGURATION HANDLERS
function setPoolLength(len) {
    poolLength = parseInt(len);
    const btn50 = document.getElementById('btnPool50');
    const btn25 = document.getElementById('btnPool25');
    if (btn50) {
        btn50.className = poolLength === 50 
            ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
            : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
    }
    if (btn25) {
        btn25.className = poolLength === 25 
            ? 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-blue-600 border-blue-600 text-white shadow-sm'
            : 'py-2.5 px-3 rounded-xl border text-xs font-bold uppercase transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
    }

    generateLapInputs();
    calculateAll();
}

function setStrokeStyle(styleName) {
    strokeStyle = styleName;
    document.querySelectorAll('.btn-stroke').forEach(btn => {
        const targetStroke = btn.getAttribute('data-stroke') || '';
        const btnText = btn.innerText.trim();
        const isMatch = (targetStroke && targetStroke === styleName)
            || styleName.includes(btnText)
            || (styleName.startsWith('Ganti') && btnText.includes('Ganti'));

        if (isMatch) {
            btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm';
        } else {
            btn.className = 'btn-stroke py-2 px-2 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
        }
    });
    calculateAll();
}

function setDistance(dist) {
    totalDistance = parseInt(dist);
    document.querySelectorAll('.btn-dist').forEach(btn => {
        const btnDist = parseInt(btn.getAttribute('data-dist') || btn.innerText);
        if (btnDist === totalDistance) {
            btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-blue-600 border-blue-600 text-white shadow-sm';
        } else {
            btn.className = 'btn-dist py-2 px-1 rounded-xl border text-xs font-bold transition bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200';
        }
    });
    generateLapInputs();
    calculateAll();
}

// 2. GENERATE LAP INPUTS & TIME FORMATTING
function generateLapInputs() {
    const lapCount = Math.max(1, Math.round(totalDistance / poolLength));
    const badge = document.getElementById('lapCountBadge');
    if (badge) badge.innerText = `${lapCount} Lap (${totalDistance}m)`;

    const tbody = document.getElementById('lapsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    for (let i = 1; i <= lapCount; i++) {
        const startM = (i - 1) * poolLength;
        const endM = i * poolLength;

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition';
        tr.innerHTML = `
            <td class="py-2 px-2 font-bold text-slate-700">${i}</td>
            <td class="py-2 px-2 text-slate-500 font-medium">${startM}-${endM}m</td>
            <td class="py-1.5 px-2">
                <input type="text" 
                        class="lap-time-input w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs" 
                        data-lap="${i}" 
                        placeholder="00.00.00" 
                        maxlength="8"
                        oninput="formatTimeInput(this)" 
                        onblur="normalizeTimeInput(this)"
                        onkeydown="handleKeyNav(event, ${i}, 'time')">
            </td>
            <td class="py-1.5 px-2">
                <input type="number" 
                        class="lap-stroke-input w-full bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-center font-mono font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-blue-500 text-xs" 
                        data-lap="${i}" 
                        placeholder="Kayuhan" 
                        min="1" max="150"
                        oninput="calculateAll()" 
                        onkeydown="handleKeyNav(event, ${i}, 'stroke')">
            </td>
            <td class="py-2 px-2 font-mono text-slate-700 lap-vel">-</td>
            <td class="py-2 px-2 font-mono text-slate-700 font-bold lap-sr">-</td>
            <td class="py-2 px-2 font-mono text-slate-700 font-bold lap-dps">-</td>
            <td class="py-2 px-2 font-mono text-blue-900 font-black lap-swolf">-</td>
        `;
        tbody.appendChild(tr);
    }
}

function formatTimeInput(input) {
    let val = input.value.replace(/\D/g, ''); 
    if (val.length > 6) val = val.substring(0, 6); 

    let formatted = '';
    if (val.length > 0) formatted += val.substring(0, 2);
    if (val.length > 2) formatted += '.' + val.substring(2, 4);
    if (val.length > 4) formatted += '.' + val.substring(4, 6);

    input.value = formatted;
    calculateAll();
}

function normalizeTimeInput(input) {
    let val = input.value.trim();
    if (!val) {
        calculateAll();
        return;
    }
    let digits = val.replace(/\D/g, '');
    if (digits.length === 0) {
        input.value = '';
        calculateAll();
        return;
    }

    let m = 0, s = 0, cs = 0;
    if (digits.length <= 2) {
        s = parseInt(digits, 10) || 0;
    } else if (digits.length <= 4) {
        s = parseInt(digits.substring(0, 2), 10) || 0;
        cs = parseInt(digits.substring(2, 4), 10) || 0;
    } else {
        let padded = digits.padStart(6, '0');
        m = parseInt(padded.substring(0, 2), 10) || 0;
        s = parseInt(padded.substring(2, 4), 10) || 0;
        cs = parseInt(padded.substring(4, 6), 10) || 0;
    }

    if (cs >= 100) { cs = 0; s++; }
    if (s >= 60 && digits.length <= 4) {
        m += Math.floor(s / 60);
        s = s % 60;
    }

    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    const csStr = String(cs).padStart(2, '0');
    input.value = `${mStr}.${sStr}.${csStr}`;
    calculateAll();
}

function handleKeyNav(e, lapIdx, type) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (type === 'time') {
            normalizeTimeInput(e.target);
            const strokeInput = document.querySelector(`.lap-stroke-input[data-lap="${lapIdx}"]`);
            if (strokeInput) strokeInput.focus();
        } else if (type === 'stroke') {
            const nextTimeInput = document.querySelector(`.lap-time-input[data-lap="${lapIdx + 1}"]`);
            if (nextTimeInput) {
                nextTimeInput.focus();
            } else {
                e.target.blur();
            }
        }
    }
}

function parseTimeToSeconds(val) {
    if (!val) return 0;
    val = val.toString().trim().replace(',', '.');
    if (val.includes(':')) {
        const parts = val.split(':');
        const min = parseFloat(parts[0]) || 0;
        const sec = parseFloat(parts[1]) || 0;
        return (min * 60) + sec;
    }
    if (val.includes('.')) {
        const parts = val.split('.');
        if (parts.length === 3) {
            const min = parseFloat(parts[0]) || 0;
            const sec = parseFloat(parts[1]) || 0;
            const cs = parseFloat(parts[2]) || 0;
            return (min * 60) + sec + (cs / 100);
        } else if (parts.length === 2) {
            const sec = parseFloat(parts[0]) || 0;
            const cs = parseFloat(parts[1]) || 0;
            return sec + (cs / 100);
        }
    }
    return parseFloat(val) || 0;
}

function formatSecondsToTime(totalSec) {
    if (!totalSec || isNaN(totalSec) || totalSec <= 0) return '00.00.00';
    let mins = Math.floor(totalSec / 60);
    let remainingSec = totalSec % 60;
    let secs = Math.floor(remainingSec);
    let cs = Math.round((remainingSec - secs) * 100);

    if (cs >= 100) { cs = 0; secs++; }
    if (secs >= 60) { secs = 0; mins++; }

    const mStr = String(mins).padStart(2, '0');
    const sStr = String(secs).padStart(2, '0');
    const csStr = String(cs).padStart(2, '0');
    return `${mStr}.${sStr}.${csStr}`;
}

// 3. CALCULATION & NORMA HIGHLIGHT
function calculateAll() {
    const timeInputs = document.querySelectorAll('.lap-time-input');
    const strokeInputs = document.querySelectorAll('.lap-stroke-input');
    const rows = document.querySelectorAll('#lapsTableBody tr');

    const ageEl = document.getElementById('athleteAge');
    const genderEl = document.getElementById('athleteGender');
    const ageVal = ageEl ? ageEl.value : 15;
    const genderVal = genderEl ? genderEl.value : 'Putra';
    const norms = getNormativeBenchmarks(ageVal, genderVal, poolLength, strokeStyle);

    const activeTierBadge = document.getElementById('activeTierBadge');
    if (activeTierBadge) {
        activeTierBadge.innerText = `Norma: ${norms.tierName}`;
    }

    // Update Dynamic Scale Rows in UI
    const swolfBox = document.getElementById('normaSwolfBox');
    if (swolfBox) {
        swolfBox.innerHTML = `
            <div class="norma-item flex justify-between p-1 rounded" id="swolf_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&lt; ${norms.swolf.sangatBaik}</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="swolf_baik"><span>Baik (Optimal)</span> <span class="font-mono">${norms.swolf.sangatBaik} - ${norms.swolf.baik}</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="swolf_cukup"><span>Cukup</span> <span class="font-mono">${norms.swolf.baik + 1} - ${norms.swolf.cukup}</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="swolf_kurang"><span>Kurang</span> <span class="font-mono">&gt; ${norms.swolf.cukup}</span></div>
        `;
    }

    const dpsBox = document.getElementById('normaDpsBox');
    if (dpsBox) {
        dpsBox.innerHTML = `
            <div class="norma-item flex justify-between p-1 rounded" id="dps_sangat_baik"><span>Sangat Baik</span> <span class="font-mono">&ge; ${norms.dps.sangatBaik.toFixed(2)} m</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="dps_baik"><span>Baik (Optimal)</span> <span class="font-mono">${norms.dps.baik.toFixed(2)} - ${(norms.dps.sangatBaik - 0.01).toFixed(2)} m</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="dps_cukup"><span>Cukup</span> <span class="font-mono">${norms.dps.cukup.toFixed(2)} - ${(norms.dps.baik - 0.01).toFixed(2)} m</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="dps_kurang"><span>Kurang</span> <span class="font-mono">&lt; ${norms.dps.cukup.toFixed(2)} m</span></div>
        `;
    }

    const srBox = document.getElementById('normaSrBox');
    if (srBox) {
        srBox.innerHTML = `
            <div class="norma-item flex justify-between p-1 rounded" id="sr_tinggi"><span>Sprint / Tinggi</span> <span class="font-mono">&gt; ${norms.sr.tinggi} SPM</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="sr_sedang"><span>Optimal / Menengah</span> <span class="font-mono">${norms.sr.sedang} - ${norms.sr.tinggi} SPM</span></div>
            <div class="norma-item flex justify-between p-1 rounded" id="sr_rendah"><span>Tempo Rendah</span> <span class="font-mono">&lt; ${norms.sr.sedang} SPM</span></div>
        `;
    }

    let totalTimeSec = 0;
    let validLapCount = 0;

    let lapTimes = [0];
    let lapSRs = [0];
    let lapVels = [0];
    let lapDPSs = [0];
    let lapSwolfs = [0];
    let lapLabels = ['0m'];

    timeInputs.forEach((timeInp, idx) => {
        const lapNum = idx + 1;
        const strokeInp = strokeInputs[idx];
        const row = rows[idx];
        if (!row) return;

        const sec = parseTimeToSeconds(timeInp.value);
        const strokes = parseFloat(strokeInp.value) || 0;

        const velCell = row.querySelector('.lap-vel');
        const srCell = row.querySelector('.lap-sr');
        const dpsCell = row.querySelector('.lap-dps');
        const swolfCell = row.querySelector('.lap-swolf');

        const endM = lapNum * poolLength;
        lapLabels.push(`${endM}m`);

        if (sec > 0 && strokes > 0) {
            validLapCount++;
            totalTimeSec += sec;

            const vel = poolLength / sec;
            const sr = (strokes / sec) * 60;
            const dps = poolLength / strokes;
            const swolf = sec + strokes;

            if (velCell) velCell.innerText = vel.toFixed(2);
            if (srCell) srCell.innerText = sr.toFixed(1);
            if (dpsCell) dpsCell.innerText = dps.toFixed(2);
            if (swolfCell) swolfCell.innerText = swolf.toFixed(1);

            lapTimes.push(parseFloat(sec.toFixed(2)));
            lapSRs.push(parseFloat(sr.toFixed(1)));
            lapVels.push(parseFloat(vel.toFixed(2)));
            lapDPSs.push(parseFloat(dps.toFixed(2)));
            lapSwolfs.push(parseFloat(swolf.toFixed(1)));
        } else if (sec > 0) {
            const vel = poolLength / sec;
            if (velCell) velCell.innerText = vel.toFixed(2);
            if (srCell) srCell.innerText = '-';
            if (dpsCell) dpsCell.innerText = '-';
            if (swolfCell) swolfCell.innerText = '-';

            lapTimes.push(parseFloat(sec.toFixed(2)));
            lapSRs.push(0);
            lapVels.push(parseFloat(vel.toFixed(2)));
            lapDPSs.push(0);
            lapSwolfs.push(0);
        } else {
            if (velCell) velCell.innerText = '-';
            if (srCell) srCell.innerText = '-';
            if (dpsCell) dpsCell.innerText = '-';
            if (swolfCell) swolfCell.innerText = '-';

            lapTimes.push(0);
            lapSRs.push(0);
            lapVels.push(0);
            lapDPSs.push(0);
            lapSwolfs.push(0);
        }
    });

    // RESET HIGHLIGHT NORMA
    document.querySelectorAll('.norma-item').forEach(el => {
        el.className = 'norma-item flex justify-between p-1 rounded text-slate-600 bg-transparent';
    });

    if (validLapCount > 0) {
        const avgSwolf = lapSwolfs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
        const avgDps = lapDPSs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
        const avgSr = lapSRs.filter(x => x > 0).reduce((a, b) => a + b, 0) / validLapCount;
        const avgSpeed = (poolLength * validLapCount) / totalTimeSec;

        const valSwolfEl = document.getElementById('valSwolf');
        const valDpsEl = document.getElementById('valDps');
        const valSrEl = document.getElementById('valSr');
        const kpiTotalTimeEl = document.getElementById('kpiTotalTime');
        const kpiAvgSpeedEl = document.getElementById('kpiAvgSpeed');

        if (valSwolfEl) valSwolfEl.innerText = avgSwolf.toFixed(1);
        if (valDpsEl) valDpsEl.innerText = `${avgDps.toFixed(2)} m`;
        if (valSrEl) valSrEl.innerText = `${avgSr.toFixed(1)} SPM`;
        if (kpiTotalTimeEl) kpiTotalTimeEl.innerText = formatSecondsToTime(totalTimeSec);
        if (kpiAvgSpeedEl) kpiAvgSpeedEl.innerText = `${avgSpeed.toFixed(2)} m/s`;

        // EVALUATE NORMA SWOLF
        const badgeSwolf = document.getElementById('badgeSwolf');
        if (badgeSwolf) {
            let swolfCategory = '';
            let activeSwolfId = '';

            if (avgSwolf < norms.swolf.sangatBaik) {
                swolfCategory = 'Sangat Baik';
                activeSwolfId = 'swolf_sangat_baik';
                badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
            } else if (avgSwolf <= norms.swolf.baik) {
                swolfCategory = 'Baik (Optimal)';
                activeSwolfId = 'swolf_baik';
                badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
            } else if (avgSwolf <= norms.swolf.cukup) {
                swolfCategory = 'Cukup';
                activeSwolfId = 'swolf_cukup';
                badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
            } else {
                swolfCategory = 'Kurang';
                activeSwolfId = 'swolf_kurang';
                badgeSwolf.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
            }
            badgeSwolf.innerText = swolfCategory;
            const activeSwolfEl = document.getElementById(activeSwolfId);
            if (activeSwolfEl) {
                activeSwolfEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
            }
        }

        // EVALUATE NORMA DPS
        const badgeDps = document.getElementById('badgeDps');
        if (badgeDps) {
            let dpsCategory = '';
            let activeDpsId = '';

            if (avgDps >= norms.dps.sangatBaik) {
                dpsCategory = 'Sangat Baik';
                activeDpsId = 'dps_sangat_baik';
                badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800';
            } else if (avgDps >= norms.dps.baik) {
                dpsCategory = 'Baik (Optimal)';
                activeDpsId = 'dps_baik';
                badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
            } else if (avgDps >= norms.dps.cukup) {
                dpsCategory = 'Cukup';
                activeDpsId = 'dps_cukup';
                badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
            } else {
                dpsCategory = 'Kurang';
                activeDpsId = 'dps_kurang';
                badgeDps.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800';
            }
            badgeDps.innerText = dpsCategory;
            const activeDpsEl = document.getElementById(activeDpsId);
            if (activeDpsEl) {
                activeDpsEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
            }
        }

        // EVALUATE NORMA STROKE RATE
        const badgeSr = document.getElementById('badgeSr');
        if (badgeSr) {
            let srCategory = '';
            let activeSrId = '';

            if (avgSr > norms.sr.tinggi) {
                srCategory = 'Sprint / Tinggi';
                activeSrId = 'sr_tinggi';
                badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800';
            } else if (avgSr >= norms.sr.sedang) {
                srCategory = 'Optimal / Menengah';
                activeSrId = 'sr_sedang';
                badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800';
            } else {
                srCategory = 'Tempo Rendah';
                activeSrId = 'sr_rendah';
                badgeSr.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase bg-slate-200 text-slate-800';
            }
            badgeSr.innerText = srCategory;
            const activeSrEl = document.getElementById(activeSrId);
            if (activeSrEl) {
                activeSrEl.className = 'norma-item flex justify-between p-1 rounded font-bold bg-blue-600 text-white shadow-sm';
            }
        }

        // PACING EVALUATION
        const pacingEl = document.getElementById('kpiPacingType');
        if (pacingEl) {
            if (validLapCount >= 2) {
                const firstHalfTime = lapTimes[1];
                const secondHalfTime = lapTimes[validLapCount];
                const diffPct = ((secondHalfTime - firstHalfTime) / firstHalfTime) * 100;

                if (secondHalfTime < firstHalfTime) {
                    pacingEl.innerHTML = `<span class="text-emerald-700 font-bold">Negative Split (${Math.abs(diffPct).toFixed(1)}% Lebih Cepat di Akhir)</span>`;
                } else if (diffPct <= 3) {
                    pacingEl.innerHTML = `<span class="text-blue-700 font-bold">Even Pace (Stabil Antar Lap, Drop ${diffPct.toFixed(1)}%)</span>`;
                } else {
                    pacingEl.innerHTML = `<span class="text-amber-700 font-bold">Positive Split (Drop-off +${diffPct.toFixed(1)}% di Akhir)</span>`;
                }
            } else {
                pacingEl.innerText = '1 Putaran Lap';
            }
        }

    } else {
        const valSwolfEl = document.getElementById('valSwolf');
        const valDpsEl = document.getElementById('valDps');
        const valSrEl = document.getElementById('valSr');
        const kpiTotalTimeEl = document.getElementById('kpiTotalTime');
        const kpiAvgSpeedEl = document.getElementById('kpiAvgSpeed');
        const kpiPacingTypeEl = document.getElementById('kpiPacingType');
        const badgeSwolf = document.getElementById('badgeSwolf');
        const badgeDps = document.getElementById('badgeDps');
        const badgeSr = document.getElementById('badgeSr');

        if (valSwolfEl) valSwolfEl.innerText = '0.0';
        if (valDpsEl) valDpsEl.innerText = '0.00 m';
        if (valSrEl) valSrEl.innerText = '0.0 SPM';
        if (kpiTotalTimeEl) kpiTotalTimeEl.innerText = '00.00.00';
        if (kpiAvgSpeedEl) kpiAvgSpeedEl.innerText = '0.00 m/s';
        if (kpiPacingTypeEl) kpiPacingTypeEl.innerText = '-';
        if (badgeSwolf) badgeSwolf.innerText = '-';
        if (badgeDps) badgeDps.innerText = '-';
        if (badgeSr) badgeSr.innerText = '-';
    }

    if (typeof updateChart === 'function') {
        updateChart(lapLabels, lapTimes, lapSRs, lapVels, lapDPSs);
    }
}

// 6. STOPWATCH INTEGRATION & REAL-TIME SYNCHRONIZATION
function openRaceStopwatch() {
    saveAthleteData();

    const nameInp = document.getElementById('athleteName');
    const ageInp = document.getElementById('athleteAge');
    const genderInp = document.getElementById('athleteGender');

    const config = {
        athleteName: nameInp ? nameInp.value.trim() : '',
        athleteAge: ageInp ? ageInp.value : '',
        athleteGender: genderInp ? genderInp.value : 'Putra',
        poolLength: poolLength,
        totalDistance: totalDistance,
        strokeStyle: strokeStyle
    };

    try {
        localStorage.setItem('race_stopwatch_config', JSON.stringify(config));
    } catch (e) {}

    const baseUrl = window.APP_URL || '';
    const stopwatchUrl = baseUrl + '/tools/race-stopwatch';
    
    // Buka di jendela popup yang ideal untuk stopwatch jika layar desktop, atau tab baru jika mobile
    const winWidth = 650;
    const winHeight = 780;
    const left = (window.screen.width / 2) - (winWidth / 2);
    const top = (window.screen.height / 2) - (winHeight / 2);

    window.open(stopwatchUrl, 'RaceStopwatchWindow', `width=${winWidth},height=${winHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`);
}

function applyStopwatchData(payload) {
    if (!payload || !Array.isArray(payload.laps)) return;

    // Perbarui konfigurasi lomba jika stopwatch merubahnya
    let needRegenerate = false;
    if (payload.poolLength && parseInt(payload.poolLength) !== poolLength) {
        poolLength = parseInt(payload.poolLength);
        needRegenerate = true;
    }
    if (payload.totalDistance && parseInt(payload.totalDistance) !== totalDistance) {
        totalDistance = parseInt(payload.totalDistance);
        needRegenerate = true;
    }
    if (payload.strokeStyle && payload.strokeStyle !== strokeStyle) {
        setStrokeStyle(payload.strokeStyle);
    }
    if (needRegenerate) {
        generateLapInputs();
    }

    if (payload.athleteName) {
        const nameInp = document.getElementById('athleteName');
        if (nameInp) nameInp.value = payload.athleteName;
        saveAthleteData();
    }

    // Suntikkan waktu split dan kayuhan ke baris tabel yang bersesuaian
    const timeInputs = document.querySelectorAll('.lap-time-input');
    const strokeInputs = document.querySelectorAll('.lap-stroke-input');

    // Kosongkan dahulu agar jika ada split yang dihapus di stopwatch, tidak tertinggal data lama
    timeInputs.forEach(inp => inp.value = '');
    strokeInputs.forEach(inp => inp.value = '');

    payload.laps.forEach((lap, idx) => {
        if (timeInputs[idx]) {
            timeInputs[idx].value = lap.splitFormatted || '';
        }
        if (strokeInputs[idx]) {
            strokeInputs[idx].value = (lap.strokes !== undefined && lap.strokes !== null && lap.strokes !== '') ? lap.strokes : '';
        }
    });

    calculateAll();
    showSyncNotification(`⏱️ Data waktu split & kayuhan dari Stopwatch berhasil disinkronkan (${payload.laps.length} Lap)!`);
}

let syncNotificationTimer = null;
function showSyncNotification(msg) {
    let toast = document.getElementById('raceSyncToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'raceSyncToast';
        toast.className = 'fixed bottom-6 right-6 z-50 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none';
        toast.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <span id="raceSyncToastMsg">${escapeHtml(msg)}</span>
        `;
        document.body.appendChild(toast);
    } else {
        const msgEl = document.getElementById('raceSyncToastMsg');
        if (msgEl) msgEl.innerText = msg;
    }

    // Tampilkan Toast
    setTimeout(() => {
        toast.classList.remove('translate-y-24', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    }, 10);

    if (syncNotificationTimer) clearTimeout(syncNotificationTimer);
    syncNotificationTimer = setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-24', 'opacity-0');
    }, 3000);
}

// Pasang Listener Komunikasi Real-time (BroadcastChannel + Storage Event)
function initStopwatchSyncListener() {
    try {
        const bc = new BroadcastChannel('race_stopwatch_channel');
        bc.onmessage = (event) => {
            if (event && event.data) {
                applyStopwatchData(event.data);
            }
        };
    } catch (e) {
        console.warn('BroadcastChannel error/unsupported:', e);
    }

    window.addEventListener('storage', (e) => {
        if (e.key === 'race_stopwatch_result' && e.newValue) {
            try {
                const payload = JSON.parse(e.newValue);
                applyStopwatchData(payload);
            } catch (err) {}
        }
    });
}

// Inisialisasi Lifecycle Aplikasi
document.addEventListener('DOMContentLoaded', () => {
    restoreAthleteData();
    if (typeof initChart === 'function') initChart();
    generateLapInputs();
    calculateAll();
    initStopwatchSyncListener();

    if (window.location.search.includes('preview_print=1')) {
        setTimeout(() => {
            if (typeof printReport === 'function') printReport();
        }, 400);
    }
});
