---
name: inertia-vue-nuxtui
description: Inertia v3 + Vue 3 + Nuxt UI 4 patterns for the WizardingCode boilerplate monolith mode. Apply this skill whenever editing files under resources/js/, app.config.ts, or packages/wizardingcode-ui/. Covers Inertia routing, Vue composition API conventions, Nuxt UI component usage, vendor-lock discipline, and the SSR-safe color mode pattern.
---

# Inertia v3 + Vue 3 + Nuxt UI 4

WC monolith frontend patterns. Always-on when working in resources/js/ or packages/wizardingcode-ui/.

## When to invoke

- Creating or editing any .vue file under resources/js/.
- Creating new pages, layouts, or components.
- Modifying app.config.ts or the WC UI package.
- Wiring Inertia routes (Laravel side AND Vue side).
- Touching color mode or vendor-locked files.

## Rules

- @rules/page-and-layout.md — Inertia v3 page + layout conventions (defineOptions, persistent layouts, head meta).
- @rules/nuxt-ui-components.md — Forbidden patterns + correct primitive usage (UModal not USlideover for confirms, WcDropzone not <input type="file">, semantic tokens only).
- @rules/vendor-lock.md — vendor-lock discipline (@vendor: header, /wc-vendor-diff, /wc-vendor-upgrade).
- @rules/inertia-routing.md — Laravel routes + Inertia::render + how to add a new page-type.

## Workflow

1. Identify domain (page, layout, component, composable, store).
2. Cite the matching rule.
3. Use `<script setup lang="ts">`.
4. Semantic tokens only (text-default, bg-default, etc.).
5. After editing, `bun run build` + Vitest + visual regression.
