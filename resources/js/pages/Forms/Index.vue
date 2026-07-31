<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface FormField {
    name: string;
    type: string;
    required?: boolean;
}

interface SiteForm {
    id: string;
    name: string;
    fields: FormField[];
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    forms: SiteForm[];
}>();

const open = ref(false);
const newForm = reactive({
    name: '',
    fields: [{ name: 'email', type: 'email', required: true }] as FormField[],
});

function addField() {
    newForm.fields.push({ name: '', type: 'text', required: false });
}

function removeField(index: number) {
    newForm.fields.splice(index, 1);
}

function createForm() {
    router.post(
        `/sites/${props.site.id}/forms`,
        { ...newForm },
        {
            onSuccess: () => {
                open.value = false;
                newForm.name = '';
                newForm.fields = [
                    { name: 'email', type: 'email', required: true },
                ];
            },
        },
    );
}

function destroy(id: string) {
    router.delete(`/sites/${props.site.id}/forms/${id}`);
}
</script>

<template>
    <Head :title="`Forms — ${site.name}`" />

    <UDashboardPanel id="forms">
        <template #header>
            <UDashboardNavbar :title="`Forms — ${site.name}`">
                <template #leading>
                    <UDashboardSidebarCollapse />
                    <UButton
                        icon="i-lucide-arrow-left"
                        variant="ghost"
                        :to="`/sites/${site.id}`"
                    />
                </template>
                <template #right>
                    <UButton
                        label="New form"
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
                    title="No forms"
                />

                <UPageCard
                    v-for="form in forms"
                    :key="form.id"
                    :title="form.name"
                    variant="subtle"
                >
                    <div class="flex items-center justify-between">
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
                            @click="destroy(form.id)"
                        />
                    </div>
                </UPageCard>
            </div>

            <UModal v-model:open="open" title="New form">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createForm">
                        <UFormField label="Name" required>
                            <UInput
                                v-model="newForm.name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Fields</p>
                            <div
                                v-for="(field, index) in newForm.fields"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <UInput
                                    v-model="field.name"
                                    placeholder="field name"
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
                                    class="w-32"
                                />
                                <UCheckbox
                                    v-model="field.required"
                                    label="Req."
                                />
                                <UButton
                                    icon="i-lucide-trash-2"
                                    size="xs"
                                    color="error"
                                    variant="ghost"
                                    @click="removeField(index)"
                                />
                            </div>
                            <UButton
                                label="Add field"
                                icon="i-lucide-plus"
                                variant="soft"
                                size="sm"
                                @click="addField"
                            />
                        </div>

                        <UButton type="submit" label="Create form" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
