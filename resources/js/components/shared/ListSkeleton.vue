<script setup lang="ts">
withDefaults(defineProps<{ rows?: number }>(), { rows: 6 });
</script>

<template>
    <div class="space-y-3" role="status" aria-label="A carregar">
        <div
            v-for="row in rows"
            :key="row"
            class="flex items-center gap-4 py-2"
        >
            <USkeleton class="size-10 rounded-lg" />
            <div class="flex-1 space-y-2">
                <USkeleton class="h-4 w-1/3" />
                <USkeleton class="h-3 w-1/5" />
            </div>
            <USkeleton class="h-8 w-20" />
        </div>
    </div>
</template>
