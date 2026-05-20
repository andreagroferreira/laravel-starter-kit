---
name: wc-gate
description: Run composer arka:gate (9-phase quality gate) and report.
---

# /wc-gate

Run the full WC quality gate:

```
composer arka:gate
```

If PASSED:
- Report verdict + duration.
- Surface gate-report.json path.
- State that Marta/Eduardo/Francisca can approve.

If FAILED:
- Show which phase failed.
- Suggest the specific command to fix (e.g. `vendor/bin/pint --format=agent` for Pint failures).
- Do NOT proceed to mark any task as done.
