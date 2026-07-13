<!-- 
    ========================================================
    SCAFFOLDING HALAMAN PUBLIK: SWIM EVENTS
    ========================================================
    
    INSTRUKSI UNTUK USER:
    1. Buka file sistem lama Anda: set-swim-system/public/events.php
    2. Copy seluruh baris HTML dari file tersebut (termasuk tag <html>, <head>, <body>).
    3. Paste semuanya ke dalam file ini (timpa seluruh instruksi ini).
    
    PENYESUAIAN YANG HARUS ANDA LAKUKAN SETELAH PASTE:
    - Ubah path gambar menjadi: <?= getenv('APP_URL') ?>/img/namafile.png
    - Ubah link tombol Login menjadi: <?= getenv('APP_URL') ?>/swim/login
    
    CONTOH LOOPING EVENT (Untuk Daftar Event Aktif):
    <?php foreach($active_events as $event): ?>
        <div class="card">
            <h3><?= htmlspecialchars($event['event_name']) ?></h3>
            <p>Lokasi: <?= htmlspecialchars($event['event_location']) ?></p>
            <p>Tanggal: <?= date('d M Y', strtotime($event['event_date_start'])) ?></p>
        </div>
    <?php endforeach; ?>
-->
