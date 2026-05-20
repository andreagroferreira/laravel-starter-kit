# Screenshots Required

Every component Obsidian note has TWO screenshots:

- `<ComponentName>-light.png`
- `<ComponentName>-dark.png`

## How to capture

1. Open Histoire (`bun run story:dev`).
2. Navigate to the component's story.
3. Toggle background preset to Light. Screenshot.
4. Toggle to Dark. Screenshot.
5. Crop to the relevant area, save to the Obsidian attachments folder.
6. Reference in the note: `![[WcEmptyState-light.png]] ![[WcEmptyState-dark.png]]`.

## Updates

If the component's appearance changes (props, slots, styling), RE-CAPTURE both screenshots. Plan 5 visual regression will catch unintended changes.
