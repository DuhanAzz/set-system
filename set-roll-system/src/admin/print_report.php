<?php
// FILE: src/admin/print_report.php
require_once __DIR__ . '/../config/database.php';

$event_id = $_GET['event_id'] ?? 0;
$heat_name = $_GET['heat_name'] ?? '';
$type = $_GET['type'] ?? 'STARTLIST'; // STARTLIST atau RESULT

if (!$event_id || !$heat_name) {
    die("Parameter event_id dan heat_name wajib diisi.");
}

// 1. Ambil Info Kejuaraan
$stmtEvent = $pdo->prepare("SELECT * FROM roll_events WHERE id = ?");
$stmtEvent->execute([$event_id]);
$event = $stmtEvent->fetch();
if (!$event) die("Kejuaraan tidak ditemukan.");

// 2. Ambil Data Hasil / Startlist
$orderBy = "p.start_grid ASC";
if ($type == 'RESULT') {
    if ($event['race_format'] == 'PTP') {
        $orderBy = "r.total_points DESC, r.finish_position ASC";
    } elseif ($event['race_format'] == 'ELIMINATION') {
        $orderBy = "r.is_eliminated ASC, r.finish_position ASC";
    } else {
        $orderBy = "r.finish_time_ms ASC";
    }
}

$query = "
    SELECT r.*, s.skater_name, s.gender, s.age_group, c.club_name, p.start_grid
    FROM roll_event_results r
    JOIN roll_skaters s ON r.skater_id = s.id
    LEFT JOIN roll_clubs c ON s.club_id = c.id
    LEFT JOIN roll_pelotons p ON r.event_id = p.event_id AND r.skater_id = p.skater_id AND r.heat_name = p.heat_name
    WHERE r.event_id = ? AND r.heat_name = ?
    ORDER BY $orderBy
";

$stmtData = $pdo->prepare($query);
$stmtData->execute([$event_id, $heat_name]);
$data = $stmtData->fetchAll();

// Helper Waktu
function formatMsToTime($ms) {
    if ($ms === null || $ms === '') return '-';
    $ms = (int)$ms;
    $minutes = floor($ms / 60000);
    $seconds = floor(($ms % 60000) / 1000);
    $milli = $ms % 1000;
    return sprintf("%02d:%02d.%03d", $minutes, $seconds, $milli);
}

$title = ($type == 'RESULT') ? 'HASIL RESMI (OFFICIAL RESULT)' : 'DAFTAR START (START LIST)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $type ?> - <?= htmlspecialchars($event['event_name']) ?></title>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 1cm; }
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #000; 
            margin: 0; 
            padding: 20px; 
            background: #fff;
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px 0; font-size: 24px; text-transform: uppercase; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; font-weight: normal; }
        .header h3 { margin: 0; font-size: 16px; font-weight: bold; }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f97316;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; }
        td.text-left { text-align: left; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak Dokumen</button>

    <div class="header">
        <h1>RESMI - SET ROLL SYSTEM</h1>
        <h2><?= htmlspecialchars($event['event_name']) ?> (<?= htmlspecialchars($event['location'] ?? '') ?>)</h2>
        <h3><?= $title ?> - <?= htmlspecialchars($heat_name) ?></h3>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">Grid</th>
                <th class="text-left">Nama Atlet</th>
                <th>Klub / Tim</th>
                <th>Kategori</th>
                <th>L/P</th>
                
                <?php if($type == 'RESULT'): ?>
                    <?php if(in_array($event['race_format'], ['TIME_TRIAL', 'SPRINT', 'DTT'])): ?>
                        <th>Waktu</th>
                        <th>Status</th>
                    <?php elseif($event['race_format'] == 'PTP'): ?>
                        <th>Total Poin</th>
                        <th>Posisi Finis</th>
                    <?php elseif($event['race_format'] == 'ELIMINATION'): ?>
                        <th>Status</th>
                        <th>Posisi Akhir</th>
                    <?php endif; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data)): ?>
                <tr><td colspan="10">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach($data as $d): ?>
                <tr>
                    <td class="font-bold"><?= htmlspecialchars($d['start_grid'] ?? '-') ?></td>
                    <td class="text-left font-bold"><?= htmlspecialchars($d['skater_name']) ?></td>
                    <td><?= htmlspecialchars($d['club_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['age_group']) ?></td>
                    <td><?= $d['gender'] ?></td>
                    
                    <?php if($type == 'RESULT'): ?>
                        <?php if(in_array($event['race_format'], ['TIME_TRIAL', 'SPRINT', 'DTT'])): ?>
                            <td style="font-family: monospace; font-size: 14px; font-weight: bold;">
                                <?= formatMsToTime($d['finish_time_ms']) ?>
                            </td>
                            <td><?= htmlspecialchars($d['advancement_status']) ?></td>
                        <?php elseif($event['race_format'] == 'PTP'): ?>
                            <td class="font-bold"><?= htmlspecialchars($d['total_points']) ?> pts</td>
                            <td><?= htmlspecialchars($d['finish_position'] ?? '-') ?></td>
                        <?php elseif($event['race_format'] == 'ELIMINATION'): ?>
                            <td><?= $d['is_eliminated'] ? 'Gugur' : 'Bertahan' ?></td>
                            <td><?= htmlspecialchars($d['finish_position'] ?? '-') ?></td>
                        <?php endif; ?>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
