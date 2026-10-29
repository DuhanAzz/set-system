/**
 * Stopwatch Analisis Lomba Renang (Race Analyzer Stopwatch)
 * Real-time lap splitting, stroke counting, individual split deletion,
 * and automatic synchronization with Race Analysis report page.
 */

// State Global Stopwatch
let isRunning = false;
let startPerfTime = 0;
let accumulatedMs = 0;
let rafId = null;

// State Lomba
let poolLength = 50;
let totalDistance = 100;
let strokeStyle = 'Gaya Bebas';
let athleteName = '';
let targetLaps = 2;

// State Putaran & Kayuhan
let currentLapIndex = 1;
let currentLapStrokes = 0;
let recordedLaps = [];
let lastSplitCumulativeMs = 0;

// Broadcast Channel & Storage Sync
const SYNC_CHANNEL_NAME = 'race_stopwatch_channel';
let broadcastChannel = null;
try {
    broadcastChannel = new BroadcastChannel(SYNC_CHANNEL_NAME);
} catch (e) {
    console.warn('BroadcastChannel not supported in this browser, relying on localStorage.');
}

// FORMATTER HELPERS
function formatTimerDisplay(ms) {
    if (ms < 0) ms = 0;
    const totalSeconds = Math.floor(ms / 1000);
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    const cs = Math.floor((ms % 1000) / 10); // Centiseconds (0-99)

    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    const csStr = String(cs).padStart(2, '0');
    return `${mStr}:${sStr}.${csStr}`;
}

function formatInputSplit(ms) {
    if (ms < 0) ms = 0;
    const totalSeconds = Math.floor(ms / 1000);
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    const cs = Math.floor((ms % 1000) / 10);

    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    const csStr = String(cs).padStart(2, '0');
    return `${mStr}.${sStr}.${csStr}`;
}

// 1. LIFECYCLE & CONFIGURATION
function initStopwatch() {
    loadConfigFromStorage();
    setupEventListeners();
    updateUIElements();
    renderRecordTable();
}

function loadConfigFromStorage() {
    try {
        const storedCfg = localStorage.getItem('race_stopwatch_config');
        if (storedCfg) {
            const cfg = JSON.parse(storedCfg);
            if (cfg.athleteName) athleteName = cfg.athleteName;
            if (cfg.poolLength) poolLength = parseInt(cfg.poolLength) || 50;
            if (cfg.totalDistance) totalDistance = parseInt(cfg.totalDistance) || 100;
            if (cfg.strokeStyle) strokeStyle = cfg.strokeStyle;
        } else {
            const savedName = localStorage.getItem('race_analyzer_athlete_name');
            if (savedName) athleteName = savedName;
        }
    } catch (e) {}

    const nameInp = document.getElementById('cfgAthleteName');
    const poolInp = document.getElementById('cfgPoolLength');
    const distInp = document.getElementById('cfgTotalDistance');
    const strokeInp = document.getElementById('cfgStrokeStyle');

    if (nameInp) nameInp.value = athleteName;
    if (poolInp) poolInp.value = String(poolLength);
    if (distInp) distInp.value = String(totalDistance);
    if (strokeInp) strokeInp.value = strokeStyle;

    recalculateTargetLaps();
}

function recalculateTargetLaps() {
    targetLaps = Math.max(1, Math.round(totalDistance / poolLength));
    const badge = document.getElementById('cfgLapTargetBadge');
    if (badge) {
        badge.innerText = `Target: ${targetLaps} Lap (${totalDistance}m)`;
    }
    updateCurrentLapIndicator();
}

function updateRaceParameters() {
    const poolInp = document.getElementById('cfgPoolLength');
    const distInp = document.getElementById('cfgTotalDistance');
    const strokeInp = document.getElementById('cfgStrokeStyle');
    const nameInp = document.getElementById('cfgAthleteName');

    if (poolInp) poolLength = parseInt(poolInp.value) || 50;
    if (distInp) totalDistance = parseInt(distInp.value) || 100;
    if (strokeInp) strokeStyle = strokeInp.value;
    if (nameInp) athleteName = nameInp.value.trim();

    recalculateTargetLaps();
    saveCurrentConfig();
    sendDataToRaceAnalysis(false);
}

