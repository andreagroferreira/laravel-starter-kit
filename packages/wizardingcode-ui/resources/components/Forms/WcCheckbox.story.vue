<script setup lang="ts">
import { ref } from 'vue';
import WcCheckbox from './WcCheckbox.vue';

const accepted = ref(false);
</script>

<template>
    <Story
        title="Forms/WcCheckbox"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcCheckbox
                v-model="accepted"
                label="Accept terms"
                description="By checking this box you agree to our terms."
            />
        </Variant>
    </Story>
</template>
