#!/usr/bin/env bash
# Blocks dangerous git operations and forbidden-file commits.

set -euo pipefail

CMD="${1:-}"

# Block --force on push
if echo "$CMD" | grep -qE 'git\s+push\s+.*(--force|-f\b)'; then
    echo "[arka:hook-block] git push --force is blocked. Use a feature branch + PR instead."
    exit 1
fi

# Block direct pushes to main/master
if echo "$CMD" | grep -qE 'git\s+push\s+.*\b(main|master)\b'; then
    BRANCH=$(git symbolic-ref --short HEAD 2>/dev/null || echo "DETACHED")
    if [[ "$BRANCH" == "main" || "$BRANCH" == "master" ]]; then
        echo "[arka:hook-block] Direct push from $BRANCH is blocked. Use a feature branch + PR."
        exit 1
    fi
fi

# Block git add of forbidden files
if echo "$CMD" | grep -qE 'git\s+add'; then
    FORBIDDEN_PATTERNS='(^\.env$|^\.env\.[^e]|\.pem$|\.key$|id_rsa|^.*\.sqlite$|/storage/logs/)'
    FILES=$(echo "$CMD" | sed -E 's/git\s+add\s+//')
    if echo "$FILES" | grep -qE "$FORBIDDEN_PATTERNS"; then
        echo "[arka:hook-block] Forbidden files in git add. Blocked."
        exit 1
    fi
fi

exit 0
