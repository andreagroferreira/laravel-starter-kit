<script setup lang="ts">
import { BlockRenderer, registry, toCssVars, type BlockType } from '@blocks';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';
import { useConfirm } from '@/composables/useConfirm';
import { statusColor, statusLabel } from '@/composables/useStatusColor';
import BlockPalette from '@/editor/BlockPalette.vue';
import BlockShell from '@/editor/BlockShell.vue';
import InspectorPanel from '@/editor/InspectorPanel.vue';
import { useAutosave } from '@/editor/useAutosave';
import { useEditorStore, type EditorBlock } from '@/editor/useEditorStore';
import type { Page, Site } from '@/types/models';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug'> & {
        settings?: Record<string, unknown> | null;
    };
    page: Page;
    blocks: {
        id: string;
        type: string;
        content: Record<string, unknown> | null;
    }[];
    forms: { id: string; name: string }[];
}>();

const confirm = useConfirm();
const toast = useToast();

const store = useEditorStore(props.blocks);

const meta = useForm({
    title: props.page.title,
    slug: props.page.slug,
    seo: {
        title: props.page.seo?.title ?? '',
        description: props.page.seo?.description ?? '',
    },
});

const url = `/sites/${props.site.id}/pages/${props.page.id}`;

const autosave = useAutosave({
    url,
    dirty: store.dirty,
    payload: () => ({
        title: meta.title,
        slug: meta.slug,
        seo: meta.seo,
        blocks: store.toPayload(),
    }),
    onSaved: () => store.markClean(),
});

const DEVICES = {
    desktop: { label: 'Desktop', icon: 'i-lucide-monitor', width: '100%' },
    tablet: { label: 'Tablet', icon: 'i-lucide-tablet', width: '768px' },
    mobile: { label: 'Telemóvel', icon: 'i-lucide-smartphone', width: '375px' },
} as const;

const device = ref<keyof typeof DEVICES>('desktop');

const siteVars = computed(() =>
    toCssVars((props.site.settings ?? {}).design ?? {}),
);

const paletteOpen = ref(false);
const insertIndex = ref<number | null>(null);

function openPalette(index: number | null = null) {
    insertIndex.value = index;
    paletteOpen.value = true;
}

function onPick(type: BlockType) {
    store.addBlock(type, insertIndex.value ?? undefined);
    insertIndex.value = null;
}

async function removeBlock(block: EditorBlock) {
    const confirmed = await confirm({
        title: `Remover o bloco «${registry[block.type].label}»?`,
        description: 'Podes desfazer com Cmd+Z enquanto não guardares.',
        confirmLabel: 'Remover',
    });

    if (confirmed) {
        store.removeBlock(block.uid);
    }
}

const saveLabel = computed(() => {
    if (autosave.state.value === 'saving') {
        return 'A guardar…';
    }
    if (autosave.state.value === 'error') {
        return 'Erro ao guardar';
    }
    if (store.dirty.value) {
        return 'Alterações por guardar';
    }
    if (autosave.lastSavedAt.value) {
        return 'Guardado';
    }

    return 'Sem alterações';
});

function saveNow() {
    autosave.save();
    toast.add({ title: 'Página guardada', color: 'success' });
}

function togglePublish() {
    router.post(`${url}/publish`, undefined, { preserveScroll: true });
}

function touch() {
    store.dirty.value = true;
}

defineShortcuts({
    meta_z: () => store.undo(),
    meta_shift_z: () => store.redo(),
    meta_s: () => autosave.save(),
});
</script>

