<script setup lang="ts">
import type { FaqContent } from '../contracts';
import BlockText from './BlockText.vue';

defineProps<{ content: FaqContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div class="mx-auto max-w-3xl space-y-8">
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="Perguntas frequentes"
                class="text-center text-3xl font-semibold"
                @commit="emit('edit', 'heading', $event)"
            />
            <dl class="divide-y divide-current/10">
                <div v-for="(item, index) in content.items" :key="index" class="py-4">
                    <dt class="font-medium">{{ item.question }}</dt>
                    <dd class="mt-1 opacity-75">{{ item.answer }}</dd>
                </div>
            </dl>
            <p
                v-if="content.items.length === 0"
                class="text-center text-sm opacity-50"
            >
                Sem perguntas — adiciona itens no painel lateral.
            </p>
        </div>
    </section>
</template>
