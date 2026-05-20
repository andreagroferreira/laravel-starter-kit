<script setup lang="ts" generic="T extends Record<string, unknown>">
defineOptions({ inheritAttrs: false });

defineProps<{
    rows: T[];
    columns?: Array<{ key: string; label?: string; [k: string]: unknown }>;
    loading?: boolean;
    nextCursor?: string | null;
    prevCursor?: string | null;
}>();

defineEmits<{
    'load-next': [];
    'load-prev': [];
}>();
</script>

<template>
    <div class="flex flex-col gap-3">
        <UTable
            :data="rows"
            :columns="columns"
            :loading="loading"
            v-bind="$attrs"
        >
            <template
                v-for="(_, name) in $slots"
                :key="name"
                #[name]="slotData"
            >
                <slot :name="name" v-bind="slotData ?? {}" />
            </template>
        </UTable>

        <div
            v-if="nextCursor || prevCursor"
            class="flex items-center justify-end gap-2"
        >
            <UButton
                variant="ghost"
                size="sm"
                :disabled="!prevCursor"
                icon="i-lucide-chevron-left"
                @click="$emit('load-prev')"
            >
                Previous
            </UButton>
            <UButton
                variant="ghost"
                size="sm"
                :disabled="!nextCursor"
                trailing-icon="i-lucide-chevron-right"
                @click="$emit('load-next')"
            >
                Next
            </UButton>
        </div>
    </div>
</template>