<template>
    <Head :title="`Editor — ${page.title}`" />

    <UDashboardPanel id="page-editor">
        <template #header>
            <UDashboardNavbar :title="page.title">
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
                    <span class="hidden text-sm text-muted sm:block">{{
                        saveLabel
                    }}</span>
                    <UButton
                        icon="i-lucide-undo-2"
                        variant="ghost"
                        size="sm"
                        :disabled="!store.canUndo.value"
                        aria-label="Desfazer"
                        @click="store.undo()"
                    />
                    <UButton
                        icon="i-lucide-redo-2"
                        variant="ghost"
                        size="sm"
                        :disabled="!store.canRedo.value"
                        aria-label="Refazer"
                        @click="store.redo()"
                    />
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
                        variant="subtle"
                        size="sm"
                        @click="togglePublish"
                    />
                    <UButton
                        label="Guardar"
                        icon="i-lucide-save"
                        size="sm"
                        :loading="autosave.state.value === 'saving'"
                        @click="saveNow"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex w-full flex-wrap items-center gap-2 px-2">
                    <UButton
                        label="Adicionar bloco"
                        icon="i-lucide-plus"
                        size="xs"
                        variant="subtle"
                        @click="openPalette()"
                    />
                    <div class="ml-auto flex items-center gap-1">
                        <UButton
                            v-for="(config, key) in DEVICES"
                            :key="key"
                            :icon="config.icon"
                            size="xs"
                            :variant="device === key ? 'solid' : 'ghost'"
                            :aria-label="config.label"
                            :aria-pressed="device === key"
                            @click="device = key"
                        />
                    </div>
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div class="flex flex-col gap-4 p-4 lg:flex-row lg:p-6">
                <div class="min-w-0 flex-1">
                    <div class="mb-4 space-y-3">
                        <UFormField
                            label="Título da página"
                            :error="meta.errors.title"
                        >
                            <UInput
                                v-model="meta.title"
                                class="w-full"
                                @update:model-value="touch"
                            />
                        </UFormField>
                        <UFormField label="Slug" :error="meta.errors.slug">
                            <UInput
                                v-model="meta.slug"
                                class="w-full"
                                @update:model-value="touch"
                            />
                        </UFormField>
                    </div>

                    <div
                        class="mx-auto overflow-hidden rounded-lg border border-default transition-[max-width]"
                        :style="{ maxWidth: DEVICES[device].width }"
                    >
                        <div class="site-preview" :style="siteVars">
                            <UEmpty
                                v-if="store.blocks.value.length === 0"
                                icon="i-lucide-layout-template"
                                title="Página vazia"
                                description="Adiciona o primeiro bloco para começar."
                                :actions="[
                                    {
                                        label: 'Adicionar bloco',
                                        icon: 'i-lucide-plus',
                                        onClick: () => openPalette(),
                                    },
                                ]"
                                class="py-20"
                            />

                            <draggable
                                v-else
                                :model-value="store.blocks.value"
                                item-key="uid"
                                handle=".block-drag-handle"
                                class="space-y-1"
                                @update:model-value="store.reorder($event)"
                            >
                                <template #item="{ element, index }">
                                    <BlockShell
                                        :label="registry[element.type].label"
                                        :selected="
                                            store.selectedUid.value ===
                                            element.uid
                                        "
                                        :is-first="index === 0"
                                        :is-last="
                                            index ===
                                            store.blocks.value.length - 1
                                        "
                                        @select="store.select(element.uid)"
                                        @move-up="
                                            store.moveBlock(element.uid, -1)
                                        "
                                        @move-down="
                                            store.moveBlock(element.uid, 1)
                                        "
                                        @duplicate="
                                            store.duplicateBlock(element.uid)
                                        "
                                        @remove="removeBlock(element)"
                                        @insert-after="openPalette(index + 1)"
                                    >
                                        <BlockRenderer
                                            :type="element.type"
                                            :content="element.content"
                                            editable
                                            @edit="
                                                (
                                                    field: string,
                                                    value: string,
                                                ) =>
                                                    store.updateField(
                                                        element.uid,
                                                        field,
                                                        value,
                                                    )
                                            "
                                        />
                                    </BlockShell>
                                </template>
                            </draggable>
                        </div>
                    </div>

                    <UPageCard title="SEO" variant="subtle" class="mt-6">
                        <div class="space-y-3">
                            <UFormField label="Meta title">
                                <UInput
                                    v-model="meta.seo.title"
                                    maxlength="70"
                                    class="w-full"
                                    @update:model-value="touch"
                                />
                            </UFormField>
                            <UFormField label="Meta description">
                                <UTextarea
                                    v-model="meta.seo.description"
                                    :rows="3"
                                    maxlength="160"
                                    class="w-full"
                                    @update:model-value="touch"
                                />
                            </UFormField>
                        </div>
                    </UPageCard>
                </div>

                <InspectorPanel
                    :block="store.selected.value"
                    :forms="forms"
                    @update="
                        (key: string, value: unknown) =>
                            store.selectedUid.value &&
                            store.updateField(
                                store.selectedUid.value,
                                key,
                                value,
                            )
                    "
                />
            </div>

            <BlockPalette v-model:open="paletteOpen" @pick="onPick" />
        </template>
    </UDashboardPanel>
</template>
