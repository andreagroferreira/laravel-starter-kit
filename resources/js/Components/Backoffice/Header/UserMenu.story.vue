<script setup lang="ts">
import UserMenu from './UserMenu.vue';
</script>

<template>
    <Story
        title="Backoffice/Header/UserMenu"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <UserMenu />
        </Variant>

        <Variant title="Collapsed">
            <UserMenu :collapsed="true" />
        </Variant>
    </Story>
</template>
