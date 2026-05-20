<script setup lang="ts">
import { ref } from 'vue';
import type { Range } from '@/types';
import DateRangePicker from './DateRangePicker.vue';

const range = ref<Range>({
    start: new Date('2026-01-01'),
    end: new Date('2026-01-31'),
});
</script>

<template>
    <Story
        title="Home/DateRangePicker"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <DateRangePicker v-model="range" />
        </Variant>
    </Story>
</template>
