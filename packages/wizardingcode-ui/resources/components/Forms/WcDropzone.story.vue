<script setup lang="ts">
import { ref } from 'vue';
import WcDropzone from './WcDropzone.vue';

const files = ref<File[]>([]);
const imageFiles = ref<File[]>([]);
const multipleFiles = ref<File[]>([]);
</script>

<template>
    <Story
        title="Forms/WcDropzone"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcDropzone v-model="files" />
        </Variant>

        <Variant title="Images only">
            <WcDropzone
                v-model="imageFiles"
                accept="image/*"
            />
        </Variant>

        <Variant title="Multiple files (PDF)">
            <WcDropzone
                v-model="multipleFiles"
                accept="application/pdf"
                :multiple="true"
            />
        </Variant>
    </Story>
</template>
