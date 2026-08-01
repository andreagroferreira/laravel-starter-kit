<script setup lang="ts">
import type { FeaturesContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: FeaturesContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div class="mx-auto max-w-5xl space-y-10">
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="Título da secção"
                class="text-center text-3xl font-semibold"
                @commit="emit('edit', 'heading', $event)"
            />
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(item, index) in content.items"
                    :key="index"
                    class="space-y-2"
                >
                    <h3 class="text-lg font-medium">
                        {{ item.title || 'Funcionalidade' }}
                    </h3>
                    <p class="opacity-75">{{ item.description }}</p>
                </div>
            </div>
            <p
                v-if="content.items.length === 0"
                class="text-center text-sm opacity-50"
            >
                Sem funcionalidades — adiciona itens no painel lateral.
            </p>
        </div>
    </section>
</template>
