<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import type { FormField, Site, SiteForm } from '@/types/models';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug'>;
    forms: SiteForm[];
}>();

const confirm = useConfirm();

const open = ref(false);
const newForm = useForm<{ name: string; fields: FormField[] }>({
    name: '',
    fields: [{ name: 'email', type: 'email', required: true }],
});

function addField() {
    newForm.fields.push({ name: '', type: 'text', required: false });
}

function removeField(index: number) {
    newForm.fields.splice(index, 1);
}

function createForm() {
    newForm.post(`/sites/${props.site.id}/forms`, {
        onSuccess: () => {
            open.value = false;
            newForm.reset();
        },
    });
}

async function destroy(form: SiteForm) {
    const confirmed = await confirm({
        title: `Apagar o formulário «${form.name}»?`,
        description: 'Os sites publicados deixam de o poder renderizar.',
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}/forms/${form.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="`Formulários — ${site.name}`" />

    <UDashboardPanel id="forms">
        <template #header>
            <UDashboardNavbar :title="`Formulários — ${site.name}`">
                <template #leading>
                    <UDashboardSidebarCollapse />
                    <UButton
                        icon="i-lucide-arrow-left"
                        variant="ghost"
                        :to="`/sites/${site.id}`"
                        aria-label="Voltar ao site"
                    />
                </template>
                <template #right>
                    <UButton
                        label="Novo formulário"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="mx-auto w-full max-w-3xl space-y-4 p-4 lg:p-6">
                <UEmpty
                    v-if="forms.length === 0"
                    icon="i-lucide-clipboard-list"
                    title="Ainda não há formulários"
                    description="Cria um formulário de contacto ou captação de leads."
                    :actions="[
                        {
                            label: 'Novo formulário',
                            icon: 'i-lucide-plus',
                            onClick: () => (open = true),
                        },
                    ]"
                />

                <UPageCard
                    v-for="form in forms"
                    :key="form.id"
                    :title="form.name"
                    variant="subtle"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div class="flex flex-wrap gap-2">
                            <UBadge
                                v-for="field in form.fields"
                                :key="field.name"
                                :label="`${field.name} (${field.type})${field.required ? ' *' : ''}`"
                                variant="subtle"
                            />
                        </div>
                        <UButton
                            icon="i-lucide-trash-2"
                            size="xs"
                            color="error"
                            variant="ghost"
                            aria-label="Apagar formulário"
                            @click="destroy(form)"
                        />
                    </div>
                </UPageCard>
            </div>

            <UModal v-model:open="open" title="Novo formulário">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createForm">
                        <UFormField
                            label="Nome"
                            required
                            :error="newForm.errors.name"
                        >
                            <UInput
                                v-model="newForm.name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Campos</p>
                            <p
                                v-if="newForm.errors.fields"
                                class="text-sm text-error"
                            >
                                {{ newForm.errors.fields }}
                            </p>
                            <div
                                v-for="(field, index) in newForm.fields"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <UInput
                                    v-model="field.name"
                                    placeholder="nome do campo"
                                    aria-label="Nome do campo"
                                    class="flex-1"
                                />
                                <USelect
                                    v-model="field.type"
                                    :items="[
                                        'text',
                                        'email',
                                        'textarea',
                                        'number',
                                        'tel',
                                        'url',
                                    ]"
                                    aria-label="Tipo do campo"
                                    class="w-32"
                                />
                                <UCheckbox
                                    v-model="field.required"
                                    label="Obrig."
                                />
                                <UButton
                                    icon="i-lucide-trash-2"
                                    size="xs"
                                    color="error"
                                    variant="ghost"
                                    aria-label="Remover campo"
                                    @click="removeField(index)"
                                />
                            </div>
                            <UButton
                                label="Adicionar campo"
                                icon="i-lucide-plus"
                                variant="soft"
                                size="sm"
                                @click="addField"
                            />
                        </div>

                        <UButton
                            type="submit"
                            label="Criar formulário"
                            block
                            :loading="newForm.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
