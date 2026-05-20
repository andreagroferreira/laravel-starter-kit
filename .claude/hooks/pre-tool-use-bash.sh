#!/usr/bin/env bash
# Blocks destructive bash patterns.

set -euo pipefail

CMD="${1:-}"

# Block rm -rf on protected paths
if echo "$CMD" | grep -qE 'rm\s+(-[rRfF]+\s+)*(/|/\*|~|\.|\.\.|/(etc|usr|var|home|root)|\.git|\.env|storage|vendor|node_modules)'; then
    if ! echo "$CMD" | grep -qE 'rm\s+(-[rRfF]+\s+)*\b(storage/arka|storage/logs|node_modules)\b'; then
        echo "[arka:hook-block] rm against protected path blocked. Use explicit narrow path if intentional."
        exit 1
    fi
fi

# Block database drops
if echo "$CMD" | grep -qiE '(drop\s+(database|schema|table)|truncate\s+(database|all))'; then
    echo "[arka:hook-block] Database/schema/table drop blocked. Use a migration."
    exit 1
fi

# Block --no-verify in commit/push
if echo "$CMD" | grep -qE 'git\s+(commit|push).*--no-verify'; then
    echo "[arka:hook-block] --no-verify blocked. Fix the hook failure instead."
    exit 1
fi

exit 0
