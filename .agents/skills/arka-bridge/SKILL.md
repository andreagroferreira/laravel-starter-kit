---
name: arka-bridge
description: ArkaOS integration enforcement for the WizardingCode boilerplate and derived projects. Activate whenever working in this project - covers constitution rules, mandatory-flow phases, KB-first research, and quality-gate orchestration.
---

# ArkaOS Bridge

ArkaOS-specific rules. Always-on in WC boilerplate and derived projects.

## When to invoke

- Every non-trivial task in the boilerplate.
- Any time you see `[ARKA:WORKFLOW-REQUIRED]` or `[ARKA:MANDATORY-FLOW]` in context.
- Whenever the user mentions ArkaOS, constitution, mandatory flow, KB-first, quality gate.

## Rules

- @rules/constitution.md - 10 non-negotiable rules + how to respect them.
- @rules/mandatory-flow.md - 13-phase canonical flow + when to bypass.
- @rules/kb-first.md - Obsidian search BEFORE Context7/WebSearch/WebFetch.
- @rules/quality-gate.md - `composer arka:gate` orchestration + Marta/Eduardo/Francisca review.

## Workflow

1. Emit `[arka:routing] <dept> -> <lead>` as first line.
2. Emit `[arka:phase:N]` before each phase.
3. Search KB FIRST.
4. Cite `[[wikilinks]]` or declare KB gap.
5. Run gate before declaring done.
