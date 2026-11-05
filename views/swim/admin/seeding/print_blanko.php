<?php
// FILE: views/swim/admin/seeding/print_blanko.php
// Digunakan untuk cetak formulir Blanko Estafet Resmi (susunan perenang estafet)

$categoryId = (int)($_GET['category_id'] ?? 0);
if ($categoryId <= 0) {
    die("Category ID tidak valid.");
}

\App\Helpers\SwimEventHelper::ensureRelayAgeColumns($pdo);

// Ambil info nomor acara & event
$stmtCat = $pdo->prepare("
    SELECT en.*, e.event_name, e.event_location, e.event_city, e.event_date_start, e.event_date_end,
           e.pool_type, e.logo_left, e.logo_right, e.relay_max_total_age as event_max_age, e.age_calculation_type
    FROM swim_event_numbers en
    JOIN swim_events e ON en.event_id = e.id
    WHERE en.id = ?
");
$stmtCat->execute([$categoryId]);
$raceInfo = $stmtCat->fetch(PDO::FETCH_ASSOC);

if (!$raceInfo) {
    die("Nomor lomba tidak ditemukan.");
}

$eventDateStart = $raceInfo['event_date_start'] ?? date('Y-m-d');
$calcType = $raceInfo['age_calculation_type'] ?? 'Dec 31';

$maxTotalAge = 0;
if (!empty($raceInfo['relay_max_total_age']) && (int)$raceInfo['relay_max_total_age'] > 0) {
    $maxTotalAge = (int)$raceInfo['relay_max_total_age'];
} elseif (!empty($raceInfo['event_max_age']) && (int)$raceInfo['event_max_age'] > 0) {
    $maxTotalAge = (int)$raceInfo['event_max_age'];
}

// Ambil seluruh tim estafet di nomor acara ini
$stmtTeams = $pdo->prepare("
    SELECT 
        re.id as relay_id,
        re.club_id,
        re.team_name,
        re.seed_time,
        re.status,
        c.nama_klub,
        s1.nama_atlet as s1_nama, s1.tanggal_lahir as s1_dob, s1.jenis_kelamin as s1_jk,
        s2.nama_atlet as s2_nama, s2.tanggal_lahir as s2_dob, s2.jenis_kelamin as s2_jk,
        s3.nama_atlet as s3_nama, s3.tanggal_lahir as s3_dob, s3.jenis_kelamin as s3_jk,
        s4.nama_atlet as s4_nama, s4.tanggal_lahir as s4_dob, s4.jenis_kelamin as s4_jk
    FROM swim_relay_entries re
    LEFT JOIN swim_clubs c ON re.club_id = c.id
    LEFT JOIN swim_swimmers s1 ON re.swimmer_1_id = s1.id
    LEFT JOIN swim_swimmers s2 ON re.swimmer_2_id = s2.id
    LEFT JOIN swim_swimmers s3 ON re.swimmer_3_id = s3.id
    LEFT JOIN swim_swimmers s4 ON re.swimmer_4_id = s4.id
    WHERE re.category_id = ?
    ORDER BY re.id ASC
");
$stmtTeams->execute([$categoryId]);
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);

$isRelay = true;
$distDisplay = \App\Helpers\SwimEventHelper::formatDistance($raceInfo['distance'], true);
$dispEventName = \App\Helpers\SwimEventHelper::formatEventName($raceInfo['event_name'], true, $raceInfo['distance']);
$cleanStroke = strtoupper(trim(str_ireplace(['Gaya', 'GAYA', 'ESTAFET', 'RELAY'], '', $raceInfo['stroke'] ?? '')));
$isMedley = (stripos($cleanStroke, 'GANTI') !== false || stripos($cleanStroke, 'MEDLEY') !== false);

$legLabels = [
    1 => $isMedley ? 'Leg 1 (Gaya Punggung)' : 'Perenang 1',
    2 => $isMedley ? 'Leg 2 (Gaya Dada)' : 'Perenang 2',
    3 => $isMedley ? 'Leg 3 (Gaya Kupu-kupu)' : 'Perenang 3',
    4 => $isMedley ? 'Leg 4 (Gaya Bebas)' : 'Perenang 4',
];

$gender_label = (in_array($raceInfo['jenis_kelamin'] ?? '', ['L','Male','Man'])) ? 'PUTRA' : 'PUTRI';
if (($raceInfo['jenis_kelamin'] ?? '') == 'Campuran') $gender_label = 'CAMPURAN';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blanko Estafet - Acara #<?= htmlspecialchars($raceInfo['event_number']) ?></title>
    <link rel="icon" type="image/png" href="<?= getenv('APP_URL') ?>/favicon.png?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background-color: #525659;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .btn-print {
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            background: #2563eb; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            font-size: 11px; letter-spacing: 0.05em;
        }
        .btn-close {
            position: fixed; top: 20px; right: 180px; z-index: 9999;
            background: #475569; color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: bold; cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); text-transform: uppercase;
            font-size: 11px;
        }
        .btn-print:hover { background: #1d4ed8; }
        .btn-close:hover { background: #334155; }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 30px auto;
            padding: 10mm 10mm 10mm 10mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        .header-title {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title h1 {
            font-size: 14pt;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            font-size: 11pt;
            font-weight: 800;
            margin: 4px 0 0 0;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .header-title p {
            font-size: 8pt;
            margin: 3px 0 0 0;
            color: #555;
            font-weight: bold;
        }

        .event-meta {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-box { font-size: 8.5pt; font-weight: bold; }
        .meta-box span { color: #64748b; font-weight: normal; font-size: 8pt; }
        .meta-limit {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 900;
            font-size: 8.5pt;
        }

        .team-card {
            border: 1.5px solid #000;
            border-radius: 6px;
            margin-bottom: 16px;
            page-break-inside: avoid;
            background: #fff;
        }
        .team-card-header {
            background: #0f172a;
            color: white;
            padding: 6px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 900;
            font-size: 9pt;
            text-transform: uppercase;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .team-card-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .team-card-table th {
            background: #e2e8f0;
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8pt;
            text-transform: uppercase;
            text-align: center;
        }
        .team-card-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .signatures {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            padding: 4px 12px 10px 12px;
            font-size: 8pt;
        }
        .sig-box {
            text-align: center;
            width: 40%;
        }
        .sig-space { height: 40px; }

        @media print {
            body { background: white !important; margin: 0 !important; }
            .sheet { margin: 0 !important; box-shadow: none !important; width: 100% !important; max-width: 210mm !important; }
            .btn-print, .btn-close { display: none !important; }
            @page { margin: 8mm; size: A4 portrait; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak Blanko</button>
    <button onclick="window.close()" class="btn-close"><i class="fas fa-times"></i> Tutup</button>

    <div class="sheet">
        <div class="header-title">
            <h1><?= htmlspecialchars(strtoupper($raceInfo['event_name'] ?? 'EVENT NAME')) ?></h1>
            <h2>FORMULIR PENETAPAN SUSUNAN ATLET ESTAFET (BLANKO ESTAFET)</h2>
            <p><?= htmlspecialchars($raceInfo['event_location'] ?? '') ?> <?= !empty($raceInfo['event_city']) ? ' - ' . htmlspecialchars($raceInfo['event_city']) : '' ?> | <?= date('d F Y', strtotime($eventDateStart)) ?></p>
        </div>

        <div class="event-meta">
            <div class="meta-box">
                <div><span>Nomor Acara:</span> #<?= htmlspecialchars($raceInfo['event_number']) ?></div>
                <div style="margin-top: 2px;"><span>Acara:</span> <?= htmlspecialchars($dispEventName) ?></div>
            </div>
            <div class="meta-box">
                <div><span>Jarak / Gaya:</span> <?= htmlspecialchars($distDisplay) ?> <?= htmlspecialchars($raceInfo['stroke']) ?></div>
                <div style="margin-top: 2px;"><span>Kategori:</span> <?= htmlspecialchars($raceInfo['age_group']) ?> - <?= $gender_label ?></div>
            </div>
            <div>
                <?php if($maxTotalAge > 0): ?>
                    <div class="meta-limit">🎂 Batas Maks. Total Umur: <?= $maxTotalAge ?> Tahun</div>
                <?php else: ?>
                    <div class="meta-limit" style="background:#e0f2fe; border-color:#0284c7; color:#0369a1;">🎂 Batas Total Umur: Bebas (Tanpa Limit)</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(empty($teams)): ?>
            <div style="text-align: center; padding: 40px; border: 2px dashed #cbd5e1; border-radius: 8px; color: #64748b; font-style: italic;">
                Belum ada tim estafet terdaftar pada nomor acara ini.
            </div>
        <?php else: ?>
            <?php foreach($teams as $team): 
                $dobs = array_filter([$team['s1_dob'] ?? null, $team['s2_dob'] ?? null, $team['s3_dob'] ?? null, $team['s4_dob'] ?? null]);
                $totalAge = \App\Helpers\SwimEventHelper::calculateTeamTotalAge($dobs, $eventDateStart, $calcType);
                $isValidAge = ($maxTotalAge <= 0) || ($totalAge <= $maxTotalAge);
            ?>
            <div class="team-card">
                <div class="team-card-header">
                    <div>TIM: <?= htmlspecialchars($team['team_name'] ?: ($team['nama_klub'] ?? 'TIM ESTAFET')) ?></div>
                    <div>KLUB: <?= htmlspecialchars($team['nama_klub'] ?? '-') ?></div>
                    <div style="font-size: 8pt; font-weight: normal; color: #cbd5e1;">SEED: <?= htmlspecialchars($team['seed_time'] ?? 'NT') ?></div>
                </div>

                <table class="team-card-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Posisi / Leg</th>
                            <th style="width: 45%;">Nama Lengkap Atlet</th>
                            <th style="width: 10%;">JK</th>
                            <th style="width: 15%;">Tgl Lahir</th>
                            <th style="width: 10%;">Umur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($leg = 1; $leg <= 4; $leg++): 
                            $name = $team["s{$leg}_nama"] ?? '';
                            $dob = $team["s{$leg}_dob"] ?? '';
                            $jk = $team["s{$leg}_jk"] ?? '';
                            $age = !empty($dob) ? \App\Helpers\SwimEventHelper::calculateSwimmerAge($dob, $eventDateStart, $calcType) : 0;
                        ?>
                        <tr>
                            <td style="font-weight: bold; background: #f8fafc; text-align: center;"><?= $legLabels[$leg] ?></td>
                            <td style="font-weight: bold;"><?= htmlspecialchars($name ?: '....................................................................') ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($jk ?: '-') ?></td>
                            <td style="text-align: center;"><?= !empty($dob) && $dob != '0000-00-00' ? date('d/m/Y', strtotime($dob)) : '.../.../......' ?></td>
                            <td style="text-align: center; font-weight: bold;"><?= $age > 0 ? $age . ' Th' : '-' ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: bold;">
                            <td colspan="4" style="text-align: right; padding-right: 10px;">
                                TOTAL AKUMULASI UMUR REGU ESTAFET:
                            </td>
                            <td style="text-align: center; font-size: 9.5pt; font-weight: 900; <?= ($maxTotalAge > 0 && !$isValidAge) ? 'color: #dc2626; background: #fee2e2;' : 'color: #15803d;' ?>">
                                <?= $totalAge > 0 ? $totalAge . ' Th' : '-' ?>
                            </td>
                        </tr>
                        <?php if($maxTotalAge > 0): ?>
                        <tr style="background: <?= $isValidAge ? '#f0fdf4' : '#fef2f2' ?>; font-size: 7.5pt;">
                            <td colspan="5" style="padding: 3px 10px; text-align: right; font-weight: bold; color: <?= $isValidAge ? '#166534' : '#991b1b' ?>;">
                                <?= $isValidAge ? '✓ Memenuhi ketentuan limit total umur (Maksimal ' . $maxTotalAge . ' Tahun)' : '⚠️ PERHATIAN: Total umur melebihi batas yang ditetapkan (' . $totalAge . ' Th > ' . $maxTotalAge . ' Th)!' ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>

                <div class="signatures">
                    <div class="sig-box">
                        <div>Official / Pelatih Tim</div>
                        <div class="sig-space"></div>
                        <div style="border-top: 1px dotted #000; display: inline-block; min-width: 140px; padding-top: 2px;">( ......................................... )</div>
                    </div>
                    <div class="sig-box">
                        <div>Tim Sekretariat Perlombaan</div>
                        <div class="sig-space"></div>
                        <div style="border-top: 1px dotted #000; display: inline-block; min-width: 140px; padding-top: 2px;">( ......................................... )</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>
