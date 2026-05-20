<script setup lang="ts">
import ColorModeSwitcher from './ColorModeSwitcher.vue';
</script>

<template>
    <Story
        title="Backoffice/Header/ColorModeSwitcher"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <ColorModeSwitcher />
        </Variant>
    </Story>
</template>