function saveCurrentConfig() {
    const nameInp = document.getElementById('cfgAthleteName');
    if (nameInp) athleteName = nameInp.value.trim();

    const cfg = {
        athleteName,
        poolLength,
        totalDistance,
        strokeStyle,
        targetLaps
    };
    try {
        localStorage.setItem('race_stopwatch_config', JSON.stringify(cfg));
    } catch (e) {}
}

// 2. STOPWATCH TIMER ENGINE
function updateTimer() {
    if (!isRunning) return;
    const now = performance.now();
    const currentMs = accumulatedMs + (now - startPerfTime);
    
    const displayEl = document.getElementById('displayMainTimer');
    if (displayEl) {
        displayEl.innerText = formatTimerDisplay(currentMs);
    }

    rafId = requestAnimationFrame(updateTimer);
}

function toggleStartStop() {
    const btn = document.getElementById('btnStartStop');
    const lbl = document.getElementById('lblStartStop');
    const icon = document.getElementById('iconStartStop');
    const btnSplit = document.getElementById('btnSplit');

    if (!isRunning) {
        // START TIMER
        isRunning = true;
        startPerfTime = performance.now();
        rafId = requestAnimationFrame(updateTimer);

        if (btn) {
            btn.className = 'col-span-1 min-h-[64px] sm:min-h-[76px] md:min-h-[82px] py-3.5 px-3 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 text-white rounded-2xl font-black text-sm sm:text-base uppercase tracking-wider shadow-lg shadow-rose-600/30 transition transform active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer';
        }
        if (lbl) lbl.innerText = 'STOP';
        if (icon) icon.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
        if (btnSplit) btnSplit.disabled = false;

    } else {
        // STOP / PAUSE TIMER
        isRunning = false;
        if (rafId) cancelAnimationFrame(rafId);
        accumulatedMs += (performance.now() - startPerfTime);

        if (btn) {
            btn.className = 'col-span-1 min-h-[64px] sm:min-h-[76px] md:min-h-[82px] py-3.5 px-3 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl font-black text-sm sm:text-base uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer';
        }
        if (lbl) lbl.innerText = 'LANJUT';
        if (icon) icon.innerHTML = '<path d="M8 5v14l11-7z"/>';
    }
}

function getCurrentTotalMs() {
    if (isRunning) {
        return accumulatedMs + (performance.now() - startPerfTime);
    }
    return accumulatedMs;
}

// 3. SPLIT & STROKE ACTIONS
function recordSplit() {
    const currentTotalMs = getCurrentTotalMs();
    if (currentTotalMs <= 0) return;

    const lapNum = recordedLaps.length + 1;
    const splitIntervalMs = Math.max(0, currentTotalMs - lastSplitCumulativeMs);

    const startM = (lapNum - 1) * poolLength;
    const endM = lapNum * poolLength;

    const lapEntry = {
        lap: lapNum,
        startM,
        endM,
        splitMs: splitIntervalMs,
        splitFormatted: formatInputSplit(splitIntervalMs),
        cumulativeMs: currentTotalMs,
        cumulativeFormatted: formatTimerDisplay(currentTotalMs),
        strokes: currentLapStrokes
    };

    recordedLaps.push(lapEntry);
    lastSplitCumulativeMs = currentTotalMs;

    // Reset stroke counter untuk putaran selanjutnya
    currentLapStrokes = 0;
    currentLapIndex = recordedLaps.length + 1;

    updateUIElements();
    renderRecordTable();
    sendDataToRaceAnalysis(false);

    // Cek apakah target lap sudah tercapai
    if (recordedLaps.length >= targetLaps) {
        if (isRunning) {
            toggleStartStop(); // Auto pause saat selesai
        }
        showToast(`🏁 Seluruh ${targetLaps} lap telah tercapai! Total: ${formatTimerDisplay(currentTotalMs)}`);
    } else {
        showToast(`⏱️ Lap ${lapNum} tercatat: ${lapEntry.splitFormatted} (${lapEntry.strokes} kayuhan)`);
    }
}

function incrementStroke() {
    currentLapStrokes++;
    updateStrokeDisplay();
}

function decrementStroke() {
    if (currentLapStrokes > 0) {
        currentLapStrokes--;
        updateStrokeDisplay();
    }
}

function updateStrokeDisplay() {
    const numEl = document.getElementById('currStrokeNum');
    const badgeEl = document.getElementById('activeLapStrokeBadge');
    if (numEl) numEl.innerText = currentLapStrokes;
    if (badgeEl) badgeEl.innerText = `🏊 ${currentLapStrokes} Kayuhan di Lap Ini`;
}

