#!/usr/bin/env bash
# pre-component-create.sh
#
# Blocks Write of a NEW Vue component file if no matching Obsidian KB note exists.
# Triggered by PreToolUse matcher on Write/Edit for paths under:
#   - resources/js/Components/**/*.vue
#   - packages/wizardingcode-ui/resources/components/**/*.vue
#
# Skipped for EDITS (file already exists) — only blocks CREATION.
#
# KB note expected at:
#   $OBSIDIAN_VAULT_PATH/Projects/Boilerplate WizardingCode/UI-UX/Components/<Category>/<Name>.md
#
# Until KB note exists, the agent must:
#   1. Open Obsidian
#   2. Create the note (props/slots/events/screenshots/do/don't per skill rules/component-anatomy)
#   3. Re-attempt the component file creation
#
# This is a NON-NEGOTIABLE rule (spec §6.5).

set -euo pipefail

FILE="${1:-}"

# Only fire on Vue component paths
if [[ "$FILE" != *"resources/js/Components/"* ]] && [[ "$FILE" != *"packages/wizardingcode-ui/resources/components/"* ]]; then
    exit 0
fi

# Skip if not a .vue file
if [[ "$FILE" != *.vue ]]; then
    exit 0
fi

# Skip Story files (they're co-located with components)
if [[ "$FILE" == *.story.vue ]]; then
    exit 0
fi

# Skip Test files
if [[ "$FILE" == *.test.vue ]]; then
    exit 0
fi

# If the file already exists, this is an EDIT, not a CREATE - allow
if [ -f "$FILE" ]; then
    exit 0
fi

# Extract component name + category from path
# resources/js/Components/Forms/WcDateRange.vue  → Forms / WcDateRange
# packages/wizardingcode-ui/resources/components/Data/WcDataTable.vue → Data / WcDataTable

COMPONENT_NAME=$(basename "$FILE" .vue)
PARENT_DIR=$(dirname "$FILE")
CATEGORY=$(basename "$PARENT_DIR")

# Default expected KB path
VAULT_PATH="${OBSIDIAN_VAULT_PATH:-}"

if [ -z "$VAULT_PATH" ]; then
    cat <<EOF
[arka:hook-warn] OBSIDIAN_VAULT_PATH not set in environment. Skipping KB-first check.
  Component being created: $FILE
  Per spec §6.5, every new component requires an Obsidian KB note BEFORE the file.
  Manually verify the note exists, or set OBSIDIAN_VAULT_PATH to enable the hook.
EOF
    exit 0
fi

KB_NOTE="$VAULT_PATH/Projects/Boilerplate WizardingCode/UI-UX/Components/$CATEGORY/$COMPONENT_NAME.md"

if [ ! -f "$KB_NOTE" ]; then
    cat <<EOF
[arka:hook-block] KB Obsidian note required BEFORE creating a new component.

  Missing note: $KB_NOTE

  Per spec §6.5 (non-negotiable):
  Every component must have its Obsidian note with props/slots/events/screenshots
  (light + dark) BEFORE the .vue file is created.

  Steps to unblock:
  1. Open Obsidian.
  2. Create the note at:
       Projects/Boilerplate WizardingCode/UI-UX/Components/$CATEGORY/$COMPONENT_NAME.md
     Use the component-anatomy template (see .agents/skills/wizardingcode-ui-kb/rules/component-anatomy.md).
  3. Retry the file creation.

  Skill reference: wizardingcode-ui-kb
EOF
    exit 1
fi

# KB note exists — proceed
echo "[arka:hook-ok] KB note found: $KB_NOTE"
exit 0
