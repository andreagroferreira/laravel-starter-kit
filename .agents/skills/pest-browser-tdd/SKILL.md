---
name: pest-browser-tdd
description: Playwright via Pest 5 browser plugin for E2E tests in WC boilerplate monolith mode. Activate for tests/Browser/. Full content lands in Plan 2.
---

# Pest Browser TDD - Skeleton

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
