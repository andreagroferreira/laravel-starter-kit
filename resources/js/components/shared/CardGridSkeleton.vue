<script setup lang="ts">
withDefaults(defineProps<{ cards?: number }>(), { cards: 8 });
</script>

<template>
    <div
        class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
        role="status"
        aria-label="A carregar"
    >
        <div v-for="card in cards" :key="card" class="space-y-2">
            <USkeleton class="aspect-square w-full rounded-lg" />
            <USkeleton class="h-3 w-2/3" />
        </div>
    </div>
</template>
