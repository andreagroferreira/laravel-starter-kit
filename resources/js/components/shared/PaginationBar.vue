<script setup lang="ts">
interface PaginatorMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

defineProps<{ paginator: PaginatorMeta }>();

const emit = defineEmits<{ 'update:page': [page: number] }>();
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="flex flex-wrap items-center justify-between gap-4 pt-4"
    >
        <span class="text-sm text-muted">{{ paginator.total }} registos</span>
        <UPagination
            :page="paginator.current_page"
            :total="paginator.total"
            :items-per-page="paginator.per_page"
            @update:page="emit('update:page', $event)"
        />
    </div>
</template>
