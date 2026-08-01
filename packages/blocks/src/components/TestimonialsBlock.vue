<script setup lang="ts">
import type { TestimonialsContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: TestimonialsContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div class="mx-auto max-w-5xl space-y-10">
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="O que dizem os clientes"
                class="text-center text-3xl font-semibold"
                @commit="emit('edit', 'heading', $event)"
            />
            <div class="grid gap-6 sm:grid-cols-2">
                <figure
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="space-y-3 rounded-[var(--site-radius)] border border-current/10 p-6"
                >
                    <blockquote class="italic">“{{ item.quote }}”</blockquote>
                    <figcaption class="flex items-center gap-3 text-sm">
                        <img
                            v-if="item.avatar_url"
                            :src="item.avatar_url"
                            alt=""
                            class="size-9 rounded-full object-cover"
                        />
                        <span>
                            <strong>{{ item.author }}</strong>
                            <span v-if="item.role" class="opacity-70">
                                · {{ item.role }}</span
                            >
                        </span>
                    </figcaption>
                </figure>
            </div>
            <p
                v-if="content.items.length === 0"
                class="text-center text-sm opacity-50"
            >
                Sem testemunhos — adiciona itens no painel lateral.
            </p>
        </div>
    </section>
</template>
