<?php

require_once __DIR__ . '/../app/Helpers/RollTeamHelper.php';

use App\Helpers\RollTeamHelper;

echo "--- RUNNING TEST: ROLL TEAM QUOTA & INTI/CADANGAN ---\n";

// 1. Distance classification
assert(RollTeamHelper::classifyDistance('Pair 500m') === 'pair');
assert(RollTeamHelper::classifyDistance('TTT') === 'ttt');
assert(RollTeamHelper::classifyDistance('Team Time Trial') === 'ttt');
assert(RollTeamHelper::classifyDistance('Team Sprint') === 'ttt');
assert(RollTeamHelper::classifyDistance('Relay 3000m') === 'relay');
assert(RollTeamHelper::classifyDistance('Relay 1000m') === 'relay');
assert(RollTeamHelper::classifyDistance('500m +D') === null);
echo "✓ Distance classification (Pair / TTT / Relay / Individual) [OK]\n";

// 2. Extract quotas & clamping
$quotas = RollTeamHelper::extractQuotas([
    'team_pair_inti' => 2,
    'team_pair_cadangan' => 1,
    'team_ttt_inti' => 4,
    'team_ttt_cadangan' => 0,
    'team_relay_inti' => 0,   // clamped to 1
    'team_relay_cadangan' => -2, // clamped to 0
]);
assert($quotas['pair'] === ['inti' => 2, 'cadangan' => 1]);
assert($quotas['ttt'] === ['inti' => 4, 'cadangan' => 0]);
assert($quotas['relay'] === ['inti' => 1, 'cadangan' => 0]);
echo "✓ Quota extraction & clamping [OK]\n";

// 3. Validate team count (Inti + Cadangan)
$custom = RollTeamHelper::extractQuotas([
    'team_pair_inti' => 2,
    'team_pair_cadangan' => 0,
    'team_ttt_inti' => 3,
    'team_ttt_cadangan' => 2,
    'team_relay_inti' => 3,
    'team_relay_cadangan' => 1,
]);

assert(RollTeamHelper::validateTeamCount('Pair 500m', 1, $custom) !== null);
assert(RollTeamHelper::validateTeamCount('Pair 500m', 2, $custom) === null);
assert(RollTeamHelper::validateTeamCount('Pair 500m', 3, $custom) !== null);

assert(RollTeamHelper::validateTeamCount('TTT', 2, $custom) !== null);
assert(RollTeamHelper::validateTeamCount('TTT', 3, $custom) === null);
assert(RollTeamHelper::validateTeamCount('TTT', 5, $custom) === null);
assert(RollTeamHelper::validateTeamCount('TTT', 6, $custom) !== null);

assert(RollTeamHelper::validateTeamCount('Relay 3000m', 2, $custom) !== null);
assert(RollTeamHelper::validateTeamCount('Relay 3000m', 3, $custom) === null);
assert(RollTeamHelper::validateTeamCount('Relay 3000m', 4, $custom) === null);
assert(RollTeamHelper::validateTeamCount('Relay 3000m', 5, $custom) !== null);
echo "✓ Team count validation (Pair / TTT / Relay) [OK]\n";

// 4. ensureColumns skips DDL inside active transaction
$mockDb = new class {
    public $inTx = true;
    public $execCalls = 0;
    public function inTransaction() { return $this->inTx; }
    public function exec($sql) { $this->execCalls++; return 1; }
};
RollTeamHelper::ensureColumns($mockDb);
assert($mockDb->execCalls === 0, 'DDL must not run inside active transaction');
$mockDb->inTx = false;
RollTeamHelper::ensureColumns($mockDb);
assert($mockDb->execCalls === 7, 'DDL should run for all 7 columns outside transaction');
echo "✓ ensureColumns transaction guard [OK]\n";

echo "--- ALL TEAM QUOTA & ROLE TESTS PASSED! ---\n";
