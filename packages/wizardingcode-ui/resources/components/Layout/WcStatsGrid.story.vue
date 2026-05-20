<script setup lang="ts">
import WcStatsGrid, { type WcStat } from './WcStatsGrid.vue';

const fourStats: WcStat[] = [
    {
        title: 'Customers',
        value: '1,234',
        icon: 'i-lucide-users',
        variation: 12.5,
    },
    {
        title: 'Revenue',
        value: '$45,678',
        icon: 'i-lucide-dollar-sign',
        variation: 8.3,
    },
    {
        title: 'Orders',
        value: '892',
        icon: 'i-lucide-shopping-cart',
        variation: -2.4,
    },
    {
        title: 'Conversion',
        value: '3.21%',
        icon: 'i-lucide-trending-up',
        variation: 0.8,
    },
];

const threeStats: WcStat[] = fourStats.slice(0, 3);
const twoStats: WcStat[] = fourStats.slice(0, 2);
const noVariation: WcStat[] = [
    { title: 'Total visits', value: '12,345', icon: 'i-lucide-eye' },
    { title: 'Total signups', value: '432', icon: 'i-lucide-user-plus' },
];
</script>

<template>
    <Story
        title="Layout/WcStatsGrid"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default (4 columns)">
            <WcStatsGrid :stats="fourStats" />
        </Variant>

        <Variant title="3 columns">
            <WcStatsGrid :stats="threeStats" :columns="3" />
        </Variant>

        <Variant title="2 columns">
            <WcStatsGrid :stats="twoStats" :columns="2" />
        </Variant>

        <Variant title="Without variation">
            <WcStatsGrid :stats="noVariation" :columns="2" />
        </Variant>
    </Story>
</template>
