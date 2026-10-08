<?php
// FILE: views/roll/admin/pelotons/detail.php

// Variables dari controller: $classData, $classId, $heatsByRound, $unseeded, $mechanism, $raceType
$isHeat = ($mechanism === 'heat');
$isStartingList = ($mechanism === 'starting_list');

// Cek status Pemula & Tim
$judul = strtoupper($classData['distance_name'] . ' - ' . $classData['group_name'] . ' - ' . $classData['roller_name'] . ' ' . $classData['gender']);
if (!empty($classData['custom_name'])) {
    $judul = strtoupper($classData['distance_name'] . ' - ' . $classData['custom_name'] . ' - ' . $classData['roller_name'] . ' ' . $classData['gender']);
}
$isTeamRace = (stripos($judul, 'pair') !== false || stripos($judul, 'relay') !== false); 
$teamSize = stripos($judul, 'pair') !== false ? 2 : (stripos($judul, 'relay') !== false ? 3 : 1);
$isPemula = (stripos($classData['roller_name'] ?? '', 'Pemula') !== false);

// Gabung data
$startingListEntries = [];
if ($isStartingList) {
    foreach ($heatsByRound as $rnd => $heats) {
        foreach ($heats as $heatName => $members) {
            foreach ($members as $m) {
                $startingListEntries[] = $m;
            }
        }
    }
    if (empty($startingListEntries)) {
        $startingListEntries = $unseeded;
    }
}
$raceNumStr = str_pad($classData['race_number'], 3, '0', STR_PAD_LEFT);
?>

