<script setup lang="ts">
import type { PricingContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: PricingContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div class="mx-auto max-w-5xl space-y-10">
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="Planos e preços"
                class="text-center text-3xl font-semibold"
                @commit="emit('edit', 'heading', $event)"
            />
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="flex flex-col gap-4 rounded-[var(--site-radius)] border p-6"
                    :class="
                        item.highlighted
                            ? 'border-[color:var(--site-accent)] shadow-lg'
                            : 'border-current/10'
                    "
                >
                    <div>
                        <h3 class="text-lg font-medium">{{ item.name }}</h3>
                        <p class="mt-1">
                            <span class="text-3xl font-bold">{{
                                item.price
                            }}</span>
                            <span v-if="item.period" class="opacity-70"
                                >/{{ item.period }}</span
                            >
                        </p>
                        <p v-if="item.description" class="mt-2 text-sm opacity-75">
                            {{ item.description }}
                        </p>
                    </div>
                    <ul class="flex-1 space-y-1 text-sm">
                        <li
                            v-for="(feature, i) in item.features"
                            :key="i"
                            class="opacity-80"
                        >
                            • {{ feature }}
                        </li>
                    </ul>
                    <a
                        v-if="item.cta_label"
                        :href="item.cta_url || '#'"
                        class="rounded-[var(--site-radius)] bg-[color:var(--site-accent)] px-4 py-2 text-center font-medium text-[color:var(--site-accent-fg)]"
                        >{{ item.cta_label }}</a
                    >
                </div>
            </div>
            <p
                v-if="content.items.length === 0"
                class="text-center text-sm opacity-50"
            >
                Sem planos — adiciona itens no painel lateral.
            </p>
        </div>
    </section>
</template>
