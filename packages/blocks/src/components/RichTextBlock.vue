<script setup lang="ts">
import type { RichTextContent } from '../contracts';

defineProps<{ content: RichTextContent; editable?: boolean }>();
</script>

<template>
    <section class="px-6 py-12 sm:px-10">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div
            class="prose mx-auto max-w-3xl"
            :class="content.html === '' ? 'opacity-50' : ''"
            v-html="content.html || '<p>Bloco de texto vazio.</p>'"
        />
    </section>
</template>
