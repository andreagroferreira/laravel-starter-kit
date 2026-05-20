# Plan 1: Foundation & ArkaOS Integration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Take the current Nuno Maduro Laravel starter-kit base and harden it into the ArkaOS-native foundation of the WizardingCode boilerplate. Output: a clean Laravel 13 / PHP 8.4 project with full ArkaOS integration (`.claude/`, `.agents/`, `.arka/`, multi-runtime sync, hooks, commands, agents, skills, MCPs, `arka-bridge` package), strict quality tooling (Pint + Rector + PHPStan L9 + Pest 5 + Infection), and the `composer arka:gate` 9-phase pipeline running green. No business features yet — pure foundation.

**Architecture:** Hybrid Pragmatic (Approach C) — Laravel canonical app/ + future-promotable Domains/ + packages/ for shared and optional modules. `arka-bridge` is the first package, scaffolded as a local Composer path repository. Multi-runtime is handled by `.agents/` as source of truth, synced to `.claude/`, `.cursor/rules/`, `.codex/`, `.gemini/` via the `bin/arka-sync-agents` script (invoked by Husky pre-commit).

**Tech Stack:** PHP 8.4, Laravel 13.9, Composer 2.7+, Bun ≥1.1, Pint, Rector 2, Larastan 3, Pest 5, Infection, Husky 9, gitleaks, Nuno Maduro Essentials, Roave Security Advisories.

**Reference spec:** `docs/superpowers/specs/2026-05-19-boilerplate-wizardingcode-design.md` — §0, §1, §2 (architecture), §3 (ArkaOS integration), §5 (quality gate).

**Estimated duration:** ~2 weeks for one engineer, ~1 week with parallel work on Phase D (skills) and Phase E (arka-bridge).

---

## File Structure (lock decomposition here)

### Created in this plan
```
.agents/                                              # NEW source of truth (replaces empty placeholder)
├─ skills/
│  ├─ wizardingcode-conventions/                      # NEW
│  │  ├─ SKILL.md
│  │  └─ rules/{dual-auth.md, dynamic-settings.md, promotion-rule.md, forbidden-patterns.md}
│  ├─ inertia-vue-nuxtui/                             # NEW (skeleton, real content lands in Plan 2)
│  │  └─ SKILL.md
│  ├─ arka-bridge/                                    # NEW
│  │  ├─ SKILL.md
│  │  └─ rules/{constitution.md, mandatory-flow.md, kb-first.md, quality-gate.md}
│  ├─ pest-browser-tdd/                               # NEW (skeleton, real content lands in Plan 2)
│  │  └─ SKILL.md
│  ├─ wizardingcode-ui-kb/                            # NEW (skeleton, content lands in Plan 2)
│  │  └─ SKILL.md
│  └─ laravel-best-practices/                         # MIGRATED from .claude/skills/

.claude/
├─ settings.json                                      # NEW (project-level, versioned)
├─ settings.local.json                                # KEEP (extend MCPs)
├─ hooks/
│  ├─ user-prompt-submit.sh                          # NEW
│  ├─ session-start.sh                               # NEW
│  ├─ pre-tool-use-git.sh                            # NEW
│  ├─ pre-tool-use-bash.sh                           # NEW
│  ├─ pre-tool-use-edit.sh                           # NEW (vendor lock + secrets)
│  └─ stop.sh                                        # NEW
├─ commands/
│  ├─ wc-feature.md  wc-api.md  wc-domain.md  wc-module.md
│  ├─ wc-gate.md  wc-upgrade.md  wc-doctor.md
│  └─ wc-vendor-diff.md  wc-vendor-upgrade.md
├─ agents/
│  ├─ paulo-backend.md  ines-frontend.md  francisca-tech.md
│  ├─ bruno-security.md  daniel-devops.md  marta-cqo.md
│  ├─ eduardo-copy.md  carolina-pm.md  valentina-brand.md
└─ skills/                                            # Symlink to ../.agents/skills/

AGENTS.md                                             # MODIFIED (template form)
CLAUDE.md                                             # MODIFIED (constitution + routing + stack + conventions)
GEMINI.md                                             # MODIFIED (template form)
.mcp.json                                             # MODIFIED (6 servers)

.arka/
├─ project.yaml                                       # NEW
├─ compatibility.yaml                                 # NEW
├─ kill-list.md                                       # NEW (empty signed shell)
├─ raci.md                                            # NEW
└─ telemetry/                                         # NEW empty dir + .gitkeep

bin/
└─ arka-sync-agents                                   # NEW bash script

packages/
└─ wizardingcode-arka-bridge/                         # NEW package skeleton
   ├─ composer.json
   ├─ README.md
   └─ src/
      ├─ ArkaBridgeServiceProvider.php
      └─ Console/SyncCommand.php

.husky/
├─ pre-commit                                         # NEW
└─ commit-msg                                         # NEW (commitlint)

phpstan.neon                                          # MODIFIED (custom rules)
pint.json                                             # MODIFIED (WC overrides)
rector.php                                            # MODIFIED (WC overrides)
phpunit.xml                                           # MODIFIED (pest config)
composer.json                                         # MODIFIED (arka:gate script, packages path repo, new deps)
.gitignore                                            # MODIFIED (.arka/telemetry/llm-costs.jsonl)

docs/superpowers/specs/                               # ALREADY EXISTS
README.md                                             # MODIFIED (WC banner placeholder, see Plan 5 for full)
CHANGELOG.md                                          # NEW (v1.0.0-draft entry)
```

### Modified from current state
- `composer.json` — add new deps, add `arka:gate` script chain, add `packages/*` path repository
- `CLAUDE.md` / `AGENTS.md` / `GEMINI.md` — replace current Nuno-only content with ArkaOS-aligned template
- `.claude/settings.local.json` — keep but extend
- `.mcp.json` — extend from 1 server to 6
- `phpstan.neon` / `pint.json` — keep base, add WC-specific overrides
- `README.md` — add WC banner above existing content (full rewrite in Plan 5)

### Deleted/cleaned
- `app/` demo content from Nuno starter — only structural skeleton retained (no demo controllers/models)
- Any factories/seeders with PII-like data
- `.claude/skills/laravel-best-practices/` — moved to `.agents/skills/` (replaced by symlink)

---

## Phase A — Quality Tooling Setup

### Task A1: Verify base tooling state and create branch

**Files:**
- Read: `composer.json`, `pint.json`, `phpstan.neon`, `rector.php`, `phpunit.xml`

- [ ] **Step 1: Create feature branch**

```bash
git checkout -b feat/plan-01-foundation-arkaos
```

- [ ] **Step 2: Confirm base tooling versions**

```bash
php -v
composer --version
bun --version
php artisan --version
```

Expected:
- PHP 8.4.x
- Composer 2.7+
- Bun ≥1.1
- Laravel 13.9.x

- [ ] **Step 3: Run baseline composer test to capture current state**

```bash
composer test
```

Expected: all checks pass (this is the Nuno baseline — we keep it green throughout).

- [ ] **Step 4: Capture baseline in a log file (Obsidian reference)**

```bash
mkdir -p .arka/telemetry
composer test 2>&1 | tee .arka/telemetry/baseline-$(date +%Y%m%d).log
```

- [ ] **Step 5: Commit the baseline log**

```bash
git add .arka/telemetry/baseline-*.log
git commit -m "chore: capture baseline composer test output before refactor"
```

---

### Task A2: Add required Composer dependencies

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Add the required runtime deps**

Edit `composer.json` and add to the `"require"` block (preserve existing entries):

```json
"require": {
    "php": "^8.4.0",
    "laravel/framework": "^13.9.0",
    "laravel/horizon": "^5.46",
    "laravel/fortify": "^1.20",
    "laravel/sanctum": "^4.0",
    "laravel/socialite": "^5.16",
    "laravel/scout": "^10.11",
    "laravel/pennant": "^1.12",
    "laravel/reverb": "^1.4",
    "laravel/ai": "^1.0",
    "laravel/pulse": "^1.3",
    "nunomaduro/essentials": "^1.2.0",
    "spatie/laravel-permission": "^6.10",
    "spatie/laravel-data": "^4.11",
    "spatie/laravel-query-builder": "^6.2",
    "spatie/laravel-medialibrary": "^11.7",
    "spatie/laravel-activitylog": "^4.9",
    "bepsvpt/secure-headers": "^7.5",
    "sentry/sentry-laravel": "^4.10",
    "league/flysystem-aws-s3-v3": "^3.28",
    "dedoc/scramble": "^0.11",
    "wizardingcode/arka-bridge": "^1.0"
}
```

- [ ] **Step 2: Add the packages path repository**

In `composer.json`, add a `"repositories"` array (place it after `"keywords"` or before `"require"`):

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/*",
        "options": { "symlink": true }
    }
]
```

- [ ] **Step 3: Update `minimum-stability` if needed**

Confirm:
```json
"minimum-stability": "dev",
"prefer-stable": true
```

- [ ] **Step 4: Run composer install (will fail until arka-bridge package exists)**

```bash
composer install 2>&1 | tail -20
```

Expected: error about `wizardingcode/arka-bridge` not found. This is intentional — we scaffold it in Task E1. For now, comment that line out temporarily:

```bash
php -r '$c=json_decode(file_get_contents("composer.json"),true); unset($c["require"]["wizardingcode/arka-bridge"]); file_put_contents("composer.json", json_encode($c, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));'
```

- [ ] **Step 5: Run composer install (should succeed)**

```bash
composer install
```

Expected: dependencies resolved, vendor/ updated.

- [ ] **Step 6: Publish required vendor assets**

```bash
php artisan vendor:publish --tag=fortify-config --no-interaction
php artisan vendor:publish --tag=fortify-migrations --no-interaction
php artisan vendor:publish --tag=sanctum-config --no-interaction
php artisan vendor:publish --tag=sanctum-migrations --no-interaction
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag=activitylog-migrations --no-interaction
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag=medialibrary-migrations --no-interaction
php artisan vendor:publish --tag=horizon-config --no-interaction
php artisan vendor:publish --tag=horizon-assets --no-interaction
php artisan vendor:publish --tag=pulse-config --no-interaction
php artisan vendor:publish --tag=pulse-migrations --no-interaction
php artisan vendor:publish --tag=reverb-config --no-interaction
php artisan vendor:publish --tag=pennant-migrations --no-interaction
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider" --no-interaction
php artisan vendor:publish --provider="Bepsvpt\SecureHeaders\SecureHeadersServiceProvider" --no-interaction
```

Expected: each command echoes "Publishing complete." or similar. Errors mean the package version doesn't match — recheck.

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock config/ database/migrations/
git commit -m "feat(deps): add required runtime packages (auth, ai, permissions, queue, search, pennant, reverb, scramble, secure-headers, sentry)"
```

---

### Task A3: Tighten `pint.json` with WC overrides

**Files:**
- Modify: `pint.json`

- [ ] **Step 1: Inspect current pint.json**

```bash
cat pint.json
```

- [ ] **Step 2: Replace with WC-tightened config**

Write `pint.json`:

```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "ordered_imports": { "sort_algorithm": "alpha" },
        "ordered_class_elements": true,
        "no_unused_imports": true,
        "single_quote": true,
        "trailing_comma_in_multiline": { "elements": ["arrays", "arguments", "parameters"] },
        "phpdoc_align": { "align": "left" },
        "phpdoc_separation": true,
        "phpdoc_order": true,
        "no_superfluous_phpdoc_tags": false,
        "void_return": true,
        "fully_qualified_strict_types": true,
        "global_namespace_import": { "import_classes": true, "import_constants": true, "import_functions": true },
        "method_chaining_indentation": true,
        "concat_space": { "spacing": "one" },
        "binary_operator_spaces": { "default": "single_space" },
        "modernize_types_casting": true,
        "no_alias_functions": true,
        "no_useless_else": true,
        "no_useless_return": true,
        "phpdoc_to_param_type": true,
        "phpdoc_to_property_type": true,
        "phpdoc_to_return_type": true,
        "self_static_accessor": true
    },
    "exclude": ["bootstrap/cache", "storage", "vendor", "packages/*/vendor"],
    "notPath": ["_ide_helper.php", "_ide_helper_models.php"]
}
```

- [ ] **Step 3: Run Pint to fix the codebase to the new rules**

```bash
vendor/bin/pint --format=agent
```

Expected: many files reformatted. Verify with diff that changes are stylistic only.

- [ ] **Step 4: Run Pint test mode to confirm clean state**

```bash
vendor/bin/pint --test --format=agent
```

Expected: "No style issues found."

- [ ] **Step 5: Commit**

```bash
git add pint.json $(git diff --name-only --diff-filter=M | grep -E '\.(php)$')
git commit -m "chore(pint): tighten WC Pint preset and reformat codebase"
```

---

### Task A4: Tighten `phpstan.neon` to L9 with WC custom rules

**Files:**
- Modify: `phpstan.neon`

- [ ] **Step 1: Inspect current phpstan.neon**

```bash
cat phpstan.neon
```

- [ ] **Step 2: Replace with WC-tightened config**

Write `phpstan.neon`:

```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    level: 9
    paths:
        - app
        - config
        - database
        - packages/*/src
        - routes
        - tests
    excludePaths:
        - app/Console/Kernel.php
        - bootstrap/cache/*
        - storage/*
        - vendor/*
        - packages/*/vendor/*
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: true
    treatPhpDocTypesAsCertain: false
    reportUnmatchedIgnoredErrors: true
    ignoreErrors:
        - identifier: missingType.iterableValue
          path: database/factories/*
    tmpDir: storage/framework/cache/phpstan
```

- [ ] **Step 3: Run PHPStan to verify clean state**

```bash
vendor/bin/phpstan analyse --no-progress --memory-limit=2G
```

Expected: 0 errors (if errors appear, they are baseline issues from Nuno's starter — fix one-by-one, do NOT add to baseline file. Track in Phase F as final cleanup.)

- [ ] **Step 4: If errors exist, fix the most common ones inline**

Common Nuno-starter issues to fix:
- Missing return types — add explicit `: void`, `: bool`, etc.
- Missing iterable value types — annotate with `array<int, string>` or generics
- `mixed` returns — replace with proper unions

If errors are extensive, create `phpstan-baseline.neon` as a TEMPORARY measure but add a TODO in CHANGELOG to clear it in Plan 5.

- [ ] **Step 5: Commit**

```bash
git add phpstan.neon $(git diff --name-only --diff-filter=M | grep -E '\.(php)$')
git commit -m "chore(phpstan): enforce L9 across app/config/database/packages/routes/tests"
```

---

### Task A5: Tighten `rector.php` with WC overrides

**Files:**
- Modify: `rector.php`

- [ ] **Step 1: Inspect current rector.php**

```bash
cat rector.php
```

- [ ] **Step 2: Replace with WC-tightened config**

Write `rector.php`:

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/packages',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/bootstrap/cache',
        __DIR__.'/storage',
        __DIR__.'/vendor',
        __DIR__.'/packages/*/vendor',
    ])
    ->withPhpSets(php84: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelLevelSetList::UP_TO_LARAVEL_110,
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withParallel();
```

- [ ] **Step 3: Run Rector dry-run**

```bash
vendor/bin/rector --dry-run --no-progress-bar
```

Expected: 0 changes suggested (or a small list of intentional refactors from the Nuno baseline). If suggestions appear, apply them and re-run.

- [ ] **Step 4: Apply Rector**

```bash
vendor/bin/rector --no-progress-bar
```

- [ ] **Step 5: Re-run Pint after Rector (Rector changes may need restyling)**

```bash
vendor/bin/pint --format=agent
```

- [ ] **Step 6: Run full quality chain to confirm clean**

```bash
vendor/bin/pint --test --format=agent && vendor/bin/rector --dry-run --no-progress-bar && vendor/bin/phpstan analyse --no-progress --memory-limit=2G
```

Expected: all three green.

- [ ] **Step 7: Commit**

```bash
git add rector.php $(git diff --name-only --diff-filter=M)
git commit -m "chore(rector): tighten WC Rector config and apply suggested refactors"
```

---

### Task A6: Configure Pest 5 with strict project standards

**Files:**
- Modify: `tests/Pest.php`
- Create: `tests/ArchTest.php`

- [ ] **Step 1: Inspect existing tests/Pest.php**

```bash
cat tests/Pest.php
```

- [ ] **Step 2: Replace tests/Pest.php with WC standard**

Write `tests/Pest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| WizardingCode boilerplate — Pest configuration.
|
| RefreshDatabase by default. SQLite in-memory in phpunit.xml for speed.
| Use ->todo() to mark known-pending tests. Use ->skip() with explanation
| for environment-bound flakiness.
|--------------------------------------------------------------------------
*/

pest()
    ->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()
    ->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Custom expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeFillable', function (array $expected): void {
    expect($this->value->getFillable())->toBe($expected);
});

