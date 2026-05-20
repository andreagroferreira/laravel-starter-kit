---
name: pest-browser-tdd
description: Playwright via Pest 5 browser plugin for E2E tests in WC boilerplate monolith mode. Apply this skill when writing tests under tests/Browser/ or when a UI flow has more than 2 user actions. Covers 3-viewport mandate, a11y assertions, Inertia visit patterns, and Pest skip discipline.
---

# Pest Browser TDD

E2E test discipline for the WC boilerplate.

## When to invoke

- Adding any new flow with more than 2 user actions (login, register, checkout, multi-step form).
- Writing or modifying files under `tests/Browser/`.
- Adding visual regression baselines (separate from Histoire stories).
- Auditing existing E2E coverage.

## Rules

- @rules/three-viewports.md — every browser test runs at mobile/tablet/desktop.
- @rules/skip-discipline.md — skip with explicit reason when Playwright unavailable.
- @rules/inertia-flows.md — how to test Inertia visits + page transitions.

## Workflow

1. Write the failing test FIRST (TDD).
2. Test at desktop viewport — get it green.
3. Add mobile + tablet variants.
4. Add a11y assertion (axe-core via Playwright).
5. Run locally if Playwright installed; otherwise `->skip()` with reason.
6. Plan 5 CI installs Playwright and runs all skipped tests.
