<?php
// FILE: views/roll/admin/export/index.php
$title = "Ekspor & Laporan";
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Ekspor Data & Laporan Cetak</h2>
    <p class="text-slate-500 text-sm">Unduh rekapitulasi lomba (Race Book, Result Book, CSV).</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- Race Book -->
    <div class="bg-white p-8 rounded-lg shadow-sm border border-slate-200 text-center hover:shadow-md transition">
        <div class="text-5xl mb-4">📖</div>
        <h3 class="font-bold text-slate-800 text-lg mb-2">Cetak Race Book</h3>
        <p class="text-sm text-slate-500 mb-6">Buku daftar peserta lengkap seluruh seri heat & final.</p>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/export/print_race_book" target="_blank" class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium shadow w-full">Cetak PDF</a>
    </div>

    <!-- Result Book -->
    <div class="bg-white p-8 rounded-lg shadow-sm border border-slate-200 text-center hover:shadow-md transition">
        <div class="text-5xl mb-4">🏆</div>
        <h3 class="font-bold text-slate-800 text-lg mb-2">Cetak Buku Hasil</h3>
        <p class="text-sm text-slate-500 mb-6">Buku rekapitulasi seluruh hasil resmi event.</p>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/export/print_result_book" target="_blank" class="inline-block px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-medium shadow w-full">Cetak PDF</a>
    </div>

    <!-- Start List CSV -->
    <div class="bg-white p-8 rounded-lg shadow-sm border border-slate-200 text-center hover:shadow-md transition">
        <div class="text-5xl mb-4">📊</div>
        <h3 class="font-bold text-slate-800 text-lg mb-2">Ekspor CSV Start List</h3>
        <p class="text-sm text-slate-500 mb-6">Format data mentah untuk dikelola eksternal.</p>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/export/generate_start_list" class="inline-block px-6 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded font-medium shadow w-full">Unduh CSV</a>
    </div>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../../layout/master_layout.php';
?>
