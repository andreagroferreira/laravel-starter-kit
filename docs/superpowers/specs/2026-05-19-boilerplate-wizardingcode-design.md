---
title: Boilerplate WizardingCode v1.0 — Design Specification
status: draft
version: 1.0.0-draft
date: 2026-05-19
owner_squad: [Paulo (Backend Lead), Ines (Frontend Lead), Francisca (QA/Tech Director), Bruno (Security Lead)]
veto_holder: André Agro Ferreira (CEO WizardingCode)
arkaos_compatibility: ">=2.0.0,<3.0.0"
laravel_compatibility: "^13.0"
php_compatibility: "^8.4"
inertia_compatibility: "^3.0"
nuxt_ui_compatibility: "^4.0"
constitution_gate: PASS (this document satisfies NON-NEGOTIABLE #7 — spec-driven)
related_docs:
  - "[[ArkaOS Constitution]]"
  - "[[fovory-supplier-sync — Project]]"
  - "[[cascais24horas-cms — Project]]"
  - "[[rockport-ecommerce — Project]]"
---

# Boilerplate WizardingCode v1.0 — Design Specification

> **One starter, ArkaOS-native. Two modes. Eight mandatory contexts. Zero negotiation on quality.**

This document is the formal specification for the WizardingCode boilerplate. It satisfies the ArkaOS Constitution NON-NEGOTIABLE #7 (spec-driven, no code without approved spec). The Quality Gate verdict for ungated implementation was REJECTED (Marta CQO); this spec resolves the blockers (owner defined, kill list signed, versioning policy, compatibility matrix).

The brainstorm that produced this spec (conclave Tier 0 with Tomas/strat, Paulo/dev, Bruno/security, Daniel/devops, Francisca/QA, Marta/CQO) is preserved in `.arka/brainstorms/2026-05-19-conclave.md`.

---

## §0 — Constitution Compliance Gate

| Requirement | Resolution |
|---|---|
| Spec exists and approved | This document, with user review gate before implementation. |
| Owner defined (RACI) | Squad ArkaOS fixo: Paulo (backend) + Ines (frontend) + Francisca (QA/tech) + Bruno (security). André veto. Release trimestral. |
| Kill list signed | §10. Items explicitly OUT of v1.0. |
| Versioning policy | §8 (semver, patch rolling, minor monthly, major quarterly). |
| ArkaOS compatibility matrix | Frontmatter + `.arka/compatibility.yaml`. |
| Drift defense | `/wc-doctor` + `/wc-upgrade` + `.arka/kill-list.md` per derived project. |
| Mandatory-flow respected | This spec was produced in phases 1-8 of the 13-phase canonical flow. |
| KB-first respected | KB Obsidian is the non-negotiable source of truth for UI/UX. Pre-commit hooks enforce. |
| Quality Gate (Marta+Eduardo+Francisca) | §5 (`composer arka:gate`), 9-phase orchestration with `gate-report.json`. |

---

## §1 — Vision & Positioning

### 1.1 What this is

A single, ArkaOS-native Laravel 13 starter for all new WizardingCode backend projects: SaaS, CMS, CRM, internal tools, public APIs. Two modes:

1. **API-only** — backend pure with versioned REST API (no Inertia, no Vue frontend).
2. **Monolith with Backoffice** — Inertia v3 + Vue 3 + Nuxt UI 4 admin panel + public/customer-facing API endpoints.

### 1.2 What this is NOT

- It is NOT a fork-able skeleton (one repo, install wizard handles divergence).
- It is NOT a family of templates (one starter, multiple modules).
- It is NOT a SaaS framework (Spark), a low-code panel (Filament), or a generic skeleton (Jetstream).
- It is NOT a vendor-locked product (no proprietary deps, MIT license).

### 1.3 Differentiator vs Jetstream / Filament / Spark

| Concern | Jetstream | Filament | Spark | **WC Boilerplate** |
|---|---|---|---|---|
| Auth out of the box | ✅ Basic | ✅ Filament-only | ✅ + billing | ✅ **Dual** (Staff Fortify + Customer Sanctum/Socialite) |
| Admin panel | ❌ | ✅ (magic) | Limited | ✅ **Nuxt UI Dashboard ported, vendor-locked** |
| Multi-tenancy | Optional | Plugin | ❌ | **Optional package** (`wizardingcode-tenant`) |
| Billing | ❌ | Plugin | ✅ (vendor-locked) | **Optional package** (`wizardingcode-billing`) |
| AI integration | ❌ | ❌ | ❌ | ✅ **Core dep** (Laravel AI, multi-provider, telemetry) |
| ArkaOS integration | ❌ | ❌ | ❌ | ✅ **Native** (constitution, hooks, MCPs, KB) |
| Dynamic settings | ❌ | DB-config | ❌ | ✅ **Mandatory principle** (storage/email/AI providers in DB) |
| Quality Gate | ❌ | ❌ | ❌ | ✅ **`composer arka:gate`** (9 phases) |

The tagline: **"Backend pronto a operar dentro da ArkaOS no minuto 1."**

### 1.4 Strategic risk #1 and mitigation

**Risk**: Drift between boilerplate and live derived projects (fovory, cascais24horas, rockport, future). If the boilerplate evolves slower than its children, it becomes a museum piece.

**Mitigation** (3 layers):
1. **Upstream rule** — every lesson learned in a live project must be PR'd back to the boilerplate by end of each sprint.
2. **`/wc-doctor`** — derived projects report drift on demand and via cron.
3. **`/wc-upgrade`** — selective patch-apply (not git pull) from boilerplate to derived project, with diff review per hunk.

---

## §2 — Architecture

### 2.1 Approach — Hybrid Pragmatic

Laravel canónico for the skeleton (Http, Models, Console, Providers, Policies). `app/Domains/{BoundedContext}/` only when complexity justifies it. `packages/wizardingcode-*` for optional modules and shared libraries reusable across derived projects.

### 2.2 Folder structure

```
boilerplate-wizardingcode/
├─ app/
│  ├─ Console/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  ├─ Middleware/
│  │  ├─ Requests/                              # FormRequests obrigatórios
│  │  └─ Resources/                             # API Resources obrigatórios
│  ├─ Models/                                   # default location until promotion
│  ├─ Providers/
│  ├─ Policies/
│  ├─ Actions/                                  # invokable single-purpose
│  ├─ Services/                                 # orchestrators
│  ├─ Settings/                                 # dynamic settings classes (typed)
│  └─ Domains/                                  # promoted bounded contexts
│     └─ {Context}/
│        ├─ Models/  Services/  Actions/  Data/  Repositories/  Policies/
├─ packages/
│  ├─ wizardingcode-arka-bridge/                # OBRIGATÓRIO (constituição ArkaOS)
│  ├─ wizardingcode-ui/                         # OBRIGATÓRIO (shared Vue components)
│  ├─ wizardingcode-billing/                    # opt-in (Stripe subscriptions)
│  ├─ wizardingcode-tenant/                     # opt-in (team_id scope leve)
│  └─ wizardingcode-cms-lite/                   # opt-in (pages, posts, media)
├─ resources/
│  ├─ js/                                       # ativado em modo monolith
│  │  ├─ app.ts                                 # Inertia bootstrap
│  │  ├─ ssr.ts                                 # SSR opcional
│  │  ├─ Pages/                                 # Inertia pages (PascalCase)
│  │  ├─ Layouts/                               # BackofficeLayout, AuthLayout, GuestLayout
│  │  ├─ Components/
│  │  ├─ Composables/
│  │  ├─ Stores/                                # Pinia
│  │  ├─ types/
│  │  ├─ utils/
│  │  └─ assets/css/main.css
│  ├─ lang/                                     # pt_PT + en (+ dynamic add via settings)
│  └─ views/                                    # blade error pages + emails
├─ routes/
│  ├─ web.php                                   # backoffice (monolith only)
│  ├─ auth.php                                  # Fortify routes
│  ├─ api.php                                   # /api/v1/*
│  ├─ channels.php                              # broadcast
│  └─ console.php
├─ tests/
│  ├─ Unit/  Feature/  Browser/                 # Pest 5 (browser via Playwright)
│  └─ Pest.php
├─ database/
│  ├─ migrations/  factories/  seeders/
├─ config/
├─ public/
├─ storage/
│  └─ arka/                                     # quality gate reports, telemetry
├─ docker/                                      # opt-in via wizard
├─ docs/
│  └─ superpowers/specs/                        # this folder
├─ .claude/                                     # see §3.1
├─ .agents/                                     # source of truth multi-runtime
├─ .codex/  .cursor/  .gemini/                  # mirrored from .agents/
├─ .arka/                                       # see §3.4
├─ AGENTS.md  CLAUDE.md  GEMINI.md              # generated from same template
├─ .mcp.json                                    # see §3.3
├─ app.config.ts                                # Nuxt UI tokens (monolith only)
├─ artisan  composer.json  package.json
├─ pint.json  phpstan.neon  rector.php  phpunit.xml
├─ vite.config.ts
├─ Dockerfile  docker-compose.yml               # opt-in
└─ README.md
```

### 2.3 Promotion rule — `app/Models` → `app/Domains/X/`

A bounded context is promoted when it satisfies **at least one**:
- ≥ 5 files relacionados (model + services + actions + data + repository)
- ≥ 2 contextos partilhados (model usado por outro contexto)
- Ciclo de vida próprio (eventos, jobs, sagas)
- Policies não-triviais (≥ 3 abilities)

Francisca audita em PR review. Não promover prematuramente (over-engineering veto).

### 2.4 Stack (versões pinadas)

| Camada | Tecnologia | Versão |
|---|---|---|
| PHP | PHP | ^8.4 |
| Framework | Laravel | ^13.0 |
| Auth Staff | Laravel Fortify | ^1.x |
| Auth Customer | Laravel Sanctum + Socialite | ^4.x / ^5.x |
| Permissions | spatie/laravel-permission | ^6.x |
| Settings | Laravel Pennant + custom model | ^1.x |
| AI | laravel/ai | ^1.x |
| Queue | Laravel Horizon | ^5.46 |
| Search | Laravel Scout + Meilisearch | ^10.x |
| Storage | league/flysystem-aws-s3-v3 + local | ^3.x |
| Media | spatie/laravel-medialibrary | ^11.x |
| Audit | spatie/laravel-activitylog | ^4.x |
| Data | spatie/laravel-data | ^4.x |
| Query | spatie/laravel-query-builder | ^6.x |
| Headers | bepsvpt/secure-headers | ^7.x |
| Tinker | laravel/tinker | ^3.x |
| Frontend bundler | Vite+ (`@voidzero-dev/vite-plus-core`) | latest |
| JS runtime | Bun | latest |
| CSS | Tailwind CSS | ^4.3 |
| UI library | Nuxt UI | ^4.0 |
| SPA bridge | inertiajs/inertia-laravel | ^3.0 |
| Vue | Vue | ^3.5 |
| State | Pinia | ^2.x |
| Routing types | tighten/ziggy OR wayfinder | latest |
| Tests | Pest + pest-plugin-browser (Playwright) + pest-plugin-laravel | ^5.0 |
| Quality | Pint + Rector + PHPStan/Larastan L9 + Infection | latest |
| Observability | Sentry + Laravel Pulse + Telescope (dev) | latest |
| Realtime | Laravel Reverb | ^1.x |
| Errors | nunomaduro/collision | ^8.x |

### 2.5 Required + optional Composer packages

**Required (always installed)**:
```
laravel/framework  ^13.0
laravel/horizon  ^5.46
laravel/fortify  ^1.x
laravel/sanctum  ^4.x
laravel/socialite  ^5.x
laravel/scout  ^10.x
laravel/pennant  ^1.x
laravel/reverb  ^1.x
laravel/ai  ^1.x
laravel/pulse  ^1.x
laravel/horizon  ^5.46
nunomaduro/essentials  ^1.2
spatie/laravel-permission  ^6.x
spatie/laravel-data  ^4.x
spatie/laravel-query-builder  ^6.x
spatie/laravel-medialibrary  ^11.x
spatie/laravel-activitylog  ^4.x
bepsvpt/secure-headers  ^7.x
sentry/sentry-laravel  ^4.x
league/flysystem-aws-s3-v3  ^3.x
dedoc/scramble  ^0.x  (OpenAPI auto-gen)
inertiajs/inertia-laravel  ^3.0  (monolith only)
wizardingcode/arka-bridge  ^1.0  (local path repo)
wizardingcode/ui  ^1.0  (local path repo, monolith only)
```

**Optional (installed by wizard)**:
```
wizardingcode/billing       (Stripe)
wizardingcode/tenant        (team_id scope)
wizardingcode/cms-lite      (pages/posts/media)
laravel/octane              (FrankenPHP, opt-in by wizard)
```

**Dev-only**:
```
laravel/boost  ^2.x
laravel/pail  ^1.x
laravel/pint  ^1.x
laravel/tinker  ^3.x
larastan/larastan  ^3.x
rector/rector  ^2.x
driftingly/rector-laravel  ^2.x
pestphp/pest  ^5.x  (+ browser, laravel, type-coverage)
infection/infection  ^0.x
fakerphp/faker  ^1.x
mockery/mockery  ^1.x
nunomaduro/collision  ^8.x
nunomaduro/pao  ^1.x
roave/security-advisories  dev-latest
```

### 2.6 Dual Auth architecture (non-negotiable)

Two completely independent auth systems, no contamination:

**Staff (backoffice users)**:
- Table: `users`
- Model: `App\Models\User`
- Guard: `web` (sessions)
- Provider: Fortify
- Features: 2FA TOTP + recovery codes, password reset, email verification, impersonate (admin), session rotation, idle timeout 30min
- Permissions: spatie/laravel-permission (roles + permissions)
- Migration shape:
  ```
  users: id, name, email, password, 2fa_secret, 2fa_recovery, email_verified_at, last_login_at, ...
  roles, permissions, model_has_roles, model_has_permissions, role_has_permissions (Spatie)
  personal_access_tokens (Sanctum)  // Staff podem ter tokens API também
  ```

**Customer (end-user)**:
- Table: `customers`
- Model: `App\Models\Customer`
- Guard: `customer` (defined in `config/auth.php`)
- Provider: Sanctum (API tokens) + Socialite (social login: Google, Apple, GitHub configuráveis)
- Features: email verification, password reset, optional 2FA, profile, soft delete (GDPR)
- Authorization: role enum simples (`CustomerRole::Free, Pro, Enterprise`)
- Migration shape:
  ```
  customers: id, name, email, password (nullable for social), avatar_url, role, last_login_at, email_verified_at, deleted_at, ...
  customer_social_accounts: customer_id, provider, provider_id, provider_token, provider_refresh_token
  customer_tokens: id, customer_id, name, token (hash), abilities, last_used_at, expires_at
  ```

**Routes** isolated:
- `routes/auth.php` — Fortify staff routes (`/admin/login`, `/admin/2fa`)
- `routes/api.php` — Customer API auth (`/api/v1/auth/register`, `/api/v1/auth/login`, `/api/v1/auth/social/{provider}`)

Each derived project may use only Staff, only Customer, or both. Wizard handles removal cleanly.

### 2.7 Dynamic Settings principle (non-negotiable)

Everything that can be dynamic IS dynamic. No `.env` for runtime configuration that ops/staff might change.

**Mechanism**: Laravel Pennant (feature flags) + custom `App\Models\AppSetting` (key/value JSON) + typed accessor classes in `app/Settings/`.

```
app/Settings/
├─ StorageSettings.php          # driver, bucket, region, encrypted access keys
├─ MailSettings.php             # provider (SES/Mailgun/SMTP/Resend), credentials, from_address
├─ AiSettings.php               # default provider, models per task, encrypted API keys, fallback chain
├─ BrandingSettings.php         # primary color, neutral color, logo_url, favicon_url
├─ LocaleSettings.php           # enabled locales (pt_PT, en, +dynamic)
├─ AuditSettings.php            # retention days per model
└─ FeatureFlags.php             # Pennant integration (per-user / per-team / global)
```

**Storage**:
- Table `app_settings`: `id`, `key` (unique), `value` (JSON), `is_encrypted` (bool), `updated_by`, `updated_at`
- Encryption: Laravel Crypt automaticamente quando `is_encrypted=true`
- Cache: each setting cached in Redis with key `setting:{key}`, invalidated on update

**Backoffice UI**:
- `/admin/settings/{section}` — pages auto-generated from typed settings classes (reflection)
- Each section has a form with proper validation, save → cache invalidate → activity log
- Permission required: `manage settings` (only assigned to super-admin role by default)

**Why not spatie/laravel-settings**: André's decision — preferes Pennant (already in core deps) + simpler key/value model with custom typed accessors. Avoids spatie-specific migrations and gives more control over the backoffice UI generation.

---

## §3 — ArkaOS Integration

### 3.1 `.claude/` structure (Claude Code source of truth)

```
.claude/
├─ settings.json                                # base, versionado, partilhado
├─ settings.local.json                          # gitignored, MCPs locais
├─ hooks/
│  ├─ user-prompt-submit.sh                    # injecta [ARKA:WORKFLOW-REQUIRED]
│  ├─ session-start.sh                         # injecta [ARKA:MANDATORY-FLOW] + cwd tag + cost summary
│  ├─ pre-tool-use-git.sh                      # bloqueia .env, *.key, *.pem, *.sqlite, id_rsa
│  ├─ pre-tool-use-bash.sh                     # bloqueia git push --force, drop database, rm -rf
│  ├─ pre-tool-use-edit.sh                     # avisa edits em ficheiros vendor (@vendor)
│  └─ stop.sh                                  # corre `composer arka:gate` antes de done
├─ commands/
│  ├─ wc-feature.md                            # /wc-feature <name>
│  ├─ wc-api.md                                # /wc-api <name>
│  ├─ wc-domain.md                             # /wc-domain <name> — promove model a Domain
│  ├─ wc-module.md                             # /wc-module <name> — cria packages/wizardingcode-<name>
│  ├─ wc-gate.md                               # /wc-gate — corre composer arka:gate
│  ├─ wc-upgrade.md                            # /wc-upgrade — apply boilerplate patches
│  ├─ wc-doctor.md                             # /wc-doctor — drift report
│  ├─ wc-vendor-diff.md                        # /wc-vendor-diff — diff vendor local vs upstream
│  └─ wc-vendor-upgrade.md                     # /wc-vendor-upgrade — upgrade vendor template
├─ agents/                                      # local squad overrides
│  ├─ paulo-backend.md                         # Tier 1 — Senior backend lead
│  ├─ ines-frontend.md                         # Tier 1 — Inertia v3 + Vue 3 + Nuxt UI specialist
│  ├─ francisca-tech.md                        # Tier 0 — Tech Director (quality gate)
│  ├─ bruno-security.md                        # Tier 1 — Security lead
│  ├─ daniel-devops.md                         # Tier 1 — CI/CD + Docker + deploy
│  ├─ marta-cqo.md                             # Tier 0 — CQO (final veto)
│  ├─ eduardo-copy.md                          # Tier 0 — Copy director
│  ├─ carolina-pm.md                           # Tier 1 — PM (spec gate, backlog)
│  └─ valentina-brand.md                       # Tier 1 — Brand & Design (UI/UX review)
└─ skills/
   ├─ laravel-best-practices/                  # existing, mantém
   ├─ wizardingcode-conventions/               # NEW — WC-specific (promotion rule, dual auth, dynamic settings)
   ├─ inertia-vue-nuxtui/                      # NEW — Inertia v3 + Vue 3 + Nuxt UI 4 patterns
   ├─ arka-bridge/                             # NEW — constitution + mandatory-flow + KB-first
   ├─ pest-browser-tdd/                        # NEW — Playwright via Pest browser
   └─ wizardingcode-ui-kb/                     # NEW — UI/UX KB-first enforcement
```

### 3.2 `CLAUDE.md` content (raiz do projeto)

Ordered sections:

1. **Constitution ArkaOS** (mandatory-flow, squad-routing, KB-first, spec-driven, quality-gate, no-secrets-commit, no-self-approval, dual-auth-discipline, dynamic-settings-only, vendor-lock-respect)
2. **Routing table local** (dev→Paulo, qa→Francisca, security→Bruno, ops→Daniel, pm→Carolina, ui→Ines/Valentina)
3. **Stack reference** (versões + comandos: `composer dev`, `composer arka:gate`, `bun run dev`, `php artisan wizardingcode:install`)
4. **Laravel Boost Guidelines** (mantém o que já está no atual CLAUDE.md)
5. **Inertia v3 + Vue 3 + Nuxt UI 4 conventions** (PascalCase pages, kebab-case components em `Components/`, Pinia stores, composables `use*`)
6. **WizardingCode rules** (promotion rule, package-per-module, FormRequest obrigatório em todos os endpoints, API Resources obrigatórios em todas as respostas API, Pint+PHPStan+Pest obrigatórios pre-commit)
7. **Forbidden patterns** (catalogados):
   - `$guarded = []` em models — REJEITADO (Bruno)
   - `$request->all()` em controllers — REJEITADO (Bruno)
   - Queries Eloquent em controllers (devem estar em Services ou Actions) — REJEITADO (Paulo)
   - Secrets em código ou em `.env` (devem estar em settings encriptados) — REJEITADO (Bruno)
   - `<input type="file">` directo (deve ser `WcDropzone`) — REJEITADO (Ines)
   - `USlideover` para confirmações destrutivas (deve ser `UModal`) — REJEITADO (Ines)
   - Cores Tailwind raw (`text-gray-900`) em componentes — REJEITADO (Valentina)
   - Componentes vendor sem header `@vendor:` — REJEITADO (Ines)
   - `composer arka:gate` skip — REJEITADO (Marta)

`AGENTS.md` e `GEMINI.md` are **generated from `CLAUDE.md`** via `arka-update sync-agents`. Single source of truth.

### 3.3 `.mcp.json` (expanded profile)

```json
{
  "mcpServers": {
    "laravel-boost":  { "command": "php", "args": ["artisan", "boost:mcp"] },
    "context7":       { "command": "...", "args": [...] },
    "obsidian":       { "command": "...", "args": [...] },
    "claude-mem":     { "command": "...", "args": [...] },
    "playwright":     { "command": "...", "args": [...] },
    "nuxt-ui":        { "command": "...", "args": [...] }
  }
}
```

Concrete configurations come from ArkaOS MCP registry via `arka-mcp apply boilerplate-wizardingcode`.

### 3.4 `.arka/` per-project state

```
.arka/
├─ project.yaml                                 # name, type, ecosystem, version, owner, raci, deployed_url
├─ compatibility.yaml                           # ArkaOS / Laravel / Inertia / Nuxt UI versions supported
├─ kill-list.md                                 # YAGNI list signed (response to Marta)
├─ raci.md                                      # who owns what
├─ brainstorms/                                 # historical brainstorm transcripts (Obsidian-linked)
└─ telemetry/
   ├─ llm-costs.jsonl                           # per-session LLM cost log
   └─ drift-reports/                            # generated by /wc-doctor
```

Example `project.yaml`:
```yaml
name: boilerplate-wizardingcode
type: boilerplate
ecosystem: wizardingcode
version: 1.0.0
owner_squad: [paulo, ines, francisca, bruno]
veto_holder: andre
arkaos_url: https://arka.wizardingcode.io/projects/boilerplate-wizardingcode
obsidian_path: Projects/Boilerplate WizardingCode
github_repo: WizardingCode/boilerplate-wizardingcode
release_cadence: { patch: rolling, minor: monthly, major: quarterly }
```

Example `compatibility.yaml`:
```yaml
boilerplate: 1.0.0
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

### 3.5 Multi-runtime sync

Source of truth: `.agents/`. All other runtime folders are mirrored from it.

```
.agents/                                        # SoT
├─ skills/  (the canonical skills folder)
└─ ...

.claude/skills/    ← symlinked or generated
.cursor/rules/     ← generated
.codex/            ← generated
.gemini/           ← generated
AGENTS.md          ← generated from CLAUDE.md template
CLAUDE.md          ← canonical
GEMINI.md          ← generated from CLAUDE.md template
```

Script `bin/arka-sync-agents` (Bash) runs via Husky `pre-commit` to enforce convergence. CI also checks (fails PR if out of sync).

---

## §4 — Install Wizard (`php artisan wizardingcode:install`)

### 4.1 Behaviour

Interactive command using Laravel Prompts. Runs once after `composer create-project`. Idempotent (can be re-run; only applies missing pieces).

### 4.2 Flow

```
1. Welcome + version banner

2. Project mode (radio):
   ◉ Monolith with backoffice (Inertia + Vue + Nuxt UI)
   ○ API-only (no frontend)

3. Optional modules (multi-select):
   ☑ wizardingcode-arka-bridge   (forced, cannot uncheck)
   ☑ wizardingcode-ui            (forced if monolith)
   ☐ wizardingcode-billing       (Stripe)
   ☐ wizardingcode-tenant        (team_id scope)
   ☐ wizardingcode-cms-lite      (pages/posts/media)

4. Auth systems (multi-select):
   ☑ Staff (Fortify + sessions + 2FA)
   ☑ Customer (Sanctum + Socialite)
   [at least one required]

5. Social providers (only if Customer auth) — multi-select:
   ☑ Google  ☐ Apple  ☐ GitHub  ☐ Microsoft  ☐ Facebook

6. Database:
   ◉ PostgreSQL 16
   ○ MySQL 8.4
   ○ SQLite (dev only — warning)

7. Cache + Queue + Session driver:
   ◉ Redis (recommended)
   ○ Database (simple, no infra)

8. Observability:
   ☑ Sentry          (errors + traces; DSN later in settings UI)
   ☑ Pulse           (Laravel Pulse dashboard)
   ☑ Telescope       (dev-only, APP_ENV=local)

9. Realtime:
   ◉ Reverb (Laravel default, self-hosted)
   ○ Pusher / Soketi
   ○ None (mail + db only)

10. Deploy target (info only — affects CI templates):
   ◉ Laravel Cloud
   ○ Forge
   ○ Docker / Compose
   ○ fly.io

11. Octane (advanced, default off):
   ☐ Enable Octane (FrankenPHP)

12. ArkaOS:
   Project name:       ____________________
   Ecosystem:          wizardingcode (default)
   Owner squad:        Paulo + Ines + Francisca + Bruno
   Obsidian vault:     Auto-detected ✓
   Register in ArkaOS: ☑ Yes

13. Execution:
   ✓ Removing unselected modules from packages/
   ✓ Publishing configs (Sanctum, Fortify, Spatie Permission, Scramble, Pulse, Reverb, MediaLibrary)
   ✓ Generating .env from .env.example
   ✓ Running migrations
   ✓ Installing JS dependencies (bun install)
   ✓ Initial build (bun run build)
   ✓ Configuring MCPs (.mcp.json from ArkaOS registry profile)
   ✓ Generating CLAUDE.md / AGENTS.md / GEMINI.md from template
   ✓ Creating .arka/project.yaml + raci.md + kill-list.md (empty, ready to sign)
   ✓ Seeding initial super-admin user (Staff) — credentials shown once
   ✓ Initializing git, initial commit "chore: wizardingcode boilerplate v1.0"
   ✓ Registering project in ArkaOS (via arka-onboard skill)

14. Next steps printout:
   composer dev                      → start dev environment
   composer arka:gate                → run quality gate
   /wc-doctor                        → check ArkaOS drift
```

### 4.3 Non-interactive mode

```bash
php artisan wizardingcode:install \
  --mode=monolith \
  --modules=billing,tenant \
  --auth=staff,customer \
  --social=google,apple \
  --db=postgres \
  --cache=redis \
  --observability=sentry,pulse,telescope \
  --realtime=reverb \
  --deploy=cloud \
  --no-octane \
  --project-name="My SaaS" \
  --register-arkaos \
  --no-interaction
```

CI-friendly. Used by `arka-startkit-init` skill when scaffolding from outside.

### 4.4 Module removal logic

If a module is unchecked, the wizard:
1. Removes the package from `composer.json` require block
2. Deletes `packages/wizardingcode-{module}/`
3. Removes related routes, migrations, seeders, factories, settings entries, navigation items
4. Removes related Inertia pages (if monolith)
5. Runs `composer dump-autoload` + `php artisan optimize:clear`
6. Adds line to `.arka/kill-list.md` so `/wc-doctor` doesn't report this as drift

---

## §5 — Quality Gate (`composer arka:gate`)

### 5.1 Phases

```
Phase  Tool                              Threshold                  Required
-----  --------------------------------  -------------------------  --------
1/9    Pint (code style)                 0 violations               always
2/9    Rector (refactoring dry-run)      0 suggested changes        always
3/9    Larastan / PHPStan L9             0 errors                   always
4/9    Pest type-coverage                100%                       always
5/9    Pest unit + feature               100% stmt, 85% branch      always
6/9    Pest browser (Playwright)         all scenarios pass         monolith only
7/9    Infection (mutation testing)      MSI ≥ 75%, Covered MSI ≥ 85%   always
8/9    Vitest (Vue components)           ≥ 80% coverage             monolith only
9/9    Security audit                    composer + bun + gitleaks 0 issues  always
```

### 5.2 Output

```
$ composer arka:gate

[1/9] Pint .............................................. ✓ 0.4s
[2/9] Rector dry-run .................................... ✓ 1.2s
[3/9] PHPStan L9 ........................................ ✓ 3.8s
[4/9] Pest type-coverage (100%) ......................... ✓ 0.9s
[5/9] Pest unit + feature (100% stmt, 85% branch) ....... ✓ 12.1s
[6/9] Pest browser (Playwright, 3 viewports) ............ ✓ 18.4s
[7/9] Infection (MSI ≥ 75%) ............................. ✓ 42.6s
[8/9] Vitest (Vue components) ........................... ✓ 6.2s
[9/9] Security audit (composer + bun + gitleaks) ........ ✓ 2.1s

✓ Quality Gate PASSED (87.7s)
  Report: storage/arka/gate-report.json
  Marta / Eduardo / Francisca podem aprovar a entrega.
```

### 5.3 `gate-report.json` format

```json
{
  "version": "1.0",
  "generated_at": "2026-05-19T12:34:56Z",
  "duration_seconds": 87.7,
  "verdict": "PASSED",
  "phases": [
    { "id": "pint", "status": "passed", "duration": 0.4, "details": {} },
    { "id": "rector", "status": "passed", "duration": 1.2, "details": {} },
    ...
  ],
  "coverage": { "statement": 100.0, "branch": 87.3, "type": 100.0, "mutation_msi": 78.4 },
  "tests": { "unit": 142, "feature": 89, "browser": 12 },
  "security": { "composer_advisories": 0, "bun_advisories": 0, "secrets_found": 0 }
}
```

### 5.4 Marta/Eduardo/Francisca review

The Quality Gate trio doesn't review code — they review `gate-report.json` + critical PRs flagged by hooks. Phase 11 of mandatory-flow forces this review before "done" status.

### 5.5 Stop hook

`.claude/hooks/stop.sh` reads the latest `gate-report.json`. If `verdict != "PASSED"` or timestamp older than 1 hour, blocks the "done" status with explanation.

---

## §6 — UI/UX Foundation

### 6.1 Port strategy

Faithful port of `nuxt-ui-templates/dashboard-vue@HEAD` to Inertia v3 + Vue 3 + Nuxt UI 4 structure. The 4 page-types (Dashboard/Home, Inbox, Customers, Settings + 3 subpages) preserved in full.

| Template (Vite + Vue Router) | Boilerplate (Inertia) |
|---|---|
| `src/main.ts` | `resources/js/app.ts` (+ `ssr.ts`) |
| `src/App.vue` | `resources/js/Layouts/BackofficeLayout.vue` |
| `src/layouts/default.vue` | `resources/js/Layouts/BackofficeLayout.vue` |
| `src/pages/index.vue` | `resources/js/Pages/Dashboard/Index.vue` |
| `src/pages/inbox.vue` | `resources/js/Pages/Inbox/Index.vue` |
| `src/pages/customers.vue` | `resources/js/Pages/Customers/Index.vue` |
| `src/pages/settings.vue` | `resources/js/Pages/Settings/Index.vue` |
| `src/pages/settings/*.vue` | `resources/js/Pages/Settings/{Members,Notifications,Security}.vue` |
| `src/components/*` | `resources/js/Components/*` + `packages/wizardingcode-ui/resources/components/*` |
| `src/composables/useDashboard.ts` | `resources/js/Composables/useDashboard.ts` (vendor-locked) |
| `src/route-map.d.ts` | Ziggy or Wayfinder-generated TS routes |
| `src/types/index.d.ts` | `resources/js/types/index.d.ts` |
| `src/utils/index.ts` | `resources/js/utils/index.ts` |
| `vue-router` calls | `usePage()`, `router.visit()` from Inertia |
| `defineRoute` | Laravel routes → `Inertia::render('Dashboard/Index', $props)` |

### 6.2 Vendor-lock mechanism

Files marked `[VENDOR]` are direct ports from the upstream template. Edits trigger warnings.

```
resources/js/
  Layouts/BackofficeLayout.vue                  [VENDOR]
  Components/Backoffice/Header/*.vue            [VENDOR]
  Composables/useDashboard.ts                   [VENDOR]
  assets/css/main.css                           [VENDOR — Nuxt UI tokens]
```

**Mechanism**:
- Each vendor file starts with header comment: `<!-- @vendor: nuxt-ui-templates/dashboard-vue@<sha> -->`
- Hook `pre-tool-use-edit.sh`: prompts "vendor file — break integrity?" before allowing edits
- Command `/wc-vendor-diff`: shows diff between local vendor and upstream
- Command `/wc-vendor-upgrade`: applies upstream upgrade hunk-by-hunk with review

### 6.3 `app.config.ts` — single source of truth for theme

The **only** allowed customization surface per derived project:

```ts
export default defineAppConfig({
  ui: {
    colors: {
      primary: 'wizarding-purple',
      neutral: 'slate'
    },
    button: { ... },
    card: { ... },
    // semantic tokens (all declared explicitly — see §6.6.E)
  }
})
```

Vendor components do NOT change. Only `app.config.ts` does.

### 6.4 `packages/wizardingcode-ui` shared component library

```
packages/wizardingcode-ui/
├─ composer.json                                # ServiceProvider (publishes assets, registers blade hints)
├─ package.json                                 # Vue components export, Bun workspace member
├─ src/
│  ├─ WizardingCodeUiServiceProvider.php
│  └─ Console/PublishCommand.php
├─ resources/
│  ├─ components/                               # exported as @wizardingcode/ui
│  │  ├─ Backoffice/                           # layout, header, sidebar (vendor-port)
│  │  │  ├─ BackofficeLayout.vue
│  │  │  ├─ Header/UserMenu.vue  TeamsMenu.vue  NotificationsSlideover.vue
│  │  │  └─ Sidebar/SidebarLinks.vue
│  │  ├─ Forms/                                # form atoms
│  │  │  ├─ WcInput.vue  WcSelect.vue  WcCheckbox.vue
│  │  │  ├─ WcDropzone.vue                    # NEVER use native <input type="file">
│  │  │  └─ WcDateRange.vue
│  │  ├─ Data/                                 # tables, lists, kanban
│  │  │  ├─ WcDataTable.vue                   # pagination cursor + sort + filter + bulk
│  │  │  ├─ WcMasterDetail.vue                # two-pane pattern (inbox-style)
│  │  │  └─ WcEmptyState.vue                  # mandatory empty pattern
│  │  ├─ Feedback/                             # toasts, modals
│  │  │  ├─ WcConfirmModal.vue                # destructive confirms (always Modal, not Slideover)
│  │  │  └─ WcToast.vue                       # 4 types: loading/success/error/info
│  │  ├─ Charts/                               # vendor-port from Home
│  │  └─ Layout/
│  │     ├─ WcPageHeader.vue                  # consistent page header
│  │     └─ WcStatsGrid.vue                   # stats card grid
│  ├─ composables/
│  │  ├─ useDashboard.ts                      # vendor
│  │  ├─ useTheme.ts                          # SSR-safe color mode
│  │  ├─ useFormErrors.ts                     # RFC7807 → inertia errors mapper
│  │  └─ useToast.ts                          # opinionated wrapper
│  └─ types/
└─ tests/                                      # Vitest unit tests for each component
```

Boilerplate `composer require wizardingcode/ui` + `bun add @wizardingcode/ui`. Derived projects pull updates via semver.

### 6.5 KB Obsidian — UI/UX source of truth (non-negotiable)

```
Projects/Boilerplate WizardingCode/UI-UX/
├─ Theme Reference/
│  ├─ Palette.md                               # primary, neutral, semantic colors (light + dark)
│  ├─ Typography.md                            # Inter scale, line-heights, weights
│  ├─ Spacing.md                               # Tailwind 4 scale, container widths
│  ├─ Motion.md                                # 200ms budget, easing, reduced-motion
│  └─ Iconography.md                           # Lucide via Nuxt UI, sizes, usage
├─ Components/                                 # 1 page per component
│  ├─ Backoffice/UserMenu.md                   # props, slots, events, screenshots (light + dark), do/don't
│  ├─ Forms/WcDropzone.md
│  ├─ Data/WcDataTable.md
│  └─ ...
├─ Patterns/
│  ├─ CRUD pattern.md                          # how to do listing + create + edit + delete consistently
│  ├─ Master-Detail pattern.md
│  ├─ Settings pattern.md
│  ├─ AI Playground pattern.md
│  └─ Error Handling pattern.md
├─ Heuristics/
│  ├─ Nielsen 10 (WC interpretation).md
│  └─ WC additions.md                          # density-first, visibility total, dark/light explicit
├─ Decision Log/
│  └─ 2026-05-19 — Why Nuxt UI Dashboard template.md
└─ MOC.md                                      # map of content for UI/UX
```

**Enforcement (non-negotiable)**:
- Hook `pre-component-create.sh`: blocks new component creation in `resources/js/Components/` or `packages/wizardingcode-ui/resources/components/` if no matching KB Obsidian note exists.
- Skill `wizardingcode-ui-kb` triggers on every edit of `resources/js/Components/**`, forces Obsidian search before proceeding.
- Routing: `[arka:routing] brand -> Valentina` for any visual task. Valentina cites KB before proposing.
- PR template requires `[ ] KB Obsidian note linked` for any UI change.

### 6.6 Cross-cutting UI/UX principles

#### A) Consistency
Only Nuxt UI `U*` components or `Wc*` components from `wizardingcode-ui`. Raw HTML styled manually = code smell.

**Enforcement**: ESLint custom rule `wc/no-raw-styled-elements` rejects `<div class="...">` patterns that behave like interactive elements.

#### B) Density-first — fewer pages, more in-context
A single workflow opens at most ONE new page. Everything else is:
- `UModal` — short forms, confirmations (NEVER slideover for confirms — fovory rule)
- `USlideover` — detail view + lateral edit (master-detail pattern)
- `UTabs` — settings, multi-section configurations
- `UAccordion` — long forms by section
- Inline edit in tables — no navigation

Anti-pattern (banned): page-per-CRUD (create/edit on separate routes when a modal fits).

#### C) Responsive — fluid up to iPad
Target breakpoints:
- Mobile (≤ 640) — sidebar drawer, tables → cards, full-screen modals
- Tablet vertical (640–1024) — sidebar collapsed, compact tables
- Tablet horizontal (1024–1366) — sidebar expanded, normal tables
- Desktop (≥ 1366) — full layout

Fluid typography via `clamp()`. No page may break before 768px. **Pest browser** runs 3 viewports in CI (mobile / tablet / desktop).

#### D) Motion — 200ms budget, reduced-motion respected
- All transitions ≤ 200ms
- Default easing: Nuxt UI `ease-out`
- Inertia page transitions: 100ms fade, brand color progress bar
- `useReducedMotion()` respected EVERYWHERE — no exceptions (a11y)
- Skeleton (not spinner) for any load > 200ms

#### E) Dark / Light mode — explicit implementation (critical focus)

Historically WC's weak point. Hard rules:

1. **No raw Tailwind colors** in components. Forbidden: `text-gray-900`, `bg-white`, `border-slate-200`. Allowed: `text-default`, `bg-default`, `border-default` (semantic Nuxt UI tokens).

2. **Explicit tokens in `app.config.ts`** (the values are placeholders pending §11.1.5 decision):
   ```ts
   ui: {
     colors: {
       primary: 'wizarding-purple',   // placeholder, final palette tracked in §11.1.5
       neutral: 'slate'                // placeholder, final palette tracked in §11.1.5
     },
     // ALL semantic tokens MUST be declared so light/dark behavior is explicit:
     // text-default, text-muted, text-toned, text-dimmed, text-highlighted
     // bg-default, bg-muted, bg-elevated, bg-accented, bg-inverted
     // border-default, border-muted, border-accented
   }
   ```

3. **Every component validated in both modes before merge**:
   - Histoire (tentatively chosen — see §11.1.4) with side-by-side light/dark toggle
   - Visual regression in CI (Playwright screenshots both modes) → PR fails if diff > 2%

4. **KB Obsidian — every component note has 2 screenshots**: light + dark. Without them, PR blocked by Valentina.

5. **ESLint plugin `eslint-plugin-wizardingcode`** rejects:
   - Classes `text-gray-*`, `bg-white`, `bg-black`, `bg-slate-*`, etc., outside tokens
   - Components missing `:class` computed via `useColorMode()` when applicable

6. **`useColorMode()` SSR-safe**: preference persisted in cookie (NOT localStorage) so Inertia SSR doesn't flash.

7. **Component checklist in PR template** (mandatory):
   - [ ] Light mode visually validated
   - [ ] Dark mode visually validated
   - [ ] KB Obsidian note with 2 screenshots

#### F) Visibility — five states always present

User never doubts what's happening. Five states mandatory:

| State | Manifestation | Standard component |
|---|---|---|
| Idle | Base state, data visible | — |
| Loading | Skeleton in lists/cards, `:loading` on buttons, Inertia.progress on navigation, inline spinner ≤ 200ms | `USkeleton`, `UButton :loading` |
| Success | Green toast + inline check animation when applicable | `useToast().add({ color:'success' })` |
| Error | Red toast + inline message (RFC7807 translated) + focus on first invalid field | `useToast()` + `UFormGroup error="..."` |
| Empty | Illustration + explanatory text + primary CTA (never silent empty list) | `WcEmptyState` |

Rules:
- Forms with `onBlur` validation + inline summary on submit (`UAlert` with errors list)
- Server RFC7807 errors automatically mapped to inputs (`errors[]` from problem+json)
- Operations > 500ms = mandatory loading state. < 500ms = no loading state (avoids flicker)
- Destructive confirmations (delete) = `UModal` with typed resource name (not checkbox)
- Toast queue limited to 3 concurrent, autoclose 5s success / 8s error / persistent for action-required

#### G) Accessibility — WCAG AA minimum
- Focus rings always visible (Nuxt UI default maintained)
- Contrast ratios validated in CI via `axe-core` (Playwright integration)
- Keyboard shortcuts catalog in `?` (`HelpDialog`)
- Screen reader labels on all icon-only buttons (hard rule: `<UButton icon=... aria-label="..." />`)
- `prefers-reduced-motion` respected

#### H) Branding tokens — KB Obsidian as catalog
- WC palette declared in `Projects/Boilerplate WizardingCode/UI-UX/Theme Reference/Palette.md`
- Type scale: Inter (system fallback)
- Logo + variants (light bg, dark bg, mono)
- Iconography: Lucide via Nuxt UI (no Heroicons, no Material mixing)

### 6.7 Enforcement layers

| Layer | Tool | Scope |
|---|---|---|
| Lint | `eslint-plugin-wizardingcode` | Vue/TS files in `resources/js/` + `packages/wizardingcode-ui/` |
| Visual regression | Playwright screenshots light + dark | CI on PR |
| KB-first | Hook `pre-component-create.sh` + skill `wizardingcode-ui-kb` | Component creation/edit |
| Vendor lock | Hook `pre-tool-use-edit.sh` | Files with `@vendor:` header |
| PR checklist | Template | Every visual PR |

### 6.8 Component PR checklist (template)

```markdown
### Visual change checklist
- [ ] KB Obsidian note exists and is linked: <wikilink>
- [ ] 2 screenshots in KB note (light + dark)
- [ ] Light mode visually validated locally
- [ ] Dark mode visually validated locally
- [ ] Validated at 3 viewports (mobile / tablet / desktop)
- [ ] `useReducedMotion()` respected
- [ ] No raw Tailwind colors used (semantic tokens only)
- [ ] Loading / error / success / empty states implemented
- [ ] Storybook (Histoire) story added
- [ ] Vitest test added (if behavior present)
- [ ] Pest browser scenario added (if user-facing flow)
```

---

## §7 — Core Contexts (the 8 mandatory)

### 7.1 Auth — Dual System (Staff + Customer)

Detailed in §2.6.

**Routes**:
- Staff: `routes/auth.php` mounted on `/admin/` prefix (Fortify default).
- Customer: `routes/api.php` `/api/v1/auth/{register,login,logout,password/forgot,password/reset,verify-email,social/{provider}}`.

**Pages** (monolith mode, Staff):
- `Pages/Auth/Login.vue`
- `Pages/Auth/ForgotPassword.vue`
- `Pages/Auth/ResetPassword.vue`
- `Pages/Auth/VerifyEmail.vue`
- `Pages/Auth/ConfirmPassword.vue`
- `Pages/Auth/TwoFactorChallenge.vue`

**Test scenarios** (must exist in boilerplate):
- Unit: `User::factory()->withTwoFactorEnabled()`, `Customer::factory()->fromSocial('google')`
- Feature: login rate limit (5/min), 2FA challenge flow, recovery code consumption, social auth callback, impersonation audit
- Browser: full login flow with 2FA (Pest browser, 3 viewports)

### 7.2 AI — Laravel AI (core dep)

Multi-provider via dynamic settings. Backoffice playground UI for staff. Cost telemetry per user/feature.

```
app/Domains/Ai/
├─ Services/
│  ├─ ChatService.php                          # high-level chat (with context)
│  ├─ EmbeddingService.php                     # for RAG
│  ├─ PromptCacheService.php                   # Anthropic prompt cache wrapper
│  └─ ProviderFallbackService.php              # configurable fallback chain
├─ Actions/
│  ├─ RunCompletion.php                        # single completion w/ telemetry
│  ├─ RunRag.php                               # Scout + embedding retrieval + completion
│  └─ GeneratePromptFile.php                   # CRUD prompts versioned in /resources/prompts/
├─ Data/
│  ├─ CompletionRequest.php  CompletionResponse.php  TokenUsage.php
└─ Models/
   ├─ AiInteraction.php                       # log of every interaction (prompt hash + tokens + cost)
   ├─ AiPrompt.php                            # versioned prompts (per feature, per locale)
   └─ AiEmbedding.php                         # for RAG
```

**Settings** (`app/Settings/AiSettings.php`):
- `default_provider`: `anthropic | openai | google | mistral`
- `providers.{name}.api_key`: encrypted
- `providers.{name}.models.{task}`: `chat`, `embedding`, `vision` mapped to model IDs
- `fallback_chain`: ordered list `[anthropic, openai, google]`
- `cost_alert_threshold_usd_per_day`: notification trigger

**Backoffice pages** (`Pages/Admin/Ai/`):
- `Playground.vue` — chat with system+user prompt + provider selector + token counter
- `Usage.vue` — cost breakdown by user/feature/time
- `Prompts.vue` — CRUD of versioned prompts in `resources/prompts/*.yaml`

**Tests**: must include provider fallback scenario (primary fails → secondary succeeds → log both attempts).

### 7.3 Backoffice — Nuxt UI Dashboard ported

Detailed in §6.

### 7.4 API — REST base

URI versioning, RFC7807, OpenAPI auto-gen.

**Versioning**: `/api/v1/`, `/api/v2/`. Each version is a separate route file: `routes/api/v1.php`, `routes/api/v2.php`.

**Error format** (RFC7807 `application/problem+json`):
```json
{
  "type": "https://wizardingcode.io/errors/validation",
  "title": "Validation Failed",
  "status": 422,
  "detail": "The given data was invalid.",
  "instance": "/api/v1/customers/42",
  "errors": [
    { "field": "email", "code": "email", "message": "Must be a valid email." }
  ]
}
```

Exception handler in `app/Exceptions/Handler.php` converts ValidationException, AuthorizationException, ModelNotFoundException, ThrottleException to RFC7807.

**OpenAPI**: auto-generated via `dedoc/scramble` from typed FormRequests + API Resources + route attributes. Published at `/docs/api/v1` (Spotlight UI), JSON at `/docs/api/v1.json`.

**Pagination**: cursor-based default (`?cursor=...&per_page=25`). Offset pagination optional via `?paginate=offset`.

**Idempotency**: middleware `Idempotent` reads `Idempotency-Key` header, caches response for 24h. Required for POST/PATCH/PUT on payment-like endpoints.

**Rate limiting**: per consumer (`Sanctum token` or `IP`), configured via Settings. Defaults: 60/min general, 5/min auth, 3/min password reset.

### 7.5 File Upload — Spatie MediaLibrary

- `spatie/laravel-medialibrary` + S3 via `league/flysystem-aws-s3-v3` (driver configurable in Settings)
- ClamAV antivirus hook (queued job after upload) for production tier
- Image conversions (`thumb`, `medium`, `large`) via MediaLibrary conversions
- Signed URLs for private files (expiring tokens)
- Chunked upload for files > 10MB
- **Vue component**: `WcDropzone` (in `wizardingcode-ui`). **NEVER `<input type="file">` directly** — fovory rule.
- Progress bar + retry logic
- Validation: MIME type real (not extension), max size from Settings, allowed types from Settings per upload context

### 7.6 Notifications — Mail + DB + Broadcast (Reverb)

- All notifications go via Laravel Notification + channels `[mail, database, broadcast]`
- Mail templates extend `wizardingcode-ui` Markdown layout (brand WC)
- Notification center component (`Wc*` in shared lib) in backoffice header
- Mark-as-read, mark-all-as-read
- Notification preferences per user (`User::notification_preferences` JSON: `{type: [channels]}`)
- Digest emails (daily / weekly) via scheduled job
- Broadcast driver: Reverb default. Settings allow switch to Pusher / Soketi.

### 7.7 i18n — pt_PT + en + dynamic add

- Default locale: `pt_PT`
- Fallback: `en`
- Strings in `resources/lang/{pt_PT,en}/`
- Inertia: `Inertia::share('translations', fn () => trans()->getMessages())` for Vue side
- Vue i18n via custom composable `useT()` (consumes Inertia-shared translations)
- Locale switcher in header (Nuxt UI dropdown)
- Date/number/currency formatting localized (Carbon + Intl)
- **Dynamic add**: backoffice page `/admin/settings/locales` allows adding new locales (e.g. `es`, `fr`, `de`) — creates folder, marks active in `LocaleSettings`. Falsies untranslated keys fall back to `en`.

### 7.8 Audit Log — spatie/activitylog

- `spatie/laravel-activitylog` configured day 0
- Trait `LogsActivity` automatically applied to: `User`, `Customer`, `Role`, `Permission`, `AppSetting`, `Order` (if billing), `Subscription` (if billing), `Page` (if cms-lite), `Post` (if cms-lite)
- Backoffice viewer at `/admin/audit-log` — timeline pattern (filterable by user/model/action/date)
- Retention: configurable in Settings per model (default 24 months, then anonimized)
- GDPR endpoint: `/api/v1/account/export` returns all activity logs for the authenticated customer

---

## §8 — Versioning & Drift Defense

### 8.1 Semver

- **Major** (`v2.0.0`) — breaking changes for derived projects (Laravel major upgrade, constitution change, package removal).
- **Minor** (`v1.1.0`) — new optional modules, new commands, new hooks (additive only).
- **Patch** (`v1.0.1`) — bugs, dep updates, security patches.

### 8.2 Release cadence

- **Patch** — rolling, immediately on CVE / critical dep.
- **Minor** — monthly, on the 1st (or next business day).
- **Major** — quarterly, with 30-day deprecation window for breaking changes.

### 8.3 `arka-bridge` versioning

`packages/wizardingcode-arka-bridge` follows ArkaOS core version (independent of boilerplate). Declared in `.arka/compatibility.yaml`.

### 8.4 `/wc-doctor` — drift report

Each derived project runs `/wc-doctor` (manually or via cron).

Reports:
- Current boilerplate version vs latest available
- Diverging files (`.claude/`, `CLAUDE.md`, hooks, gate script, vendor components)
- Core packages with different versions
- Suggested migrations (which patches are applicable automatically)
- Items in `kill-list.md` (not reported as drift)

Output: `.arka/telemetry/drift-reports/{date}.md`.

### 8.5 `/wc-upgrade` — selective patch-apply

Applies patches from boilerplate to derived project. NOT git pull — patch-apply with diff review per hunk.

Steps:
1. Fetches latest boilerplate version
2. Computes diff vs current local
3. Filters out items in `kill-list.md`
4. Presents each hunk with: file, change, motivation (linked to boilerplate release notes)
5. User approves / rejects per hunk
6. Applies approved hunks
7. Runs `composer arka:gate` to verify nothing broke

### 8.6 `.arka/kill-list.md` (per project)

Each derived project may declare what it removed. `/wc-doctor` reads this and doesn't report drift on those items.

Example:
```markdown
# Kill List — My SaaS

- [x] wizardingcode-tenant — single tenant, scope removed on 2026-05-19 (André)
- [x] 2FA — internal users only, disabled on 2026-05-19 (André)
- [x] wizardingcode-cms-lite — using cascais24horas-cms-style instead (Paulo)
```

Each entry signed (initials in parens) and dated.

---

## §9 — Owner & RACI

### 9.1 Squad

| Role | Owner | Responsibilities |
|---|---|---|
| Backend Lead | Paulo | Architecture decisions, dual auth, dynamic settings, packages |
| Frontend Lead | Ines | Inertia + Vue + Nuxt UI integration, vendor port fidelity |
| QA / Tech Director | Francisca | Quality gate, test discipline, coverage thresholds, review of gate-report |
| Security Lead | Bruno | OWASP, secrets, headers, GDPR, hooks, audit log |
| CQO (veto) | Marta | Final quality gate veto (with Eduardo + Francisca) |
| Copy Director (veto) | Eduardo | Text reviews (PR descriptions, docs, error messages, i18n) |
| Brand & Design | Valentina | KB Obsidian UI/UX, visual regression, dark/light mode validation |
| Veto holder | André | Strategic / architectural reversals |

### 9.2 RACI for releases

| Activity | R | A | C | I |
|---|---|---|---|---|
| Patch release | Paulo | Marta | Bruno, Francisca | Squad, derived projects |
| Minor release | Squad | Marta | Eduardo, Valentina | André, derived projects |
| Major release | Squad | André | Marta, all leads | Derived projects (30d notice) |
| Security hotfix | Bruno | Bruno | Marta, Paulo | Squad, derived projects |
| Vendor upgrade (Nuxt UI Dashboard) | Ines | Francisca | Valentina, Paulo | Squad |

### 9.3 Decision protocol

- **Day-to-day**: squad consensus (3 of 4 leads agree). André informed.
- **Architectural change**: requires spec update + spec re-review.
- **Constitution coupling change**: requires `arka` department approval + ArkaOS compatibility matrix bump.
- **Breaking change**: requires major bump + 30-day notice + migration guide.

---

## §10 — Out of Scope (kill list v1)

Items explicitly NOT in v1.0:

| Item | Why excluded | Revisit |
|---|---|---|
| `stancl/tenancy` multi-DB tenancy | Invasive, only one project (yet) would need it | v1.5 if 2+ projects need it |
| GraphQL API base | Not requested, REST covers needs | v2.0 if requested |
| Vue Storefront / Headless commerce | Out of WC scope (Shopify lives elsewhere) | Never |
| Native mobile templates | Out of scope, web-only | v3.0 |
| Built-in CMS WYSIWYG (Tiptap, etc.) | Optional via `wizardingcode-cms-lite` | If cms-lite gains traction |
| Built-in CRM kanban | Optional, will be opt-in module later | v1.5 |
| AI agent orchestration (multi-agent) | Use ArkaOS for orchestration; boilerplate is single-agent | Never |
| Onboarding tour library (Driver.js) | Premature, not standardized yet | v1.2 |
| Server-side analytics built-in | Pulse covers ops, app analytics is per-project | Never |
| Multi-DB read replicas configuration | Per-project infra decision | Never |
| Octane FrankenPHP as default | Optimization premature | Make default in v2.0 if data supports |

---

## §11 — Open Questions & Risks

### 11.1 Open questions (resolve before implementation plan)

1. **Visual regression tool**: Playwright + custom snapshot diff OR Chromatic OR Percy. Cost vs setup tradeoff — decide before Phase 8 (Quality Gate + CI/CD).
2. **OpenAPI generator**: `dedoc/scramble` is current pick — Bruno/Paulo to confirm it supports Sanctum + Spatie Permission via attributes adequately before Phase 5.
3. **TS route types**: Wayfinder (newer, first-party) vs Ziggy (mature, battle-tested). Tentative: Wayfinder if stable by Phase 4; fallback Ziggy.
4. **Component dev environment**: Histoire (Vue-first, lighter) vs Storybook 8 (richer addons). Tentative decision: **Histoire** — confirm in Phase 8 by running a 1-day spike.
5. **WC palette**: Valentina to finalize primary + neutral choices before Phase 4 (Inertia + Nuxt UI port). Placeholder used in spec: `wizarding-purple` + `slate`.

### 11.2 Risks tracked

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Drift between boilerplate and derived projects | High | High | `/wc-doctor` + `/wc-upgrade` + upstream rule |
| Constitution ArkaOS change breaks derived projects | Medium | High | `compatibility.yaml` + semver + `arka-bridge` versioning |
| Vendor template (Nuxt UI Dashboard) upstream breaking change | Medium | Medium | Vendor lock + `/wc-vendor-upgrade` with hunk review |
| Dynamic settings encryption key loss | Low | Critical | Key rotation policy, backup key in secrets vault |
| Quality gate flakiness (browser tests) | Medium | Low | Retry 3x, then fail. Browser tests in dedicated CI job. |
| Light mode regressions (historical pain) | High | Medium | Visual regression CI + 2-screenshot KB rule + ESLint plugin |
| New developer onboarding time | Medium | Medium | Storybook + KB Obsidian + onboarding checklist |

---

## §12 — Acceptance Criteria

v1.0 is shipped when ALL the following are true:

### Functional
- [ ] `composer create-project wizardingcode/boilerplate` works on fresh machine
- [ ] `php artisan wizardingcode:install` interactive flow completes successfully
- [ ] `php artisan wizardingcode:install --no-interaction --mode=api-only --auth=customer` produces working API-only project
- [ ] `php artisan wizardingcode:install --no-interaction --mode=monolith --modules=billing,tenant` produces working monolith
- [ ] Dual auth flows (Staff login + 2FA + Customer register + social Google) work end-to-end
- [ ] AI playground completes a chat round-trip with Anthropic + OpenAI fallback simulation
- [ ] All 4 vendor pages (Dashboard, Inbox, Customers, Settings) render fidelity-checked
- [ ] Dynamic settings UI: storage provider switch (local → S3) works without restart
- [ ] Audit log viewer shows entries for User and Customer changes
- [ ] i18n switcher works pt_PT ↔ en, dynamic add `es` works
- [ ] File upload via `WcDropzone` with progress + ClamAV stub works
- [ ] Notification center receives broadcast notification from Reverb

### Quality
- [ ] `composer arka:gate` passes 9/9 phases
- [ ] PHPStan L9: 0 errors
- [ ] Pest type-coverage: 100%
- [ ] Pest unit + feature: 100% statement, 85% branch coverage
- [ ] Infection MSI: ≥ 75%
- [ ] Pest browser: 3 viewports (mobile / tablet / desktop) green
- [ ] Vitest: ≥ 80% component coverage
- [ ] Security audit: 0 advisories (composer + bun + gitleaks)
- [ ] Visual regression: 0 unintended diffs (light + dark per component)

### ArkaOS
- [ ] `.claude/`, `.agents/`, `.cursor/`, `.codex/`, `.gemini/` synced via `arka-update sync-agents`
- [ ] `CLAUDE.md` = `AGENTS.md` = `GEMINI.md` (regenerated from same template)
- [ ] `.mcp.json` includes 6 servers (laravel-boost, context7, obsidian, claude-mem, playwright, nuxt-ui)
- [ ] `.arka/project.yaml`, `compatibility.yaml`, `kill-list.md`, `raci.md` present and valid
- [ ] All 8 local agents have `.claude/agents/*.md` files
- [ ] All 9 slash commands work (`/wc-feature`, `/wc-api`, `/wc-domain`, `/wc-module`, `/wc-gate`, `/wc-upgrade`, `/wc-doctor`, `/wc-vendor-diff`, `/wc-vendor-upgrade`)
- [ ] `/wc-doctor` reports 0 drift on the boilerplate itself
- [ ] Obsidian vault has `Projects/Boilerplate WizardingCode/UI-UX/` populated with Theme Reference + 5+ Components + 3+ Patterns

### Documentation
- [ ] README.md updated with WC branding + setup + links to docs
- [ ] `docs/superpowers/specs/2026-05-19-boilerplate-wizardingcode-design.md` (this file) reviewed and approved
- [ ] CHANGELOG.md initialized with v1.0.0 entry
- [ ] Migration guide from `nunomaduro/laravel-starter-kit` documented
- [ ] Each `packages/wizardingcode-*` has its own README

### Sign-off
- [ ] Marta CQO: gate-report APPROVED
- [ ] Eduardo Copy: PR text + docs APPROVED
- [ ] Francisca Tech: technical review APPROVED
- [ ] André: final sign-off

---

## Annex A — Implementation phases (high-level, becomes implementation plan)

The detailed implementation plan goes to a separate document (next phase, via `superpowers:writing-plans` skill). Here is the high-level shape:

1. **Phase 1 — Foundation** (week 1): Strip starter-kit demo, install required deps, configure Pint/Rector/PHPStan/Pest, set up `arka:gate` script.
2. **Phase 2 — ArkaOS Integration** (week 2): `.claude/` structure, hooks, commands, agents, skills, MCPs, `.arka/`, multi-runtime sync.
3. **Phase 3 — Dual Auth** (week 3): Users + Customers tables, Fortify config, Sanctum config, Socialite, 2FA, Spatie Permission, Pages, tests.
4. **Phase 4 — Inertia + Nuxt UI Dashboard Port** (week 4): `resources/js/`, vendor port 4 pages, `wizardingcode-ui` package, app.config.ts, color mode.
5. **Phase 5 — Dynamic Settings + Core Contexts** (week 5): AppSetting model, typed settings, backoffice settings UI, AI base, i18n, audit log, file upload, notifications, API base + OpenAPI.
6. **Phase 6 — Install Wizard** (week 6): `php artisan wizardingcode:install`, all flags, module removal logic, ArkaOS registration.
7. **Phase 7 — Optional Modules** (week 7): billing, tenant, cms-lite (each as separate package).
8. **Phase 8 — Quality Gate + CI/CD** (week 8): GitHub Actions, branch protection, conventional commits, visual regression, Storybook (Histoire), security scans.
9. **Phase 9 — Versioning & Drift Defense** (week 9): `/wc-doctor`, `/wc-upgrade`, vendor diff/upgrade commands, compatibility matrix.
10. **Phase 10 — Documentation + Sign-off** (week 10): README, migration guide, KB Obsidian populated, final review, Marta/Eduardo/Francisca/André approval, v1.0.0 tag.

Total: ~10 weeks single-thread, ~6 weeks with squad parallel.

---

## Annex B — Forbidden patterns catalog

Forbidden everywhere (PHPStan custom rules + ESLint custom rules + reviewer manual checks):

| Pattern | Why forbidden | Replacement |
|---|---|---|
| `$guarded = []` in models | Mass-assignment risk (Bruno) | `$fillable = [...]` explicit |
| `$request->all()` in controllers | Unvalidated input (Bruno) | FormRequest + `$request->validated()` |
| Eloquent queries in controllers | Bypasses Service layer (Paulo) | Move to Service or Action |
| Inline `validate(['field' => '...'])` in controllers | Inconsistent validation (Paulo) | FormRequest class |
| Secrets in `.env` for runtime config (storage/email/AI creds) | Should be in dynamic settings (André) | `AppSetting` encrypted |
| `<input type="file">` directly in templates | Inconsistent UX (Ines) | `WcDropzone` from `wizardingcode-ui` |
| `USlideover` for destructive confirmations | UX rule (fovory) | `UModal` with `WcConfirmModal` |
| Raw Tailwind colors in components (`text-gray-*`, `bg-white`) | Dark mode breaks (Valentina) | Semantic tokens (`text-default`, etc.) |
| Vendor components without `@vendor:` header | Vendor lock bypass (Ines) | Add header on port |
| Skipping `composer arka:gate` | Quality gate bypass (Marta) | `--no-arka-gate` flag does NOT exist |
| Untranslated user-facing strings | i18n breaks (Eduardo) | `__('key')` or `useT('key')` |
| `Auth::user()` in places that should be guard-explicit | Dual auth contamination | `auth('web')->user()` or `auth('customer')->user()` |

---

*End of specification.*
