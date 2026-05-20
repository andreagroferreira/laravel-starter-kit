<script setup lang="ts">
import { ref } from 'vue';
import WcSelect from './WcSelect.vue';

const selected = ref<string | number | null>(null);

const items = [
    { label: 'Active', value: 'active' },
    { label: 'Pending', value: 'pending' },
    { label: 'Archived', value: 'archived' },
];
</script>

<template>
    <Story
        title="Forms/WcSelect"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcSelect
                v-model="selected"
                :items="items"
                label="Status"
                placeholder="Choose a status"
            />
        </Variant>
    </Story>
</template>
