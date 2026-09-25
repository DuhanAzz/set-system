<?php
$createFile = __DIR__ . '/views/roll/admin/entries/create.php';
$indexFile = __DIR__ . '/views/roll/user/token_entries/index.php';

$createContent = file_get_contents($createFile);
$indexContent = file_get_contents($indexFile);

// Extract top part of index.php (before modals)
$indexTop = substr($indexContent, 0, strpos($indexContent, '<!-- Modal Pendaftaran Atlet -->'));

// Extract forms from create.php
// From "<!-- FORM INDIVIDU -->" up to the end of the file.
$createFormsAndJs = substr($createContent, strpos($createContent, '<!-- FORM INDIVIDU -->'));

// Remove the TOKEN form from createFormsAndJs
$tokenFormStart = strpos($createFormsAndJs, '<div id="form_token"');
if ($tokenFormStart !== false) {
    // Find the end of form_token div
    // Since we know it's relatively flat, we can just search for the next closing tag that matches.
    // Or just string replace out the chunk. We know it ends with `</div>` before `<script>`
    $scriptStart = strpos($createFormsAndJs, '<script>');
    $tokenSection = substr($createFormsAndJs, $tokenFormStart, $scriptStart - $tokenFormStart);
    $createFormsAndJs = str_replace($tokenSection, '', $createFormsAndJs);
}

// Replace $targetEventId with $event['id']
$createFormsAndJs = str_replace('$targetEventId', '$event[\'id\']', $createFormsAndJs);

// Lock "Pilih Klub" for Individu
$klubIndv = '
                    <div>
                        <input type="hidden" name="club_id" value="<?= $club_id ?>">
                    </div>
';
// Remove the select for indv_club_select
$createFormsAndJs = preg_replace('/<div>\s*<label[^>]*>Pilih Klub.*?<\/select>\s*<\/div>/s', $klubIndv, $createFormsAndJs, 1);

// Remove "onchange="loadAthletes..."" from team_club_select, replace with hidden club_id
$teamClubSelectPattern = '/<select id="team_club_select_<\?= \$i \?>".*?<\/select>/s';
$teamClubHidden = '<input type="hidden" name="team_club_id_<?= $i ?>" value="<?= $club_id ?>">';
$createFormsAndJs = preg_replace($teamClubSelectPattern, $teamClubHidden, $createFormsAndJs);

// Replace "Pilih Klub" label in Team
$createFormsAndJs = str_replace('<p class="text-[9px] font-bold text-slate-400 uppercase">Pilih klub asal atlet, lalu pilih atletnya.</p>', '<p class="text-[9px] font-bold text-slate-400 uppercase">Pilih anggota tim dari klub Anda.</p>', $createFormsAndJs);

// Replace the form action URLs to point to token_registration addEntry
$createFormsAndJs = str_replace('/roll/admin/entries/manual_add', '/roll/user/token_registration/addEntry', $createFormsAndJs);

// Replace tab switch buttons class to not include the token tab (remove the token tab related code in JS)
$createFormsAndJs = preg_replace('/document\.getElementById\(\'form_token\'\)\.classList\.add\(\'hidden\'\);/', '', $createFormsAndJs);
$createFormsAndJs = preg_replace('/document\.getElementById\(\'tab_btn_token\'\).*?;/', '', $createFormsAndJs);
$createFormsAndJs = preg_replace('/\} else if \(tab === \'token\'\) \{.*?\}/s', '', $createFormsAndJs);


// Fix JS: In create.php, athletes are loaded via AJAX. For user side, we already have $athletes.
// We need to initialize the athletes dropdowns on page load using $athletes.
$jsInit = '
const myAthletes = <?= json_encode($athletes) ?>;
const myClubId = <?= $club_id ?>;

function populateAthleteSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    select.innerHTML = \'<option value="">- Pilih Atlet -</option>\';
    myAthletes.forEach(a => {
        select.innerHTML += `<option value="${a.id}" data-dob="${a.birth_date}" data-gender="${a.gender}">${a.skater_name} (${a.gender === \'M\' ? \'Putra\' : \'Putri\'})</option>`;
    });
    select.disabled = false;
}

window.addEventListener(\'DOMContentLoaded\', function() {
    populateAthleteSelect(\'indv_skater_select\');
    for(let i=1; i<=4; i++) {
        populateAthleteSelect(\'team_skater_select_\' + i);
    }
});
';

$createFormsAndJs = str_replace('let athletesCache = {};', $jsInit . "\nlet athletesCache = {};", $createFormsAndJs);

// Remove JS function loadAthletes entirely
$createFormsAndJs = preg_replace('/function loadAthletes\(clubId, targetSelectId\) \{.*?\n\}/s', 'function loadAthletes(c,t) {}', $createFormsAndJs);

$finalContent = $indexTop . "\n" . $createFormsAndJs;

file_put_contents($indexFile, $finalContent);
echo "Successfully rewrote index.php\n";
