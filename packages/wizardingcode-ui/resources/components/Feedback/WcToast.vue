<script setup lang="ts">
/**
 * Mount once in the application shell. Nuxt UI's `UApp` already renders
 * the global `<UToaster />`, so this component is a thin marker / opt-in
 * point: drop it in a layout to guarantee a toaster is present and to
 * provide an obvious anchor point that consumers can grep for.
 *
 * Imperative dispatch goes through `useToast()` from `@wizardingcode/ui`.
 */
defineProps<{
    /**
     * Override the default toast placement.
     * Nuxt UI accepts: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left' | 'top-center' | 'bottom-center'
     */
    placement?: string;
}>();
</script>

<template>
    <UToaster v-bind="$attrs" />
</template>
