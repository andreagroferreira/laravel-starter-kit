---
name: wizardingcode-ui-kb
description: KB-first enforcement for UI/UX in the WC boilerplate. Triggers on any edit of resources/js/Components/ or packages/wizardingcode-ui/. Searches Obsidian Projects/Boilerplate WizardingCode/UI-UX/ BEFORE proposing changes. Apply this skill before creating or modifying any visual component. Plan 5 wires the pre-component-create hook.
---

# WizardingCode UI KB-First

UI/UX changes are KB-driven. No new component without an Obsidian note.

## When to invoke

- Creating a new Vue component under resources/js/Components/ or packages/wizardingcode-ui/.
- Editing an existing component (visual change, prop addition, behavior change).
- Designing a new pattern (CRUD, master-detail, settings tabs, etc.).
- Auditing dark/light mode consistency.

## Rules

- @rules/kb-before-create.md — search the vault first; create the note BEFORE the component.
- @rules/screenshots-required.md — light + dark screenshots mandatory per component note.
- @rules/component-anatomy.md — required sections in every Component Obsidian note.

## Workflow

1. Search `Projects/Boilerplate WizardingCode/UI-UX/Components/` for the component name.
2. If exists: read it; align your changes; update screenshots if appearance changes.
3. If not: create the note BEFORE writing code.
4. Reference the note in the PR description: `KB: [[Components/WcEmptyState]]`.