// 4. HAPUS REKAMAN LAP (DELETE SPLIT)
function deleteSplit(index) {
    if (index < 0 || index >= recordedLaps.length) return;

    const removedLap = recordedLaps[index];
    recordedLaps.splice(index, 1);

    // Rekalkulasi urutan lap & waktu interval split
    let runningCumMs = 0;
    recordedLaps.forEach((lap, i) => {
        lap.lap = i + 1;
        lap.startM = i * poolLength;
        lap.endM = (i + 1) * poolLength;
        lap.splitMs = Math.max(0, lap.cumulativeMs - runningCumMs);
        lap.splitFormatted = formatInputSplit(lap.splitMs);
        runningCumMs = lap.cumulativeMs;
    });

    lastSplitCumulativeMs = runningCumMs;
    currentLapIndex = recordedLaps.length + 1;

    updateUIElements();
    renderRecordTable();
    sendDataToRaceAnalysis(false);

    showToast(`🗑️ Lap ${removedLap.lap} berhasil dihapus.`);
}

function resetStopwatch() {
    if (isRunning) {
        if (!confirm('Stopwatch sedang berjalan. Yakin ingin mereset seluruh data waktu dan kayuhan?')) {
            return;
        }
        toggleStartStop();
    } else if (recordedLaps.length > 0) {
        if (!confirm('Yakin ingin mereset seluruh waktu split dan kayuhan yang terekam?')) {
            return;
        }
    }

    if (rafId) cancelAnimationFrame(rafId);
    isRunning = false;
    accumulatedMs = 0;
    startPerfTime = 0;
    currentLapIndex = 1;
    currentLapStrokes = 0;
    recordedLaps = [];
    lastSplitCumulativeMs = 0;

    const displayEl = document.getElementById('displayMainTimer');
    if (displayEl) displayEl.innerText = '00:00.00';

    const btn = document.getElementById('btnStartStop');
    const lbl = document.getElementById('lblStartStop');
    const icon = document.getElementById('iconStartStop');
    const btnSplit = document.getElementById('btnSplit');

    if (btn) {
        btn.className = 'col-span-1 min-h-[64px] sm:min-h-[76px] md:min-h-[82px] py-3.5 px-3 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white rounded-2xl font-black text-sm sm:text-base uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer';
    }
    if (lbl) lbl.innerText = 'MULAI';
    if (icon) icon.innerHTML = '<path d="M8 5v14l11-7z"/>';
    if (btnSplit) btnSplit.disabled = true;

    updateUIElements();
    renderRecordTable();
    sendDataToRaceAnalysis(false);

    showToast('🔄 Stopwatch telah direset.');
}

// 5. RENDER UI & TABLE
function updateUIElements() {
    updateCurrentLapIndicator();
    updateStrokeDisplay();

    const lblSplit = document.getElementById('lblSplit');
    if (lblSplit) {
        lblSplit.innerText = `SPLIT (LAP ${currentLapIndex})`;
    }

    const recCountEl = document.getElementById('recordedLapsCount');
    if (recCountEl) {
        recCountEl.innerText = recordedLaps.length;
    }
}

function updateCurrentLapIndicator() {
    const textEl = document.getElementById('currentLapStatusText');
    const dotEl = document.getElementById('lapDotIndicator');

    if (recordedLaps.length >= targetLaps) {
        if (textEl) textEl.innerText = `🏁 SELURUH TARGET TERCAPAI (${recordedLaps.length}/${targetLaps} LAP)`;
        if (dotEl) dotEl.className = 'w-2 h-2 rounded-full bg-emerald-400';
    } else {
        const startM = (currentLapIndex - 1) * poolLength;
        const endM = currentLapIndex * poolLength;
        if (textEl) textEl.innerText = `PUTARAN AKTIF: LAP ${currentLapIndex} (${startM} - ${endM}m)`;
        if (dotEl) dotEl.className = 'w-2 h-2 rounded-full bg-blue-500';
    }
}

