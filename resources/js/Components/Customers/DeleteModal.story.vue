<script setup lang="ts">
import DeleteModal from './DeleteModal.vue';
</script>

<template>
    <Story
        title="Customers/DeleteModal"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <DeleteModal :count="3">
                <UButton color="error" icon="i-lucide-trash">
                    Delete selected
                </UButton>
            </DeleteModal>
        </Variant>
    </Story>
</template>
