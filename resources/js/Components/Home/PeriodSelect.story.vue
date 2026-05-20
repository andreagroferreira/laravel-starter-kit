<script setup lang="ts">
import { ref } from 'vue';
import type { Period, Range } from '@/types';
import PeriodSelect from './PeriodSelect.vue';

const period = ref<Period>('daily');
const range: Range = {
    start: new Date('2026-01-01'),
    end: new Date('2026-01-31'),
};
</script>

<template>
    <Story
        title="Home/PeriodSelect"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <PeriodSelect v-model="period" :range="range" />
        </Variant>
    </Story>
</template>
