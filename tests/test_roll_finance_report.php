<?php

require_once __DIR__ . '/../app/Helpers/RollFinanceHelper.php';

use App\Helpers\RollFinanceHelper;

echo "--- RUNNING TEST: ROLL ADMIN FINANCE REPORT ---\n";

// Stub PDO supporting prepared queries used in RollFinanceHelper::buildEventFinanceReport
$mockDb = new class {
    public $queries = [];
    public function inTransaction() { return true; } // Skip DDL inside getEventFees
    public function prepare($sql) {
        $this->queries[] = $sql;
        return new class($sql) {
            private $sql;
            public function __construct($sql) { $this->sql = $sql; }
            public function execute($params = []) { return true; }
            public function fetch($mode = null) {
                if (strpos($this->sql, 'FROM roll_events WHERE id') !== false) {
                    return [
                        'fee_speed' => 400000, 'fee_speed_extra' => 150000, 'quota_speed_basic' => 2,
                        'fee_standart' => 350000, 'fee_standart_extra' => 0, 'quota_std_basic' => 2,
                        'fee_pemula' => 250000, 'fee_pemula_extra' => 100000, 'quota_pemula_basic' => 2,
                        'fee_eksebisi' => 150000, 'allow_pemula_standart_mix' => 0
                    ];
                }
                return false;
            }
            public function fetchAll($mode = null) {
                // 1. Regular entries (Club 10: 1 Speed skater with 3 races -> 400k + 150k = 550k; Club 20: 1 Pemula skater -> 250k Unpaid)
                if (strpos($this->sql, "e.manual_invoice_code = ''") !== false && strpos($this->sql, 'FROM roll_entries') !== false && strpos($this->sql, 'FROM roll_clubs') === false) {
                    return [
                        ['club_id' => 10, 'skater_id' => 1, 'class_name' => 'Speed', 'distance_name' => '200m', 'team_name' => null, 'race_class_id' => 1, 'created_at' => '2026-11-26 10:00:00'],
                        ['club_id' => 10, 'skater_id' => 1, 'class_name' => 'Speed', 'distance_name' => '500m', 'team_name' => null, 'race_class_id' => 2, 'created_at' => '2026-11-26 10:00:00'],
                        ['club_id' => 10, 'skater_id' => 1, 'class_name' => 'Speed', 'distance_name' => '1000m', 'team_name' => null, 'race_class_id' => 3, 'created_at' => '2026-11-26 10:00:00'],
                        ['club_id' => 20, 'skater_id' => 2, 'class_name' => 'Pemula', 'distance_name' => '100m', 'team_name' => null, 'race_class_id' => 4, 'created_at' => '2026-11-26 11:00:00'],
                    ];
                }
                // 2. Clubs + roll_payments (Club 10 Paid with stale stored_amount=0, Club 20 has no roll_payments row -> Unpaid)
                if (strpos($this->sql, 'FROM roll_clubs c') !== false) {
                    return [
                        ['club_id' => 10, 'club_name' => 'FORTIS', 'email' => 'fortis@test.id', 'payment_id' => 5, 'status' => 'Paid', 'stored_amount' => 0, 'payment_proof' => 'proof.jpg', 'payment_created_at' => '2026-11-26 12:00:00'],
                        ['club_id' => 20, 'club_name' => 'UNPAID CLUB', 'email' => 'unpaid@test.id', 'payment_id' => null, 'status' => 'Unpaid', 'stored_amount' => null, 'payment_proof' => null, 'payment_created_at' => null],
                    ];
                }
                // 3. Manual/Token entries (MAN-TOK-1 has 1 Standar skater)
                if (strpos($this->sql, 'manual_invoice_code IS NOT NULL') !== false && strpos($this->sql, 'FROM roll_entries e') !== false) {
                    return [
                        ['manual_invoice_code' => 'MAN-TOK-001', 'entry_club_id' => 30, 'entry_club_name' => 'TOKEN CLUB', 'skater_id' => 3, 'class_name' => 'Standart', 'distance_name' => '500m', 'team_name' => null, 'race_class_id' => 5, 'created_at' => '2026-11-26 13:00:00'],
                    ];
                }
                // 4. Token custom fee override (custom_fee_standart = 300000)
                if (strpos($this->sql, 'FROM roll_event_tokens') !== false) {
                    return [
                        ['manual_invoice_code' => 'MAN-TOK-001', 'custom_fee_speed' => null, 'custom_fee_standart' => 300000, 'custom_fee_pemula' => null, 'custom_fee_relay' => null],
                    ];
                }
                // 5. roll_manual_payments (MAN-TOK-001 Paid, stored_amount = 0)
                if (strpos($this->sql, 'FROM roll_manual_payments m') !== false) {
                    return [
                        ['id' => 9, 'club_id' => 30, 'status' => 'Paid', 'stored_amount' => 0, 'payment_proof' => 'tok.jpg', 'created_at' => '2026-11-26 14:00:00', 'invoice_code' => 'MAN-TOK-001', 'club_name' => 'TOKEN CLUB'],
                    ];
                }
                return [];
            }
        };
    }
};

$report = RollFinanceHelper::buildEventFinanceReport($mockDb, 5);
assert(count($report['transactions']) === 3);

// Dynamic calculation recovers stale/0 stored_amount & applies token custom fee override
assert($report['totals']['paid_klub'] === 550000.0, 'Club 10 must be 400k + 150k extra');
assert($report['totals']['paid_token'] === 300000.0, 'Token must use custom fee 300k');
assert($report['totals']['paid'] === 850000.0);
assert($report['totals']['unpaid'] === 250000.0, 'Club 20 without roll_payments row must appear as Unpaid 250k');
assert($report['totals']['potensi'] === 1100000.0);
assert($report['totals']['count_paid'] === 2);
assert($report['totals']['count_unpaid'] === 1);
echo "✓ Dynamic billing, stale-0 recovery, token custom fee & unpaid inclusion [OK]\n";

// Category reconciliation matches Paid total
$catTotal = $report['categories']['Speed']['total']
          + $report['categories']['Standart']['total']
          + $report['categories']['Pemula']['total']
          + $report['categories']['Lainnya']['total'];
assert($catTotal === 850000.0);
assert($report['categories']['Speed']['skaters'] === 1);
assert($report['categories']['Speed']['extra_count'] === 1);
assert($report['categories']['Standart']['skaters'] === 1);
echo "✓ Category breakdown reconciliation [OK]\n";

// Filtering by status, type, and search
assert(count(RollFinanceHelper::filterFinanceTransactions($report['transactions'], 'Paid', 'all', '')) === 2);
assert(count(RollFinanceHelper::filterFinanceTransactions($report['transactions'], 'Paid', 'Token', '')) === 1);
assert(count(RollFinanceHelper::filterFinanceTransactions($report['transactions'], 'all', 'all', 'fortis')) === 1);
assert(count(RollFinanceHelper::filterFinanceTransactions($report['transactions'], 'Unpaid', 'Klub', '')) === 1);
echo "✓ Transaction filtering (status / type / search) [OK]\n";

echo "--- ALL FINANCE REPORT TESTS PASSED! ---\n";
