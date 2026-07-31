<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { statusColor } from '@/composables/useStatusColor';
import type { Site } from '@/types/models';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

defineProps<{
    sites: Site[];
}>();

const open = ref(false);
const form = useForm({
    name: '',
    slug: '',
    type: 'site',
    domain: '',
});

function submit() {
    form.transform((data) => ({ ...data, domain: data.domain || null })).post(
        '/sites',
        {
            onSuccess: () => {
                open.value = false;
                form.reset();
            },
        },
    );
}

const TYPE_LABELS: Record<string, string> = {
    site: 'Site',
    landing: 'Landing page',
    news: 'Jornal',
};
</script>

<template>
    <Head title="Sites" />

    <UDashboardPanel id="sites">
        <template #header>
            <UDashboardNavbar title="Sites">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="Novo site"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="p-4 lg:p-6">
                <UEmpty
                    v-if="sites.length === 0"
                    icon="i-lucide-globe"
                    title="Ainda não há sites"
                    description="Cria o primeiro site para começar."
                    :actions="[
                        {
                            label: 'Novo site',
                            icon: 'i-lucide-plus',
                            onClick: () => (open = true),
                        },
                    ]"
                />

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <UPageCard
                        v-for="site in sites"
                        :key="site.id"
                        :title="site.name"
                        :description="`${site.slug} · ${site.pages_count ?? 0} páginas`"
                        :to="`/sites/${site.id}`"
                    >
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="TYPE_LABELS[site.type] ?? site.type"
                                color="neutral"
                                variant="subtle"
                            />
                            <UBadge
                                :label="
                                    site.status === 'published'
                                        ? 'Publicado'
                                        : 'Rascunho'
                                "
                                :color="statusColor(site.status)"
                                variant="subtle"
                            />
                        </div>
                    </UPageCard>
                </div>
            </div>

            <UModal v-model:open="open" title="Novo site">
                <template #body>
                    <form class="space-y-4" @submit.prevent="submit">
                        <UFormField
                            label="Nome"
                            required
                            :error="form.errors.name"
                        >
                            <UInput
                                v-model="form.name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UFormField
                            label="Slug"
                            required
                            :error="form.errors.slug"
                        >
                            <UInput v-model="form.slug" class="w-full" />
                        </UFormField>
                        <UFormField
                            label="Tipo"
                            required
                            :error="form.errors.type"
                        >
                            <USelect
                                v-model="form.type"
                                :items="[
                                    { label: 'Site', value: 'site' },
                                    { label: 'Landing page', value: 'landing' },
                                    { label: 'Jornal', value: 'news' },
                                ]"
                                class="w-full"
                            />
                        </UFormField>
                        <UFormField
                            label="Domínio próprio"
                            :error="form.errors.domain"
                        >
                            <UInput
                                v-model="form.domain"
                                placeholder="opcional"
                                class="w-full"
                            />
                        </UFormField>
                        <UButton
                            type="submit"
                            label="Criar site"
                            block
                            :loading="form.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
