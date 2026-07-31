<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { statusColor, statusLabel } from '@/composables/useStatusColor';
import type { Page, Site, SiteVersion } from '@/types/models';
import { formatDateTime } from '@/utils/format';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug' | 'type' | 'status'>;
    pages: Page[];
    versions: SiteVersion[];
}>();

const confirm = useConfirm();
const toast = useToast();

const open = ref(false);
const form = useForm({ title: '', slug: '' });

function createPage() {
    form.post(`/sites/${props.site.id}/pages`, {
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}

async function publishSite() {
    const confirmed = await confirm({
        title: 'Publicar o site?',
        description:
            'É criada uma nova versão imutável do schema, visível publicamente.',
        confirmLabel: 'Publicar',
        destructive: false,
    });

    if (confirmed) {
        router.post(`/sites/${props.site.id}/publish`, undefined, {
            onSuccess: () =>
                toast.add({ title: 'Site publicado', color: 'success' }),
        });
    }
}

function togglePage(page: Page) {
    router.post(`/sites/${props.site.id}/pages/${page.id}/publish`, undefined, {
        preserveScroll: true,
    });
}

async function destroyPage(page: Page) {
    const confirmed = await confirm({
        title: `Apagar «${page.title}»?`,
        description: 'A página fica recuperável durante 30 dias.',
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}/pages/${page.id}`, {
            preserveScroll: true,
        });
    }
}

async function destroySite() {
    const confirmed = await confirm({
        title: `Apagar o site «${props.site.name}»?`,
        description:
            'Todas as páginas, artigos e conteúdos deixam de estar acessíveis. Recuperável durante 30 dias.',
        confirmLabel: 'Apagar site',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}`);
    }
}
</script>

<template>
    <Head :title="site.name" />

    <UDashboardPanel id="site-detail">
        <template #header>
            <UDashboardNavbar :title="site.name">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        icon="i-lucide-trash-2"
                        color="error"
                        variant="ghost"
                        aria-label="Apagar site"
                        @click="destroySite"
                    />
                    <UButton
                        label="Publicar site"
                        icon="i-lucide-rocket"
                        color="primary"
                        @click="publishSite"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div
                    class="flex flex-wrap items-center gap-2 px-2 text-sm text-muted"
                >
                    <UBadge :label="site.type" variant="subtle" />
                    <span>{{ site.slug }}</span>
                    <span v-if="versions.length"
                        >· última publicação
                        {{ formatDateTime(versions[0].published_at) }}</span
                    >
                </div>
                <div class="ml-auto flex flex-wrap items-center gap-1">
                    <UButton
                        label="Artigos"
                        size="xs"
                        variant="ghost"
                        icon="i-lucide-newspaper"
                        :to="`/sites/${site.id}/posts`"
                    />
                    <UButton
                        label="Menus"
                        size="xs"
                        variant="ghost"
                        icon="i-lucide-list-tree"
                        :to="`/sites/${site.id}/menus`"
                    />
                    <UButton
                        label="Formulários"
                        size="xs"
                        variant="ghost"
                        icon="i-lucide-clipboard-list"
                        :to="`/sites/${site.id}/forms`"
                    />
                    <UButton
                        label="Redirects"
                        size="xs"
                        variant="ghost"
                        icon="i-lucide-corner-up-right"
                        :to="`/sites/${site.id}/redirects`"
                    />
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div class="space-y-6 p-4 lg:p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Páginas</h2>
                        <UButton
                            label="Nova página"
                            icon="i-lucide-plus"
                            variant="subtle"
                            @click="open = true"
                        />
                    </div>

                    <UEmpty
                        v-if="pages.length === 0"
                        title="Ainda não há páginas"
                        description="Cria a primeira página do site."
                        icon="i-lucide-file"
                        :actions="[
                            {
                                label: 'Nova página',
                                icon: 'i-lucide-plus',
                                onClick: () => (open = true),
                            },
                        ]"
                    />

                    <div
                        v-else
                        class="divide-y divide-default rounded-lg border border-default"
                    >
                        <div
                            v-for="page in pages"
                            :key="page.id"
                            class="flex flex-wrap items-center justify-between gap-2 p-3"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium">
                                    {{ page.title }}
                                </p>
                                <p class="truncate text-sm text-muted">
                                    {{ page.slug }} ·
                                    {{ page.blocks_count }} blocos
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <UBadge
                                    :label="statusLabel(page.status)"
                                    :color="statusColor(page.status)"
                                    variant="subtle"
                                />
                                <UButton
                                    :label="
                                        page.status === 'published'
                                            ? 'Despublicar'
                                            : 'Publicar'
                                    "
                                    size="xs"
                                    variant="ghost"
                                    @click="togglePage(page)"
                                />
                                <UButton
                                    icon="i-lucide-pencil"
                                    size="xs"
                                    variant="ghost"
                                    :to="`/sites/${site.id}/pages/${page.id}`"
                                    aria-label="Editar página"
                                />
                                <UButton
                                    icon="i-lucide-trash-2"
                                    size="xs"
                                    color="error"
                                    variant="ghost"
                                    aria-label="Apagar página"
                                    @click="destroyPage(page)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="versions.length" class="space-y-3">
                    <h2 class="text-lg font-semibold">Versões publicadas</h2>
                    <div
                        class="divide-y divide-default rounded-lg border border-default"
                    >
                        <div
                            v-for="version in versions"
                            :key="version.id"
                            class="flex items-center justify-between p-3 text-sm"
                        >
                            <span class="text-muted">
                                {{ formatDateTime(version.published_at) }}
                            </span>
                            <UBadge
                                :label="
                                    version.origin === 'agent'
                                        ? 'Agente'
                                        : 'Backoffice'
                                "
                                color="neutral"
                                variant="subtle"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <UModal v-model:open="open" title="Nova página">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createPage">
                        <UFormField
                            label="Título"
                            required
                            :error="form.errors.title"
                        >
                            <UInput
                                v-model="form.title"
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
                        <UButton
                            type="submit"
                            label="Criar página"
                            block
                            :loading="form.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
