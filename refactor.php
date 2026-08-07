<?php
$file = 'views/roll/admin/pelotons/print_full.php';
$content = file_get_contents($file);

// 1. Add $fullBookByDay
$search1 = "ksort(\$scheduleByDay);";
$replace1 = "ksort(\$scheduleByDay);

\$fullBookByDay = [];
foreach (\$fullBook as \$cid => \$data) {
    \$dayDigit = (int)substr(\$data['meta']['nomor'], 0, 1);
    if (\$dayDigit === 0) \$dayDigit = 1;
    \$fullBookByDay[\$dayDigit][\$cid] = \$data;
}
ksort(\$fullBookByDay);";
$content = str_replace($search1, $replace1, $content);

// 2. We will wrap the layout in a loop by day.
// Find the entire `<tbody> ... </tbody>` that holds the schedule and race books.
// It starts at line 327 and ends at line 609.
// But wait, there are nested tbodys inside `schedule-table` and `data-table`!
// Using regex on HTML is prone to error. Let's use precise string replacements instead.

// The start of the main td:
$startMarker = '                    <!-- ============================================== -->
                    <!-- JADWAL OTOMATIS (Sesuai dengan print_schedule) -->
                    <!-- ============================================== -->';

// We want to insert the outer loop before it:
$loopStart = '                    <?php 
                    $allDays = array_unique(array_merge(array_keys($scheduleByDay), array_keys($fullBookByDay)));
                    sort($allDays);
                    $isFirstDay = true;
                    foreach ($allDays as $day): 
                        $dayClasses = $scheduleByDay[$day] ?? [];
                        $dayFullBook = $fullBookByDay[$day] ?? [];
                    ?>
                        <?php if (!$isFirstDay): ?>
                            <div style="page-break-before: always;"></div>
                        <?php endif; ?>
                        
                        <!-- ============================================== -->
                        <!-- JADWAL OTOMATIS (Sesuai dengan print_schedule) -->
                        <!-- ============================================== -->';
$content = str_replace($startMarker, $loopStart, $content);

// Now, we need to change how the schedule is displayed.
// And ends at: `endforeach; ` just before `</tbody></table></div>endif; `

$scheduleLoopStart = '                                    <?php foreach ($scheduleByDay as $day => $dayClasses): 
                                        $dateStr = \'\';';
$newScheduleStart = '                                    <?php
                                        $dateStr = \'\';';
$content = str_replace($scheduleLoopStart, $newScheduleStart, $content);

// And the end of the schedule loop
$scheduleLoopEnd = '                                        <?php
                                            }
                                            if (!empty($pemulaGroup)) {
                                                $renderPemulaGroupFull($pemulaGroup);
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </tbody>';
$newScheduleEnd = '                                        <?php
                                            }
                                            if (!empty($pemulaGroup)) {
                                                $renderPemulaGroupFull($pemulaGroup);
                                            }
                                        ?>
                                </tbody>';
$content = str_replace($scheduleLoopEnd, $newScheduleEnd, $content);

// We need to change the condition for displaying schedule to only check $dayClasses
$scheduleCondOld = '<?php if ($showScheduleAuto && empty($scheduleImage) && !empty($scheduleByDay)): ?>';
$scheduleCondNew = '<?php if ($showScheduleAuto && empty($scheduleImage) && !empty($dayClasses)): ?>';
$content = str_replace($scheduleCondOld, $scheduleCondNew, $content);

// Now we do the Race Books
// Currently it loops over $fullBook: `<?php foreach($fullBook as $cid => $data): `
$raceBookCondOld = '<?php if(empty($fullBook)): ?>';
$raceBookCondNew = '<?php if(empty($dayFullBook)): ?>';
$content = str_replace($raceBookCondOld, $raceBookCondNew, $content);

$raceBookLoopOld = '<?php foreach($fullBook as $cid => $data):';
$raceBookLoopNew = '<?php foreach($dayFullBook as $cid => $data):';
$content = str_replace($raceBookLoopOld, $raceBookLoopNew, $content);

// And finally, close the main outer loop at the very end of the main <td>
$endMarker = '                        <?php endforeach; ?>
                    <?php endif; ?>

                </td>';
$loopEnd = '                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php 
                    $isFirstDay = false;
                    endforeach; 
                    ?>
                </td>';
$content = str_replace($endMarker, $loopEnd, $content);


file_put_contents($file, $content);
echo "Refactored print_full.php successfully.";
