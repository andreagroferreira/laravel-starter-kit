# Changelog — Boilerplate WizardingCode

All notable changes documented here. Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Semver.

## [Unreleased — v1.0.0]

### Plan 1: Foundation & ArkaOS Integration (2026-05-19)

**Added**
- ArkaOS constitution in `CLAUDE.md` (10 rules) + sync to `AGENTS.md` + `GEMINI.md` via `bin/arka-sync-agents`.
- `.arka/` project state (project.yaml, compatibility.yaml, kill-list.md, raci.md, telemetry/).
- `.agents/` as multi-runtime source of truth, symlinked into `.claude/`, `.cursor/`, `.codex/`, `.gemini/`, `.github/`, `.junie/`, `.kiro/`.
- 6 `.claude/hooks/` scripts (user-prompt-submit, session-start, pre-tool-use-git, pre-tool-use-bash, pre-tool-use-edit, stop).
- 9 `.claude/commands/` slash commands (wc-feature, wc-api, wc-domain, wc-module, wc-gate, wc-upgrade, wc-doctor, wc-vendor-diff, wc-vendor-upgrade).
- 9 `.claude/agents/` local squad (Paulo, Ines, Francisca, Bruno, Daniel, Marta, Eduardo, Carolina, Valentina).
- 6 skills in `.agents/skills/` (laravel-best-practices migrated, wizardingcode-conventions, arka-bridge, inertia-vue-nuxtui [skeleton], pest-browser-tdd [skeleton], wizardingcode-ui-kb [skeleton]).
- `.mcp.json` extended to 6 servers (laravel-boost, context7, obsidian, claude-mem, playwright, nuxt-ui).
- `packages/wizardingcode-arka-bridge` package with `arka:sync` Artisan command.
- `composer arka:gate` 9-phase quality gate with JSON report.
- Husky pre-commit (pint --dirty, gitleaks, forbidden files) + commit-msg (commitlint conventional).
- Pest 5 config with pt_PT faker, SQLite memory, ArchTest invariants.
- Infection mutation testing configured (deps installed; MSI thresholds temporarily 0/0 to be raised in Plan 2 once dual auth tests expand coverage).

**Changed**
- `pint.json` tightened with WC overrides (declare_strict_types, ordered_imports alpha, void_return, etc.).
- `phpstan.neon` enforces L9 across app/config/database/routes/tests (packages/*/src added when packages exist).
- `rector.php` includes Laravel set + level set + WC-specific paths.
- `composer.json` deps: added Fortify, Sanctum, Socialite, Scout, Pennant, Reverb, AI, Pulse, Spatie packages, Sentry, Scramble, secure-headers, Infection.
- `composer.json` scripts: added `arka:gate`, `arka:sync`, `test:browser`, `test:mutation`, `test:security`.

**Security**
- Pre-commit gitleaks integration.
- Forbidden files blocked from `git add`: `.env`, `*.pem`, `*.key`, `id_rsa`, `*.sqlite`.
- `roave/security-advisories` retained as dev-latest.

**Notes**
- Plans 2-5 will add: Inertia + Vue + Nuxt UI port (Plan 2), Dynamic Settings + 8 core contexts (Plan 3), Install Wizard + optional modules (Plan 4), CI/CD + drift defense + v1.0 tag (Plan 5).
- `phpstan-baseline.neon` (91 entries) is a temporary measure to be cleared in Plan 5 as types tighten.
- Infection MSI thresholds set to 0 in Plan 1; raised to 75/85 in Plan 2 once dual auth + features increase test surface.
