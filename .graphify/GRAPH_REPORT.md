# Graph Report - .  (2026-07-15)

## Corpus Check
- 264 files · ~212,252 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 440 nodes · 256 edges · 26 communities detected
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output
- Edge kinds: contains: 131 · method: 106 · calls: 19


## Input Scope
- Requested: auto
- Resolved: committed (source: cli)
- Included files: 264 · Candidates: 550
- Excluded: 272 untracked · 470 ignored · 1 sensitive · 14 missing committed
- Recommendation: Use --scope all or graphify.yaml inputs.corpus for a knowledge-base folder.

## Graph Freshness
- Built from Git commit: `b62e8f5`
- Compare this hash to `git rev-parse HEAD` before trusting freshness-sensitive graph output.
## God Nodes (most connected - your core abstractions)
1. `EventsController` - 12 edges
2. `SeedingController` - 11 edges
3. `SwimmersController` - 11 edges
4. `EventProfileController` - 9 edges
5. `RecordsController` - 9 edges
6. `User` - 9 edges
7. `UsersController` - 8 edges
8. `LoginController` - 6 edges
9. `SettingsController` - 6 edges
10. `DashboardController` - 6 edges

## Surprising Connections (you probably didn't know these)
- None detected - all connections are within the same source files.

## Communities

### Community 0 - "Community 0"
Cohesion: 0.07
Nodes (29): roll_clubs, roll_entries, roll_event_details, roll_event_results, roll_events, roll_hero_images, roll_pelotons, roll_site_settings (+21 more)

### Community 1 - "Community 1"
Cohesion: 0.19
Nodes (1): EventsController

### Community 2 - "Community 2"
Cohesion: 0.21
Nodes (1): SeedingController

### Community 3 - "Community 3"
Cohesion: 0.20
Nodes (1): SwimmersController

### Community 4 - "Community 4"
Cohesion: 0.18
Nodes (2): r(), s()

### Community 5 - "Community 5"
Cohesion: 0.27
Nodes (1): EventProfileController

### Community 6 - "Community 6"
Cohesion: 0.27
Nodes (1): RecordsController

### Community 7 - "Community 7"
Cohesion: 0.20
Nodes (1): User

### Community 8 - "Community 8"
Cohesion: 0.25
Nodes (1): UsersController

### Community 9 - "Community 9"
Cohesion: 0.29
Nodes (1): DashboardController

### Community 10 - "Community 10"
Cohesion: 0.29
Nodes (1): LoginController

### Community 11 - "Community 11"
Cohesion: 0.29
Nodes (1): SettingsController

### Community 13 - "Community 13"
Cohesion: 0.33
Nodes (1): HomeController

### Community 14 - "Community 14"
Cohesion: 0.33
Nodes (1): MasterSettingsController

### Community 15 - "Community 15"
Cohesion: 0.33
Nodes (1): ProfileController

### Community 16 - "Community 16"
Cohesion: 0.40
Nodes (1): EntriesController

### Community 17 - "Community 17"
Cohesion: 0.40
Nodes (1): MaintenanceController

### Community 18 - "Community 18"
Cohesion: 0.40
Nodes (1): Database

### Community 19 - "Community 19"
Cohesion: 0.50
Nodes (1): MasterController

### Community 20 - "Community 20"
Cohesion: 0.50
Nodes (1): MasterFinanceController

### Community 21 - "Community 21"
Cohesion: 0.50
Nodes (1): RelayController

### Community 22 - "Community 22"
Cohesion: 0.83
Nodes (3): calculateTotalSharpness(), getBestSwimmerRanking(), timeToSeconds()

### Community 27 - "Community 27"
Cohesion: 0.67
Nodes (2): hitungKU(), hitungUsia()

### Community 29 - "Community 29"
Cohesion: 0.67
Nodes (1): Controller

### Community 31 - "Community 31"
Cohesion: 0.67
Nodes (2): event_historical_records, record_packages

### Community 35 - "Community 35"
Cohesion: 0.67
Nodes (1): TestController

## Knowledge Gaps
- **31 isolated node(s):** `record_packages`, `event_historical_records`, `roll_clubs`, `roll_entries`, `roll_events` (+26 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **Thin community `Community 1`** (1 nodes): `EventsController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 2`** (1 nodes): `SeedingController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 3`** (1 nodes): `SwimmersController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 4`** (2 nodes): `r()`, `s()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 5`** (1 nodes): `EventProfileController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 6`** (1 nodes): `RecordsController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 7`** (1 nodes): `User`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 8`** (1 nodes): `UsersController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 9`** (1 nodes): `DashboardController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 10`** (1 nodes): `LoginController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 11`** (1 nodes): `SettingsController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 13`** (1 nodes): `HomeController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 14`** (1 nodes): `MasterSettingsController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 15`** (1 nodes): `ProfileController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 16`** (1 nodes): `EntriesController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 17`** (1 nodes): `MaintenanceController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 18`** (1 nodes): `Database`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 19`** (1 nodes): `MasterController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 20`** (1 nodes): `MasterFinanceController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 21`** (1 nodes): `RelayController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 27`** (2 nodes): `hitungKU()`, `hitungUsia()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 29`** (1 nodes): `Controller`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 31`** (2 nodes): `event_historical_records`, `record_packages`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Community 35`** (1 nodes): `TestController`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **What connects `record_packages`, `event_historical_records`, `roll_clubs` to the rest of the system?**
  _31 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.06666666666666667 - nodes in this community are weakly interconnected._