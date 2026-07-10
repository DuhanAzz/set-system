<!-- Footer Dashboard -->
<footer class="bg-white border-t border-slate-200 mt-auto py-6 sm:ml-64 relative z-40 transition-all">
    <div class="px-6 mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-sm text-slate-500 font-medium">
            &copy; <?= date('Y') ?> <span class="font-black text-orange-500 tracking-tight">SET<span class="text-slate-800">ROLL</span></span>. Enterprise Edition.
        </div>
        <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
            <a href="#" class="hover:text-orange-500 transition">Bantuan Pusat</a>
            <span class="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
            <a href="#" class="hover:text-orange-500 transition">Kebijakan Privasi</a>
            <span class="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
            <span class="bg-orange-50 text-orange-600 px-2 py-0.5 rounded-md border border-orange-100">Versi 1.0.0</span>
        </div>
    </div>
</footer>

<script>
    // Pastikan modal dan dropdown dari Flowbite diinisialisasi ulang jika konten dinamis
    if (typeof initFlowbite === 'function') {
        initFlowbite();
    }
</script>
</body>
</html>
