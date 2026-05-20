---
name: wc-upgrade
description: Apply boilerplate upstream patches to this derived project (hunk-by-hunk, with kill-list awareness).
---

# /wc-upgrade

Selective patch-apply from `WizardingCode/boilerplate-wizardingcode` upstream to current project. NOT a git pull.

Implemented in Plan 5 - placeholder here. For now, manual steps:

1. `[arka:routing] dev -> Paulo`.
2. Fetch latest boilerplate version: `git fetch git@github.com:WizardingCode/boilerplate-wizardingcode.git main`.
3. Inspect diff: `git diff FETCH_HEAD..HEAD -- '*.claude/*' '*.arka/*' 'bin/*' 'CLAUDE.md' 'composer.json'`.
4. Filter out items present in `.arka/kill-list.md`.
5. Apply approved hunks via `git apply --3way` or interactive cherry-pick.
6. Run `composer arka:gate` to verify nothing broke.
7. Commit: `chore(upgrade): apply boilerplate patches from <version>`.

(Full implementation in Plan 5: Quality Gate, Drift Defense, Release.)
