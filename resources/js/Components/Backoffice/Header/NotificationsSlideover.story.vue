<script setup lang="ts">
import NotificationsSlideover from './NotificationsSlideover.vue';
</script>

<template>
    <Story
        title="Backoffice/Header/NotificationsSlideover"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <NotificationsSlideover />
        </Variant>
    </Story>
</template>
