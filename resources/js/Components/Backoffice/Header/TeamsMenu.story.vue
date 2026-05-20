<script setup lang="ts">
import TeamsMenu from './TeamsMenu.vue';
</script>

<template>
    <Story
        title="Backoffice/Header/TeamsMenu"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <TeamsMenu />
        </Variant>

        <Variant title="Collapsed">
            <TeamsMenu :collapsed="true" />
        </Variant>
    </Story>
</template>
