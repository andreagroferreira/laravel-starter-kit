---
name: wizardingcode-ui-kb
description: KB-first enforcement for UI/UX in the WC boilerplate. Triggers on any edit of resources/js/Components/ or packages/wizardingcode-ui/. Searches Obsidian Projects/Boilerplate WizardingCode/UI-UX/ BEFORE proposing changes. Full enforcement arrives in Plan 2.
---

# WizardingCode UI KB-First - Skeleton

Full enforcement arrives in Plan 2 (Inertia port + UI/UX foundation).

## Surface area (placeholder)

- Obsidian vault path: `Projects/Boilerplate WizardingCode/UI-UX/`.
- Required notes per component: props, slots, events, screenshots (light + dark), do/don't.
- Hook: `pre-component-create.sh` blocks creation without matching KB note (Plan 2).

## Hard rules (forward declaration)

- New component -> KB note required BEFORE PR opens.
- Edited component -> KB note updated BEFORE merge.
- 2 screenshots (light + dark) required.

## Status

Plan 1: skeleton. Plan 2: pre-component-create hook + Histoire integration.
