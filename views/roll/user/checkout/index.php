<?php include __DIR__ . '/../../../layout/sidebar_roll_user.php'; ?>
<div class="p-6 sm:ml-64 pt-24 bg-slate-50 min-h-screen font-sans">
    <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Tagihan (Checkout)</h1>
    <h2 class="text-xl font-bold mt-4">Belum Dibayar</h2>
    <pre><?php print_r($unpaidEntries); ?></pre>
    <h2 class="text-xl font-bold mt-8">Riwayat (Pending / Paid)</h2>
    <pre><?php print_r($historyEntries); ?></pre>
</div>
