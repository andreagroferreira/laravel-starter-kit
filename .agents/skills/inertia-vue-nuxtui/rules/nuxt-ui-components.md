# Nuxt UI 4 Component Usage

## Forbidden patterns

- `<input type="file">` → use `<WcDropzone>` from `@wizardingcode/ui` (regra fovory).
- `USlideover` for destructive confirms → use `<UModal>` + `<WcConfirmModal>` (regra fovory).
- Raw Tailwind palette colors (`text-gray-900`, `bg-white`) → use semantic tokens.

## Semantic tokens (Nuxt UI 4)

- `text-default` / `text-muted` / `text-toned` / `text-dimmed` / `text-highlighted`
- `bg-default` / `bg-muted` / `bg-elevated` / `bg-accented` / `bg-inverted`
- `border-default` / `border-muted` / `border-accented`

## Common primitives

- `UButton :loading :disabled icon variant color size`
- `UInput v-model type autocomplete`
- `UModal :open` (NEVER `USlideover` for confirms)
- `UCheckbox`, `USelect`, `URadioGroup`
- `UFormField :label :error :hint :required` (wraps UInput etc.)
- `UToast` via `useToast()` composable
- `UDashboardSidebar`, `UDashboardSearch`, `UDashboardNavbar` (vendor patterns)
- `UCard`, `UAlert`, `UBadge`

## Color tokens (configured in app.config.ts)

- `color: primary` (default brand action color — currently violet, placeholder pending §11.1.5)
- `color: neutral` (default UI surface neutrals)
- `color: success | warning | error | info` (status feedback)
