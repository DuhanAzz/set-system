<?php
$inputFile = 'u381696286_setsystem (3).sql';
$outputFile = 'migration_to_swim.sql';

$in = fopen($inputFile, 'r');
if (!$in) die("Could not open input file.");

$out = fopen($outputFile, 'w');

fwrite($out, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSTART TRANSACTION;\nSET time_zone = \"+00:00\";\n\n");
fwrite($out, "-- \n-- Data Migration: Prefixing old tables with 'swim_'\n-- \n\n");

$tablesToPrefix = [
    'athlete_records', 'clubs', 'documents', 'dq_rules', 'event_age_groups',
    'event_entries', 'event_historical_records', 'event_numbers', 'event_seeding',
    'event_sponsors', 'events', 'hero_images', 'hero_slides', 'master_records',
    'payments', 'record_packages', 'site_settings', 'swimmer_transfers',
    'swimmers', 'system_logs', 'users'
];

$insideInsert = false;

while (($line = fgets($in)) !== false) {
    if (preg_match('/^INSERT INTO `([^`]+)`(.*)$/s', $line, $matches)) {
        $table = $matches[1];
        if (in_array($table, $tablesToPrefix)) {
            $newTable = 'swim_' . $table;
            $newLine = "REPLACE INTO `" . $newTable . "`" . $matches[2];
            fwrite($out, $newLine);
            
            if (substr(trim($line), -1) !== ';') {
                $insideInsert = true;
            }
        }
    } elseif ($insideInsert) {
        fwrite($out, $line);
        if (substr(trim($line), -1) === ';') {
            $insideInsert = false;
        }
    }
}

fwrite($out, "\nCOMMIT;\n");
fclose($in);
fclose($out);
echo "Success: Created $outputFile\n";
