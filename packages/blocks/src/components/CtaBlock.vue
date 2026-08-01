<script setup lang="ts">
import type { CtaContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: CtaContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div
            class="mx-auto flex max-w-4xl flex-col items-center gap-5 rounded-[var(--site-radius)] bg-[color:var(--site-accent)]/10 p-10 text-center"
        >
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="Chamada à ação"
                class="text-2xl font-semibold sm:text-3xl"
                @commit="emit('edit', 'heading', $event)"
            />
            <BlockText
                :value="content.description"
                tag="p"
                multiline
                :editable="editable"
                placeholder="Descrição"
                class="max-w-xl opacity-80"
                @commit="emit('edit', 'description', $event)"
            />
            <a
                v-if="content.label"
                :href="content.url || '#'"
                class="rounded-[var(--site-radius)] bg-[color:var(--site-accent)] px-6 py-3 font-medium text-[color:var(--site-accent-fg)]"
                >{{ content.label }}</a
            >
        </div>
    </section>
</template>
