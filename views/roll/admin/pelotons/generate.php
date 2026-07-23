<div class="bg-slate-50 min-h-screen flex items-center justify-center p-6 mt-16 font-sans">
    <div class="bg-white p-10 rounded-[2rem] shadow-2xl max-w-md w-full text-center border border-slate-100">
        
        <div class="mb-6 relative">
            <div class="w-20 h-20 border-8 border-indigo-50 border-t-indigo-600 rounded-full animate-spin mx-auto"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-indigo-600 font-black text-sm" id="progressText">0%</span>
            </div>
        </div>

        <h2 class="text-2xl font-black text-slate-800 uppercase italic mb-2">Memproses Seeding</h2>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-8">Mohon jangan tutup halaman ini</p>

        <div class="bg-slate-900 rounded-2xl p-4 h-48 overflow-y-auto text-left flex flex-col-reverse custom-scrollbar" id="logContainer">
            <p class="text-[10px] font-mono text-emerald-400 opacity-50">> Inisialisasi engine seeding...</p>
        </div>

    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #334155; border-radius: 10px; }
    #logContainer p { margin-bottom: 4px; font-size: 10px; font-family: monospace; color: #4ade80; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classes = <?= json_encode($classes) ?>;
    let currentIndex = 0;
    const total = classes.length;
    const logContainer = document.getElementById('logContainer');
    const progressText = document.getElementById('progressText');

    function addLog(msg) {
        const p = document.createElement('p');
        p.innerText = "> " + msg;
        logContainer.prepend(p);
    }

    async function processNext() {
        if (currentIndex >= total) {
            addLog("SELESAI! Semua nomor perlombaan telah di-seeding.");
            progressText.innerText = "100%";
            setTimeout(() => {
                window.location.href = '<?= getenv("APP_URL") ?>/roll/admin/pelotons'; 
            }, 1500);
            return;
        }

        const cls = classes[currentIndex];
        const percent = Math.round(((currentIndex) / total) * 100);
        progressText.innerText = percent + "%";
        
        let className = "RACE " + String(cls.race_number).padStart(3, '0') + " - " + cls.distance_name + " " + cls.roller_name;
        addLog("Processing: " + className + "...");

        try {
            const response = await fetch("<?= getenv('APP_URL') ?>/roll/admin/pelotons/process?class_id=" + cls.class_id);
            const data = await response.json();
            
            if (data.success) {
                addLog("OK.");
            } else {
                addLog("ERROR: " + (data.message || "Unknown error"));
            }
        } catch (error) {
            addLog("FAILED API REQUEST");
        }

        currentIndex++;
        setTimeout(processNext, 100); // 100ms buffer 
    }

    if(total > 0) {
        addLog("Ditemukan " + total + " kelas untuk di-seeding.");
        processNext();
    } else {
        addLog("TIDAK ADA KELAS UNTUK EVENT INI!");
        progressText.innerText = "100%";
        setTimeout(() => {
            window.location.href = '<?= getenv("APP_URL") ?>/roll/admin/pelotons'; 
        }, 2000);
    }
});
</script>
