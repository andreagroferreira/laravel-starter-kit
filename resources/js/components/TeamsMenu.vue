<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    collapsed?: boolean;
}>();

const page = usePage();

const tenantName = computed(() => {
    const user = page.props.auth?.user as { name?: string } | undefined;

    return user?.name ?? 'Workspace';
});

const appName = import.meta.env.VITE_APP_NAME || 'App';
</script>

<template>
    <div class="flex items-center gap-2 overflow-hidden px-1 py-1">
        <div
            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary"
        >
            <UIcon name="i-lucide-wand-sparkles" class="size-4" />
        </div>
        <div v-if="!collapsed" class="min-w-0">
            <p class="truncate text-sm font-semibold text-highlighted">
                {{ appName }}
            </p>
            <p class="truncate text-xs text-muted">{{ tenantName }}</p>
        </div>
    </div>
</template>
