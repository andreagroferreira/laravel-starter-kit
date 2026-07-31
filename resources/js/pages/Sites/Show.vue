<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface PageRow {
    id: string;
    title: string;
    slug: string;
    status: string;
    blocks_count: number;
    published_at: string | null;
}

interface VersionRow {
    id: string;
    origin: string;
    published_at: string | null;
    created_at: string;
}

const props = defineProps<{
    site: {
        id: string;
        name: string;
        slug: string;
        type: string;
        status: string;
    };
    pages: PageRow[];
    versions: VersionRow[];
}>();

const open = ref(false);
const form = reactive({ title: '', slug: '' });

function createPage() {
    router.post(
        `/sites/${props.site.id}/pages`,
        { ...form },
        {
            onSuccess: () => {
                open.value = false;
                form.title = '';
                form.slug = '';
            },
        },
    );
}

function publishSite() {
    router.post(`/sites/${props.site.id}/publish`);
}

function togglePage(page: PageRow) {
    router.post(`/sites/${props.site.id}/pages/${page.id}/publish`);
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
                        label="Publish site"
                        icon="i-lucide-rocket"
                        color="primary"
                        @click="publishSite"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex items-center gap-2 px-2 text-sm text-muted">
                    <UBadge :label="site.type" variant="subtle" />
                    <span>{{ site.slug }}</span>
                    <span v-if="versions.length"
                        >· last published
                        {{ versions[0].published_at ?? 'never' }}</span
                    >
                </div>
                <div class="ml-auto flex items-center gap-1">
                    <UButton
                        label="Posts"
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
                        label="Forms"
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
            <div class="space-y-4 p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Pages</h2>
                    <UButton
                        label="New page"
                        icon="i-lucide-plus"
                        variant="subtle"
                        @click="open = true"
                    />
                </div>

                <UEmpty
                    v-if="pages.length === 0"
                    title="No pages"
                    icon="i-lucide-file"
                />

                <div
                    v-else
                    class="divide-y divide-default rounded-lg border border-default"
                >
                    <div
                        v-for="page in pages"
                        :key="page.id"
                        class="flex items-center justify-between p-3"
                    >
                        <div>
                            <p class="font-medium">{{ page.title }}</p>
                            <p class="text-sm text-muted">
                                {{ page.slug }} · {{ page.blocks_count }} blocks
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="page.status"
                                :color="
                                    page.status === 'published'
                                        ? 'success'
                                        : 'neutral'
                                "
                                variant="subtle"
                            />
                            <UButton
                                :label="
                                    page.status === 'published'
                                        ? 'Unpublish'
                                        : 'Publish'
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
                            />
                        </div>
                    </div>
                </div>
            </div>

            <UModal v-model:open="open" title="New page">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createPage">
                        <UFormField label="Title" required>
                            <UInput v-model="form.title" class="w-full" />
                        </UFormField>
                        <UFormField label="Slug" required>
                            <UInput v-model="form.slug" class="w-full" />
                        </UFormField>
                        <UButton type="submit" label="Create page" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
