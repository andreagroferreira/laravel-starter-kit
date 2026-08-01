<script setup lang="ts">
import { inject, ref } from 'vue';
import type { FormContent } from '../contracts';
import { RESOLVE_FORM, SUBMIT_LEAD } from '../injection';
import BlockText from './BlockText.vue';

const props = defineProps<{ content: FormContent; editable?: boolean }>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();

const resolveForm = inject(RESOLVE_FORM, null);
const submitLead = inject(SUBMIT_LEAD, null);

const definition = resolveForm?.(props.content.form_id) ?? null;
const values = ref<Record<string, string>>({});
const honeypot = ref('');
const state = ref<'idle' | 'sending' | 'done' | 'error'>('idle');

async function submit() {
    if (!submitLead || state.value === 'sending') {
        return;
    }

    state.value = 'sending';

    try {
        await submitLead(props.content.form_id, {
            ...values.value,
            _website: honeypot.value,
        });
        state.value = 'done';
    } catch {
        state.value = 'error';
    }
}
</script>

<template>
    <section class="px-6 py-16 sm:px-10">
        <div class="mx-auto max-w-xl space-y-6">
            <BlockText
                :value="content.heading"
                tag="h2"
                :editable="editable"
                placeholder="Fala connosco"
                class="text-3xl font-semibold"
                @commit="emit('edit', 'heading', $event)"
            />
            <BlockText
                :value="content.description"
                tag="p"
                multiline
                :editable="editable"
                placeholder="Descrição do formulário"
                class="opacity-80"
                @commit="emit('edit', 'description', $event)"
            />

            <p v-if="state === 'done'" class="rounded-[var(--site-radius)] bg-[color:var(--site-accent)]/10 p-4">
                Obrigado! Entramos em contacto em breve.
            </p>

            <form v-else class="space-y-4" @submit.prevent="submit">
                <div
                    v-for="field in definition?.fields ?? []"
                    :key="field.name"
                    class="space-y-1"
                >
                    <label :for="`f-${field.name}`" class="text-sm font-medium">
                        {{ field.name }}
                        <span v-if="field.required" aria-hidden="true">*</span>
                    </label>
                    <textarea
                        v-if="field.type === 'textarea'"
                        :id="`f-${field.name}`"
                        v-model="values[field.name]"
                        :required="field.required"
                        rows="4"
                        class="w-full rounded-[var(--site-radius)] border border-current/20 bg-transparent p-2"
                    />
                    <input
                        v-else
                        :id="`f-${field.name}`"
                        v-model="values[field.name]"
                        :type="field.type"
                        :required="field.required"
                        class="w-full rounded-[var(--site-radius)] border border-current/20 bg-transparent p-2"
                    />
                </div>

                <p v-if="!definition" class="text-sm opacity-50">
                    Nenhum formulário associado — escolhe um no painel lateral.
                </p>

                <!-- Honeypot: hidden from humans, irresistible to bots. -->
                <input
                    v-model="honeypot"
                    type="text"
                    name="_website"
                    tabindex="-1"
                    autocomplete="off"
                    aria-hidden="true"
                    class="hidden"
                />

                <button
                    v-if="definition"
                    type="submit"
                    class="rounded-[var(--site-radius)] bg-[color:var(--site-accent)] px-6 py-3 font-medium text-[color:var(--site-accent-fg)]"
                    :disabled="state === 'sending'"
                >
                    {{ content.submit_label || 'Enviar' }}
                </button>

                <p v-if="state === 'error'" class="text-sm">
                    Não foi possível enviar. Tenta novamente.
                </p>
            </form>
        </div>
    </section>
</template>
