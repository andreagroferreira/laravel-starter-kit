<script setup lang="ts">
import type { HeroContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: HeroContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section
        class="relative overflow-hidden px-6 py-20 text-center sm:px-10 sm:py-28"
    >
        <img
            v-if="content.image_url"
            :src="content.image_url"
            alt=""
            class="absolute inset-0 size-full object-cover opacity-20"
        />
        <div class="relative mx-auto max-w-3xl space-y-5">
            <BlockText
                :value="content.heading"
                tag="h1"
                :editable="editable"
                placeholder="Título principal"
                class="text-4xl font-bold tracking-tight sm:text-5xl"
                @commit="emit('edit', 'heading', $event)"
            />
            <BlockText
                :value="content.subheading"
                tag="p"
                multiline
                :editable="editable"
                placeholder="Subtítulo"
                class="text-lg opacity-80"
                @commit="emit('edit', 'subheading', $event)"
            />
            <a
                v-if="content.cta_label"
                :href="content.cta_url || '#'"
                class="inline-block rounded-[var(--site-radius)] bg-[color:var(--site-accent)] px-6 py-3 font-medium text-[color:var(--site-accent-fg)]"
                >{{ content.cta_label }}</a
            >
        </div>
    </section>
</template>
