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
