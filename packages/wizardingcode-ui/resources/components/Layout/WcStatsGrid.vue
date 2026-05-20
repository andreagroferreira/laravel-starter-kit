<script setup lang="ts">
export interface WcStat {
    title: string;
    value: string | number;
    icon?: string;
    variation?: number;
    formatter?: (value: number) => string;
}

defineProps<{
    stats: WcStat[];
    columns?: 2 | 3 | 4;
}>();
</script>

<template>
    <div
        :class="[
            'grid grid-cols-1 gap-4',
            columns === 2 && 'sm:grid-cols-2',
            columns === 3 && 'sm:grid-cols-2 lg:grid-cols-3',
            (!columns || columns === 4) && 'sm:grid-cols-2 lg:grid-cols-4',
        ]"
    >
        <UCard v-for="stat in stats" :key="stat.title">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase text-muted">
                        {{ stat.title }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-highlighted">
                        {{ stat.value }}
                    </p>
                </div>
                <UIcon
                    v-if="stat.icon"
                    :name="stat.icon"
                    class="text-2xl text-muted"
                />
            </div>
            <p
                v-if="typeof stat.variation === 'number'"
                :class="[
                    'mt-2 text-xs font-medium',
                    stat.variation >= 0 ? 'text-success' : 'text-error',
                ]"
            >
                {{ stat.variation >= 0 ? '+' : '' }}{{ stat.variation }}%
            </p>
        </UCard>
    </div>
</template>
