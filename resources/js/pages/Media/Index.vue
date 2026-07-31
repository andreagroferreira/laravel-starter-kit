<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, useTemplateRef } from 'vue';
import PaginationBar from '@/components/shared/PaginationBar.vue';
import SearchInput from '@/components/shared/SearchInput.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useTableQuery } from '@/composables/useTableQuery';
import type { MediaAsset } from '@/types/models';
import type { Paginated } from '@/types/pagination';
import { humanSize } from '@/utils/format';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface Asset extends Pick<
    MediaAsset,
    'id' | 'url' | 'alt' | 'size' | 'mime_type'
> {
    filename: string;
}

defineProps<{
    assets: Paginated<Asset>;
    filters: { search: string };
}>();

const toast = useToast();
const confirm = useConfirm();
const { state, goToPage } = useTableQuery({ only: ['assets', 'filters'] });

const fileInput = useTemplateRef<HTMLInputElement>('fileInput');
const dragging = ref(false);

const form = useForm<{ file: File | null; alt: string }>({
    file: null,
    alt: '',
});

const ACCEPT = '.jpg,.jpeg,.png,.webp,.gif,.svg,.pdf';

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;

    if (input.files?.length) {
        upload(input.files[0]);
        input.value = '';
    }
}

function onDrop(event: DragEvent) {
    dragging.value = false;
    const file = event.dataTransfer?.files?.[0];

    if (file) {
        upload(file);
    }
}

function upload(file: File) {
    form.file = file;
    form.post('/media', {
        forceFormData: true,
        onFinish: () => form.reset(),
        onSuccess: () =>
            toast.add({ title: 'Ficheiro carregado', color: 'success' }),
        onError: () =>
            toast.add({
                title: form.errors.file ?? 'Ficheiro não suportado',
                color: 'error',
            }),
    });
}

function copyUrl(url: string) {
    navigator.clipboard.writeText(url);
    toast.add({ title: 'URL copiado', color: 'success' });
}

async function destroy(asset: Asset) {
    const confirmed = await confirm({
        title: `Apagar «${asset.filename}»?`,
        description: 'O ficheiro é removido do armazenamento definitivamente.',
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/media/${asset.id}`, {
            preserveScroll: true,
            onSuccess: () =>
                toast.add({ title: 'Ficheiro apagado', color: 'success' }),
        });
    }
}

const editing = ref<Asset | null>(null);
const altForm = useForm({ alt: '' });

function openAltEditor(asset: Asset) {
    editing.value = asset;
    altForm.alt = asset.alt ?? '';
}

function saveAlt() {
    if (!editing.value) {
        return;
    }

    altForm.put(`/media/${editing.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
            toast.add({
                title: 'Texto alternativo guardado',
                color: 'success',
            });
        },
    });
}
</script>

<template>
    <Head title="Media" />

    <UDashboardPanel id="media">
        <template #header>
            <UDashboardNavbar title="Biblioteca de media">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="Carregar"
                        icon="i-lucide-upload"
                        :loading="form.processing"
                        @click="fileInput?.click()"
                    />
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        :accept="ACCEPT"
                        aria-label="Escolher ficheiro"
                        @change="onFileChange"
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex w-full items-center gap-2 px-2">
                    <SearchInput
                        v-model="state.search"
                        placeholder="Pesquisar ficheiros…"
                    />
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div
                class="p-4 lg:p-6"
                :class="
                    dragging
                        ? 'rounded-lg outline-2 outline-primary outline-dashed'
                        : ''
                "
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop"
            >
                <UEmpty
                    v-if="assets.data.length === 0 && state.search === ''"
                    icon="i-lucide-image"
                    title="Ainda não há media"
                    description="Carrega imagens (ou arrasta-as para aqui) para as usares nas páginas e artigos."
                    :actions="[
                        {
                            label: 'Carregar ficheiro',
                            icon: 'i-lucide-upload',
                            onClick: () => fileInput?.click(),
                        },
                    ]"
                />

                <UEmpty
                    v-else-if="assets.data.length === 0"
                    icon="i-lucide-search-x"
                    title="Sem resultados"
                    description="Nenhum ficheiro corresponde à pesquisa."
                />

                <div
                    v-else
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6"
                >
                    <div
                        v-for="asset in assets.data"
                        :key="asset.id"
                        class="group relative overflow-hidden rounded-lg border border-default bg-elevated/30"
                    >
                        <div class="aspect-square overflow-hidden">
                            <img
                                v-if="asset.mime_type?.startsWith('image/')"
                                :src="asset.url"
                                :alt="asset.alt ?? asset.filename"
                                class="size-full object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center"
                            >
                                <UIcon
                                    name="i-lucide-file"
                                    class="size-10 text-muted"
                                />
                            </div>
                        </div>

                        <div
                            class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/70 px-2 py-1.5 opacity-0 transition-opacity group-focus-within:opacity-100 group-hover:opacity-100"
                        >
                            <span class="truncate text-xs text-white">{{
                                humanSize(asset.size)
                            }}</span>
                            <div class="flex items-center">
                                <UButton
                                    icon="i-lucide-captions"
                                    size="xs"
                                    color="neutral"
                                    variant="ghost"
                                    aria-label="Editar texto alternativo"
                                    @click="openAltEditor(asset)"
                                />
                                <UButton
                                    icon="i-lucide-copy"
                                    size="xs"
                                    color="neutral"
                                    variant="ghost"
                                    aria-label="Copiar URL"
                                    @click="copyUrl(asset.url)"
                                />
                                <UButton
                                    icon="i-lucide-trash-2"
                                    size="xs"
                                    color="error"
                                    variant="ghost"
                                    aria-label="Apagar ficheiro"
                                    @click="destroy(asset)"
                                />
                            </div>
                        </div>

                        <p class="truncate px-2 py-1 text-xs text-muted">
                            {{ asset.filename }}
                        </p>
                    </div>
                </div>

                <PaginationBar :paginator="assets" @update:page="goToPage" />
            </div>

            <UModal
                :open="editing !== null"
                title="Texto alternativo"
                description="Descreve a imagem para leitores de ecrã e SEO."
                @update:open="editing = null"
            >
                <template #body>
                    <form class="space-y-4" @submit.prevent="saveAlt">
                        <UFormField label="Alt" :error="altForm.errors.alt">
                            <UInput
                                v-model="altForm.alt"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UButton
                            type="submit"
                            label="Guardar"
                            block
                            :loading="altForm.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