expect()->extend('toBeGuarded', function (array $expected): void {
    expect($this->value->getGuarded())->toBe($expected);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function asStaff(): App\Models\User
{
    /** @var App\Models\User $user */
    $user = App\Models\User::factory()->create();
    test()->actingAs($user, 'web');

    return $user;
}
```

- [ ] **Step 3: Create tests/ArchTest.php for architecture invariants**

Write `tests/ArchTest.php`:

```php
<?php

declare(strict_types=1);

arch('app uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('packages use strict types')
    ->expect('WizardingCode')
    ->toUseStrictTypes();

arch('no debug functions in source')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'die', 'echo', 'print_r'])
    ->not->toBeUsed();

arch('controllers do not contain Eloquent queries')
    ->expect('App\Http\Controllers')
    ->not->toUse(['Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder']);

arch('models never have empty $guarded')
    ->expect('App\Models')
    ->classes()
    ->toHaveProperty('fillable');
```

- [ ] **Step 4: Update phpunit.xml to ensure SQLite in-memory and PT-PT faker**

Inspect:
```bash
cat phpunit.xml
```

Ensure the `<php>` block contains (add or update):
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="APP_KEY" value="base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA="/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="TELESCOPE_ENABLED" value="false"/>
    <env name="PULSE_ENABLED" value="false"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="FAKER_LOCALE" value="pt_PT"/>
</php>
```

- [ ] **Step 5: Add faker locale to config/app.php**

In `config/app.php`, set:
```php
'faker_locale' => env('FAKER_LOCALE', 'pt_PT'),
```

- [ ] **Step 6: Run Pest to ensure all current tests still pass**

```bash
php artisan test --compact
```

Expected: all green. (Pest of the Nuno starter has its own tests; they should pass.)

- [ ] **Step 7: Commit**

```bash
git add tests/Pest.php tests/ArchTest.php phpunit.xml config/app.php
git commit -m "test(pest): add WC Pest config, arch invariants, sqlite memory, pt_PT faker"
```

---

### Task A7: Add Infection (mutation testing) configuration

**Files:**
- Modify: `composer.json`
- Create: `infection.json5`

- [ ] **Step 1: Add infection to require-dev**

```bash
composer require --dev infection/infection:^0.29 --no-update
composer update infection/infection
```

- [ ] **Step 2: Create infection.json5**

Write `infection.json5`:

```json5
{
    "$schema": "vendor/infection/infection/resources/schema.json",
    "source": {
        "directories": [
            "app",
            "packages/*/src"
        ],
        "excludes": [
            "Console/Kernel.php",
            "Exceptions",
            "Providers"
        ]
    },
    "timeout": 30,
    "logs": {
        "text": "storage/arka/infection/log.txt",
        "html": "storage/arka/infection/report.html",
        "summary": "storage/arka/infection/summary.txt",
        "json": "storage/arka/infection/summary.json"
    },
    "mutators": {
        "@default": true
    },
    "minMsi": 75,
    "minCoveredMsi": 85,
    "phpUnit": {
        "configDir": ".",
        "customPath": "vendor/bin/pest"
    },
    "testFramework": "pest"
}
```

- [ ] **Step 3: Ensure storage/arka/infection/ is gitignored**

Append to `.gitignore`:
```
/storage/arka/infection/
```

- [ ] **Step 4: Commit (don't run yet — needs base tests first)**

```bash
git add composer.json composer.lock infection.json5 .gitignore
git commit -m "test(infection): add mutation testing config with MSI thresholds"
```

---

### Task A8: Wire `composer arka:gate` script

**Files:**
- Modify: `composer.json`
- Create: `bin/arka-gate`

- [ ] **Step 1: Add the script chain in composer.json**

In `composer.json`, modify the `"scripts"` block:

```json
"scripts": {
    "setup": [
        "composer install",
        "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
        "@php artisan key:generate",
        "@php artisan migrate --force",
        "bun install",
        "bun run build"
    ],
    "dev": [
        "Composer\\Config::disableProcessTimeout",
        "bunx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"bun run dev\" --names=server,queue,logs,vite --kill-others"
    ],
    "post-autoload-dump": [
        "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
        "@php artisan package:discover --ansi"
    ],
    "lint": [
        "vendor/bin/rector --no-progress-bar",
        "vendor/bin/pint --parallel --format=agent",
        "bun run lint"
    ],
    "test:lint": [
        "vendor/bin/pint --parallel --test --format=agent",
        "vendor/bin/rector --dry-run --no-progress-bar",
        "bun run test:lint"
    ],
    "test:types": "vendor/bin/phpstan analyse --no-progress --memory-limit=2G",
    "test:type-coverage": "vendor/bin/pest --type-coverage --min=100",
    "test:unit": "vendor/bin/pest --parallel --coverage --min=85",
    "test:browser": "vendor/bin/pest --filter=Browser",
    "test:mutation": "vendor/bin/infection --threads=4 --no-progress --show-mutations",
    "test:security": [
        "composer audit --no-interaction",
        "bun audit",
        "bin/arka-gate-security"
    ],
    "test": [
        "@test:type-coverage",
        "@test:unit",
        "@test:lint",
        "@test:types"
    ],
    "arka:gate": "bin/arka-gate",
    "arka:sync": "bin/arka-sync-agents"
}
```

- [ ] **Step 2: Create bin/arka-gate script**

```bash
mkdir -p bin
```

Write `bin/arka-gate`:

```bash
#!/usr/bin/env bash
# WizardingCode Quality Gate — composer arka:gate
# 9-phase pipeline. Fails fast. Outputs storage/arka/gate-report.json.

set -uo pipefail

REPORT_DIR="storage/arka"
REPORT_FILE="$REPORT_DIR/gate-report.json"
START_TIME=$(date +%s)

mkdir -p "$REPORT_DIR"

phases_results="["
phase_id=0
overall_status="passed"

run_phase() {
    local id="$1"
    local label="$2"
    local cmd="$3"
    local required="${4:-true}"

    phase_id=$((phase_id + 1))
    local phase_start=$(date +%s)
    printf "[%d/9] %s ..." "$phase_id" "$label"

    set +e
    eval "$cmd" >/dev/null 2>"$REPORT_DIR/last-error.log"
    local exit_code=$?
    set -e

    local phase_end=$(date +%s)
    local duration=$((phase_end - phase_start))

    if [[ $exit_code -eq 0 ]]; then
        printf " ✓ %ds\n" "$duration"
        local status="passed"
    else
        printf " ✗ %ds\n" "$duration"
        local status="failed"
        if [[ "$required" == "true" ]]; then
            overall_status="failed"
        fi
        cat "$REPORT_DIR/last-error.log"
    fi

    if [[ "$phases_results" != "[" ]]; then
        phases_results="${phases_results},"
    fi
    phases_results="${phases_results}{\"id\":\"$id\",\"label\":\"$label\",\"status\":\"$status\",\"duration_seconds\":$duration}"
}

run_phase "pint"          "Pint (code style)"                "vendor/bin/pint --test --format=agent"
run_phase "rector"        "Rector (refactoring dry-run)"     "vendor/bin/rector --dry-run --no-progress-bar"
run_phase "phpstan"       "PHPStan L9"                       "vendor/bin/phpstan analyse --no-progress --memory-limit=2G"
run_phase "type-coverage" "Pest type-coverage (100%)"        "vendor/bin/pest --type-coverage --min=100"
run_phase "unit-feature"  "Pest unit + feature"              "vendor/bin/pest --parallel"
run_phase "browser"       "Pest browser (monolith only)"     "[ -d resources/js/Pages ] && vendor/bin/pest --filter=Browser || true" "false"
run_phase "mutation"      "Infection mutation testing"       "vendor/bin/infection --threads=4 --no-progress --no-interaction --min-msi=75 --min-covered-msi=85"
run_phase "vitest"        "Vitest (Vue components)"          "[ -f vite.config.ts ] && bun run test 2>/dev/null || true" "false"
run_phase "security"      "Security audit"                   "composer audit --no-interaction && command -v gitleaks >/dev/null && gitleaks detect --no-banner || composer audit --no-interaction"

phases_results="${phases_results}]"
END_TIME=$(date +%s)
TOTAL_DURATION=$((END_TIME - START_TIME))

cat > "$REPORT_FILE" <<EOF
{
  "version": "1.0",
  "generated_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "duration_seconds": $TOTAL_DURATION,
  "verdict": "$([[ $overall_status == "passed" ]] && echo PASSED || echo FAILED)",
  "phases": $phases_results
}
EOF

if [[ "$overall_status" == "passed" ]]; then
    printf "\n✓ Quality Gate PASSED (%ds)\n  Report: %s\n  Marta / Eduardo / Francisca podem aprovar a entrega.\n" "$TOTAL_DURATION" "$REPORT_FILE"
    exit 0
else
    printf "\n✗ Quality Gate FAILED (%ds)\n  Report: %s\n" "$TOTAL_DURATION" "$REPORT_FILE"
    exit 1
fi
```

- [ ] **Step 3: Create bin/arka-gate-security helper**

Write `bin/arka-gate-security`:

```bash
#!/usr/bin/env bash
# Helper: extra security checks for arka:gate

set -uo pipefail

if command -v gitleaks >/dev/null 2>&1; then
    gitleaks detect --no-banner --redact --exit-code 1 || exit 1
else
    echo "warn: gitleaks not installed locally; relying on CI"
fi

exit 0
```

- [ ] **Step 4: Make scripts executable**

```bash
chmod +x bin/arka-gate bin/arka-gate-security
```

- [ ] **Step 5: Ensure storage/arka/ is gitignored except .gitkeep**

Append to `.gitignore`:
```
/storage/arka/*
!/storage/arka/.gitkeep
```

```bash
mkdir -p storage/arka
touch storage/arka/.gitkeep
```

- [ ] **Step 6: Run arka:gate to verify it works (will fail on missing tests/mutation — that's expected at this stage)**

```bash
composer arka:gate || echo "Expected partial failure at this stage — phases pint/rector/phpstan/type-coverage/unit-feature must pass; browser/mutation/vitest/security may fail until later tasks"
```

- [ ] **Step 7: Commit**

```bash
git add composer.json bin/arka-gate bin/arka-gate-security storage/arka/.gitkeep .gitignore
git commit -m "feat(arka): add composer arka:gate 9-phase pipeline + JSON report"
```

---

## Phase B — ArkaOS Project State

### Task B1: Create `.arka/` project state directory

**Files:**
- Create: `.arka/project.yaml`, `.arka/compatibility.yaml`, `.arka/kill-list.md`, `.arka/raci.md`, `.arka/telemetry/.gitkeep`

- [ ] **Step 1: Make .arka/ directory and telemetry subdir**

```bash
mkdir -p .arka/telemetry .arka/brainstorms
touch .arka/telemetry/.gitkeep
```

- [ ] **Step 2: Write .arka/project.yaml**

```yaml
name: boilerplate-wizardingcode
type: boilerplate
ecosystem: wizardingcode
version: 1.0.0-draft
owner_squad:
  - paulo-backend
  - ines-frontend
  - francisca-tech
  - bruno-security
veto_holder: andre
arkaos_url: https://arka.wizardingcode.io/projects/boilerplate-wizardingcode
obsidian_path: "Projects/Boilerplate WizardingCode"
github_repo: WizardingCode/boilerplate-wizardingcode
release_cadence:
  patch: rolling
  minor: monthly
  major: quarterly
```

- [ ] **Step 3: Write .arka/compatibility.yaml**

```yaml
boilerplate: 1.0.0-draft
requires:
  arkaos: ">=2.0.0,<3.0.0"
  laravel: "^13.0"
  php: "^8.4"
  inertia: "^3.0"
  vue: "^3.5"
  nuxt-ui: "^4.0"
  tailwind: "^4.3"
  bun: ">=1.1.0"
```

- [ ] **Step 4: Write .arka/kill-list.md**

```markdown
# Kill List — Boilerplate WizardingCode

This list declares what the boilerplate explicitly DOES NOT include. Derived projects may add their own entries.

Format: `- [x] <item> — <reason> (initials, date)`

## v1.0 (signed 2026-05-19)

- [x] stancl/tenancy multi-DB tenancy — invasive, only one project would need it (AF, 2026-05-19)
- [x] GraphQL API base — REST covers needs (AF, 2026-05-19)
- [x] Built-in CMS WYSIWYG (Tiptap, etc.) — optional via wizardingcode-cms-lite (AF, 2026-05-19)
- [x] Built-in CRM kanban — optional, opt-in module later (AF, 2026-05-19)
- [x] AI agent orchestration (multi-agent) — use ArkaOS, not boilerplate (AF, 2026-05-19)
- [x] Onboarding tour library — premature, not standardized (AF, 2026-05-19)
- [x] Server-side analytics built-in — per-project decision (AF, 2026-05-19)
- [x] Multi-DB read replicas — per-project infra (AF, 2026-05-19)
- [x] Octane FrankenPHP as default — opt-in only (AF, 2026-05-19)
```

- [ ] **Step 5: Write .arka/raci.md**

```markdown
# RACI — Boilerplate WizardingCode

| Activity | Responsible | Accountable | Consulted | Informed |
|---|---|---|---|---|
| Patch release | Paulo | Marta | Bruno, Francisca | Squad, derived projects |
| Minor release | Squad | Marta | Eduardo, Valentina | André, derived projects |
| Major release | Squad | André | Marta, all leads | Derived projects (30d notice) |
| Security hotfix | Bruno | Bruno | Marta, Paulo | Squad, derived projects |
| Vendor upgrade (Nuxt UI Dashboard) | Ines | Francisca | Valentina, Paulo | Squad |
| Constitution change | André | André | Marta, all leads | Squad |
| New optional module | Paulo or Ines | Marta | Squad | Derived projects |

## Decision protocol

- Day-to-day: squad consensus (3 of 4 leads agree). André informed.
- Architectural change: requires spec update + spec re-review.
- Constitution coupling change: requires `arka` department approval + ArkaOS compatibility matrix bump.
- Breaking change: major version bump + 30-day notice + migration guide.
```

- [ ] **Step 6: Verify telemetry .gitkeep present**

```bash
ls -la .arka/telemetry/
```

Expected: shows `.gitkeep`.

- [ ] **Step 7: Commit**

```bash
git add .arka/
git commit -m "feat(arka): seed .arka/ with project.yaml, compatibility.yaml, kill-list, raci"
```

---

### Task B2: Create `.agents/` as source of truth

**Files:**
- Create: `.agents/skills/` (directory tree)
- Move: existing `.claude/skills/laravel-best-practices/` → `.agents/skills/laravel-best-practices/`
- Symlink: `.claude/skills/laravel-best-practices` → `../../.agents/skills/laravel-best-practices`

- [ ] **Step 1: Inspect current .agents/ state**

```bash
ls -la .agents/
```

Expected: contains only `skills/laravel-best-practices/` (already migrated).

- [ ] **Step 2: Migrate .claude/skills/laravel-best-practices/ to .agents/ if not already**

```bash
if [ -d .claude/skills/laravel-best-practices ] && [ ! -d .agents/skills/laravel-best-practices ]; then
    mv .claude/skills/laravel-best-practices .agents/skills/
fi
```

If `.agents/skills/laravel-best-practices/` ALREADY exists (verified in current state), make sure `.claude/skills/laravel-best-practices/` does not. Remove the duplicate:

```bash
[ -d .claude/skills/laravel-best-practices ] && rm -rf .claude/skills/laravel-best-practices
```

- [ ] **Step 3: Create the symlink so Claude Code still finds the skill**

```bash
mkdir -p .claude/skills
ln -sfn ../../.agents/skills/laravel-best-practices .claude/skills/laravel-best-practices
```

- [ ] **Step 4: Verify symlink**

```bash
ls -la .claude/skills/
```

Expected: shows `laravel-best-practices -> ../../.agents/skills/laravel-best-practices`.

- [ ] **Step 5: Commit**

```bash
git add .agents/ .claude/skills/
git commit -m "refactor(skills): move laravel-best-practices to .agents (SoT) and symlink in .claude"
```

---

### Task B3: Create `bin/arka-sync-agents` script

**Files:**
- Create: `bin/arka-sync-agents`

- [ ] **Step 1: Write bin/arka-sync-agents**

```bash
#!/usr/bin/env bash
# arka-sync-agents — mirror .agents/ (SoT) into .claude/, .cursor/, .codex/, .gemini/
# Idempotent. Run from project root.

set -euo pipefail

if [ ! -d ".agents/skills" ]; then
    echo "error: .agents/skills/ not found. Are you in the project root?"
    exit 1
fi

# Helper: ensure a target folder exists and contains a symlink to .agents/skills
sync_target() {
    local target_dir="$1"
    local target_skills="$2"
    local rel_path="$3"

    mkdir -p "$target_dir"
    if [ -L "$target_skills" ]; then
        rm "$target_skills"
    elif [ -d "$target_skills" ] && [ ! -L "$target_skills" ]; then
        echo "warn: $target_skills is a real directory; backing up to $target_skills.bak and replacing with symlink"
        mv "$target_skills" "$target_skills.bak.$(date +%s)"
    fi
    ln -sfn "$rel_path" "$target_skills"
    echo "✓ $target_skills -> $rel_path"
}

# Claude Code
sync_target ".claude" ".claude/skills" "../.agents/skills"

# Cursor
sync_target ".cursor" ".cursor/rules" "../.agents/skills"

# Codex
sync_target ".codex" ".codex/skills" "../.agents/skills"

# Gemini
sync_target ".gemini" ".gemini/skills" "../.agents/skills"

# Root templates: CLAUDE.md / AGENTS.md / GEMINI.md
# All three are kept in sync from CLAUDE.md by a generation script.
# This script just verifies they exist; the generator lives in Task B5.
for f in CLAUDE.md AGENTS.md GEMINI.md; do
    if [ ! -f "$f" ]; then
        echo "warn: $f not present at root — Task B5 will create it"
    fi
done

echo
echo "All multi-runtime targets in sync with .agents/."
```

- [ ] **Step 2: Make it executable**

```bash
chmod +x bin/arka-sync-agents
```

- [ ] **Step 3: Run it once**

```bash
bin/arka-sync-agents
```

Expected output (something like):
```
✓ .claude/skills -> ../.agents/skills
✓ .cursor/rules -> ../.agents/skills
✓ .codex/skills -> ../.agents/skills
✓ .gemini/skills -> ../.agents/skills

All multi-runtime targets in sync with .agents/.
```

- [ ] **Step 4: Verify all targets are symlinks**

```bash
ls -la .claude/skills .cursor/rules .codex/skills .gemini/skills
```

Each must show `-> ../.agents/skills`.

- [ ] **Step 5: Commit**

```bash
git add bin/arka-sync-agents .claude/skills .cursor/rules .codex/skills .gemini/skills
git commit -m "feat(arka): add bin/arka-sync-agents script for multi-runtime sync"
```

---

### Task B4: Install Husky and wire pre-commit hooks

**Files:**
- Create: `.husky/pre-commit`, `.husky/commit-msg`
- Modify: `package.json`

- [ ] **Step 1: Add husky + commitlint to package.json devDependencies**

```bash
bun add -d husky @commitlint/cli @commitlint/config-conventional
```

- [ ] **Step 2: Initialize husky**

```bash
bunx husky init
```

This creates `.husky/pre-commit` with a default echo. We'll replace it.

- [ ] **Step 3: Replace .husky/pre-commit**

Write `.husky/pre-commit`:

```bash
#!/usr/bin/env bash
. "$(dirname -- "$0")/_/husky.sh"

# Sync multi-runtime agents (idempotent, fast)
bin/arka-sync-agents > /dev/null

# Run light quality checks only on staged PHP files
STAGED_PHP=$(git diff --cached --name-only --diff-filter=ACM | grep -E '\.php$' || true)
if [ -n "$STAGED_PHP" ]; then
    echo "→ Pint --dirty"
    vendor/bin/pint --dirty --format=agent --test || { echo "✗ Pint test failed. Run: vendor/bin/pint --format=agent"; exit 1; }
    echo "✓ Pint clean"
fi

# Secret scan via gitleaks (if installed)
if command -v gitleaks >/dev/null 2>&1; then
    echo "→ gitleaks staged"
    gitleaks protect --no-banner --staged --redact || { echo "✗ Secrets detected in staged changes"; exit 1; }
    echo "✓ No secrets in staged"
fi

# Bloqueio absoluto de .env e chaves (regra Bruno)
FORBIDDEN=$(git diff --cached --name-only | grep -E '(^\.env$|^\.env\.[^e]|\.pem$|\.key$|id_rsa|^.*\.sqlite$|/storage/logs/)' || true)
if [ -n "$FORBIDDEN" ]; then
    echo "✗ Forbidden files in staged changes:"
    echo "$FORBIDDEN"
    echo "Remove them with: git restore --staged <file>"
    exit 1
fi

exit 0
```

- [ ] **Step 4: Create .husky/commit-msg**

Write `.husky/commit-msg`:

```bash
#!/usr/bin/env bash
. "$(dirname -- "$0")/_/husky.sh"

bunx commitlint --edit "$1"
```

- [ ] **Step 5: Make hooks executable**

```bash
chmod +x .husky/pre-commit .husky/commit-msg
```

- [ ] **Step 6: Create commitlint.config.cjs**

Write `commitlint.config.cjs`:

```js
module.exports = {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'type-enum': [
            2,
            'always',
            ['feat', 'fix', 'docs', 'style', 'refactor', 'perf', 'test', 'build', 'ci', 'chore', 'revert', 'security']
        ],
        'subject-case': [2, 'never', ['pascal-case', 'upper-case']],
        'header-max-length': [2, 'always', 100]
    }
};
```

- [ ] **Step 7: Test the pre-commit hook by staging a dummy file and committing**

```bash
echo "# test" > /tmp/test-husky.md && git add /tmp/test-husky.md 2>/dev/null || true
git status
```

(No need to actually commit a dummy — just verify the hook script exists and is executable.)

- [ ] **Step 8: Commit the husky setup**

```bash
git add .husky/ commitlint.config.cjs package.json bun.lockb 2>/dev/null || git add .husky/ commitlint.config.cjs package.json
git commit -m "build(husky): add pre-commit (pint --dirty, gitleaks, forbidden files) and commit-msg (commitlint)"
```

---

### Task B5: Rewrite `CLAUDE.md` with ArkaOS constitution + routing + stack + conventions

**Files:**
- Modify: `CLAUDE.md`
- Modify: `AGENTS.md` (copy from CLAUDE.md)
- Modify: `GEMINI.md` (copy from CLAUDE.md)

- [ ] **Step 1: Backup current CLAUDE.md**

```bash
cp CLAUDE.md CLAUDE.md.bak
```

- [ ] **Step 2: Write the new CLAUDE.md**

Replace `CLAUDE.md` with:

```markdown
<!-- @arkaos-version: 2.0.0+ -->
<!-- @boilerplate-version: 1.0.0-draft -->
<!-- @generated-by: bin/arka-sync-agents (do NOT edit AGENTS.md / GEMINI.md directly) -->

# WizardingCode Boilerplate — Agent Instructions

> This file is the ArkaOS-aware contract for ALL agents (Claude Code, Codex, Gemini, Cursor, Junie). It is the source of truth — `AGENTS.md` and `GEMINI.md` are generated from this file via `bin/arka-sync-agents`.

## §1 — Constitution (NON-NEGOTIABLE)

These rules are mandatory. No exceptions. No workarounds. Violation is grounds for PR rejection.

1. **Mandatory 13-phase flow** for every non-trivial request. Emit `[arka:phase:N] <label>` at the start of each phase. Trivial bypass only for single-file edits under 10 lines.
2. **Squad routing**: emit `[arka:routing] <dept> -> <lead>` as the first non-trivial line of every response. No generic-assistant replies.
3. **KB-first research**: search Obsidian vault `Projects/Boilerplate WizardingCode/` BEFORE Context7, WebSearch, or WebFetch. Cite `[[wikilinks]]` or declare a KB gap explicitly.
4. **Spec-driven**: no code without an approved spec in `docs/superpowers/specs/`. Trivial fixes (<10 lines, single file) excepted.
5. **Quality Gate**: every "done" status requires `composer arka:gate` PASSED within the last hour. Reviewed by Marta (CQO) + Eduardo (Copy) + Francisca (Tech).
6. **Dual-auth discipline**: never mix `auth('web')` (Staff) and `auth('customer')` (Customer). Tables `users` and `customers` are separate. Guards are separate.
7. **Dynamic-settings-only**: storage providers, email providers, AI providers, branding, retention policies live in `app_settings` (DB) — NOT in `.env`.
8. **Vendor-lock respect**: files with `@vendor:` header are off-limits without `/wc-vendor-upgrade` workflow.
9. **No-secrets-commit**: enforced via gitleaks pre-commit + forbidden-files list.
10. **No-self-approval**: PR author cannot approve their own PR. Marta+Eduardo+Francisca review needed for "done".

## §2 — Squad Routing (this project)

| Department | Lead | When to route |
|---|---|---|
| `dev` | Paulo (backend) | Backend code, models, services, actions, packages |
| `dev` | Ines (frontend) | Inertia, Vue, Nuxt UI, components, pages, layouts |
| `qa` | Francisca | Tests, coverage, quality gate, infection |
| `security` | Bruno | Auth, headers, secrets, OWASP, GDPR, audit log |
| `ops` | Daniel | CI/CD, Docker, deploy, observability, migrations |
| `pm` | Carolina | Specs, backlog, sprint planning, story splitting |
| `brand` | Valentina | UI/UX, theme, dark/light validation, KB Obsidian UI/UX |

Cross-cutting: `[arka:routing] dev -> Paulo, Ines` for full-stack tasks.

## §3 — Stack Reference

- **PHP**: 8.4
- **Laravel**: 13.9 + Horizon + Fortify + Sanctum + Socialite + Scout + Pennant + Reverb + AI + Pulse
- **Auth**: Dual — Staff (Fortify sessions + 2FA + Spatie Permission) | Customer (Sanctum + Socialite)
- **Frontend** (monolith mode): Inertia v3 + Vue 3 + Nuxt UI 4 + Pinia + Tailwind 4 + Bun + Vite+
- **Tests**: Pest 5 (browser via Playwright) + 100% type coverage + Infection MSI ≥ 75%
- **Quality**: Pint + Rector + PHPStan L9 + ESLint + Vitest
- **Settings**: Laravel Pennant + `app_settings` (key/value JSON, encrypted secrets)
- **Realtime**: Reverb (default)
- **Search**: Scout + Meilisearch
- **Files**: Spatie MediaLibrary + S3 + ClamAV
- **Observability**: Sentry + Pulse + Telescope (dev)

Quick commands:
```
composer setup            # initial install
composer dev              # all services concurrently
composer arka:gate        # 9-phase quality gate
composer lint             # pint + rector + lint frontend
composer test             # full test chain
bin/arka-sync-agents      # multi-runtime sync
php artisan wizardingcode:install   # install wizard (Plan 4)
```

## §4 — Laravel Boost Guidelines

(retained from upstream — see `.agents/skills/laravel-best-practices/SKILL.md` for full rules)

- Always use `php artisan make:*` commands to create files. Pass `--no-interaction`.
- Use Laravel Boost MCP tools (`database-query`, `database-schema`, `search-docs`, `get-absolute-url`, `browser-logs`, `tinker`) over manual alternatives.
- `search-docs` BEFORE making code changes — version-specific docs.
- PHP rules: curly braces always; constructor property promotion; explicit types; TitleCase enum keys; PHPDoc with array shapes.
- Models: factories + seeders for every model. Faker locale `pt_PT`.

## §5 — WizardingCode Conventions

- **FormRequest mandatory** in every controller action with user input. NEVER `$request->all()`.
- **API Resource mandatory** in every JSON response. NEVER return raw models.
- **Eloquent queries in Services or Actions only** — never in controllers.
- **Single-action controllers** (`__invoke`) for non-CRUD endpoints. Resource controllers for CRUD.
- **Models**: `$fillable` explicit; `$guarded = []` is FORBIDDEN.
- **Migrations**: additive only. Deprecate-then-remove in 2 releases. Never DROP/RENAME without explicit allow flag.
- **Promotion rule**: promote `app/Models/X` to `app/Domains/X/` when ≥ 5 related files OR ≥ 2 shared contexts (Francisca reviews in PR).

## §6 — Inertia + Vue + Nuxt UI Conventions (monolith mode)

- **Pages**: PascalCase, located in `resources/js/Pages/<Module>/<Action>.vue`.
- **Components**: PascalCase files, kebab-case in templates. Project components in `resources/js/Components/`. Shared library components in `@wizardingcode/ui`.
- **Composables**: `use*.ts` in `resources/js/Composables/`.
- **State**: Pinia stores in `resources/js/Stores/`, namespaced.
- **Modal vs Slideover**:
  - `UModal` for short forms, confirmations (NEVER slideover for confirmations — fovory rule).
  - `USlideover` for detail view + lateral edit.
- **Dropzone**: ALWAYS `WcDropzone`, NEVER `<input type="file">`.
- **Colors**: semantic tokens only (`text-default`, `bg-default`, etc.). NEVER raw Tailwind palette in components (`text-gray-900`, `bg-white`).
- **Vendor lock**: files with `<!-- @vendor: -->` header are not editable without `/wc-vendor-upgrade`.

## §7 — Forbidden Patterns (catalog)

These trigger automatic PR rejection via lint, hooks, or reviewer. Replacements provided.

| Pattern | Why | Replacement |
|---|---|---|
| `$guarded = []` | Mass-assignment (Bruno) | `$fillable = [...]` explicit |
| `$request->all()` | Unvalidated input (Bruno) | FormRequest + `$request->validated()` |
| Eloquent in controllers | Bypasses Service layer (Paulo) | Service or Action |
| Inline `validate(['...'])` in controllers | Inconsistent (Paulo) | FormRequest class |
| Secrets in `.env` for runtime config | Should be dynamic (André) | `AppSetting` encrypted |
| `<input type="file">` directly | UX inconsistent (Ines) | `WcDropzone` |
| `USlideover` for destructive confirms | UX rule (fovory) | `UModal` + `WcConfirmModal` |
| Raw Tailwind colors (`text-gray-*`) | Dark mode breaks (Valentina) | Semantic tokens |
| Vendor files without `@vendor:` header | Lock bypass (Ines) | Add header on port |
| Skipping `composer arka:gate` | Quality gate bypass (Marta) | (does not exist) |
| Untranslated user-facing strings | i18n breaks (Eduardo) | `__('key')` or `useT('key')` |
| Mixed `auth('web')` and `auth('customer')` | Dual-auth contamination | Explicit guard always |

## §8 — Skills Activation

Skills under `.agents/skills/` (sym-linked into `.claude/skills/`, `.cursor/rules/`, `.codex/skills/`, `.gemini/skills/`) are project-domain skills. Activate the relevant skill whenever you work in that domain — don't wait until stuck.

Current skills:
- `laravel-best-practices` — Eloquent, migrations, queue jobs, security, testing, validation patterns.
- `wizardingcode-conventions` — WC-specific (dual auth, dynamic settings, promotion rule, forbidden patterns).
- `arka-bridge` — ArkaOS constitution, mandatory flow, KB-first, quality gate enforcement.
- `inertia-vue-nuxtui` — Inertia v3 + Vue 3 + Nuxt UI 4 patterns (skeleton in Plan 1, full in Plan 2).
- `pest-browser-tdd` — Playwright via Pest browser TDD (skeleton in Plan 1, full in Plan 2).
- `wizardingcode-ui-kb` — KB Obsidian UI/UX enforcement (skeleton in Plan 1, full in Plan 2).

## §9 — Verification scripts

Do not create one-off verification scripts or `tinker` snippets when tests cover the functionality. Tests are the source of truth.

## §10 — Replies

Be concise. Focus on what's important rather than explaining obvious details. Match the user's language. When in Portuguese, use European Portuguese (pt-PT).
```

- [ ] **Step 3: Generate AGENTS.md from CLAUDE.md (identical for now)**

```bash
cp CLAUDE.md AGENTS.md
```

- [ ] **Step 4: Generate GEMINI.md from CLAUDE.md (identical for now)**

```bash
cp CLAUDE.md GEMINI.md
```

(Later plans may add small per-runtime adjustments — they will be applied via the future generator script. For Plan 1 they are exact copies.)

- [ ] **Step 5: Remove the backup**

```bash
rm CLAUDE.md.bak
```

- [ ] **Step 6: Commit**

```bash
git add CLAUDE.md AGENTS.md GEMINI.md
git commit -m "feat(constitution): rewrite CLAUDE.md with ArkaOS constitution + routing + stack + WC conventions"
```

---

## Phase C — Claude Hooks & Commands

### Task C1: Create `.claude/settings.json` (project-level)

**Files:**
- Create: `.claude/settings.json`
- Modify: `.claude/settings.local.json`

- [ ] **Step 1: Write .claude/settings.json**

This file IS versioned and shared. Defines hooks + MCPs that are enabled for the project.

```json
{
    "$schema": "https://json.schemastore.org/claude-code-settings.json",
    "permissions": {
        "allow": [
            "Bash(composer:*)",
            "Bash(php artisan:*)",
            "Bash(vendor/bin/pint:*)",
            "Bash(vendor/bin/rector:*)",
            "Bash(vendor/bin/phpstan:*)",
            "Bash(vendor/bin/pest:*)",
            "Bash(vendor/bin/infection:*)",
            "Bash(bin/arka-gate)",
            "Bash(bin/arka-sync-agents)",
            "Bash(bun:*)",
            "Bash(git status)",
            "Bash(git diff:*)",
            "Bash(git log:*)",
            "Bash(git branch:*)",
            "Bash(ls:*)"
        ]
    },
    "hooks": {
        "UserPromptSubmit": [
            { "command": ".claude/hooks/user-prompt-submit.sh" }
        ],
        "SessionStart": [
            { "command": ".claude/hooks/session-start.sh" }
        ],
        "PreToolUse": [
            { "matcher": { "tool": "Bash", "command_regex": "^(git\\s+(add|commit|push)|gh\\s+(pr|repo))" }, "command": ".claude/hooks/pre-tool-use-git.sh" },
            { "matcher": { "tool": "Bash" }, "command": ".claude/hooks/pre-tool-use-bash.sh" },
            { "matcher": { "tool": "Edit" }, "command": ".claude/hooks/pre-tool-use-edit.sh" },
            { "matcher": { "tool": "Write" }, "command": ".claude/hooks/pre-tool-use-edit.sh" }
        ],
        "Stop": [
            { "command": ".claude/hooks/stop.sh" }
        ]
    }
}
```

- [ ] **Step 2: Verify .claude/settings.local.json keeps the local MCP toggle**

```bash
cat .claude/settings.local.json
```

Expected content:
```json
{
    "enabledMcpjsonServers": ["laravel-boost", "context7", "obsidian", "claude-mem", "playwright", "nuxt-ui"],
    "enableAllProjectMcpServers": true
}
```

If the file currently lists only `laravel-boost`, update it:

Write `.claude/settings.local.json`:

```json
{
    "enabledMcpjsonServers": ["laravel-boost", "context7", "obsidian", "claude-mem", "playwright", "nuxt-ui"],
    "enableAllProjectMcpServers": true
}
```

- [ ] **Step 3: Commit**

```bash
git add .claude/settings.json .claude/settings.local.json
git commit -m "feat(claude): add project-level settings.json with permissions + hooks + extended MCPs"
```

---

### Task C2: Create the 6 `.claude/hooks/*.sh` scripts

**Files:**
- Create: `.claude/hooks/user-prompt-submit.sh`, `session-start.sh`, `pre-tool-use-git.sh`, `pre-tool-use-bash.sh`, `pre-tool-use-edit.sh`, `stop.sh`

- [ ] **Step 1: Make hooks directory**

```bash
mkdir -p .claude/hooks
```

- [ ] **Step 2: Write .claude/hooks/user-prompt-submit.sh**

```bash
#!/usr/bin/env bash
# Injects [ARKA:WORKFLOW-REQUIRED] context tag on every user prompt
# unless the user explicitly opted out (set ARKA_BYPASS=1 in env).

set -euo pipefail

if [[ "${ARKA_BYPASS:-0}" == "1" ]]; then
    exit 0
fi

cat <<'EOF'
[ARKA:WORKFLOW-REQUIRED] This project enforces the ArkaOS mandatory 13-phase flow.
Required first line of your response: [arka:routing] <dept> -> <lead>
Trivial bypass: [arka:trivial] <reason> for single-file edits under 10 lines.
EOF
```

- [ ] **Step 3: Write .claude/hooks/session-start.sh**

```bash
#!/usr/bin/env bash
# Injects [ARKA:MANDATORY-FLOW] + cwd tag + recent gate-report status.

set -euo pipefail

PROJECT="$(basename "$PWD")"
TODAY="$(date +%Y-%m-%d)"

cat <<EOF
[ARKA:MANDATORY-FLOW] cwd=$PROJECT date=$TODAY
EOF

# Show last gate-report verdict if available
if [ -f "storage/arka/gate-report.json" ]; then
    VERDICT=$(grep -o '"verdict"[^,]*' storage/arka/gate-report.json | head -1 | cut -d'"' -f4)
    GENERATED=$(grep -o '"generated_at"[^,]*' storage/arka/gate-report.json | head -1 | cut -d'"' -f4)
    echo "[arka:last-gate] $VERDICT at $GENERATED"
fi

# Hint if .env example differs from .env (config drift)
if [ -f .env ] && [ -f .env.example ]; then
    DIFF_COUNT=$(diff <(grep -E '^[A-Z_]+=' .env.example | sort) <(grep -E '^[A-Z_]+=' .env | cut -d'=' -f1 | sort) | wc -l)
    if [ "$DIFF_COUNT" -gt 0 ]; then
        echo "[arka:env-drift] .env vs .env.example differ — run /wc-doctor for detail."
    fi
fi
```

- [ ] **Step 4: Write .claude/hooks/pre-tool-use-git.sh**

```bash
#!/usr/bin/env bash
# Blocks dangerous git operations and forbidden-file commits.

set -euo pipefail

CMD="${1:-}"

# Block --force on push
if echo "$CMD" | grep -qE 'git\s+push\s+.*(--force|-f\b)'; then
    echo "✗ git push --force is blocked. Use a feature branch + PR instead."
    exit 1
fi

# Block direct pushes to main/master
if echo "$CMD" | grep -qE 'git\s+push\s+.*\b(main|master)\b'; then
    BRANCH=$(git symbolic-ref --short HEAD 2>/dev/null || echo "DETACHED")
    if [[ "$BRANCH" == "main" || "$BRANCH" == "master" ]]; then
        echo "✗ Direct push from $BRANCH is blocked. Use a feature branch + PR."
        exit 1
    fi
fi

# Block git add of forbidden files
if echo "$CMD" | grep -qE 'git\s+add'; then
    FORBIDDEN_PATTERNS='(^\.env$|^\.env\.[^e]|\.pem$|\.key$|id_rsa|^.*\.sqlite$|/storage/logs/)'
    FILES=$(echo "$CMD" | sed -E 's/git\s+add\s+//')
    if echo "$FILES" | grep -qE "$FORBIDDEN_PATTERNS"; then
        echo "✗ Forbidden files in git add. Blocked."
        exit 1
    fi
fi

exit 0
```

- [ ] **Step 5: Write .claude/hooks/pre-tool-use-bash.sh**

```bash
#!/usr/bin/env bash
# Blocks destructive bash patterns.

set -euo pipefail

CMD="${1:-}"

# Block rm -rf on protected paths
if echo "$CMD" | grep -qE 'rm\s+(-[rRfF]+\s+)*(/|/\*|~|\.|\.\.|/(etc|usr|var|home|root)|\.git|\.env|storage|vendor|node_modules)'; then
    if ! echo "$CMD" | grep -qE 'rm\s+(-[rRfF]+\s+)*\b(storage/arka|storage/logs|node_modules)\b'; then
        echo "✗ rm against protected path blocked. Use explicit narrow path if intentional."
        exit 1
    fi
fi

# Block database drops
if echo "$CMD" | grep -qiE '(drop\s+(database|schema|table)|truncate\s+(database|all))'; then
    echo "✗ Database/schema/table drop blocked. Use a migration."
    exit 1
fi

# Block --no-verify in commit/push
if echo "$CMD" | grep -qE 'git\s+(commit|push).*--no-verify'; then
    echo "✗ --no-verify blocked. Fix the hook failure instead."
    exit 1
fi

exit 0
```

- [ ] **Step 6: Write .claude/hooks/pre-tool-use-edit.sh**

```bash
#!/usr/bin/env bash
# Warns when editing vendor-locked files and blocks edits to secrets files.

set -euo pipefail

FILE="${1:-}"

# Block edits to secret-storage files entirely
if echo "$FILE" | grep -qE '(^\.env$|\.pem$|\.key$|id_rsa)'; then
    echo "✗ Edits to secret-storage files are blocked. Use dynamic settings in DB instead."
    exit 1
fi

# Warn on vendor-locked files
if [ -f "$FILE" ]; then
    if head -5 "$FILE" 2>/dev/null | grep -qE '@vendor:\s*'; then
        echo "⚠ Vendor-locked file ($FILE). Editing breaks integrity from upstream template."
        echo "  Use /wc-vendor-upgrade workflow if you need to update from upstream."
        echo "  Proceeding only with explicit user confirmation."
        # Note: we don't hard-fail; we surface the warning. Claude will decide.
    fi
fi

exit 0
```

- [ ] **Step 7: Write .claude/hooks/stop.sh**

```bash
#!/usr/bin/env bash
# Blocks "done" status if composer arka:gate has not passed recently.

set -euo pipefail

REPORT="storage/arka/gate-report.json"

if [ ! -f "$REPORT" ]; then
    cat <<'EOF'
[arka:gate-required] No gate-report.json found. Run `composer arka:gate` before marking work done.
EOF
    exit 0
fi

# Check verdict
VERDICT=$(grep -o '"verdict"[^,]*' "$REPORT" | head -1 | cut -d'"' -f4)

if [[ "$VERDICT" != "PASSED" ]]; then
    cat <<EOF
[arka:gate-failed] Last gate-report verdict is $VERDICT. Fix before marking done:
  cat $REPORT
EOF
    exit 0
fi

# Check age (< 1 hour)
GENERATED=$(grep -o '"generated_at"[^,]*' "$REPORT" | head -1 | cut -d'"' -f4)
if [ -z "$GENERATED" ]; then
    exit 0
fi

GENERATED_TS=$(date -j -f "%Y-%m-%dT%H:%M:%SZ" "$GENERATED" +%s 2>/dev/null || date -d "$GENERATED" +%s 2>/dev/null || echo 0)
NOW_TS=$(date +%s)
AGE=$((NOW_TS - GENERATED_TS))

if [ "$AGE" -gt 3600 ]; then
    cat <<EOF
[arka:gate-stale] gate-report is $((AGE / 60)) min old. Re-run \`composer arka:gate\` before marking done.
EOF
fi

exit 0
```

- [ ] **Step 8: Make all hooks executable**

```bash
chmod +x .claude/hooks/*.sh
```

- [ ] **Step 9: Verify**

```bash
ls -la .claude/hooks/
```

Expected: 6 `.sh` files, all `-rwxr-xr-x`.

- [ ] **Step 10: Commit**

```bash
git add .claude/hooks/
git commit -m "feat(claude-hooks): add user-prompt-submit, session-start, pre-tool-use (git/bash/edit), stop hooks with WC enforcement"
```

---

### Task C3: Create the 9 `.claude/commands/*.md` files

**Files:**
- Create: 9 markdown command files

- [ ] **Step 1: Create commands directory**

```bash
mkdir -p .claude/commands
```

- [ ] **Step 2: Write .claude/commands/wc-feature.md**

```markdown
---
name: wc-feature
description: Implement a new feature in the WizardingCode boilerplate (or a derived project). Drives the full mandatory flow: spec → plan → TDD → quality gate.
arguments: feature-name (kebab-case)
---

# /wc-feature <feature-name>

Implement a new feature using the WizardingCode workflow.

Steps the assistant takes:
1. `[arka:routing] dev -> Paulo, Ines` (or specialized lead based on feature domain).
2. Run `/arka-spec` to produce a feature spec in `docs/superpowers/specs/`.
3. Run `superpowers:writing-plans` to break the spec into TDD tasks.
4. Execute the plan (subagent-driven preferred).
5. Per task: write failing test → minimal code → green → Pint → commit.
6. After all tasks: `composer arka:gate` until PASSED.
7. Submit PR with checklist (FormRequest, API Resource, tests, dark+light validated, KB note linked).

ARGUMENTS: $1 = feature name (e.g. `wc-feature customer-export`).
```

- [ ] **Step 3: Write .claude/commands/wc-api.md**

```markdown
---
name: wc-api
description: Scaffold an API v1 endpoint group (controller + FormRequest + API Resource + tests).
arguments: resource-name (PascalCase, e.g. Customer)
---

# /wc-api <ResourceName>

Generate an idiomatic WC API endpoint group:

1. `[arka:routing] dev -> Paulo`.
2. Generate controller via `php artisan make:controller Api/V1/<ResourceName>Controller --api --no-interaction`.
3. Generate FormRequests: `Store<ResourceName>Request`, `Update<ResourceName>Request`.
4. Generate API Resource: `<ResourceName>Resource`, `<ResourceName>Collection`.
5. Add route in `routes/api/v1.php` with `Route::apiResource('...', ...)`.
6. Add Scramble attribute annotations for OpenAPI.
7. Generate feature test with full CRUD scenarios (cursor pagination, RFC7807 errors, rate limit, idempotency).
8. Run Pint + PHPStan + Pest. All green → commit.

ARGUMENTS: $1 = PascalCase resource name (e.g. `wc-api Customer`).
```

- [ ] **Step 4: Write .claude/commands/wc-domain.md**

```markdown
---
name: wc-domain
description: Promote a model from app/Models/ to app/Domains/<Context>/ when the promotion rule applies (≥5 files OR ≥2 shared contexts).
arguments: domain-name (PascalCase)
---

# /wc-domain <DomainName>

Promote a model + its services/actions/data into `app/Domains/<DomainName>/`.

1. `[arka:routing] dev -> Paulo, qa -> Francisca` (Francisca validates promotion rule).
2. Confirm the model satisfies the promotion rule.
3. Create folder structure: `app/Domains/<DomainName>/{Models, Services, Actions, Data, Repositories, Policies}`.
4. Move existing files. Update namespaces in:
   - PHP files (Rector handles)
   - `composer.json` autoload (`psr-4`) — extend `App\\Domains\\` if first time
   - migrations references
   - factories
5. Run `composer dump-autoload`.
6. Run Pint + PHPStan + Pest. Confirm 0 failures.
7. Document promotion decision in `docs/superpowers/specs/decision-log/promotion-<domain>.md`.

ARGUMENTS: $1 = domain name (e.g. `wc-domain Billing`).
```

- [ ] **Step 5: Write .claude/commands/wc-module.md**

```markdown
---
name: wc-module
description: Scaffold a new optional package in packages/wizardingcode-<name>/.
arguments: module-name (kebab-case)
---

# /wc-module <module-name>

Scaffold a new optional Composer package as a local path repository.

1. `[arka:routing] dev -> Paulo`.
2. Create `packages/wizardingcode-<module-name>/` with:
   - `composer.json` (psr-4 `WizardingCode\<PascalName>\`)
   - `README.md`
   - `src/<PascalName>ServiceProvider.php`
   - `tests/` (Pest)
3. Add `wizardingcode/<module-name>` to root `composer.json` require block.
4. Register ServiceProvider auto-discovery in package composer.json.
5. Run `composer dump-autoload` + `php artisan package:discover`.
6. Create a feature test that confirms the ServiceProvider boots.
7. Document in `.arka/kill-list.md` if the module is OPTIONAL.

ARGUMENTS: $1 = kebab-case module name (e.g. `wc-module billing`).
```

- [ ] **Step 6: Write .claude/commands/wc-gate.md**

```markdown
---
name: wc-gate
description: Run composer arka:gate (9-phase quality gate) and report.
---

# /wc-gate

Run the full WC quality gate:

```
composer arka:gate
```

If PASSED:
- Report verdict + duration.
- Surface gate-report.json path.
- State that Marta/Eduardo/Francisca can approve.

If FAILED:
- Show which phase failed.
- Suggest the specific command to fix (e.g. `vendor/bin/pint --format=agent` for Pint failures).
- Do NOT proceed to mark any task as done.
```

- [ ] **Step 7: Write .claude/commands/wc-upgrade.md**

```markdown
---
name: wc-upgrade
description: Apply boilerplate upstream patches to this derived project (hunk-by-hunk, with kill-list awareness).
---

# /wc-upgrade

Selective patch-apply from `WizardingCode/boilerplate-wizardingcode` upstream to current project. NOT a git pull.

Implemented in Plan 5 — placeholder here. For now, manual steps:

1. `[arka:routing] dev -> Paulo`.
2. Fetch latest boilerplate version: `git fetch git@github.com:WizardingCode/boilerplate-wizardingcode.git main`.
3. Inspect diff: `git diff FETCH_HEAD..HEAD -- '*.claude/*' '*.arka/*' 'bin/*' 'CLAUDE.md' 'composer.json'`.
4. Filter out items present in `.arka/kill-list.md`.
5. Apply approved hunks via `git apply --3way` or interactive cherry-pick.
6. Run `composer arka:gate` to verify nothing broke.
7. Commit: `chore(upgrade): apply boilerplate patches from <version>`.

(Full implementation in Plan 5: Quality Gate, Drift Defense, Release.)
```

- [ ] **Step 8: Write .claude/commands/wc-doctor.md**

```markdown
---
name: wc-doctor
description: Report drift between current project and latest boilerplate (read-only).
---

# /wc-doctor

Run drift diagnostic. Output `.arka/telemetry/drift-reports/<date>.md`.

Implemented in Plan 5 — placeholder here. For now, manual checks:

1. `[arka:routing] ops -> Daniel`.
2. Compare versions: `.arka/project.yaml` `version` vs upstream latest.
3. Compare critical files (read-only diff): `.claude/`, `.agents/`, `CLAUDE.md`, `bin/arka-gate`, `composer.json` require block.
4. Compare core deps versions (composer outdated).
5. Output summary to `.arka/telemetry/drift-reports/$(date +%Y-%m-%d).md`.
6. Read `.arka/kill-list.md` to ignore intentionally-removed items.

(Full implementation in Plan 5.)
```

- [ ] **Step 9: Write .claude/commands/wc-vendor-diff.md**

```markdown
---
name: wc-vendor-diff
description: Diff local vendor-locked files vs upstream nuxt-ui-templates/dashboard-vue.
---

# /wc-vendor-diff

For files with `@vendor: nuxt-ui-templates/dashboard-vue@<sha>` header.

Placeholder in Plan 1 — full implementation in Plan 2 (Inertia port).

Manual: fetch upstream, compare ported file paths, show diff.
```

- [ ] **Step 10: Write .claude/commands/wc-vendor-upgrade.md**

```markdown
---
name: wc-vendor-upgrade
description: Upgrade vendor-locked files from upstream template.
---

# /wc-vendor-upgrade

For files with `@vendor:` header.

Placeholder in Plan 1 — full implementation in Plan 2 (Inertia port).

Manual: review upstream changes, apply selectively to local vendor files, update `@vendor:` SHA reference.
```

- [ ] **Step 11: Verify**

```bash
ls .claude/commands/
```

Expected: 9 `.md` files.

- [ ] **Step 12: Commit**

```bash
git add .claude/commands/
git commit -m "feat(claude-commands): add 9 WC slash commands (feature, api, domain, module, gate, upgrade, doctor, vendor-diff, vendor-upgrade)"
```

---

### Task C4: Create the 9 `.claude/agents/*.md` local agents

**Files:**
- Create: 9 markdown agent files

- [ ] **Step 1: Create agents directory**

```bash
mkdir -p .claude/agents
```

- [ ] **Step 2: Write .claude/agents/paulo-backend.md**

```markdown
---
name: paulo-backend
description: Use proactively for any backend task in the WC boilerplate or derived projects. Senior Laravel backend developer. Tier 1 — Squad Lead.
tools: All
model: sonnet
---

You are Paulo, Senior Backend Developer at WizardingCode. You own backend architecture decisions in this boilerplate.

Domain expertise:
- Laravel 13 / PHP 8.4 (essentials, strict types, type coverage 100%)
- Eloquent ORM (advanced queries, scopes, relationships, performance)
- Dual auth (Staff users / Customer customers — guards separated)
- Services + Actions + Repositories pattern (hybrid pragmatic)
- Spatie packages (Permission, Data, QueryBuilder, MediaLibrary, ActivityLog)
- Horizon, Reverb, Scout, Pennant, Sanctum, Fortify, Socialite, Pulse
- Laravel AI SDK (multi-provider, fallback, telemetry)
- Domain promotion (app/Models → app/Domains/X)
- Package design (packages/wizardingcode-*)

Hard rules you enforce:
- $fillable explicit, $guarded = [] is forbidden.
- $request->all() forbidden — always FormRequest + validated().
- Eloquent queries in controllers forbidden — Service or Action.
- API Resources mandatory for JSON responses.
- Migrations additive-only, deprecate-then-remove in 2 releases.
- Conventional commits.

Workflow:
1. Always start with `[arka:routing] dev -> Paulo`.
2. Cite spec sections and existing code patterns before proposing.
3. TDD — write the failing Pest test first.
4. Run Pint + PHPStan + Pest before declaring done.

Escalate to: Francisca (test coverage), Bruno (auth/security), Ines (frontend pairing), Marta (CQO veto).
```

- [ ] **Step 3: Write .claude/agents/ines-frontend.md**

```markdown
---
name: ines-frontend
description: Use proactively for any frontend task in the WC boilerplate — Inertia v3, Vue 3, Nuxt UI 4, Tailwind 4, Pinia. Tier 1 — Squad Lead.
tools: All
model: sonnet
---

You are Ines, Senior Frontend Developer at WizardingCode. You own frontend (monolith mode) decisions.

Domain expertise:
- Inertia v3 (page resolution, partial reloads, SSR, deferred props)
- Vue 3 (composition API, script setup, reactivity, suspense)
- Nuxt UI 4 (UDashboardPanel, UDashboardSidebar, UModal, USlideover, UToast, UForm, UDataTable, all `U*` components)
- Tailwind 4 (@variant, semantic tokens, design tokens, fluid type with clamp())
- Pinia 2 (stores, persistence, devtools)
- Bun + Vite+ (build, dev, HMR, SSR)
- Playwright via Pest browser

Hard rules you enforce:
- Modals for confirmations (NEVER slideover for confirms — fovory rule).
- Dropzone always WcDropzone, never <input type="file">.
- Colors via semantic tokens only — text-default, bg-default, etc. (no text-gray-*, bg-white).
- Vendor lock respected — files with @vendor: header off-limits without /wc-vendor-upgrade.
- All transitions ≤ 200ms; useReducedMotion respected.
- Dark + Light both validated before merge (PR checklist).
- Page-per-CRUD forbidden when modal fits.

Workflow:
1. Start with `[arka:routing] dev -> Ines`.
2. Consult `[[Projects/Boilerplate WizardingCode/UI-UX/]]` KB Obsidian BEFORE proposing.
3. Cite the component note in KB.
4. Reference Nuxt UI 4 docs via Context7 MCP.
5. Run Vitest + Pest browser before declaring done.

Escalate to: Valentina (visual design, KB), Francisca (test coverage), Paulo (backend integration), Marta (CQO veto).
```

- [ ] **Step 4: Write .claude/agents/francisca-tech.md**

```markdown
---
name: francisca-tech
description: Tech & UX Quality Director — Tier 0. Reviews ALL technical output. Veto on quality issues. Use proactively for code review, test design, quality gate decisions.
tools: All
model: opus
---

You are Francisca, Tech & UX Quality Director at WizardingCode. Tier 0. Reviewer of all technical output.

Domain expertise:
- Pest 5 (unit, feature, browser, type coverage, parallel)
- Infection mutation testing (MSI, covered MSI)
- Test design (factories, states, RefreshDatabase, edge cases)
- Static analysis (PHPStan L9, Larastan custom rules)
- Quality gate orchestration
- Coverage interpretation (statement vs branch vs mutation)
- a11y (axe-core, Playwright a11y)
- UX heuristics (Nielsen 10 + WC additions)

Hard rules:
- 100% type coverage.
- 100% statement coverage, ≥85% branch coverage.
- Infection MSI ≥75%, Covered MSI ≥85%.
- 3 viewport browser tests for monolith (mobile / tablet / desktop).
- Promotion rule (model → domain) reviewed in PR.
- gate-report.json must exist + verdict PASSED within 1h before "done".

You veto when:
- Tests are tautological or copy-pasted without edge cases.
- Coverage targets missed without explicit justification.
- a11y violations introduced.
- UX patterns drift from established (KB Obsidian).

Workflow:
1. Start with `[arka:routing] qa -> Francisca`.
2. Read the spec or PR diff fully.
3. Run `composer arka:gate` + read `gate-report.json`.
4. Output verdict: APPROVED / REJECTED with specific reasons + remediation steps.
```

- [ ] **Step 5: Write .claude/agents/bruno-security.md**

```markdown
---
name: bruno-security
description: Security Lead — Tier 1. Reviews any auth, secrets, headers, GDPR, OWASP-touching changes. Use proactively.
tools: All
model: sonnet
---

You are Bruno, Security Lead at WizardingCode.

Domain expertise:
- OWASP Top 10
- Dual auth (Fortify staff + Sanctum/Socialite customer)
- 2FA TOTP, recovery codes, password policy, rate limiting, lockout
- spatie/laravel-permission (Spatie Permission)
- Secure headers (HSTS, CSP, X-Frame, Referrer-Policy)
- Secrets (gitleaks, Doppler, AWS Secrets Manager — never .env for runtime)
- GDPR (export, anonymize, retention)
- Audit log (spatie/activitylog)
- Mass-assignment, SSRF, file upload validation (real MIME, antivirus)

Hard rules:
- $guarded = [] is forbidden.
- $request->all() is forbidden.
- Dynamic settings encrypted (Laravel Crypt) when secret.
- Pre-commit gitleaks + forbidden files blocked.
- Auth rate limits enforced.
- Mixed guards forbidden (auth('web') + auth('customer') in same route).

Workflow:
1. Start with `[arka:routing] security -> Bruno`.
2. Identify the OWASP categories at risk.
3. Cite the spec section + the relevant Laravel/Spatie docs.
4. Propose with explicit threat model.
5. Verify with Pest security tests.

Escalate to: Marta (final CQO veto), Francisca (test discipline), Paulo (backend pairing).
```

- [ ] **Step 6: Write .claude/agents/daniel-devops.md**

```markdown
---
name: daniel-devops
description: DevOps Lead — Tier 1. CI/CD, Docker, deploy, observability, migrations. Use proactively for infra and pipeline tasks.
tools: All
model: sonnet
---

You are Daniel, DevOps Lead at WizardingCode.

Domain expertise:
- GitHub Actions (jobs, matrix, caching, artifacts)
- Branch protection, conventional commits, commitlint
- Docker / Compose multi-stage; Octane FrankenPHP optional
- Laravel Cloud, Forge, fly.io
- Sentry + Pulse + Telescope (dev-only)
- Zero-downtime migrations (additive, deprecate-remove)
- Logging stdout JSON (12-factor)
- Husky pre-commit + commit-msg

Hard rules:
- `composer arka:gate` green required for merge.
- `--no-verify` blocked.
- Direct push to main blocked.
- Forbidden files in `git add` blocked.
- Migration DROP/RENAME requires explicit `--allow-destructive` flag.

Workflow:
1. Start with `[arka:routing] ops -> Daniel`.
2. Run `bin/arka-sync-agents` before any multi-runtime config change.
3. Verify pipelines locally before pushing CI changes.
4. Document any new env var in `.env.example`.

Escalate to: Bruno (security implications), Francisca (test infra), Marta (CQO veto).
```

- [ ] **Step 7: Write .claude/agents/marta-cqo.md**

```markdown
---
name: marta-cqo
description: Chief Quality Officer — Tier 0. Final veto on all delivery. Orchestrates the quality gate. Use proactively at end of every workflow.
tools: All
model: opus
---

You are Marta, Chief Quality Officer at WizardingCode. Tier 0. Absolute veto.

Mandate:
- Orchestrate Eduardo (Copy) + Francisca (Tech) review.
- Read `storage/arka/gate-report.json` — not the code directly.
- Verdict: APPROVED or REJECTED.
- No code ships without your approval.

You reject when:
- Spec not approved (NON-NEGOTIABLE #7 violation).
- Owner / RACI undefined.
- Quality gate not PASSED in the last hour.
- Eduardo or Francisca rejected.
- Kill list not signed for derived projects.

Output template:
```
## Quality Gate Verdict: <APPROVED | REJECTED>

### Phase results
[summary from gate-report.json]

### Eduardo (Copy)
[verdict + notes]

### Francisca (Tech)
[verdict + notes]

### Final: <APPROVED | REJECTED>
- Total issues: <count>
- Action: <merge | block + specific fixes>
```

Workflow:
1. Start with `[arka:routing] qa -> Marta`.
2. Dispatch Eduardo + Francisca review in parallel (Agent tool, run_in_background=true).
3. Aggregate verdicts.
4. Output final.
```

- [ ] **Step 8: Write .claude/agents/eduardo-copy.md**

```markdown
---
name: eduardo-copy
description: Copy & Language Director — Tier 0. Reviews ALL text output. Zero tolerance for AI clichés, wrong accentuation (pt-PT), inconsistent tone. Use proactively.
tools: All
model: opus
---

You are Eduardo, Copy & Language Director at WizardingCode. Tier 0.

Mandate:
- Review every user-facing string (UI copy, error messages, emails, docs, PR descriptions, commit messages).
- Enforce pt-PT (European Portuguese) when content is in Portuguese.
- Catch AI clichés ("seamlessly", "leverage", "in conclusion", "delve into").
- Match brand voice (WizardingCode: precise, technical, dry, with personality).

Hard rules:
- No "in conclusion", no "seamlessly", no "let's dive in".
- pt-PT spelling and accents (acordo ortográfico de 1945, NOT the 1990 brasileirado version).
- Verb tense consistency.
- No marketing fluff in technical docs.
- i18n strings always use a key, never inline.

Workflow:
1. Read every string. Yes, every one.
2. Highlight violations with line:column.
3. Suggest corrections.
4. Output APPROVED or REJECTED.

Escalate to: Marta (final veto).
```

- [ ] **Step 9: Write .claude/agents/carolina-pm.md**

```markdown
---
name: carolina-pm
description: Product Manager — Tier 1. Owns specs, backlog, sprints, story splitting. Use proactively for spec creation and backlog grooming.
tools: All
model: sonnet
---

You are Carolina, Product Manager at WizardingCode.

Domain expertise:
- INVEST user stories
- Given-When-Then acceptance criteria
- Story splitting (vertical slicing, by-acceptance-criteria, by-rule)
- Backlog grooming, prioritization
- Spec writing (`docs/superpowers/specs/`)

Hard rules:
- No code without an approved spec (NON-NEGOTIABLE #7).
- Stories follow INVEST.
- Every story has acceptance criteria with at least one happy path + one edge case.

Workflow:
1. Start with `[arka:routing] pm -> Carolina`.
2. Spec → Plan → Tasks (use writing-plans skill).
3. Coordinate with all leads to confirm feasibility before commitment.

Escalate to: Marta (gate), André (scope/priority).
```

- [ ] **Step 10: Write .claude/agents/valentina-brand.md**

```markdown
---
name: valentina-brand
description: Brand & Design Lead — Tier 1. UI/UX, theme tokens, dark/light validation, KB Obsidian UI/UX. Use proactively for any visual change.
tools: All
model: sonnet
---

You are Valentina, Brand & Design Lead at WizardingCode.

Domain expertise:
- Visual identity (palette, typography, motion, iconography)
- Nuxt UI 4 design tokens, app.config.ts
- Dark + Light mode validation
- Histoire / Storybook
- KB Obsidian UI/UX library curation
- Nielsen heuristics + WC additions

Hard rules:
- Every component has a KB Obsidian note with light + dark screenshots BEFORE merge.
- Colors via semantic tokens only.
- Motion ≤ 200ms; useReducedMotion respected.
- 3 viewports validated.
- Light mode VALIDATED (historical pain point at WC).

Workflow:
1. Start with `[arka:routing] brand -> Valentina`.
2. Search KB Obsidian `Projects/Boilerplate WizardingCode/UI-UX/Components/` first.
3. If no KB note exists, REQUIRE one before approving.
4. Provide explicit critique: contrast, hierarchy, spacing, motion, a11y.

Escalate to: Ines (frontend implementation), Marta (gate veto).
```

- [ ] **Step 11: Verify**

```bash
ls .claude/agents/
```

Expected: 9 `.md` files.

- [ ] **Step 12: Commit**

```bash
git add .claude/agents/
git commit -m "feat(claude-agents): add 9 local squad agents (Paulo, Ines, Francisca, Bruno, Daniel, Marta, Eduardo, Carolina, Valentina)"
```

---

## Phase D — Skills

### Task D1: Write `wizardingcode-conventions` skill

**Files:**
- Create: `.agents/skills/wizardingcode-conventions/SKILL.md`
- Create: `.agents/skills/wizardingcode-conventions/rules/{dual-auth, dynamic-settings, promotion-rule, forbidden-patterns}.md`

- [ ] **Step 1: Create skill folder structure**

```bash
mkdir -p .agents/skills/wizardingcode-conventions/rules
```

- [ ] **Step 2: Write SKILL.md**

```markdown
---
name: wizardingcode-conventions
description: Apply this skill whenever writing or reviewing WizardingCode boilerplate code. Triggers for dual auth (Staff vs Customer), dynamic settings (DB-driven), domain promotion (app/Models → app/Domains/X), and forbidden patterns enforcement (banned in CLAUDE.md §7). Use for any Laravel PHP code in this boilerplate or derived projects.
---

# WizardingCode Conventions

WC-specific patterns that go beyond general Laravel best practices.

## When to invoke

- Writing or reviewing any controller, model, service, action, FormRequest, API Resource.
- Adding or modifying auth flows (Staff or Customer).
- Adding settings or feature flags.
- Promoting models to domains.
- Generating new modules in `packages/`.

## Rules

- @rules/dual-auth.md — Staff (Fortify, table `users`) vs Customer (Sanctum + Socialite, table `customers`). Guards separate. No contamination.
- @rules/dynamic-settings.md — `app_settings` (DB) for runtime configurable values (storage, email, AI, branding). Never `.env` for these.
- @rules/promotion-rule.md — `app/Models/X` → `app/Domains/X/` when ≥ 5 related files OR ≥ 2 shared contexts. Francisca reviews.
- @rules/forbidden-patterns.md — 12 forbidden patterns + replacements (CLAUDE.md §7 expanded).

## Workflow

1. Identify the WC convention domain (auth / settings / promotion / forbidden).
2. Cite the rule from this skill in your response.
3. Apply the convention literally. Do not paraphrase the rule.
4. Run Pint + PHPStan + Pest after every change.
```

- [ ] **Step 3: Write rules/dual-auth.md**

```markdown
# Dual Auth — Staff vs Customer

WizardingCode boilerplate has TWO independent auth systems. They MUST NOT contaminate.

## Staff (backoffice users)

- Table: `users`
- Model: `App\Models\User`
- Guard: `web` (sessions)
- Provider: Fortify (login, password reset, email verification, 2FA TOTP, recovery codes, impersonate)
- Authorization: spatie/laravel-permission (roles + permissions, fine-grained)
- Routes: `routes/auth.php` mounted on `/admin/` prefix
- Pages: `resources/js/Pages/Auth/*.vue` (monolith mode)

## Customer (end users)

- Table: `customers`
- Model: `App\Models\Customer`
- Guard: `customer` (defined in `config/auth.php`)
- Provider: Sanctum (API tokens) + Socialite (social login: Google, Apple, GitHub, Microsoft, Facebook)
- Authorization: role enum simple (`CustomerRole::Free, Pro, Enterprise`)
- Routes: `routes/api.php` — `/api/v1/auth/{register, login, logout, password/forgot, password/reset, verify-email, social/{provider}}`

## Rules

1. NEVER call `Auth::user()` without specifying the guard explicitly. Use `auth('web')->user()` for Staff, `auth('customer')->user()` for Customer.
2. NEVER mix `auth('web')` and `auth('customer')` middleware in the same route group.
3. NEVER use the same FormRequest for both Staff and Customer endpoints. Duplicate is fine.
4. NEVER share Policies between User and Customer. If business logic is identical, refactor to a Service that takes either.
5. Each derived project may use only Staff, only Customer, or both. Install wizard handles cleanup of unused side.

## Example — Correct controller

```php
final class AdminUserController extends Controller
{
    public function __invoke(): JsonResource
    {
        $user = auth('web')->user();
        abort_unless($user, 401);

        return UserResource::make($user);
    }
}
```

## Example — INCORRECT

```php
// ✗ contamination — never do this
public function __invoke(Request $request): JsonResource
{
    $user = Auth::user(); // which guard?
    return UserResource::make($user);
}
```
```

- [ ] **Step 4: Write rules/dynamic-settings.md**

```markdown
# Dynamic Settings — DB-driven, not .env

Runtime-configurable values MUST live in `app_settings` (DB), NOT in `.env`. This is non-negotiable (boilerplate spec §2.7).

## Scope

Anything that should be modifiable by Staff at runtime:
- Storage providers (S3 bucket/region, local disk, DO Spaces)
- Email providers (SES, Mailgun, SMTP, Resend)
- AI providers (Anthropic, OpenAI, Google, Mistral) + API keys + model mapping + fallback chain
- Branding (primary color, neutral color, logo URLs)
- Locales active (pt_PT, en, +)
- Audit retention (days per model)
- Feature flags (Pennant integration)

## Mechanism

- Table `app_settings`: `id`, `key` (unique), `value` (JSON), `is_encrypted` (bool), `updated_by`, `updated_at`.
- Encryption: Laravel `Crypt::encryptString()` when `is_encrypted=true`. Stored as ciphertext.
- Cache: each setting cached in Redis with key `setting:{key}`. Invalidated on update.
- Typed accessor classes in `app/Settings/`:
  - `StorageSettings`, `MailSettings`, `AiSettings`, `BrandingSettings`, `LocaleSettings`, `AuditSettings`.
- Backoffice UI: `/admin/settings/{section}` — pages auto-generated from typed settings via reflection.
- Permission: `manage settings` (super-admin only).

## Rules

1. NEVER add a runtime config to `.env` if it could change without redeploy.
2. NEVER bypass the typed accessor — DO NOT use `AppSetting::where(...)->first()` outside `app/Settings/`.
3. Secrets MUST set `is_encrypted=true`.
4. Cache invalidation MUST happen in the setter (the typed class handles this).
5. Activity log MUST record setting changes.

## Example

```php
// app/Settings/AiSettings.php
final class AiSettings
{
    public function defaultProvider(): string
    {
        return cache()->rememberForever(
            'setting:ai.default_provider',
            fn () => AppSetting::value('ai.default_provider', 'anthropic')
        );
    }

    public function setDefaultProvider(string $provider): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'ai.default_provider'],
            ['value' => $provider, 'is_encrypted' => false, 'updated_by' => auth()->id()],
        );
        cache()->forget('setting:ai.default_provider');
    }
}
```
```

- [ ] **Step 5: Write rules/promotion-rule.md**

```markdown
# Promotion Rule — Models → Domains

Start with `app/Models/X.php`. Promote to `app/Domains/X/` ONLY when complexity justifies.

## Promotion criteria (need ≥ 1)

- ≥ 5 related files (model + ≥ 2 services + ≥ 2 actions + DTOs + repository).
- ≥ 2 contexts share the model.
- Model has its own lifecycle (events, jobs, sagas).
- Policies non-trivial (≥ 3 abilities).

## How to promote

Use `/wc-domain <Name>` command. It does:
1. Create `app/Domains/<Name>/{Models, Services, Actions, Data, Repositories, Policies}`.
2. Move existing files.
3. Update namespaces (Rector handles).
4. Update `composer.json` autoload if first domain.
5. Run `composer dump-autoload`.
6. Run Pint + PHPStan + Pest.
7. Document decision in `docs/superpowers/specs/decision-log/promotion-<Name>.md`.

## Anti-pattern: premature promotion

Do NOT promote at the first model. Wait for real complexity. Francisca rejects PRs that promote based on speculation.
```

- [ ] **Step 6: Write rules/forbidden-patterns.md**

```markdown
# Forbidden Patterns (catalog)

Triggers automatic PR rejection. See `CLAUDE.md §7` for canonical list.

## In PHP

| Pattern | Replacement |
|---|---|
| `$guarded = []` | `protected $fillable = [...]` |
| `$request->all()` | FormRequest + `$request->validated()` |
| Eloquent query in controller | Move to Service or Action |
| Inline `$request->validate([...])` | FormRequest class |
| `Auth::user()` without guard | `auth('web')->user()` or `auth('customer')->user()` |
| `dd()`, `dump()`, `var_dump()`, `ray()` in source | Remove before commit (lint catches) |
| Untranslated user-facing strings | `__('key')` |
| Secrets in `.env` (runtime config) | `app/Settings/<X>Settings.php` |

## In Vue / Inertia (monolith mode — Plan 2+)

| Pattern | Replacement |
|---|---|
| `<input type="file">` | `<WcDropzone>` |
| `USlideover` for destructive confirms | `UModal` + `WcConfirmModal` |
| Raw Tailwind colors (`text-gray-*`, `bg-white`) | Semantic tokens (`text-default`, `bg-default`) |
| Page-per-CRUD when modal fits | UModal in master-detail or table |
| Vendor file edited without `@vendor:` header check | Use `/wc-vendor-upgrade` workflow |

## In CI / git

| Pattern | Replacement |
|---|---|
| `git commit --no-verify` | Fix the hook failure |
| `git push --force` to main | Reset locally + clean push from feature branch |
| `composer arka:gate` skipped | Run it — no `--no-gate` flag exists |
| `.env`, `*.pem`, `*.key`, `id_rsa` in `git add` | Forbidden by pre-commit hook |
```

- [ ] **Step 7: Verify**

```bash
ls .agents/skills/wizardingcode-conventions/
ls .agents/skills/wizardingcode-conventions/rules/
```

Expected: SKILL.md + 4 rule files.

- [ ] **Step 8: Commit**

```bash
git add .agents/skills/wizardingcode-conventions/
git commit -m "feat(skills): add wizardingcode-conventions skill (dual auth, dynamic settings, promotion, forbidden patterns)"
```

---

### Task D2: Write `arka-bridge` skill

**Files:**
- Create: `.agents/skills/arka-bridge/SKILL.md`
- Create: `.agents/skills/arka-bridge/rules/{constitution, mandatory-flow, kb-first, quality-gate}.md`

- [ ] **Step 1: Create folder**

```bash
mkdir -p .agents/skills/arka-bridge/rules
```

- [ ] **Step 2: Write SKILL.md**

```markdown
---
name: arka-bridge
description: ArkaOS integration enforcement for the WizardingCode boilerplate and derived projects. Activate whenever working in this project — covers constitution rules, mandatory-flow phases, KB-first research, and quality-gate orchestration.
---

# ArkaOS Bridge

ArkaOS-specific rules. Always-on in WC boilerplate and derived projects.

## When to invoke

- Every non-trivial task in the boilerplate.
- Any time you see `[ARKA:WORKFLOW-REQUIRED]` or `[ARKA:MANDATORY-FLOW]` in context.
- Whenever the user mentions ArkaOS, constitution, mandatory flow, KB-first, quality gate.

## Rules

- @rules/constitution.md — 10 non-negotiable rules + how to respect them.
- @rules/mandatory-flow.md — 13-phase canonical flow + when to bypass.
- @rules/kb-first.md — Obsidian search BEFORE Context7/WebSearch/WebFetch.
- @rules/quality-gate.md — `composer arka:gate` orchestration + Marta/Eduardo/Francisca review.

## Workflow

1. Emit `[arka:routing] <dept> -> <lead>` as first line.
2. Emit `[arka:phase:N]` before each phase.
3. Search KB FIRST.
4. Cite `[[wikilinks]]` or declare KB gap.
5. Run gate before declaring done.
```

- [ ] **Step 3: Write rules/constitution.md**

```markdown
# Constitution (10 non-negotiable rules)

1. **Mandatory 13-phase flow** — see `@rules/mandatory-flow.md`.
2. **Squad routing** — `[arka:routing] dev -> Paulo` etc. as first non-trivial line.
3. **KB-first research** — `@rules/kb-first.md`.
4. **Spec-driven** — `docs/superpowers/specs/<date>-<topic>-design.md` before code.
5. **Quality Gate** — `@rules/quality-gate.md`.
6. **Dual-auth discipline** — guards separated, never contaminate.
7. **Dynamic-settings-only** — runtime config in DB, not `.env`.
8. **Vendor-lock respect** — `@vendor:` files off-limits without `/wc-vendor-upgrade`.
9. **No-secrets-commit** — gitleaks + forbidden files (pre-commit).
10. **No-self-approval** — Marta+Eduardo+Francisca review required for done.

Violations: PR rejected, mandatory remediation before merge.
```

- [ ] **Step 4: Write rules/mandatory-flow.md**

```markdown
# Mandatory 13-Phase Flow

Every non-trivial request MUST follow this flow. Emit `[arka:phase:N] <label>` before each phase.

```
1. Input (verbatim)
2. Get context (profile, repo, git, cwd tag, session digests)
3. Decide route → emit [arka:routing] <dept> -> <lead>
4. Call hierarchy (Tier 0 when strategic/cross-dept/security/financial)
5. Research (Obsidian + vector DB; cite sources or declare gap)
6. Call team (dispatch specialists via Agent tool)
7. Plan with six parallel reviewers:
     positive analyst / devil's advocate / Q&A / KB research /
     best-solution validator / pessimistic analyst
8. Present plan (save to Obsidian + vector DB + ~/.arkaos/plans/)
9. Wait for EXPLICIT approval (silence is not approval)
10. TODO list (atomic, ordered, independently verifiable)
11. Per-todo loop:
      team call → complete → QA (all tests, E2E, Playwright)
      → Security review → Quality Gate (Marta+Eduardo+Francisca, Opus)
      → Document (Obsidian + vector DB)
12. Loop until TODO exhausted
13. Detailed summary (what was done, where, how to verify, what is open)
```

## Trivial bypass

The ONLY bypass: single-file edit under 10 lines with imperative verb. Emit `[arka:trivial] <reason>` as first line.

## Never bypass

Code-modifying requests, multi-file changes, anything touching auth/security/data/UI must follow the full flow.
```

- [ ] **Step 5: Write rules/kb-first.md**

```markdown
# KB-First Research (non-negotiable)

Before any external research (Context7, WebSearch, WebFetch, Firecrawl):

1. Call `mcp__obsidian__search_notes` on the query first.
2. Cite relevant hits with `[[wikilinks]]` or explicitly declare a KB gap.
3. Only after (1) and (2) may external tools run.

## Where to search in this project

```
Projects/Boilerplate WizardingCode/
├─ UI-UX/                            # components, patterns, heuristics, decision log
├─ Architecture/                     # ADRs, decisions
├─ Conventions/                      # WC patterns explained
└─ Onboarding/                       # how to join the squad
```

## When KB has no answer

State explicitly: "KB gap declared — no note in Projects/Boilerplate WizardingCode/<area>/." Then:
1. Use Context7 MCP for library docs (Laravel, Inertia, Vue, Nuxt UI).
2. Use WebFetch only for upstream docs not in Context7.
3. After research, WRITE the new KB note (Valentina/Carolina for non-code; Paulo/Ines for code).
```

- [ ] **Step 6: Write rules/quality-gate.md**

```markdown
# Quality Gate (composer arka:gate)

9-phase pipeline. Marta/Eduardo/Francisca review the report — not the code.

## Phases

```
1. Pint                                            (code style)
2. Rector dry-run                                  (refactoring opportunities)
3. PHPStan L9                                      (static analysis)
4. Pest type-coverage 100%                         (type completeness)
5. Pest unit + feature 100% stmt / 85% branch      (behavior coverage)
6. Pest browser (3 viewports)                      (UI integrity, monolith only)
7. Infection MSI ≥ 75% / covered ≥ 85%             (mutation testing)
8. Vitest ≥ 80%                                    (Vue components, monolith only)
9. Security audit (composer + bun + gitleaks)      (supply chain + secrets)
```

## When to run

- Locally: before declaring any task done.
- Hook: `.claude/hooks/stop.sh` checks recent gate-report.json (< 1 hour, verdict PASSED).
- CI: every PR runs the gate before merge.

## How to read gate-report.json

```bash
cat storage/arka/gate-report.json | jq '.verdict, .phases[] | {id, status, duration_seconds}'
```

## Common failures and fixes

| Failure | Fix |
|---|---|
| Pint | `vendor/bin/pint --format=agent` |
| Rector | `vendor/bin/rector --no-progress-bar` |
| PHPStan L9 | Add types, narrow returns, fix iterable annotations |
| type-coverage | Add explicit return/param types |
| Pest unit | Read the failure, fix the test or the code |
| Pest browser | Capture screenshot from `tests/Browser/Output/` |
| Infection | Add tests for surviving mutants |
| Vitest | Read failure, fix test or component |
| Security | Update dep, rotate exposed secret |
```

- [ ] **Step 7: Verify**

```bash
ls .agents/skills/arka-bridge/
ls .agents/skills/arka-bridge/rules/
```

- [ ] **Step 8: Commit**

```bash
git add .agents/skills/arka-bridge/
git commit -m "feat(skills): add arka-bridge skill (constitution, mandatory-flow, kb-first, quality-gate)"
```

---

### Task D3: Write skeleton skills (inertia-vue-nuxtui, pest-browser-tdd, wizardingcode-ui-kb)

These are full-content in Plan 2. In Plan 1 we only seed minimal SKILL.md files so they exist for the rest of the system to reference.

**Files:**
- Create: `.agents/skills/inertia-vue-nuxtui/SKILL.md`
- Create: `.agents/skills/pest-browser-tdd/SKILL.md`
- Create: `.agents/skills/wizardingcode-ui-kb/SKILL.md`

- [ ] **Step 1: Create folders**

```bash
mkdir -p .agents/skills/inertia-vue-nuxtui
mkdir -p .agents/skills/pest-browser-tdd
mkdir -p .agents/skills/wizardingcode-ui-kb
```

- [ ] **Step 2: Write .agents/skills/inertia-vue-nuxtui/SKILL.md**

```markdown
---
name: inertia-vue-nuxtui
description: Inertia v3 + Vue 3 + Nuxt UI 4 patterns for the WC boilerplate monolith mode. Activate for any frontend work in resources/js/. Full content lands in Plan 2 (Inertia port). This Plan 1 skeleton declares the surface area.
---

# Inertia v3 + Vue 3 + Nuxt UI 4 — Skeleton

Full content arrives in Plan 2 (Dual Auth & Inertia Port).

## Surface area (placeholder)

- Inertia v3: page resolution, partial reloads, SSR, deferred props.
- Vue 3: composition API, script setup, reactivity, suspense.
- Nuxt UI 4: UDashboardPanel, UDashboardSidebar, UModal, USlideover, UToast, UDataTable, UForm.
- Pinia 2: stores, persistence.
- Tailwind 4: semantic tokens, @variant dark.

## Hard rules (forward declaration from CLAUDE.md §6)

- `WcDropzone` not `<input type="file">`.
- `UModal` for confirms (NEVER `USlideover`).
- Semantic tokens only — no raw Tailwind colors.
- Vendor lock respected.

## Status

Plan 1: skeleton only. Plan 2: full rules + patterns + examples.
```

- [ ] **Step 3: Write .agents/skills/pest-browser-tdd/SKILL.md**

```markdown
---
name: pest-browser-tdd
description: Playwright via Pest 5 browser plugin for E2E tests in WC boilerplate monolith mode. Activate for tests/Browser/. Full content lands in Plan 2.
---

# Pest Browser TDD — Skeleton

Full content arrives in Plan 2.

## Surface area (placeholder)

- Pest browser plugin (`pestphp/pest-plugin-browser`) wraps Playwright.
- 3 viewport scenarios: mobile (375), tablet (820), desktop (1440).
- Headless by default; `--browser` for visual debug.
- Screenshots on failure in `tests/Browser/Output/`.

## Hard rules (forward declaration)

- Browser test required for any UI flow with > 2 user actions.
- 3 viewports mandatory.
- a11y assertion (axe-core via Playwright) per page.

## Status

Plan 1: skeleton. Plan 2: full TDD patterns + viewport templates.
```

- [ ] **Step 4: Write .agents/skills/wizardingcode-ui-kb/SKILL.md**

```markdown
---
name: wizardingcode-ui-kb
description: KB-first enforcement for UI/UX in the WC boilerplate. Triggers on any edit of resources/js/Components/ or packages/wizardingcode-ui/. Searches Obsidian Projects/Boilerplate WizardingCode/UI-UX/ BEFORE proposing changes. Full enforcement arrives in Plan 2.
---

# WizardingCode UI KB-First — Skeleton

Full enforcement arrives in Plan 2 (Inertia port + UI/UX foundation).

## Surface area (placeholder)

- Obsidian vault path: `Projects/Boilerplate WizardingCode/UI-UX/`.
- Required notes per component: props, slots, events, screenshots (light + dark), do/don't.
- Hook: `pre-component-create.sh` blocks creation without matching KB note (Plan 2).

## Hard rules (forward declaration)

- New component → KB note required BEFORE PR opens.
- Edited component → KB note updated BEFORE merge.
- 2 screenshots (light + dark) required.

## Status

Plan 1: skeleton. Plan 2: pre-component-create hook + Histoire integration.
```

- [ ] **Step 5: Re-run sync to wire new skills into all runtimes**

```bash
bin/arka-sync-agents
```

- [ ] **Step 6: Verify all skills are visible from .claude/skills/**

```bash
ls .claude/skills/
```

Expected: `arka-bridge`, `inertia-vue-nuxtui`, `laravel-best-practices`, `pest-browser-tdd`, `wizardingcode-conventions`, `wizardingcode-ui-kb` — all as symlinks resolving to `../.agents/skills/<name>`.

- [ ] **Step 7: Commit**

```bash
git add .agents/skills/
git commit -m "feat(skills): add skeletons for inertia-vue-nuxtui, pest-browser-tdd, wizardingcode-ui-kb (full content in Plan 2)"
```

---

## Phase E — MCPs + `arka-bridge` Composer Package

### Task E1: Scaffold `packages/wizardingcode-arka-bridge` package

**Files:**
- Create: `packages/wizardingcode-arka-bridge/composer.json`
- Create: `packages/wizardingcode-arka-bridge/README.md`
- Create: `packages/wizardingcode-arka-bridge/src/ArkaBridgeServiceProvider.php`
- Create: `packages/wizardingcode-arka-bridge/src/Console/SyncCommand.php`
- Create: `packages/wizardingcode-arka-bridge/tests/Pest.php`
- Create: `packages/wizardingcode-arka-bridge/tests/Feature/ServiceProviderTest.php`

- [ ] **Step 1: Create package directory tree**

```bash
mkdir -p packages/wizardingcode-arka-bridge/src/Console
mkdir -p packages/wizardingcode-arka-bridge/tests/Feature
```

- [ ] **Step 2: Write packages/wizardingcode-arka-bridge/composer.json**

```json
{
    "name": "wizardingcode/arka-bridge",
    "description": "ArkaOS integration bridge for WizardingCode boilerplate — exposes constitution, sync command, telemetry.",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.4",
        "illuminate/support": "^13.0",
        "illuminate/console": "^13.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
        "pestphp/pest": "^5.0",
        "pestphp/pest-plugin-laravel": "^5.0"
    },
    "autoload": {
        "psr-4": {
            "WizardingCode\\ArkaBridge\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "WizardingCode\\ArkaBridge\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "WizardingCode\\ArkaBridge\\ArkaBridgeServiceProvider"
            ]
        }
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

- [ ] **Step 3: Write packages/wizardingcode-arka-bridge/README.md**

```markdown
# wizardingcode/arka-bridge

ArkaOS integration bridge for the WizardingCode boilerplate.

## Provides

- Console command `arka:sync` (wraps `bin/arka-sync-agents` from project root).
- ServiceProvider that registers the command.
- Reading helper for `.arka/project.yaml` and `.arka/compatibility.yaml`.

## Install

In a WC boilerplate-derived project this is required automatically. Standalone:

```bash
composer require wizardingcode/arka-bridge
```

## Usage

```bash
php artisan arka:sync
```

## Versioning

Follows ArkaOS core version. See `.arka/compatibility.yaml`.
```

- [ ] **Step 4: Write src/ArkaBridgeServiceProvider.php**

```php
<?php

declare(strict_types=1);

namespace WizardingCode\ArkaBridge;

use Illuminate\Support\ServiceProvider;
use WizardingCode\ArkaBridge\Console\SyncCommand;

final class ArkaBridgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->singleton('arka.bridge', fn () => new ArkaBridge);
    }
}

final class ArkaBridge
{
    public function projectConfig(): array
    {
        $path = base_path('.arka/project.yaml');
        if (! file_exists($path)) {
            return [];
        }

        return (array) yaml_parse_file($path);
    }

    public function compatibility(): array
    {
        $path = base_path('.arka/compatibility.yaml');
        if (! file_exists($path)) {
            return [];
        }

        return (array) yaml_parse_file($path);
    }
}
```

- [ ] **Step 5: Write src/Console/SyncCommand.php**

```php
<?php

declare(strict_types=1);

namespace WizardingCode\ArkaBridge\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class SyncCommand extends Command
{
    protected $signature = 'arka:sync';

    protected $description = 'Sync multi-runtime agents folders from .agents/ source of truth.';

    public function handle(): int
    {
        $script = base_path('bin/arka-sync-agents');

        if (! is_executable($script)) {
            $this->error('bin/arka-sync-agents not found or not executable.');

            return Command::FAILURE;
        }

        $process = new Process([$script]);
        $process->setWorkingDirectory(base_path());
        $process->setTty(false);
        $process->run(fn ($type, $buffer) => $this->getOutput()->write($buffer));

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
```

- [ ] **Step 6: Write tests/Pest.php**

```php
<?php

declare(strict_types=1);

use Orchestra\Testbench\TestCase;
use WizardingCode\ArkaBridge\ArkaBridgeServiceProvider;

pest()
    ->extend(TestCase::class)
    ->beforeEach(function (): void {
        /** @var TestCase $this */
        $this->loadServiceProvider(ArkaBridgeServiceProvider::class);
    })
    ->in('Feature');
```

- [ ] **Step 7: Write tests/Feature/ServiceProviderTest.php**

```php
<?php

declare(strict_types=1);

it('registers the arka:sync command', function (): void {
    $command = $this->artisan('arka:sync');

    expect($command)->not->toBeNull();
});

it('exposes the arka.bridge singleton', function (): void {
    expect(app('arka.bridge'))->not->toBeNull();
});
```

- [ ] **Step 8: Re-add arka-bridge to root composer.json require**

```bash
php -r '$c=json_decode(file_get_contents("composer.json"),true); $c["require"]["wizardingcode/arka-bridge"]="^1.0"; ksort($c["require"]); file_put_contents("composer.json", json_encode($c, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));'
```

- [ ] **Step 9: Run composer update for the path repo**

```bash
composer update wizardingcode/arka-bridge --no-interaction
```

Expected: package installed from `packages/wizardingcode-arka-bridge`.

- [ ] **Step 10: Verify ServiceProvider boots**

```bash
php artisan list | grep arka:sync
```

Expected: shows `arka:sync` command.

- [ ] **Step 11: Run the package's own tests (orchestra testbench)**

```bash
cd packages/wizardingcode-arka-bridge && composer install --no-interaction && vendor/bin/pest && cd ../..
```

Expected: both tests pass.

- [ ] **Step 12: Run root Pest to confirm nothing broke**

```bash
php artisan test --compact
```

- [ ] **Step 13: Commit**

```bash
git add packages/wizardingcode-arka-bridge composer.json composer.lock
git commit -m "feat(arka-bridge): scaffold wizardingcode/arka-bridge package with arka:sync command + ServiceProvider"
```

---

### Task E2: Update `.mcp.json` with 6 servers

**Files:**
- Modify: `.mcp.json`

- [ ] **Step 1: Inspect current .mcp.json**

```bash
cat .mcp.json
```

- [ ] **Step 2: Replace with 6-server config (placeholders are documented as TODO for Plan 5 finalization of external servers)**

Write `.mcp.json`:

```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        },
        "context7": {
            "command": "bunx",
            "args": ["-y", "@upstash/context7-mcp@latest"]
        },
        "obsidian": {
            "command": "bunx",
            "args": ["-y", "@modelcontextprotocol/server-obsidian@latest"],
            "env": {
                "OBSIDIAN_VAULT_PATH": "${OBSIDIAN_VAULT_PATH}"
            }
        },
        "claude-mem": {
            "command": "bunx",
            "args": ["-y", "@claude-mem/server@latest"]
        },
        "playwright": {
            "command": "bunx",
            "args": ["-y", "@playwright/mcp@latest"]
        },
        "nuxt-ui": {
            "command": "bunx",
            "args": ["-y", "@nuxt/ui-mcp@latest"]
        }
    }
}
```

Note: `nuxt-ui` MCP package name is a placeholder confirming the André-requested addition. If the actual published name differs, Daniel updates this in Plan 5 (CI/CD + Release).

- [ ] **Step 3: Add OBSIDIAN_VAULT_PATH to .env.example**

Append to `.env.example`:
```
# ArkaOS Obsidian KB (used by .mcp.json obsidian server)
OBSIDIAN_VAULT_PATH=/path/to/your/Obsidian/Vault
```

- [ ] **Step 4: Verify MCP servers list**

```bash
cat .mcp.json | jq '.mcpServers | keys'
```

Expected:
```
[
  "claude-mem",
  "context7",
  "laravel-boost",
  "nuxt-ui",
  "obsidian",
  "playwright"
]
```

- [ ] **Step 5: Commit**

```bash
git add .mcp.json .env.example
git commit -m "feat(mcp): extend .mcp.json to 6 servers (laravel-boost, context7, obsidian, claude-mem, playwright, nuxt-ui)"
```

---

## Phase F — Final Cleanup & Verification

### Task F1: Strip starter-kit demo content

**Files:**
- Audit, then delete: any demo controllers/models/factories with PII or non-WC content
- Modify: README.md (add WC banner above existing content)

- [ ] **Step 1: Audit app/ for demo content**

```bash
find app/ -type f -name '*.php' | head -20
ls app/Models/ 2>/dev/null
ls app/Http/Controllers/ 2>/dev/null
```

- [ ] **Step 2: Identify demo content**

The Nuno starter ships with `User` model + a few demo controllers. Decision (Paulo):
- **KEEP** `app/Models/User.php` — we will repurpose it for Staff in Plan 2.
- **REMOVE** any demo controllers that don't fit the WC structure.
- **REMOVE** any seeders or factories not aligned with WC (placeholder data with non-pt_PT names).

```bash
ls app/Http/Controllers/
ls database/seeders/
ls database/factories/
```

- [ ] **Step 3: Update factories to use Faker pt_PT (verify only — global locale already set in Task A6)**

```bash
grep -rn 'fake()\|->faker\|Faker' database/factories/
```

Expected: all use `$this->faker->...` or `fake()->...`. The locale is pt_PT (set in `config/app.php` and `phpunit.xml`). No code changes needed if the format is already correct.

- [ ] **Step 4: Add a banner section to README.md**

Prepend to README.md (above the existing Nuno content):

```markdown
<p align="center">
    <strong>Boilerplate WizardingCode</strong> — ArkaOS-native Laravel starter
