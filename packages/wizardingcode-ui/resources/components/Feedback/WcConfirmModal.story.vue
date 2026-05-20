<script setup lang="ts">
import { ref } from 'vue';
import WcConfirmModal from './WcConfirmModal.vue';

const openDefault = ref(true);
const openDestructive = ref(true);
const openMinimal = ref(true);
</script>

<template>
    <Story
        title="Feedback/WcConfirmModal"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcConfirmModal
                v-model:open="openDefault"
                title="Confirm action"
                description="Are you sure you want to continue with this action?"
                confirm-label="Confirm"
                cancel-label="Cancel"
            />
        </Variant>

        <Variant title="Destructive">
            <WcConfirmModal
                v-model:open="openDestructive"
                title="Delete customer"
                description="This will permanently remove the customer. This action cannot be undone."
                confirm-label="Delete"
                cancel-label="Keep"
                confirm-color="error"
            />
        </Variant>

        <Variant title="Minimal (title only)">
            <WcConfirmModal
                v-model:open="openMinimal"
                title="Discard changes?"
            />
        </Variant>
    </Story>
</template>
