<script setup lang="ts">
defineProps<{
    icon?: string;
    title: string;
    description?: string;
    actionLabel?: string;
}>();

defineEmits<{
    action: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center px-4 py-12 text-center"
        role="status"
        aria-live="polite"
    >
        <UIcon
            :name="icon ?? 'i-lucide-package'"
            class="mb-4 text-6xl text-muted"
        />
        <h3 class="text-lg font-semibold text-highlighted">
            {{ title }}
        </h3>
        <p
            v-if="description"
            class="mt-2 max-w-md text-sm text-muted"
        >
            {{ description }}
        </p>
        <UButton
            v-if="actionLabel"
            color="primary"
            class="mt-6"
            @click="$emit('action')"
        >
            {{ actionLabel }}
        </UButton>
    </div>
</template>
