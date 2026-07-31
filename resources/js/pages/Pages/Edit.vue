<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import draggable from 'vuedraggable';
import BlockEditor from '../../components/BlockEditor.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface Block {
    id?: string;
    type: string;
    content: Record<string, unknown>;
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    page: {
        id: string;
        title: string;
        slug: string;
        status: string;
        seo: Record<string, string> | null;
    };
    blocks: {
        id: string;
        type: string;
        content: Record<string, unknown> | null;
        sort_order: number;
    }[];
    blockTypes: string[];
}>();

const toast = useToast();

const form = reactive({
    title: props.page.title,
    slug: props.page.slug,
    seo: {
        title: props.page.seo?.title ?? '',
        description: props.page.seo?.description ?? '',
    },
    blocks: props.blocks.map((block) => ({
        type: block.type,
        content: block.content ?? {},
    })) as Block[],
});

const saving = ref(false);

const blockLabels: Record<string, string> = {
    hero: 'Hero',
    rich_text: 'Rich text',
    image: 'Image',
    cta: 'Call to action',
    features: 'Features',
    testimonials: 'Testimonials',
    pricing: 'Pricing',
    faq: 'FAQ',
    form: 'Form',
};

const blockIcons: Record<string, string> = {
    hero: 'i-lucide-layout-template',
    rich_text: 'i-lucide-text',
    image: 'i-lucide-image',
    cta: 'i-lucide-mouse-pointer-click',
    features: 'i-lucide-list-checks',
    testimonials: 'i-lucide-quote',
    pricing: 'i-lucide-tag',
    faq: 'i-lucide-circle-help',
    form: 'i-lucide-clipboard-list',
};

function addBlock(type: string) {
    form.blocks.push({
        type,
        content: type === 'rich_text' ? { html: '' } : {},
    });
}

function removeBlock(index: number) {
    form.blocks.splice(index, 1);
}

function save() {
    saving.value = true;
    router.put(
        `/sites/${props.site.id}/pages/${props.page.id}`,
        { ...form },
        {
            onFinish: () => {
                saving.value = false;
            },
            onSuccess: () =>
                toast.add({ title: 'Page saved', color: 'success' }),
        },
    );
}

function togglePublish() {
    router.post(`/sites/${props.site.id}/pages/${props.page.id}/publish`);
}
</script>

<template>
    <Head :title="`Edit — ${page.title}`" />

    <UDashboardPanel id="page-edit">
        <template #header>
            <UDashboardNavbar>
                <template #leading>
                    <UDashboardSidebarCollapse />
                    <UButton
                        icon="i-lucide-arrow-left"
                        variant="ghost"
                        :to="`/sites/${site.id}`"
                    />
                    <span class="text-sm text-muted"
                        >{{ site.name }} / Pages</span
                    >
                </template>
                <template #right>
                    <UBadge
                        :label="page.status"
                        :color="
                            page.status === 'published' ? 'success' : 'neutral'
                        "
                        variant="subtle"
                    />
                    <UButton
                        :label="
                            page.status === 'published'
                                ? 'Unpublish'
                                : 'Publish'
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
                        placeholder="Page title"
                        class="w-full"
                        :ui="{ base: 'text-2xl font-semibold' }"
                        variant="none"
                    />

                    <UEmpty
                        v-if="form.blocks.length === 0"
                        icon="i-lucide-layout-template"
                        title="Empty page"
                        description="Add your first block to start building."
                    />

                    <draggable
                        v-model="form.blocks"
                        item-key="type"
                        handle=".drag-handle"
                        ghost-class="opacity-40"
                        class="space-y-3"
                    >
                        <template #item="{ element, index }">
                            <UPageCard variant="outline" class="relative">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <UIcon
                                            name="i-lucide-grip-vertical"
                                            class="drag-handle size-4 cursor-grab text-muted"
                                        />
                                        <UIcon
                                            :name="
                                                blockIcons[element.type] ??
                                                'i-lucide-box'
                                            "
                                            class="size-4 text-primary"
                                        />
                                        <span class="text-sm font-medium">{{
                                            blockLabels[element.type] ??
                                            element.type
                                        }}</span>
                                    </div>
                                    <UButton
                                        icon="i-lucide-trash-2"
                                        size="xs"
                                        color="error"
                                        variant="ghost"
                                        @click="removeBlock(index)"
                                    />
                                </div>

                                <div class="mt-3">
                                    <BlockEditor
                                        v-model="element.content"
                                        :type="element.type"
                                    />
                                </div>
                            </UPageCard>
                        </template>
                    </draggable>

                    <UDropdownMenu
                        :items="
                            blockTypes.map((type) => ({
                                label: blockLabels[type] ?? type,
                                icon: blockIcons[type] ?? 'i-lucide-box',
                                onSelect: () => addBlock(type),
                            }))
                        "
                    >
                        <UButton
                            label="Add block"
                            icon="i-lucide-plus"
                            variant="soft"
                            block
                        />
                    </UDropdownMenu>
                </div>

                <div class="space-y-4">
                    <UPageCard title="Page settings" variant="subtle">
                        <div class="space-y-3">
                            <UFormField label="Slug">
                                <UInput v-model="form.slug" class="w-full" />
                            </UFormField>
                            <UFormField label="SEO title">
                                <UInput
                                    v-model="form.seo.title"
                                    class="w-full"
                                    maxlength="70"
                                />
                            </UFormField>
                            <UFormField label="SEO description">
                                <UTextarea
                                    v-model="form.seo.description"
                                    :rows="3"
                                    class="w-full"
                                    maxlength="160"
                                />
                            </UFormField>
                        </div>
                    </UPageCard>
                </div>
            </div>
        </template>
    </UDashboardPanel>
</template>
