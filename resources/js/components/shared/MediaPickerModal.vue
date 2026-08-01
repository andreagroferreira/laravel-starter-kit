<script setup lang="ts">
import { watchDebounced } from '@vueuse/core';
import { ref, watch } from 'vue';

interface PickerAsset {
    id: string;
    url: string;
    filename: string;
    alt: string | null;
    mime_type: string | null;
}

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{ pick: [asset: PickerAsset] }>();

const assets = ref<PickerAsset[]>([]);
const search = ref('');
const loading = ref(false);

async function load() {
    loading.value = true;

    try {
        const response = await fetch(
            `/media/picker?search=${encodeURIComponent(search.value)}`,
            { headers: { Accept: 'application/json' } },
        );

        if (response.ok) {
            assets.value = (
                (await response.json()) as { data: PickerAsset[] }
            ).data;
        }
    } finally {
        loading.value = false;
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        load();
    }
});

watchDebounced(search, () => open.value && load(), { debounce: 300 });

function pick(asset: PickerAsset) {
    emit('pick', asset);
    open.value = false;
}
</script>

<template>
    <UModal
        v-model:open="open"
        title="Escolher media"
        description="Seleciona uma imagem da biblioteca."
        :ui="{ content: 'max-w-3xl' }"
    >
        <template #body>
            <div class="space-y-4">
                <UInput
                    v-model="search"
                    icon="i-lucide-search"
                    placeholder="Pesquisar ficheiros…"
                    class="w-full"
                    aria-label="Pesquisar media"
                />

                <div
                    v-if="loading"
                    class="grid grid-cols-3 gap-3 sm:grid-cols-4"
                    role="status"
                    aria-label="A carregar"
                >
                    <USkeleton
                        v-for="i in 8"
                        :key="i"
                        class="aspect-square rounded-lg"
                    />
                </div>

                <UEmpty
                    v-else-if="assets.length === 0"
                    icon="i-lucide-image"
                    title="Sem ficheiros"
                    description="Carrega imagens na biblioteca de media primeiro."
                />

                <div v-else class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    <button
                        v-for="asset in assets"
                        :key="asset.id"
                        type="button"
                        class="overflow-hidden rounded-lg border border-default hover:border-primary"
                        @click="pick(asset)"
                    >
                        <img
                            v-if="asset.mime_type?.startsWith('image/')"
                            :src="asset.url"
                            :alt="asset.alt ?? asset.filename"
                            class="aspect-square w-full object-cover"
                            loading="lazy"
                        />
                        <span
                            v-else
                            class="flex aspect-square items-center justify-center"
                        >
                            <UIcon name="i-lucide-file" class="size-8" />
                        </span>
                        <span
                            class="block truncate px-2 py-1 text-xs text-muted"
                        >
                            {{ asset.filename }}
                        </span>
                    </button>
                </div>
            </div>
        </template>
    </UModal>
</template>
