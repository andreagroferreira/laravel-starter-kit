<script setup lang="ts">
import type { Range } from '@/types';
import Sales from './Sales.vue';

const range: Range = {
    start: new Date('2026-01-01'),
    end: new Date('2026-01-31'),
};
</script>

<template>
    <Story
        title="Home/Sales"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <Sales period="daily" :range="range" />
        </Variant>
    </Story>
</template>
