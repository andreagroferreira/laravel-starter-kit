# Inertia v3 Page + Layout Conventions

## Page structure

Every Page is at `resources/js/Pages/<Module>/<Action>.vue` (PascalCase). The page declares its layout via Inertia v3's `defineOptions({ layout })` pattern.

```vue
<script setup lang="ts">
import BackofficeLayout from '@/Layouts/BackofficeLayout.vue';

defineOptions({ layout: BackofficeLayout });

defineProps<{ /* page-specific props */ }>();
</script>

<template>
    <!-- page content (BackofficeLayout's <slot /> renders this) -->
</template>
```

## Persistent layouts (Settings tabs etc.)

```vue
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineOptions({ layout: [BackofficeLayout, SettingsLayout] });
```

The outer (BackofficeLayout) wraps the inner (SettingsLayout) which wraps the page. Use this when a sub-section has its own sidebar/tabs.

## Head meta

```vue
import { Head } from '@inertiajs/vue3';

<Head title="Dashboard" />
```

## Path conventions

- `@/Pages/...` for pages
- `@/Layouts/...` for layouts
- `@/Components/...` for project components
- `@wizardingcode/ui` for shared package components
- `@/Composables/...` for composables
- `@/Stores/...` for Pinia stores

## Routes

Backoffice pages live under `/admin/*` (Plan 2 B2). Customer-facing pages would live under `/`. Use literal path strings (Ziggy deferred per spec §11.1.3).
