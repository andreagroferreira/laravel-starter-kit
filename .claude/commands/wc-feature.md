---
name: wc-feature
description: Implement a new feature in the WizardingCode boilerplate (or a derived project). Drives the full mandatory flow: spec to plan to TDD to quality gate.
arguments: feature-name (kebab-case)
---

# /wc-feature <feature-name>

Implement a new feature using the WizardingCode workflow.

Steps the assistant takes:
1. `[arka:routing] dev -> Paulo, Ines` (or specialized lead based on feature domain).
2. Run `/arka-spec` to produce a feature spec in `docs/superpowers/specs/`.
3. Run `superpowers:writing-plans` to break the spec into TDD tasks.
4. Execute the plan (subagent-driven preferred).
5. Per task: write failing test, then minimal code, then green, then Pint, then commit.
6. After all tasks: `composer arka:gate` until PASSED.
7. Submit PR with checklist (FormRequest, API Resource, tests, dark+light validated, KB note linked).

ARGUMENTS: $1 = feature name (e.g. `wc-feature customer-export`).
