<script setup lang="ts">
const model = defineModel<string>({ required: true });

defineProps<{ placeholder?: string }>();
</script>

<template>
    <UInput
        v-model="model"
        icon="i-lucide-search"
        :placeholder="placeholder ?? 'Pesquisar…'"
        class="w-full max-w-xs"
        aria-label="Pesquisar"
    />
</template>
