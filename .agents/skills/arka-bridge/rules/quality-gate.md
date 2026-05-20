# Quality Gate (composer arka:gate)

9-phase pipeline. Marta/Eduardo/Francisca review the report - not the code.

## Phases

```
1. Pint                                            (code style)
2. Rector dry-run                                  (refactoring opportunities)
3. PHPStan L9                                      (static analysis)
4. Pest type-coverage 100%                         (type completeness)
5. Pest unit + feature 100% stmt / 85% branch      (behavior coverage)
6. Pest browser (3 viewports)                      (UI integrity, monolith only)
7. Infection MSI >=75% / covered >=85%             (mutation testing)
8. Vitest >=80%                                    (Vue components, monolith only)
9. Security audit (composer + bun + gitleaks)      (supply chain + secrets)
```

## When to run

- Locally: before declaring any task done.
- Hook: `.claude/hooks/stop.sh` checks recent gate-report.json (< 1 hour, verdict PASSED).
- CI: every PR runs the gate before merge.

## How to read gate-report.json

```bash
cat storage/arka/gate-report.json | jq '.verdict, .phases[] | {id, status, duration_seconds}'
```

## Common failures and fixes

| Failure | Fix |
|---|---|
| Pint | `vendor/bin/pint --format=agent` |
| Rector | `vendor/bin/rector --no-progress-bar` |
| PHPStan L9 | Add types, narrow returns, fix iterable annotations |
| type-coverage | Add explicit return/param types |
| Pest unit | Read the failure, fix the test or the code |
| Pest browser | Capture screenshot from `tests/Browser/Output/` |
| Infection | Add tests for surviving mutants |
| Vitest | Read failure, fix test or component |
| Security | Update dep, rotate exposed secret |
