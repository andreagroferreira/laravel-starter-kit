#!/usr/bin/env bash
# Injects [ARKA:WORKFLOW-REQUIRED] context tag on every user prompt
# unless the user explicitly opted out (set ARKA_BYPASS=1 in env).

set -euo pipefail

if [[ "${ARKA_BYPASS:-0}" == "1" ]]; then
    exit 0
fi

cat <<'EOF'
[ARKA:WORKFLOW-REQUIRED] This project enforces the ArkaOS mandatory 13-phase flow.
Required first line of your response: [arka:routing] <dept> -> <lead>
Trivial bypass: [arka:trivial] <reason> for single-file edits under 10 lines.
EOF
