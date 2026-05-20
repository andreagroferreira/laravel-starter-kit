# KB Note Before Component

The Obsidian note is the SOURCE OF TRUTH for a component's API + appearance. The code implements the note.

## When creating a new Wc* or vendor component:

1. Open Obsidian. Navigate to `Projects/Boilerplate WizardingCode/UI-UX/Components/`.
2. Create `<Category>/<ComponentName>.md` (e.g. `Forms/WcDateRange.md`).
3. Write the note (see @rules/component-anatomy.md).
4. THEN write the .vue file.
5. Cross-reference: the .vue file has a `<!-- KB: Components/Forms/WcDateRange -->` comment at top.

## Enforcement

Plan 5 wires `.claude/hooks/pre-component-create.sh` that blocks `Write` on a new component file if no matching KB note exists. Until then: voluntary discipline + PR review.
