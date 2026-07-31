<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    profile: {
        name: string;
        tone_of_voice: string | null;
        glossary: Record<string, string>;
        examples: string[];
        guardrails: Record<string, unknown>;
    } | null;
}>();

const toast = useToast();

const form = reactive({
    name: props.profile?.name ?? '',
    tone_of_voice: props.profile?.tone_of_voice ?? '',
    glossary: Object.entries(props.profile?.glossary ?? {}).map(
        ([term, definition]) => ({
            term,
            definition,
        }),
    ),
    examples: [...(props.profile?.examples ?? [])],
});

const newExample = ref('');

function addGlossaryRow() {
    form.glossary.push({ term: '', definition: '' });
}

function removeGlossaryRow(index: number) {
    form.glossary.splice(index, 1);
}

function addExample() {
    if (newExample.value.trim() === '') return;

    form.examples.push(newExample.value.trim());
    newExample.value = '';
}

function removeExample(index: number) {
    form.examples.splice(index, 1);
}

const saving = ref(false);

function save() {
    saving.value = true;
    router.put(
        '/settings/brand',
        {
            name: form.name,
            tone_of_voice: form.tone_of_voice,
            glossary: Object.fromEntries(
                form.glossary
                    .filter((row) => row.term.trim() !== '')
                    .map((row) => [row.term, row.definition]),
            ),
            examples: form.examples,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
            },
            onSuccess: () =>
                toast.add({ title: 'Brand voice guardada', color: 'success' }),
            onError: (errors) =>
                toast.add({
                    title: Object.values(errors)[0] ?? 'Dados inválidos.',
                    color: 'error',
                }),
        },
    );
}
</script>

<template>
    <Head title="Brand voice" />

    <SettingsLayout>
        <UPageCard
            title="Brand voice"
            description="Injeta tom, glossário e exemplos em todas as gerações AI do tenant."
            variant="naked"
            orientation="horizontal"
            class="mb-4"
        >
            <UButton label="Save" class="w-fit lg:ms-auto" @click="save" />
        </UPageCard>

        <UPageCard variant="subtle">
            <div class="space-y-4">
                <UFormField label="Profile name" required>
                    <UInput v-model="form.name" class="w-full" />
                </UFormField>

                <UFormField
                    label="Tone of voice"
                    description="Ex: direto, técnico mas acessível, sem jargão."
                >
                    <UTextarea
                        v-model="form.tone_of_voice"
                        :rows="3"
                        class="w-full"
                    />
                </UFormField>
            </div>
        </UPageCard>

        <UPageCard title="Glossary" variant="subtle">
            <div class="space-y-2">
                <div
                    v-for="(row, index) in form.glossary"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <UInput
                        v-model="row.term"
                        placeholder="Term"
                        class="flex-1"
                    />
                    <UInput
                        v-model="row.definition"
                        placeholder="Definition"
                        class="flex-1"
                    />
                    <UButton
                        icon="i-lucide-trash-2"
                        size="xs"
                        color="error"
                        variant="ghost"
                        @click="removeGlossaryRow(index)"
                    />
                </div>
                <UButton
                    label="Add term"
                    icon="i-lucide-plus"
                    variant="soft"
                    size="sm"
                    @click="addGlossaryRow"
                />
            </div>
        </UPageCard>

        <UPageCard title="Style examples" variant="subtle">
            <div class="space-y-2">
                <div
                    v-for="(example, index) in form.examples"
                    :key="index"
                    class="flex items-start justify-between gap-2 rounded-lg border border-default p-2"
                >
                    <p class="text-sm">{{ example }}</p>
                    <UButton
                        icon="i-lucide-trash-2"
                        size="xs"
                        color="error"
                        variant="ghost"
                        @click="removeExample(index)"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <UInput
                        v-model="newExample"
                        placeholder="Write an example in your voice…"
                        class="flex-1"
                        @keydown.enter.prevent="addExample"
                    />
                    <UButton
                        label="Add"
                        variant="soft"
                        size="sm"
                        @click="addExample"
                    />
                </div>
            </div>
        </UPageCard>
    </SettingsLayout>
</template>
