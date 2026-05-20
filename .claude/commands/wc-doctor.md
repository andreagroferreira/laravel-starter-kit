---
name: wc-doctor
description: Report drift between current project and latest boilerplate (read-only).
---

# /wc-doctor

Run drift diagnostic. Output `.arka/telemetry/drift-reports/<date>.md`.

Implemented in Plan 5 - placeholder here. For now, manual checks:

1. `[arka:routing] ops -> Daniel`.
2. Compare versions: `.arka/project.yaml` `version` vs upstream latest.
3. Compare critical files (read-only diff): `.claude/`, `.agents/`, `CLAUDE.md`, `bin/arka-gate`, `composer.json` require block.
4. Compare core deps versions (composer outdated).
5. Output summary to `.arka/telemetry/drift-reports/$(date +%Y-%m-%d).md`.
6. Read `.arka/kill-list.md` to ignore intentionally-removed items.

(Full implementation in Plan 5.)
