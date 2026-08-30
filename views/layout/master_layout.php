<?php
// FILE: views/layout/master_layout.php
?>
<!-- MASTER LAYOUT WRAPPER -->
<?php 
$uri = $_SERVER['REQUEST_URI'];
$module = 'core'; // default

if (strpos($uri, '/swim/') !== false) {
    $module = 'swim';
} elseif (strpos($uri, '/roll/') !== false) {
    $module = 'roll';
}

// Fallback logic in case roll files don't exist yet
$topbarFile = __DIR__ . "/topbar_{$module}.php";
if (!file_exists($topbarFile)) $topbarFile = __DIR__ . '/topbar_core.php';

$sidebarFile = __DIR__ . "/sidebar_{$module}.php";
if (!file_exists($sidebarFile)) $sidebarFile = __DIR__ . '/sidebar_core.php';

include $topbarFile;
include $sidebarFile;
?>
<!-- MAIN CONTENT WRAPPER -->
<div id="main-wrapper" class="p-6 sm:ml-64 pt-20 min-h-screen bg-slate-50">
    <?= $content ?? '' ?>
</div>

<!-- GLOBAL CUSTOM CONFIRM MODAL OVERRIDE -->
<div id="globalConfirmModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="globalConfirmBackdrop"></div>
    <!-- Modal Dialog -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-11/12 max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="globalConfirmDialog">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-800 mb-1" id="globalConfirmTitle">Konfirmasi</h3>
            <p class="text-sm text-slate-500 font-bold whitespace-pre-wrap" id="globalConfirmMessage">Apakah Anda yakin?</p>
        </div>
        <div class="flex border-t border-slate-100 bg-slate-50/50">
            <button type="button" class="w-1/2 px-4 py-4 text-sm font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors" id="globalConfirmCancel">Batal</button>
            <button type="button" class="w-1/2 px-4 py-4 text-sm font-black text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors border-l border-slate-100" id="globalConfirmOk">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('globalConfirmModal');
    const backdrop = document.getElementById('globalConfirmBackdrop');
    const dialog = document.getElementById('globalConfirmDialog');
    const msgEl = document.getElementById('globalConfirmMessage');
    const btnCancel = document.getElementById('globalConfirmCancel');
    const btnOk = document.getElementById('globalConfirmOk');
    
    let currentCallback = null;

    window.showCustomConfirm = function(message, callback) {
        msgEl.innerText = message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        currentCallback = callback;
        
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        
        backdrop.classList.remove('opacity-0');
        dialog.classList.remove('scale-95', 'opacity-0');
        dialog.classList.add('scale-100', 'opacity-100');
    };

    function hideCustomConfirm() {
        backdrop.classList.add('opacity-0');
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            currentCallback = null;
        }, 300);
    }

    btnCancel.addEventListener('click', hideCustomConfirm);
    backdrop.addEventListener('click', hideCustomConfirm);
    
    btnOk.addEventListener('click', () => {
        if (currentCallback) currentCallback();
        hideCustomConfirm();
    });

    // Intercept forms
    document.querySelectorAll('form').forEach(form => {
        const onsubmitAttr = form.getAttribute('onsubmit');
        if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
            const match = onsubmitAttr.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
            const message = match ? match[1].replace(/\\n/g, '\n') : 'Apakah Anda yakin?';
            
            // Remove the inline onsubmit
            form.removeAttribute('onsubmit');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                window.showCustomConfirm(message, () => {
                    form.submit();
                });
            });
        }
    });

    // Intercept links & buttons (onclick)
    const interceptClickElements = document.querySelectorAll('a[onclick*="confirm("], button[onclick*="confirm("]');
    interceptClickElements.forEach(el => {
        const onclickAttr = el.getAttribute('onclick');
        if (onclickAttr && onclickAttr.includes('confirm(')) {
            const match = onclickAttr.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
            const message = match ? match[1].replace(/\\n/g, '\n') : 'Apakah Anda yakin?';
            
            el.removeAttribute('onclick');
            
            el.addEventListener('click', function(e) {
                e.preventDefault();
                window.showCustomConfirm(message, () => {
                    if (el.tagName.toLowerCase() === 'a') {
                        window.location.href = el.href;
                    } else if (el.tagName.toLowerCase() === 'button') {
                        if (el.type === 'submit') {
                            const parentForm = el.closest('form');
                            if (parentForm) parentForm.submit();
                        }
                    }
                });
            });
        }
    });
});
</script>

<!-- Penutup tag HTML yang dibuka di topbar.php -->
</body>
</html>
