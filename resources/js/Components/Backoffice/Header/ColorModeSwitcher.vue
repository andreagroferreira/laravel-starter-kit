<script setup lang="ts">
import { computed } from 'vue';
import type { DropdownMenuItem } from '@nuxt/ui';
import { useColorMode, type ColorMode } from '@/Composables/useColorMode';

const { mode, setMode } = useColorMode();

const triggerIcon = computed(() => {
    if (mode.value === 'dark') {
        return 'i-lucide-moon';
    }

    if (mode.value === 'light') {
        return 'i-lucide-sun';
    }

    return 'i-lucide-monitor';
});

const items = computed<DropdownMenuItem[][]>(() => [
    [
        {
            label: 'Light',
            icon: 'i-lucide-sun',
            type: 'checkbox',
            checked: mode.value === 'light',
            onSelect(e: Event) {
                e.preventDefault();
                setMode('light' satisfies ColorMode);
            },
        },
        {
            label: 'Dark',
            icon: 'i-lucide-moon',
            type: 'checkbox',
            checked: mode.value === 'dark',
            onSelect(e: Event) {
                e.preventDefault();
                setMode('dark' satisfies ColorMode);
            },
        },
        {
            label: 'System',
            icon: 'i-lucide-monitor',
            type: 'checkbox',
            checked: mode.value === 'system',
            onSelect(e: Event) {
                e.preventDefault();
                setMode('system' satisfies ColorMode);
            },
        },
    ],
]);
</script>

<template>
    <UDropdownMenu
        :items="items"
        :content="{ align: 'end', collisionPadding: 12 }"
    >
        <UButton
            :icon="triggerIcon"
            color="neutral"
            variant="ghost"
            :aria-label="`Color mode: ${mode}`"
        />
    </UDropdownMenu>
</template>
