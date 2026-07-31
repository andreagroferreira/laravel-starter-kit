<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface PostRow {
    id: string;
    title: string;
    slug: string;
    status: string;
    excerpt: string | null;
    published_at: string | null;
    categories: { id: string; name: string }[];
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    posts: { data: PostRow[] };
    categories: { id: string; name: string }[];
}>();

const search = ref('');
const statusFilter = ref<string | null>(null);

const filtered = computed(() =>
    props.posts.data.filter((post) => {
        const matchesSearch =
            search.value === '' ||
            post.title.toLowerCase().includes(search.value.toLowerCase()) ||
            post.slug.toLowerCase().includes(search.value.toLowerCase());
        const matchesStatus =
            statusFilter.value === null || post.status === statusFilter.value;

        return matchesSearch && matchesStatus;
    }),
);

const open = ref(false);
const form = reactive({
    title: '',
    slug: '',
});

function createPost() {
    router.post(
        `/sites/${props.site.id}/posts`,
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

function statusColor(status: string): 'success' | 'warning' | 'neutral' {
    if (status === 'published') return 'success';
    if (status === 'scheduled') return 'warning';

    return 'neutral';
}
</script>

<template>
    <Head :title="`Posts — ${site.name}`" />

    <UDashboardPanel id="posts">
        <template #header>
            <UDashboardNavbar :title="`Posts — ${site.name}`">
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
                        label="New post"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex w-full items-center gap-2 px-2">
                    <UInput
                        v-model="search"
                        icon="i-lucide-search"
                        placeholder="Search posts…"
                        class="max-w-xs"
                    />
                    <USelect
                        v-model="statusFilter"
                        :items="[
                            { label: 'All statuses', value: null },
                            { label: 'Draft', value: 'draft' },
                            { label: 'Published', value: 'published' },
                            { label: 'Scheduled', value: 'scheduled' },
                        ]"
                        class="w-40"
                    />
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div class="p-4 lg:p-6">
                <UEmpty
                    v-if="filtered.length === 0"
                    title="No posts found"
                    icon="i-lucide-newspaper"
                    :actions="[
                        {
                            label: 'New post',
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
                        v-for="post in filtered"
                        :key="post.id"
                        class="flex items-center justify-between p-4 hover:bg-elevated/40"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ post.title }}</p>
                            <p class="truncate text-sm text-muted">
                                /{{ post.slug }}
                                <span v-if="post.categories.length">
                                    ·
                                    {{
                                        post.categories
                                            .map((c) => c.name)
                                            .join(', ')
                                    }}
                                </span>
                                <span v-if="post.published_at">
                                    · {{ post.published_at }}</span
                                >
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="post.status"
                                :color="statusColor(post.status)"
                                variant="subtle"
                            />
                            <UButton
                                icon="i-lucide-pencil"
                                size="xs"
                                variant="ghost"
                                :to="`/sites/${site.id}/posts/${post.id}`"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <UModal v-model:open="open" title="New post">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createPost">
                        <UFormField label="Title" required>
                            <UInput
                                v-model="form.title"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UFormField label="Slug" required>
                            <UInput v-model="form.slug" class="w-full" />
                        </UFormField>
                        <UButton type="submit" label="Create post" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
