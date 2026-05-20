#!/usr/bin/env bash
# Injects [ARKA:MANDATORY-FLOW] + cwd tag + recent gate-report status.

set -euo pipefail

PROJECT="$(basename "$PWD")"
TODAY="$(date +%Y-%m-%d)"

cat <<EOF
[ARKA:MANDATORY-FLOW] cwd=$PROJECT date=$TODAY
EOF

# Show last gate-report verdict if available
if [ -f "storage/arka/gate-report.json" ]; then
    VERDICT=$(grep -o '"verdict"[^,]*' storage/arka/gate-report.json | head -1 | cut -d'"' -f4)
    GENERATED=$(grep -o '"generated_at"[^,]*' storage/arka/gate-report.json | head -1 | cut -d'"' -f4)
    echo "[arka:last-gate] $VERDICT at $GENERATED"
fi

# Hint if .env example differs from .env (config drift)
if [ -f .env ] && [ -f .env.example ]; then
    DIFF_COUNT=$(diff <(grep -E '^[A-Z_]+=' .env.example | sort) <(grep -E '^[A-Z_]+=' .env | cut -d'=' -f1 | sort) | wc -l)
    if [ "$DIFF_COUNT" -gt 0 ]; then
        echo "[arka:env-drift] .env vs .env.example differ - run /wc-doctor for detail."
    fi
fi
