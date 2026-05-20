<script setup lang="ts">
import type { Range } from '@/types';
import Chart from './Chart.vue';

const range: Range = {
    start: new Date('2026-01-01'),
    end: new Date('2026-01-31'),
};
</script>

<template>
    <Story
        title="Home/Chart"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <Chart period="daily" :range="range" />
        </Variant>
    </Story>
</template>
