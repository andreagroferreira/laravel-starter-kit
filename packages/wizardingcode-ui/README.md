# @wizardingcode/ui

Shared Vue 3 UI components for the WizardingCode boilerplate. Thin opinionated wrappers over **Nuxt UI 4** primitives that encode WC conventions:

- Semantic tokens only (`bg-default`, `text-highlighted`, `text-muted`, ...) — never raw Tailwind colors.
- `UModal` for confirmations — **never** `USlideover`.
- Dropzones for file inputs — **never** native `<input type="file">`.
- Mandatory `WcEmptyState` for empty data lists.
- Full light + dark mode support.

## Install

In a WC boilerplate-derived project the package is auto-required via the `packages/*` path repository. Standalone:

```bash
composer require wizardingcode/ui
```

The JS side is consumed via the tsconfig/vite alias `@wizardingcode/ui` (already wired in the boilerplate).

## Components

### Forms

| Component       | Purpose                                                |
| --------------- | ------------------------------------------------------ |
| `WcDropzone`    | Drag-and-drop file upload (NEVER use native `<input>`) |
| `WcInput`       | Text input wrapping `UInput` + `UFormField`            |
| `WcSelect`      | Select wrapping `USelect` + `UFormField`               |
| `WcCheckbox`    | Checkbox wrapping `UCheckbox` + `UFormField`           |
| `WcDateRange`   | Date range picker                                      |

### Data

| Component        | Purpose                                       |
| ---------------- | --------------------------------------------- |
| `WcDataTable`    | Table with cursor pagination slot             |
| `WcMasterDetail` | Two-column master/detail layout               |
| `WcEmptyState`   | Mandatory empty-list pattern (icon + CTA)     |

### Feedback

| Component        | Purpose                                                  |
| ---------------- | -------------------------------------------------------- |
| `WcConfirmModal` | Confirmation modal (`UModal`, NEVER `USlideover`)        |
| `WcToast`        | Opinionated toast helpers (`success`, `error`, `info`)   |

### Layout

| Component       | Purpose                                                |
| --------------- | ------------------------------------------------------ |
| `WcPageHeader`  | Page title + breadcrumbs + actions slot                |
| `WcStatsGrid`   | 3-4 column responsive grid of stat cards               |

## Composables

| Composable       | Purpose                                                              |
| ---------------- | -------------------------------------------------------------------- |
| `useTheme`       | Color mode helper (full SSR-safe persistence lands in Plan 2 / F1)   |
| `useFormErrors`  | Maps a RFC7807 problem+json response into Inertia-style errors       |
| `useToast`       | Wrapper around Nuxt UI's `useToast` with semantic helpers            |

## Usage

```vue
<script setup lang="ts">
import { WcInput, WcConfirmModal, useToast } from '@wizardingcode/ui';

const toast = useToast();
const open = ref(false);

function handleConfirm(): void {
    toast.success('Saved!');
    open.value = false;
}
</script>

<template>
    <WcInput v-model="name" label="Name" required />
    <WcConfirmModal
        v-model:open="open"
        title="Delete this item?"
        confirm-color="error"
        @confirm="handleConfirm"
    />
</template>
```

## Versioning

Follows the boilerplate. See root `.arka/compatibility.yaml`.
