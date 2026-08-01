<script setup lang="ts">
import type { ImageContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: ImageContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <figure class="mx-auto max-w-4xl space-y-3 px-6 py-12 sm:px-10">
        <img
            v-if="content.url"
            :src="content.url"
            :alt="content.alt"
            class="w-full rounded-[var(--site-radius)] object-cover"
            loading="lazy"
        />
        <div
            v-else
            class="flex aspect-[16/9] w-full items-center justify-center rounded-[var(--site-radius)] border border-dashed border-current/20 text-sm opacity-50"
        >
            Sem imagem escolhida
        </div>
        <BlockText
            :value="content.caption"
            tag="figcaption"
            :editable="editable"
            placeholder="Legenda"
            class="text-center text-sm opacity-70"
            @commit="emit('edit', 'caption', $event)"
        />
    </figure>
</template>