function renderRecordTable() {
    const tbody = document.getElementById('stopwatchTableBody');
    if (!tbody) return;

    if (recordedLaps.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyRecordRow">
                <td colspan="6" class="py-6 text-slate-500 font-medium italic">
                    Belum ada putaran lap terekam. Tekan tombol <strong class="text-emerald-400">MULAI</strong> lalu <strong class="text-blue-400">SPLIT</strong> saat perenang menyelesaikan putaran lap.
                </td>
            </tr>
        `;
        return;
    }

    let rowsHtml = '';
    recordedLaps.forEach((lap, idx) => {
        rowsHtml += `
            <tr class="hover:bg-slate-800/60 transition">
                <td class="py-2.5 px-3 font-extrabold text-blue-400">Lap ${lap.lap}</td>
                <td class="py-2.5 px-3 text-slate-400 font-medium">${lap.startM} - ${lap.endM}m</td>
                <td class="py-2.5 px-3 font-mono font-bold text-white bg-slate-800/40">${lap.splitFormatted}</td>
                <td class="py-2.5 px-3 font-mono text-slate-400">${lap.cumulativeFormatted}</td>
                <td class="py-2.5 px-3 font-mono font-bold text-amber-400">${lap.strokes}</td>
                <td class="py-2.5 px-3">
                    <button type="button" 
                            onclick="deleteSplit(${idx})" 
                            title="Hapus waktu lap ini"
                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 rounded-lg text-xs font-bold border border-rose-500/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus</span>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = rowsHtml;
}

// 6. REAL-TIME SYNCHRONIZATION WITH RACE ANALYSIS
function sendDataToRaceAnalysis(shouldFocusOpener = false) {
    saveCurrentConfig();

    const payload = {
        athleteName,
        poolLength,
        totalDistance,
        strokeStyle,
        targetLaps,
        laps: recordedLaps.map(l => ({
            lap: l.lap,
            splitFormatted: l.splitFormatted,
            cumulativeFormatted: l.cumulativeFormatted,
            strokes: l.strokes
        })),
        timestamp: Date.now()
    };

    // 1. Simpan ke LocalStorage agar langsung dibaca tab lain via storage event
    try {
        localStorage.setItem('race_stopwatch_result', JSON.stringify(payload));
    } catch (e) {}

    // 2. BroadcastChannel untuk komunikasi seketika (instant)
    if (broadcastChannel) {
        try {
            broadcastChannel.postMessage(payload);
        } catch (e) {}
    }

    if (shouldFocusOpener) {
        showToast('✅ Mengirim data & mengalihkan ke Analisis Lomba...');

        // 1. Coba tutup jendela stopwatch jika dibuka via popup/tab oleh Analisis Lomba
        let closedSuccessfully = false;
        if (window.opener && !window.opener.closed) {
            try {
                window.opener.focus();
            } catch (e) {}
            try {
                window.close();
                closedSuccessfully = true;
            } catch (e) {}
        } else {
            try {
                window.close();
            } catch (e) {}
        }

        // 2. Fallback untuk iPad/Mobile Safari/Chrome yang membatasi window.close()
        // Mengalihkan tab saat ini kembali ke halaman Analisis Lomba dengan replace (tanpa loop back)
        setTimeout(() => {
            try {
                window.close();
            } catch (e) {}

            const targetUrl = (window.APP_URL || '') + '/tools/race-analysis';
            // Hanya redirect jika tab belum tertutup
            window.location.replace(targetUrl);
        }, 150);
    }
}

// 7. TOAST NOTIFICATION
let toastTimer = null;
function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    const msgEl = document.getElementById('toastMsg');
    if (!toast || !msgEl) return;

    msgEl.innerText = msg;
    toast.classList.remove('translate-y-24', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-24', 'opacity-0');
    }, 2500);
}

// 8. KEYBOARD SHORTCUTS
function setupEventListeners() {
    window.addEventListener('keydown', (e) => {
        // Jangan tangkap jika sedang mengetik di input form
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;

        if (e.code === 'Space') {
            e.preventDefault();
            toggleStartStop();
        } else if (e.key === 'l' || e.key === 'L' || e.key === 's' || e.key === 'S') {
            e.preventDefault();
            if (isRunning || accumulatedMs > 0) recordSplit();
        } else if (e.key === 'k' || e.key === 'K') {
            e.preventDefault();
            incrementStroke();
        } else if (e.key === 'u' || e.key === 'U' || e.key === 'Backspace') {
            e.preventDefault();
            decrementStroke();
        }
    });
}

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    initStopwatch();
});