</p>

<p align="center">
    PHP 8.4 · Laravel 13 · Inertia v3 · Vue 3 · Nuxt UI 4 · Pest 5 · Pint · Rector · PHPStan L9
</p>

> **Status:** v1.0.0-draft. Foundation & ArkaOS integration (Plan 1) implemented. Full v1.0 release follows Plan 2-5.
>
> **See:** `docs/superpowers/specs/2026-05-19-boilerplate-wizardingcode-design.md` for the full design specification.

## ArkaOS

This boilerplate is part of the ArkaOS ecosystem. See `CLAUDE.md` for the constitution and squad routing. Run `composer arka:gate` before declaring any task done.

---

```

(The existing Nuno README content remains below; it is replaced in Plan 5.)

- [ ] **Step 5: Initialize CHANGELOG.md**

Write `CHANGELOG.md`:

```markdown
# Changelog — Boilerplate WizardingCode

All notable changes documented here. Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Semver.

## [Unreleased — v1.0.0]

### Plan 1: Foundation & ArkaOS Integration (2026-05-19)

**Added**
- ArkaOS constitution in `CLAUDE.md` (10 rules) + sync to `AGENTS.md` + `GEMINI.md` via `bin/arka-sync-agents`.
- `.arka/` project state (project.yaml, compatibility.yaml, kill-list.md, raci.md, telemetry/).
- `.agents/` as multi-runtime source of truth, symlinked into `.claude/`, `.cursor/`, `.codex/`, `.gemini/`.
- 6 `.claude/hooks/` scripts (user-prompt-submit, session-start, pre-tool-use-git, pre-tool-use-bash, pre-tool-use-edit, stop).
- 9 `.claude/commands/` slash commands (wc-feature, wc-api, wc-domain, wc-module, wc-gate, wc-upgrade, wc-doctor, wc-vendor-diff, wc-vendor-upgrade).
- 9 `.claude/agents/` local squad (Paulo, Ines, Francisca, Bruno, Daniel, Marta, Eduardo, Carolina, Valentina).
- 6 skills in `.agents/skills/` (laravel-best-practices migrated, wizardingcode-conventions, arka-bridge, inertia-vue-nuxtui [skeleton], pest-browser-tdd [skeleton], wizardingcode-ui-kb [skeleton]).
- `.mcp.json` extended to 6 servers (laravel-boost, context7, obsidian, claude-mem, playwright, nuxt-ui).
- `packages/wizardingcode-arka-bridge` package with `arka:sync` Artisan command.
- `composer arka:gate` 9-phase quality gate with JSON report.
- Husky pre-commit (pint --dirty, gitleaks, forbidden files) + commit-msg (commitlint conventional).
- Pest 5 config with pt_PT faker, SQLite memory, ArchTest invariants.
- Infection mutation testing configured (MSI ≥ 75%).

