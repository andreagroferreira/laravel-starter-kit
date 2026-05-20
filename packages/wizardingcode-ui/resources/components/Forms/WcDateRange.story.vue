<script setup lang="ts">
import { ref } from 'vue';
import WcDateRange, { type WcDateRangeValue } from './WcDateRange.vue';

const range = ref<WcDateRangeValue>({
    start: new Date('2026-01-01'),
    end: new Date('2026-01-31'),
});
</script>

<template>
    <Story
        title="Forms/WcDateRange"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcDateRange
                v-model="range"
                label="Date range"
                hint="Pick a start and end date."
            />
        </Variant>
    </Story>
</template>
