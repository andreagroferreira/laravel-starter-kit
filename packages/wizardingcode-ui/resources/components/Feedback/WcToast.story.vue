<script setup lang="ts">
import WcToast from './WcToast.vue';
</script>

<template>
    <Story
        title="Feedback/WcToast"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcToast />
            <p class="p-4 text-sm text-muted">
                Toaster anchor mounted. Toasts are dispatched imperatively via
                <code>useToast()</code>.
            </p>
        </Variant>
    </Story>
</template>
