#!/usr/bin/env bash
# Warns when editing vendor-locked files and blocks edits to secrets files.

set -euo pipefail

FILE="${1:-}"

# Block edits to secret-storage files entirely
if echo "$FILE" | grep -qE '(^\.env$|\.pem$|\.key$|id_rsa)'; then
    echo "[arka:hook-block] Edits to secret-storage files are blocked. Use dynamic settings in DB instead."
    exit 1
fi

# Warn on vendor-locked files
if [ -f "$FILE" ]; then
    if head -5 "$FILE" 2>/dev/null | grep -qE '@vendor:\s*'; then
        echo "[arka:hook-warn] Vendor-locked file ($FILE). Editing breaks integrity from upstream template."
        echo "  Use /wc-vendor-upgrade workflow if you need to update from upstream."
        echo "  Proceeding only with explicit user confirmation."
    fi
fi

exit 0
