<script setup lang="ts">
import AddModal from './AddModal.vue';
</script>

<template>
    <Story
        title="Customers/AddModal"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <AddModal />
        </Variant>
    </Story>
</template>
