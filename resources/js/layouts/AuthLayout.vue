<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();

const appName = import.meta.env.VITE_APP_NAME || 'App';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-muted/30 p-4"
    >
        <div class="mb-6 flex items-center gap-2">
            <UIcon name="i-lucide-wand-sparkles" class="size-7 text-primary" />
            <span class="text-xl font-bold">{{ appName }}</span>
        </div>

        <UCard class="w-full max-w-sm">
            <template #header>
                <h1 class="text-lg font-semibold">{{ title }}</h1>
                <p v-if="description" class="text-sm text-muted">
                    {{ description }}
                </p>
            </template>

            <slot />

            <template v-if="$slots.footer" #footer>
                <slot name="footer" />
            </template>
        </UCard>
    </div>
</template>
