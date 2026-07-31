<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import RichTextEditor from '../../components/RichTextEditor.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface Category {
    id: string;
    name: string;
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    post: {
        id: string;
        title: string;
        slug: string;
        status: string;
        excerpt: string | null;
        body: string | null;
        seo: Record<string, string> | null;
        published_at: string | null;
    };
    categories: Category[];
    selectedCategories: string[];
}>();

const toast = useToast();

const form = reactive({
    title: props.post.title,
    slug: props.post.slug,
    excerpt: props.post.excerpt ?? '',
    body: props.post.body ?? '',
    seo: {
        title: props.post.seo?.title ?? '',
        description: props.post.seo?.description ?? '',
    },
    categories: [...props.selectedCategories],
});

const saving = ref(false);
const aiLoading = ref<string | null>(null);

function save() {
    saving.value = true;
    router.put(
        `/sites/${props.site.id}/posts/${props.post.id}`,
        { ...form },
        {
            onFinish: () => {
                saving.value = false;
            },
            onSuccess: () =>
                toast.add({ title: 'Post saved', color: 'success' }),
        },
    );
}

function togglePublish() {
    router.post(`/sites/${props.site.id}/posts/${props.post.id}/publish`);
}

async function generateSeo() {
    aiLoading.value = 'seo';
    try {
        const response = await fetch(`/sites/${props.site.id}/ai/seo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
                ),
            },
            body: JSON.stringify({
                block_type: 'rich_text',
                briefing: `${form.title}\n\n${form.excerpt}\n\n${form.body.slice(0, 3000)}`,
            }),
        });

        if (!response.ok) throw new Error('AI request failed');

        const data = await response.json();
        form.seo.title = data.meta_title ?? form.seo.title;
        form.seo.description = data.meta_description ?? form.seo.description;
        toast.add({ title: 'SEO generated with AI', color: 'success' });
    } catch {
        toast.add({ title: 'AI request failed', color: 'error' });
    } finally {
        aiLoading.value = null;
    }
}
</script>

<template>
    <Head :title="`Edit — ${post.title}`" />

    <UDashboardPanel id="post-edit">
        <template #header>
            <UDashboardNavbar>
                <template #leading>
                    <UDashboardSidebarCollapse />
                    <UButton
                        icon="i-lucide-arrow-left"
                        variant="ghost"
                        :to="`/sites/${site.id}/posts`"
                    />
                    <span class="text-sm text-muted"
                        >{{ site.name }} / Posts</span
                    >
                </template>
                <template #right>
                    <UBadge
                        :label="post.status"
                        :color="
                            post.status === 'published'
                                ? 'success'
                                : post.status === 'scheduled'
                                  ? 'warning'
                                  : 'neutral'
                        "
                        variant="subtle"
                    />
                    <UButton
                        :label="
                            post.status === 'published'
                                ? 'Unpublish'
                                : 'Publish'
                        "
                        :color="
                            post.status === 'published' ? 'neutral' : 'primary'
                        "
                        variant="subtle"
                        @click="togglePublish"
                    />
                    <UButton
                        label="Save"
                        icon="i-lucide-save"
                        :loading="saving"
                        @click="save"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div
                class="mx-auto grid w-full max-w-6xl gap-6 p-4 lg:grid-cols-[1fr_320px] lg:p-6"
            >
                <div class="space-y-4">
                    <UInput
                        v-model="form.title"
                        placeholder="Post title"
                        class="w-full"
                        :ui="{ base: 'text-2xl font-semibold' }"
                        variant="none"
                    />

                    <RichTextEditor
                        v-model="form.body"
                        placeholder="Write the article…"
                    />
                </div>

                <div class="space-y-4">
                    <UPageCard title="Publishing" variant="subtle">
                        <div class="space-y-3">
                            <UFormField label="Slug">
                                <UInput v-model="form.slug" class="w-full" />
                            </UFormField>
                            <UFormField label="Categories">
                                <USelectMenu
                                    v-model="form.categories"
                                    :items="
                                        categories.map((c) => ({
                                            label: c.name,
                                            value: c.id,
                                        }))
                                    "
                                    multiple
                                    class="w-full"
                                />
                            </UFormField>
                            <UFormField label="Excerpt">
                                <UTextarea
                                    v-model="form.excerpt"
                                    :rows="3"
                                    class="w-full"
                                />
                            </UFormField>
                        </div>
                    </UPageCard>

                    <UPageCard title="SEO" variant="subtle">
                        <div class="space-y-3">
                            <UFormField label="Meta title">
                                <UInput
                                    v-model="form.seo.title"
                                    class="w-full"
                                    maxlength="70"
                                />
                            </UFormField>
                            <UFormField label="Meta description">
                                <UTextarea
                                    v-model="form.seo.description"
                                    :rows="3"
                                    class="w-full"
                                    maxlength="160"
                                />
                            </UFormField>
                            <UButton
                                label="Generate with AI"
                                icon="i-lucide-sparkles"
                                variant="soft"
                                block
                                :loading="aiLoading === 'seo'"
                                @click="generateSeo"
                            />
                        </div>
                    </UPageCard>
                </div>
            </div>
        </template>
    </UDashboardPanel>
</template>
