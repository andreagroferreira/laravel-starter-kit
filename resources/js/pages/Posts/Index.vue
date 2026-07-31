<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PaginationBar from '@/components/shared/PaginationBar.vue';
import SearchInput from '@/components/shared/SearchInput.vue';
import { useConfirm } from '@/composables/useConfirm';
import { statusColor, statusLabel } from '@/composables/useStatusColor';
import { useTableQuery } from '@/composables/useTableQuery';
import type { Category, Post, Site } from '@/types/models';
import type { Paginated } from '@/types/pagination';
import { formatDate } from '@/utils/format';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug'>;
    posts: Paginated<Post>;
    categories: Category[];
    filters: { search: string; status: string; category: string };
}>();

const confirm = useConfirm();

const { state, goToPage } = useTableQuery({
    only: ['posts', 'filters'],
    filters: { status: props.filters.status, category: props.filters.category },
});

const open = ref(false);
const form = useForm({ title: '', slug: '' });

function createPost() {
    form.post(`/sites/${props.site.id}/posts`, {
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}

async function destroyPost(post: Post) {
    const confirmed = await confirm({
        title: `Apagar «${post.title}»?`,
        description: 'O artigo fica recuperável durante 30 dias.',
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}/posts/${post.id}`, {
            preserveScroll: true,
        });
    }
}

const hasActiveFilters = () =>
    state.search !== '' ||
    state.filters.status !== '' ||
    state.filters.category !== '';
</script>

<template>
    <Head :title="`Artigos — ${site.name}`" />

    <UDashboardPanel id="posts">
        <template #header>
            <UDashboardNavbar :title="`Artigos — ${site.name}`">
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
                        label="Novo artigo"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex w-full flex-wrap items-center gap-2 px-2">
                    <SearchInput
                        v-model="state.search"
                        placeholder="Pesquisar artigos…"
                    />
                    <USelect
                        v-model="state.filters.status"
                        :items="[
                            { label: 'Todos os estados', value: '' },
                            { label: 'Rascunho', value: 'draft' },
                            { label: 'Publicado', value: 'published' },
                            { label: 'Agendado', value: 'scheduled' },
                        ]"
                        class="w-44"
                        aria-label="Filtrar por estado"
                    />
                    <USelect
                        v-model="state.filters.category"
                        :items="[
                            { label: 'Todas as categorias', value: '' },
                            ...categories.map((category) => ({
                                label: category.name,
                                value: category.id,
                            })),
                        ]"
                        class="w-48"
                        aria-label="Filtrar por categoria"
                    />
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div class="p-4 lg:p-6">
                <UEmpty
                    v-if="posts.data.length === 0 && !hasActiveFilters()"
                    title="Ainda não há artigos"
                    description="Cria o primeiro artigo deste site."
                    icon="i-lucide-newspaper"
                    :actions="[
                        {
                            label: 'Novo artigo',
                            icon: 'i-lucide-plus',
                            onClick: () => (open = true),
                        },
                    ]"
                />

                <UEmpty
                    v-else-if="posts.data.length === 0"
                    title="Sem resultados"
                    description="Nenhum artigo corresponde à pesquisa ou filtros."
                    icon="i-lucide-search-x"
                />

                <div
                    v-else
                    class="divide-y divide-default rounded-lg border border-default"
                >
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="flex flex-wrap items-center justify-between gap-2 p-4 hover:bg-elevated/40"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ post.title }}</p>
                            <p class="truncate text-sm text-muted">
                                /{{ post.slug }}
                                <span v-if="post.categories?.length">
                                    ·
                                    {{
                                        post.categories
                                            .map((c) => c.name)
                                            .join(', ')
                                    }}
                                </span>
                                <span v-if="post.published_at">
                                    · {{ formatDate(post.published_at) }}</span
                                >
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="statusLabel(post.status)"
                                :color="statusColor(post.status)"
                                variant="subtle"
                            />
                            <UButton
                                icon="i-lucide-pencil"
                                size="xs"
                                variant="ghost"
                                :to="`/sites/${site.id}/posts/${post.id}`"
                                aria-label="Editar artigo"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="xs"
                                color="error"
                                variant="ghost"
                                aria-label="Apagar artigo"
                                @click="destroyPost(post)"
                            />
                        </div>
                    </div>
                </div>

                <PaginationBar :paginator="posts" @update:page="goToPage" />
            </div>

            <UModal v-model:open="open" title="Novo artigo">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createPost">
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
                            label="Criar artigo"
                            block
                            :loading="form.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
