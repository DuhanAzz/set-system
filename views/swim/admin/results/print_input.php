<?php
// FILE: views/swim/admin/results/print_input.php
// Digunakan murni untuk print (seperti view_startlist.php)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Input Hasil - Acara #<?= htmlspecialchars($raceInfo['event_number']) ?></title>
    <style>
        /* RESET & PRINT COLOR */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { margin: 0; padding: 20px; font-family: 'Arial Narrow', sans-serif; background: #525659; }
        
        /* TOMBOL NAVIGASI DI LUAR KERTAS */
        .no-print { display: flex; justify-content: space-between; align-items: center; width: 210mm; margin: 0 auto 15px auto; }
        .btn { padding: 10px 20px; font-weight: bold; text-decoration: none; border-radius: 6px; font-family: Arial, sans-serif; font-size: 10pt; box-shadow: 0 4px 6px rgba(0,0,0,0.2); cursor: pointer; border: none; }
        .btn-back { background: #374151; color: white; }
        .btn-print { background: #2563eb; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* KERTAS A4 */
        .page-wrapper { background: white; width: 210mm; margin: 0 auto; padding: 0 10mm; min-height: 297mm; position: relative; box-shadow: 0 0 15px rgba(0,0,0,0.5); display: flex; flex-direction: column; }
        
        /* HEADER (KOP Acara) */
        .event-header { position: relative; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding: 2px 0; margin-top: 10mm; margin-bottom: 0px; background: #fff; min-height: 35px; }
        .eh-left-group { display: flex; flex-direction: column; justify-content: center; width: 180px; line-height: 1.1; z-index: 2; position: relative; background: white; }
        .eh-number { font-size: 14pt; font-weight: 900; margin-bottom: 2px; color: #000; }
        .eh-date { font-size: 8pt; font-weight: bold; font-style: normal; color: #000; }
        .eh-center { position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%); text-align: center; width: 60%; z-index: 1; }
        .eh-title  { font-size: 11pt; font-weight: 800; text-transform: uppercase; color: #000; }
        .eh-right  { width: 80px; text-align: center; z-index: 2; position: relative; background: white; color: #000; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;}
        
        /* TABEL SERI / HEAT */
        .heat-title { text-align: right; font-size: 9pt; font-weight: bold; text-transform: uppercase; margin-top: 12px; margin-bottom: 2px; color: #000; }
        
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 2px; font-size: 8pt; }
        .data-table th { background-color: #e5e7eb; color: #000; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding: 2px 2px; border-top: 1px solid #000; border-bottom: 2px solid #000; text-align: center; }
        .data-table td { padding: 4px 4px; border-bottom: 1px solid #ccc; vertical-align: middle; font-weight: bold !important; color: #000; } 
        
        .col-ln { width: 5%; text-align: center; background: #f8f9fa; border-right: 1px solid #eee; font-weight: bold; white-space: nowrap; }
        .col-nama { text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
        .col-ku { width: 10%; text-align: center; white-space: nowrap; }
        .col-tim { width: 22%; text-align: left; padding-left: 5px; white-space: normal; line-height: 1.1; }
        .col-waktu { width: 12%; text-align: right; padding-right: 5px; white-space: nowrap; font-family: 'Courier New', monospace; }
        .col-hasil { width: 12%; text-align: center; color: #000; letter-spacing: 0px; white-space: nowrap; }
        
        .data-table tr:nth-child(even) { background-color: #f9fafb; }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-wrapper { margin: 0; width: 100%; box-shadow: none; padding: 0 10mm; min-height: 100vh; page-break-after: always; }
            .event-header { margin-top: 5mm; }
        }
    </style>
</head>
<body>

<?php 
$partType = strtolower($eventProfile['participation_type'] ?? 'club');
$isSchoolEvent = (strpos($partType, 'school') !== false || strpos($partType, 'sekolah') !== false);
$eventYear = date('Y', strtotime($eventProfile['event_date_start'] ?? date('Y')));

if (!function_exists('getKULabelInput')) {
    function getKULabelInput($dob, $evtYear, $groups) {
        if(!$dob || $dob == '0000-00-00') return '-';
        $age = $evtYear - (int)date('Y', strtotime($dob));
        foreach($groups as $g) {
            if ($age >= $g['min_age'] && $age <= $g['max_age']) return $g['group_name'];
        }
        return "DILUAR KATEGORI ($age TH)";
    }
}
?>

    <div class="no-print">
        <button onclick="window.close()" class="btn btn-back">← TUTUP</button>
        <button onclick="window.print()" class="btn btn-print">🖨️ CETAK HALAMAN INI</button>
    </div>

    <div class="page-wrapper">
        <div class="event-header">
            <div class="eh-left-group">
                <div class="eh-number">ACARA #<?= htmlspecialchars($raceInfo['event_number'] ?? '') ?></div>
            </div>
            <div class="eh-center">
                <div class="eh-title"><?= htmlspecialchars($raceInfo['event_name'] ?? '') ?></div>
            </div>
            <div class="eh-right">
                <img id="printQrCode" src="" alt="QR" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 2px;">
                <span style="font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1;">LIVE<br>RESULT</span>
            </div>
        </div>

        <?php if(empty($heats)): ?>
            <div style="text-align:center; padding: 40px; font-style: italic; color: #666;">Belum ada peserta di nomor acara ini.</div>
        <?php else: ?>
            <?php foreach($heats as $heatNo => $lanesData): ?>
            <div class="heat-title">SERI <?= str_pad($heatNo, 2, '0', STR_PAD_LEFT) ?></div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-ln">LN</th>
                        <th class="col-nama">NAMA ATLET</th>
                        <th class="col-ku">KU</th>
                        <th class="col-tim">TIM</th>
                        <th class="col-waktu">WAKTU</th>
                        <th class="col-hasil">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    for($ln = 0; $ln <= 9; $ln++): 
                        $s = $lanesData[$ln] ?? null; 
                        if (!$s && isset($usedLanes) && !in_array($ln, $usedLanes)) continue;
                    ?>
                    <tr>
                        <td class="col-ln"><?= $ln ?></td>
                        <?php if($s): 
                            $kuLabel = getKULabelInput($s['tanggal_lahir'] ?? '0000-00-00', $eventYear, $ageGroups ?? []);
                            if (isset($raceInfo['is_relay']) && $raceInfo['is_relay'] == 1) {
                                $teamName = $s['club_name'] ?? '';
                            } else {
                                $teamName = $isSchoolEvent ? (!empty($s['asal_sekolah']) ? $s['asal_sekolah'] : ($s['club_name'] ?? '')) : ($s['club_name'] ?? '');
                            }
                            $is_real_dq = (($s['is_dq']??0) == 1 && !in_array($s['dq_reason'], ['DNF', 'DNS', '']));
                            $status_val = $is_real_dq ? 'DQ' : ($s['dq_reason'] ?? '');
                        ?>
                            <td class="col-nama"><?= htmlspecialchars($s['nama_atlet'] ?? '') ?></td>
                            <td class="col-ku"><?= htmlspecialchars($kuLabel) ?></td>
                            <td class="col-tim"><?= htmlspecialchars($teamName) ?></td>
                            <td class="col-waktu"><?= htmlspecialchars($s['final_time'] ?? '') ?></td>
                            <td class="col-hasil"><?= htmlspecialchars($status_val) ?></td>
                        <?php else: ?>
                            <td colspan="5" style="color: #ccc; font-style: italic;">&lt; KOSONG &gt;</td>
                        <?php endif; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const qrImage = document.getElementById('printQrCode');
    const storageKey = "qr_link_cat_<?= $cat_id ?>"; 
    const defaultUrl = "<?= getenv('APP_URL') ?>/swim/results";
    
    let link = localStorage.getItem(storageKey);
    if (!link) link = defaultUrl;
    
    if (qrImage) {
        qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(link);
    }
    
    // Auto print on load (opsional)
    setTimeout(() => { window.print(); }, 1000);
});
</script>
</body>
</html>