<style>
    /* TABEL RACE BOOK (HEAT) STYLE MIRRORING PRINT_FULL */
    .event-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 4px; margin-top: 10px; page-break-inside: avoid; }
    .eh-left-group { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
    .eh-number { font-size: 9pt; font-weight: 900; background: #000; color: #fff; display: inline-block; padding: 2px 6px; border-radius: 4px 4px 0 0; align-self: flex-start; }
    .eh-center { flex-grow: 1; text-align: center; }
    .eh-title { font-size: 13pt; font-weight: 900; text-transform: uppercase; color: #000; font-style: italic; line-height: 1.2; }
    .eh-right { min-width: 120px; text-align: right; font-size: 9pt; font-weight: 900; color: #000; }
    
    .heat-title { font-size: 9pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2px; margin-top: 4px; border-bottom: 1px dashed #000; padding-bottom: 2px; }
    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid; }
    .data-table th { border: 1px solid #000; background-color: #eee; padding: 2px 4px; text-align: left; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
    .data-table td { border: 1px solid #000; padding: 2px 4px; font-size: 8.5pt; vertical-align: middle; color: #000; }
    .data-table th.col-ln, .data-table td.col-ln { width: 40px; text-align: center; font-weight: bold; }
    .data-table th.col-bib, .data-table td.col-bib { width: 60px; text-align: center; font-weight: bold; }
    .data-table th.col-nama { width: 40%; }
    
    .round-title { background-color: #e2e8f0; color: #1e293b; text-align: center; padding: 3px; margin-top: 6px; margin-bottom: 4px; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; page-break-inside: avoid; }

    @media print {
        @page { margin: 10mm; size: A4; }
        body { background: white; margin: 0; }
        .print-hidden, aside, header, nav { display: none !important; }
        .full-page-container { padding: 0 !important; margin: 0 !important; width: 100% !important; box-shadow: none !important; }
    }
</style>

<div class="max-w-7xl mx-auto mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 print-hidden px-4">
    <div>
        <a href="<?= getenv('APP_URL') ?>/roll/admin/pelotons" class="bg-slate-800 text-white px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-700 transition inline-flex items-center gap-2">
            <span>⬅</span> Kembali
        </a>
    </div>
    
    <div class="flex items-center gap-2">
        <?php if($raceType === 'time_trial'): ?>
            <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl border border-blue-200 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <span>⏱️</span> Time Trial
            </div>
        <?php elseif($isStartingList): ?>
            <div class="bg-purple-50 text-purple-700 px-4 py-2 rounded-xl border border-purple-200 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <span>📋</span> Starting List
            </div>
        <?php else: ?>
            <div class="bg-orange-50 text-orange-700 px-4 py-2 rounded-xl border border-orange-200 text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <span>🔥</span> Heat System
            </div>
        <?php endif; ?>
    </div>

    <button onclick="window.print()" class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-500 transition shadow-lg inline-flex items-center gap-2">
        <span>🖨️</span> Cetak Halaman
    </button>
</div>

<!-- KERTAS A4 PREVIEW -->
<div class="full-page-container w-full max-w-[210mm] min-h-[297mm] bg-white mx-auto shadow-2xl p-[10mm] sm:p-[15mm] text-black">
    
    <div class="event-header">
        <div class="eh-left-group">
            <div class="eh-number">RACE <?= $raceNumStr ?></div>
        </div>
        <div class="eh-center"><div class="eh-title"><?= htmlspecialchars($judul) ?></div></div>
        <div class="eh-right"><?= $isHeat ? 'PENYISIHAN' : 'FINAL' ?></div>
    </div>

    <?php if($isHeat): ?>
    <!-- ============================================================ -->
    <!-- KONTEN HEAT -->
    <!-- ============================================================ -->
    <?php 
        $hasAnyHeat = false;
        foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $rnd):
            $roundHeats = $heatsByRound[$rnd] ?? [];
            if(!empty($roundHeats)) $hasAnyHeat = true;
        endforeach;
    ?>
    
    <?php if(!$hasAnyHeat): ?>
        <div style="text-align:center; padding: 50px; font-weight:bold; color: #888;">BELUM ADA HEAT PADA KELAS INI</div>
    <?php else: ?>
        <?php foreach(['Kualifikasi', 'Perempat Final', 'Semi Final', 'Final'] as $rnd): 
            $roundHeats = $heatsByRound[$rnd] ?? [];
            if(empty($roundHeats)) continue;
        ?>
            <?php if(count($heatsByRound) > 1): ?>
                <div class="round-title">BABAK <?= htmlspecialchars($rnd) ?></div>
            <?php endif; ?>
            
            <?php foreach($roundHeats as $heatName => $members): ?>
                <div class="heat-title"><?= htmlspecialchars($heatName) ?> <span style="font-size: 8pt; color: #666; font-weight: normal; margin-left: 10px;">(<?= count($members) ?> Atlet)</span></div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php if($isTeamRace): ?>
                                <th class="col-ln">NO</th>
                                <th>NAMA TIM</th>
                                <th class="col-bib">NO. BIB</th>
                                <th class="col-nama">NAMA ATLET</th>
                                <th>KLUB / KONTINGEN</th>
                            <?php else: ?>
                                <th class="col-ln">LANE</th>
                                <th class="col-bib">NO. BIB</th>
                                <th class="col-nama">NAMA ATLET</th>
                                <th>KLUB / KONTINGEN</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($isTeamRace): ?>
                            <?php 
                            $teamChunks = array_chunk($members, $teamSize);
                            $teamIndex = 1;
                            foreach($teamChunks as $teamMembers): 
                                $first = true;
                                $rowspan = count($teamMembers);
                                foreach($teamMembers as $m):
                            ?>
                            <tr>
                                <?php if($first): ?>
                                <td class="col-ln text-center" rowspan="<?= $rowspan ?>"><?= $teamIndex ?></td>
                                <td rowspan="<?= $rowspan ?>" style="font-weight: bold; color: #444;"><?= htmlspecialchars(!empty($m['team_name']) && $m['team_name'] !== '-' ? $m['team_name'] : 'Regu '.$teamIndex) ?></td>
                                <?php endif; ?>
                                <td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                <td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td>
                                <td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td>
                            </tr>
                            <?php 
                                $first = false;
                                endforeach; 
                                $teamIndex++;
                            endforeach; 
                            ?>
                        <?php else: ?>
                            <?php foreach($members as $m): ?>
                            <tr>
                                <td class="col-ln"><?= htmlspecialchars($m['start_grid'] ?? '-') ?></td>
                                <td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                <td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td>
                                <td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if(empty($members)): ?>
                        <tr>
                            <td colspan="<?= $isTeamRace ? 5 : 4 ?>" style="text-align: center; padding: 10px; color: #888;">&lt;Belum ada atlet&gt;</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

        <?php endforeach; ?>
    <?php endif; ?>

    <?php else: ?>
    <!-- ============================================================ -->
    <!-- KONTEN STARTING LIST -->
    <!-- ============================================================ -->
    <?php if(empty($startingListEntries)): ?>
        <div style="text-align:center; padding: 50px; font-weight:bold; color: #888;">BELUM ADA DATA STARTING LIST</div>
    <?php else: ?>
        <?php 
        $startingHeats = $heatsByRound['Kualifikasi'] ?? [];
        if(empty($startingHeats) && !empty($unseeded)) {
            $startingHeats['Draft'] = $unseeded;
        }
        ?>

        <?php foreach($startingHeats as $grpName => $grpMembers): ?>
            <?php if(count($startingHeats) > 1): ?>
                <div class="heat-title"><?= htmlspecialchars($grpName) ?> <span style="font-size: 8pt; color: #666; font-weight: normal; margin-left: 10px;">(<?= count($grpMembers) ?> Atlet)</span></div>
            <?php endif; ?>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <?php if($isTeamRace): ?>
                            <th class="col-ln">NO</th>
                            <th>NAMA TIM</th>
                            <th class="col-bib">NO. BIB</th>
                            <th class="col-nama">NAMA ATLET</th>
                            <th>KLUB / KONTINGEN</th>
                            <?php if($raceType === 'time_trial'): ?><th style="width: 100px; text-align: center;">WAKTU</th><?php endif; ?>
                        <?php else: ?>
                            <th class="col-ln"><?= $raceType === 'time_trial' ? 'URUT' : 'LANE' ?></th>
                            <th class="col-bib">NO. BIB</th>
                            <th class="col-nama">NAMA ATLET</th>
                            <th>KLUB / KONTINGEN</th>
                            <?php if($raceType === 'time_trial'): ?><th style="width: 100px; text-align: center;">WAKTU</th><?php endif; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if($isTeamRace): ?>
                        <?php 
                        $teamChunks = array_chunk($grpMembers, $teamSize);
                        $teamIndex = 1;
                        foreach($teamChunks as $teamMembers): 
                            $first = true;
                            $rowspan = count($teamMembers);
                            foreach($teamMembers as $m):
                        ?>
                        <tr>
                            <?php if($first): ?>
                            <td class="col-ln text-center" rowspan="<?= $rowspan ?>"><?= $teamIndex ?></td>
                            <td rowspan="<?= $rowspan ?>" style="font-weight: bold; color: #444;"><?= htmlspecialchars(!empty($m['team_name']) && $m['team_name'] !== '-' ? $m['team_name'] : 'Regu '.$teamIndex) ?></td>
                            <?php endif; ?>
                            <td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                            <td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td>
                            <td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td>
                            <?php if($first && $raceType === 'time_trial'): ?>
                                <td rowspan="<?= $rowspan ?>" style="text-align: center; color: #ccc;">________</td>
                            <?php endif; ?>
                        </tr>
                        <?php 
                            $first = false;
                            endforeach; 
                            $teamIndex++;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <?php 
                        if ($isPemula) {
                            $byGroup = [];
                            foreach ($grpMembers as $m) {
                                $g = $m['group_name'] ?? 'Lainnya';
                                $byGroup[$g][] = $m;
                            }
                            $overallIndex = 1;
                            foreach ($byGroup as $gName => $gMembers) {
                                ?>
                                <tr>
                                    <td colspan="<?= $raceType === 'time_trial' ? 5 : 4 ?>" style="background-color: #e2e8f0; font-weight: bold; text-align: center; font-size: 9pt; padding: 4px; border: 1px solid #000; text-transform: uppercase;">
                                        <?= htmlspecialchars($gName) ?>
                                    </td>
                                </tr>
                                <?php
                                foreach ($gMembers as $m) {
                                    ?>
                                    <tr>
                                        <td class="col-ln text-center font-bold"><?= $raceType === 'time_trial' ? $overallIndex++ : htmlspecialchars($m['start_grid'] ?? '-') ?></td>
                                        <td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                        <td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td>
                                        <td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td>
                                        <?php if($raceType === 'time_trial'): ?>
                                            <td style="text-align: center; color: #ccc;">________</td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php
                                }
                            }
                        } else {
                            foreach($grpMembers as $idx => $m): ?>
                            <tr>
                                <td class="col-ln text-center font-bold"><?= $raceType === 'time_trial' ? ($idx+1) : htmlspecialchars($m['start_grid'] ?? '-') ?></td>
                                <td class="col-bib"><?= htmlspecialchars($m['bib_number'] ?? '-') ?></td>
                                <td class="col-nama" style="font-weight: bold;"><?= htmlspecialchars($m['skater_name']) ?></td>
                                <td><?= htmlspecialchars($m['club_name'] ?? '-') ?></td>
                                <?php if($raceType === 'time_trial'): ?>
                                    <td style="text-align: center; color: #ccc;">________</td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; 
                        }
                        ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>

</div>
