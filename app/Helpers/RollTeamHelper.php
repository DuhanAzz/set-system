<?php

namespace App\Helpers;

class RollTeamHelper {

    public static function getDefaultQuotas(): array {
        return [
            'pair'  => ['inti' => 2, 'cadangan' => 0],
            'ttt'   => ['inti' => 3, 'cadangan' => 1],
            'relay' => ['inti' => 3, 'cadangan' => 1],
        ];
    }

    /**
     * Tambah kolom kuota tim & peran anggota secara aman di luar transaksi aktif.
     */
    public static function ensureColumns($db): void {
        if (!$db) return;
        $inTx = method_exists($db, 'inTransaction') ? $db->inTransaction() : false;
        if ($inTx) return;

        $ddlStatements = [
            "ALTER TABLE roll_events ADD COLUMN team_pair_inti INT NOT NULL DEFAULT 2",
            "ALTER TABLE roll_events ADD COLUMN team_pair_cadangan INT NOT NULL DEFAULT 0",
            "ALTER TABLE roll_events ADD COLUMN team_ttt_inti INT NOT NULL DEFAULT 3",
            "ALTER TABLE roll_events ADD COLUMN team_ttt_cadangan INT NOT NULL DEFAULT 1",
            "ALTER TABLE roll_events ADD COLUMN team_relay_inti INT NOT NULL DEFAULT 3",
            "ALTER TABLE roll_events ADD COLUMN team_relay_cadangan INT NOT NULL DEFAULT 1",
            "ALTER TABLE roll_entries ADD COLUMN team_role VARCHAR(20) NULL DEFAULT NULL",
        ];

        foreach ($ddlStatements as $sql) {
            try {
                $db->exec($sql);
            } catch (\Throwable $e) {}
        }
    }

    public static function extractQuotas(array $eventRow): array {
        $defaults = self::getDefaultQuotas();
        $result = [];
        foreach ($defaults as $type => $def) {
            $intiKey = "team_{$type}_inti";
            $cadKey  = "team_{$type}_cadangan";
            $inti = isset($eventRow[$intiKey]) && $eventRow[$intiKey] !== null ? (int)$eventRow[$intiKey] : $def['inti'];
            $cad  = isset($eventRow[$cadKey]) && $eventRow[$cadKey] !== null ? (int)$eventRow[$cadKey] : $def['cadangan'];
            $result[$type] = [
                'inti'     => max(1, $inti),
                'cadangan' => max(0, $cad),
            ];
        }
        return $result;
    }

    public static function getEventQuotas($db, int $eventId): array {
        $defaults = self::getDefaultQuotas();
        if (!$eventId || !$db) return $defaults;

        self::ensureColumns($db);

        try {
            $stmt = $db->prepare("
                SELECT team_pair_inti, team_pair_cadangan,
                       team_ttt_inti, team_ttt_cadangan,
                       team_relay_inti, team_relay_cadangan
                FROM roll_events WHERE id = ?
            ");
            $stmt->execute([$eventId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return self::extractQuotas($row);
            }
        } catch (\Throwable $e) {}

        return $defaults;
    }

    /**
     * Klasifikasikan nama jarak ke jenis nomor tim: 'pair', 'ttt', 'relay', atau null (individu).
     */
    public static function classifyDistance(string $distanceName): ?string {
        $d = strtolower(trim($distanceName));
        if ($d === '') return null;

        if (strpos($d, 'pair') !== false) {
            return 'pair';
        }
        if (preg_match('/\bttt\b/', $d) || strpos($d, 'team time trial') !== false || strpos($d, 'team sprint') !== false) {
            return 'ttt';
        }
        if (strpos($d, 'relay') !== false || strpos($d, 'team') !== false) {
            return 'relay';
        }
        return null;
    }

    public static function getQuotaForDistance(string $distanceName, array $quotas): array {
        $type = self::classifyDistance($distanceName) ?? 'relay';
        $defaults = self::getDefaultQuotas();
        $cfg = $quotas[$type] ?? $defaults[$type];
        $inti = max(1, (int)($cfg['inti'] ?? 1));
        $cadangan = max(0, (int)($cfg['cadangan'] ?? 0));

        return [
            'type'     => $type,
            'inti'     => $inti,
            'cadangan' => $cadangan,
            'min'      => $inti,
            'max'      => $inti + $cadangan,
        ];
    }

    public static function validateTeamCount(string $distanceName, int $count, array $quotas, string $labelSuffix = ''): ?string {
        $q = self::getQuotaForDistance($distanceName, $quotas);
        $label = trim($distanceName . ($labelSuffix ? ' ' . $labelSuffix : ''));

        if ($count < $q['min'] || $count > $q['max']) {
            if ($q['cadangan'] > 0) {
                return "Validasi Gagal: Kelas {$label} harus terdiri dari {$q['inti']} sampai {$q['max']} atlet ({$q['inti']} Inti + maks {$q['cadangan']} Cadangan). Anda mendaftar {$count} atlet.";
            }
            return "Validasi Gagal: Kelas {$label} harus terdiri dari tepat {$q['inti']} atlet ({$q['inti']} Inti). Anda mendaftar {$count} atlet.";
        }

        return null;
    }
}
