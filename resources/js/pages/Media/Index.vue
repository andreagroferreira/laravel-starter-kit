<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface Asset {
    id: string;
    url: string;
    filename: string;
    alt: string | null;
    size: number;
    mime_type: string | null;
}

defineProps<{
    assets: { data: Asset[] };
}>();

const toast = useToast();

const uploading = ref(false);
const form = useForm<{ file: File | null; alt: string }>({
    file: null,
    alt: '',
});

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;

    if (input.files?.length) {
        form.file = input.files[0];
        upload();
    }
}

function upload() {
    if (!form.file) return;

    uploading.value = true;
    form.post('/media', {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;
            form.reset();
        },
        onSuccess: () =>
            toast.add({ title: 'File uploaded', color: 'success' }),
    });
}

function copyUrl(url: string) {
    navigator.clipboard.writeText(url);
    toast.add({ title: 'URL copied', color: 'success' });
}

function destroy(id: string) {
    router.delete(`/media/${id}`, {
        onSuccess: () =>
            toast.add({ title: 'Asset deleted', color: 'success' }),
    });
}

function humanSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<template>
    <Head title="Media" />

    <UDashboardPanel id="media">
        <template #header>
            <UDashboardNavbar title="Media library">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="Upload"
                        icon="i-lucide-upload"
                        :loading="uploading"
                        @click="($refs.fileInput as HTMLInputElement).click()"
                    />
                    <input
                        ref="fileInput"
                        type="file"
                        class="hidden"
                        @change="onFileChange"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="p-4 lg:p-6">
                <UEmpty
                    v-if="assets.data.length === 0"
                    icon="i-lucide-image"
                    title="No media yet"
                    description="Upload images to use them in your pages and posts."
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
                            class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/70 px-2 py-1.5 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <span class="truncate text-xs text-white">{{
                                humanSize(asset.size)
                            }}</span>
                            <div class="flex items-center">
                                <UButton
                                    icon="i-lucide-copy"
                                    size="xs"
                                    color="neutral"
                                    variant="ghost"
                                    @click="copyUrl(asset.url)"
                                />
                                <UButton
                                    icon="i-lucide-trash-2"
                                    size="xs"
                                    color="error"
                                    variant="ghost"
                                    @click="destroy(asset.id)"
                                />
                            </div>
                        </div>

                        <p class="truncate px-2 py-1 text-xs text-muted">
                            {{ asset.filename }}
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </UDashboardPanel>
</template>
