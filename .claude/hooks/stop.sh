#!/usr/bin/env bash
# Blocks "done" status if composer arka:gate has not passed recently.

set -euo pipefail

REPORT="storage/arka/gate-report.json"

if [ ! -f "$REPORT" ]; then
    cat <<'EOF'
[arka:gate-required] No gate-report.json found. Run `composer arka:gate` before marking work done.
EOF
    exit 0
fi

VERDICT=$(grep -o '"verdict"[^,]*' "$REPORT" | head -1 | cut -d'"' -f4)

if [[ "$VERDICT" != "PASSED" ]]; then
    cat <<EOF
[arka:gate-failed] Last gate-report verdict is $VERDICT. Fix before marking done:
  cat $REPORT
EOF
    exit 0
fi

GENERATED=$(grep -o '"generated_at"[^,]*' "$REPORT" | head -1 | cut -d'"' -f4)
if [ -z "$GENERATED" ]; then
    exit 0
fi

GENERATED_TS=$(date -j -f "%Y-%m-%dT%H:%M:%SZ" "$GENERATED" +%s 2>/dev/null || date -d "$GENERATED" +%s 2>/dev/null || echo 0)
NOW_TS=$(date +%s)
AGE=$((NOW_TS - GENERATED_TS))

if [ "$AGE" -gt 3600 ]; then
    cat <<EOF
[arka:gate-stale] gate-report is $((AGE / 60)) min old. Re-run \`composer arka:gate\` before marking done.
EOF
fi

exit 0
