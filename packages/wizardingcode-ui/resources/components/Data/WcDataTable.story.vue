<script setup lang="ts">
import WcDataTable from './WcDataTable.vue';

const rows = [
    { id: 1, name: 'Alice', email: 'alice@example.com', status: 'active' },
    { id: 2, name: 'Bob', email: 'bob@example.com', status: 'inactive' },
    { id: 3, name: 'Carol', email: 'carol@example.com', status: 'active' },
];

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Name' },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'status', header: 'Status' },
];
</script>

<template>
    <Story
        title="Data/WcDataTable"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcDataTable :rows="rows" :columns="columns" />
        </Variant>
    </Story>
</template>
