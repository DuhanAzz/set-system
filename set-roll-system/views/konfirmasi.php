<!-- UI Konfirmasi Universal -->
<div id="universal-confirm-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="confirm-modal-box">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                ⚠️
            </div>
            <h3 class="text-xl font-black text-slate-900 mb-2" id="confirm-title">Konfirmasi Aksi</h3>
            <p class="text-sm font-medium text-slate-500 mb-6" id="confirm-message">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            <div class="flex gap-3 justify-center">
                <button type="button" onclick="closeConfirmModal()" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition w-1/2">
                    Batal
                </button>
                <button type="button" id="confirm-action-btn" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg shadow-orange-500/30 transition transform hover:-translate-y-0.5 w-1/2">
                    Ya, Lanjut
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let confirmTargetUrl = null;
    let confirmTargetForm = null;

    // Fungsi Utama yang dipanggil di tombol
    function confirmAction(e, message, target, type = 'url') {
        if (e) e.preventDefault();
        
        // Reset
        confirmTargetUrl = null;
        confirmTargetForm = null;

        // Set Data
        document.getElementById('confirm-message').innerText = message;
        if (type === 'url') {
            confirmTargetUrl = target;
        } else if (type === 'form') {
            confirmTargetForm = target;
        }

        // Tampilkan Modal
        const modal = document.getElementById('universal-confirm-modal');
        const box = document.getElementById('confirm-modal-box');
        
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        
        modal.classList.remove('opacity-0');
        box.classList.remove('scale-95');
        
        return false;
    }

    function closeConfirmModal() {
        const modal = document.getElementById('universal-confirm-modal');
        const box = document.getElementById('confirm-modal-box');
        
        modal.classList.add('opacity-0');
        box.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Aksi saat klik "Ya"
    document.getElementById('confirm-action-btn').addEventListener('click', function() {
        if (confirmTargetUrl) {
            window.location.href = confirmTargetUrl;
        } else if (confirmTargetForm) {
            const form = typeof confirmTargetForm === 'string' ? document.getElementById(confirmTargetForm) : confirmTargetForm;
            if (form) form.submit();
        }
        closeConfirmModal();
    });
</script>
