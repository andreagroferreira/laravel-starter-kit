---
name: wizardingcode-conventions
description: Apply this skill whenever writing or reviewing WizardingCode boilerplate code. Triggers for dual auth (Staff vs Customer), dynamic settings (DB-driven), domain promotion (app/Models to app/Domains/X), and forbidden patterns enforcement (banned in CLAUDE.md section 7). Use for any Laravel PHP code in this boilerplate or derived projects.
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

- @rules/dual-auth.md - Staff (Fortify, table `users`) vs Customer (Sanctum + Socialite, table `customers`). Guards separate. No contamination.
- @rules/dynamic-settings.md - `app_settings` (DB) for runtime configurable values (storage, email, AI, branding). Never `.env` for these.
- @rules/promotion-rule.md - `app/Models/X` to `app/Domains/X/` when 5+ related files OR 2+ shared contexts. Francisca reviews.
- @rules/forbidden-patterns.md - Forbidden patterns + replacements (CLAUDE.md section 7 expanded).

## Workflow

1. Identify the WC convention domain (auth / settings / promotion / forbidden).
2. Cite the rule from this skill in your response.
3. Apply the convention literally. Do not paraphrase the rule.
4. Run Pint + PHPStan + Pest after every change.