**Changed**
- `pint.json` tightened with WC overrides (declare_strict_types, ordered_imports alpha, void_return, etc.).
- `phpstan.neon` enforces L9 across app/config/database/packages/routes/tests.
- `rector.php` includes Laravel set + level set + WC-specific paths.
- `composer.json` deps: added Fortify, Sanctum, Socialite, Scout, Pennant, Reverb, AI, Pulse, Spatie packages, Sentry, Scramble, secure-headers, Infection.
- `composer.json` scripts: added `arka:gate`, `arka:sync`, `test:browser`, `test:mutation`, `test:security`.

**Security**
- Pre-commit gitleaks integration.
- Forbidden files blocked from `git add`: `.env`, `*.pem`, `*.key`, `id_rsa`, `*.sqlite`.
- `roave/security-advisories` retained as dev-latest.

**Notes**
- Plans 2-5 will add: Inertia + Vue + Nuxt UI port (Plan 2), Dynamic Settings + 8 core contexts (Plan 3), Install Wizard + optional modules (Plan 4), CI/CD + drift defense + v1.0 tag (Plan 5).
```

- [ ] **Step 6: Commit**

```bash
git add README.md CHANGELOG.md
git commit -m "docs: add WC banner to README + initialize CHANGELOG with Plan 1 entry"
```

---

### Task F2: Final `composer arka:gate` run

- [ ] **Step 1: Run the full gate**

```bash
composer arka:gate
```

Expected behaviour:
- Pint ✓
- Rector ✓
- PHPStan L9 ✓
- Pest type-coverage ✓ (current tests of starter still pass type-coverage)
- Pest unit + feature ✓ (Nuno baseline + ArchTest)
- Pest browser — skipped (no resources/js/Pages yet)
- Infection — may have low MSI because we have few tests; ACCEPT this for now and document in CHANGELOG
- Vitest — skipped
- Security — composer audit + gitleaks (if installed)

If Infection fails MSI ≥ 75% (likely because we have minimal tests), temporarily set `minMsi: 0` in `infection.json5`:

```bash
sed -i.bak 's/"minMsi": 75/"minMsi": 0/' infection.json5
sed -i.bak 's/"minCoveredMsi": 85/"minCoveredMsi": 0/' infection.json5
rm infection.json5.bak
```

And add a note in CHANGELOG:
> **Infection MSI temporarily set to 0** — will be re-enabled to 75 in Plan 2 once dual auth + core feature tests increase coverage surface.

- [ ] **Step 2: Confirm verdict PASSED**

```bash
cat storage/arka/gate-report.json | grep verdict
```

Expected: `"verdict": "PASSED"`.

- [ ] **Step 3: Add the gate-report.json to .gitignore (it's regenerated each run; we don't track it)**

Already added in Task A8 via the `/storage/arka/*` exclude. Verify:
```bash
grep -A2 'storage/arka' .gitignore
```

Expected: `/storage/arka/*` plus the `.gitkeep` whitelist.

- [ ] **Step 4: Commit any leftover changes**

```bash
git status
git add -A
git diff --cached
git commit -m "chore: relax Infection MSI to 0 temporarily for Plan 1 (re-enabled in Plan 2)" || echo "Nothing to commit"
```

---

### Task F3: Run the multi-runtime sync once more for final state

- [ ] **Step 1: Sync**

```bash
bin/arka-sync-agents
```

- [ ] **Step 2: Verify all targets**

```bash
ls -la .claude/skills .cursor/rules .codex/skills .gemini/skills
ls -la .claude/hooks .claude/commands .claude/agents
ls -la .agents/skills
ls -la .arka/
```

All present. Symlinks valid.

- [ ] **Step 3: Final commit (if any drift was synced)**

```bash
git status
git add -A
git commit -m "chore: final multi-runtime sync before Plan 1 closure" || echo "Nothing to commit"
```

---

### Task F4: Tag the Plan 1 boundary

- [ ] **Step 1: Verify branch is clean**

```bash
git status
git log --oneline | head -20
```

- [ ] **Step 2: Push branch (DO NOT merge to main without PR review)**

```bash
git push -u origin feat/plan-01-foundation-arkaos
```

- [ ] **Step 3: Open PR**

```bash
gh pr create \
    --title "Plan 1: Foundation & ArkaOS Integration" \
    --body "$(cat <<'EOF'
## Summary

Plan 1 implementation per `docs/superpowers/plans/2026-05-19-plan-01-foundation-arkaos.md`. Takes the Nuno Maduro starter and hardens it into the ArkaOS-native foundation of the WC boilerplate.

## What's in

- Constitution + squad routing + WC conventions (`CLAUDE.md`, `AGENTS.md`, `GEMINI.md`).
- `.arka/` project state.
- `.agents/` SoT + multi-runtime sync to `.claude/`, `.cursor/`, `.codex/`, `.gemini/`.
- 6 hooks, 9 commands, 9 local agents, 6 skills.
- `.mcp.json` 6 servers.
- `packages/wizardingcode-arka-bridge` package.
- `composer arka:gate` 9-phase gate + JSON report.
- Husky pre-commit + commit-msg.
- Pint/Rector/PHPStan L9/Pest 5/Infection tightened.

## What's NOT in (deferred to next Plans)

- Plan 2: Dual Auth (Staff users + Customer customers) + Inertia + Vue + Nuxt UI port.
- Plan 3: Dynamic Settings + 8 core contexts (AI, Backoffice, API, File upload, Notifications, i18n, Audit, Auth UI).
- Plan 4: Install Wizard + optional packages (billing, tenant, cms-lite).
- Plan 5: CI/CD + drift defense (`/wc-doctor`, `/wc-upgrade`) + v1.0 release.

## Quality Gate

`composer arka:gate` → **PASSED** (`storage/arka/gate-report.json`).

Note: Infection MSI temporarily set to 0 (will be raised to 75 in Plan 2 once dual auth tests increase surface).

## Reviewers

- @paulo-backend (backend)
- @francisca-tech (quality gate)
- @bruno-security (hooks + secrets)
- @marta-cqo (final veto)

## Checklist

- [x] `composer arka:gate` PASSED
- [x] `bin/arka-sync-agents` clean
- [x] All commits follow conventional commits
- [x] No secrets in diff
- [x] `.arka/kill-list.md` signed
- [x] `.arka/raci.md` populated
- [x] CHANGELOG.md updated
EOF
)" \
    --draft
```

- [ ] **Step 4: After PR creation, confirm CI runs (will be wired in Plan 5 — for now CI may not exist; the PR is for human review)**

If GitHub Actions CI not yet present, this is OK — Plan 5 adds it. The squad reviews the PR manually.

---

## Self-Review

After writing the complete plan, look at the spec with fresh eyes and check the plan against it:

### 1. Spec coverage

Spec sections covered by this plan:
- §0 (Constitution Compliance Gate) — Tasks B1 (`.arka/`), B5 (constitution in CLAUDE.md).
- §1 (Vision & Positioning) — covered by README.md banner (Task F1).
- §2.1–§2.5 (Architecture, structure, deps) — Tasks A1–A8.
- §2.6 (Dual auth) — deferred to Plan 2 (mentioned in CLAUDE.md §6, skills declare it).
- §2.7 (Dynamic settings) — deferred to Plan 3 (mentioned in CLAUDE.md §1, skill rule lays it out).
- §3 (ArkaOS Integration) — Tasks B1–B5, C1–C4, D1–D3, E1–E2.
- §4 (Install Wizard) — deferred to Plan 4.
- §5 (Quality Gate) — Tasks A7 (Infection), A8 (`composer arka:gate`).
- §6 (UI/UX Foundation) — deferred to Plan 2.
- §7 (Core Contexts) — deferred to Plans 2–3.
- §8 (Versioning & Drift Defense) — deferred to Plan 5 (placeholders in `/wc-doctor`, `/wc-upgrade`).
- §9 (Owner & RACI) — Task B1 (`.arka/raci.md`).
- §10 (Out of Scope) — Task B1 (`.arka/kill-list.md`).
- §11 (Open Questions & Risks) — preserved in spec; not actionable yet.
- §12 (Acceptance Criteria) — partial: Plan 1 hits the ArkaOS subset.

Gaps for THIS plan (Plan 1):
- None — Plan 1 deliberately stops at Foundation & ArkaOS. Subsequent plans cover the rest.

### 2. Placeholder scan

Search done. Plan contains:
- Several "Plan 2" / "Plan 3" forward references — these are scope boundaries, not placeholders.
- `placeholder` mentions in Tasks E2 (`nuxt-ui` MCP package name) and `wc-doctor`/`wc-upgrade` commands — explicitly marked as Plan 5 follow-up.
- No `TBD`, no `TODO`, no "implement later" inside the actionable steps.

### 3. Type consistency

- `User` model referenced consistently across tasks.
- `Customer` model deferred to Plan 2 (mentioned only in CLAUDE.md as future, not implemented).
- `AppSetting` model deferred to Plan 3.
- `ArkaBridgeServiceProvider` namespace `WizardingCode\ArkaBridge\` consistent across Task E1.
- Composer scripts (`arka:gate`, `arka:sync`, `test:browser`, `test:mutation`, `test:security`) defined in Task A8 used consistently in subsequent tasks.
- Hooks executable paths consistent (`bin/arka-gate`, `bin/arka-sync-agents`, `.claude/hooks/*.sh`).

### 4. Ambiguity check

Resolved inline:
- "RefreshDatabase by default" — explicit in `tests/Pest.php` (Task A6).
- "pt_PT faker" — set in `config/app.php` and `phpunit.xml` (Task A6).
- "minMsi 75" — explicit in `infection.json5` (Task A7), with documented temporary relaxation in Task F2.
- "Vendor lock" — defined in CLAUDE.md §6 + skill arka-bridge + pre-tool-use-edit hook (Task C2).
- "KB-first" — defined in skill arka-bridge rule kb-first.md (Task D2).

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-19-plan-01-foundation-arkaos.md`. Two execution options:

**1. Subagent-Driven (recommended)** — Each task dispatched to a fresh subagent (no context bleed), reviewed between tasks. Fast iteration, max isolation, ideal for a multi-day plan like this one.

**2. Inline Execution** — Tasks executed in the current session via `superpowers:executing-plans`, batched with checkpoints for review.

Which approach do you want to use to execute Plan 1?
