<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import type { Menu, MenuItem, Site } from '@/types/models';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug'>;
    menus: Menu[];
}>();

const toast = useToast();
const confirm = useConfirm();

const open = ref(false);
const newMenu = useForm({ name: '' });

const editing = ref<Record<string, MenuItem[]>>(
    Object.fromEntries(
        props.menus.map((menu) => [menu.id, [...(menu.items ?? [])]]),
    ),
);

function createMenu() {
    newMenu.post(`/sites/${props.site.id}/menus`, {
        onSuccess: () => {
            open.value = false;
            newMenu.reset();
            router.reload({ only: ['menus'] });
        },
    });
}

function addItem(menuId: string) {
    editing.value[menuId]?.push({ label: '', url: '/' });
}

function removeItem(menuId: string, index: number) {
    editing.value[menuId]?.splice(index, 1);
}

const savingId = ref<string | null>(null);

function saveMenu(menu: Menu) {
    savingId.value = menu.id;
    router.put(
        `/sites/${props.site.id}/menus/${menu.id}`,
        { items: editing.value[menu.id] ?? [] },
        {
            preserveScroll: true,
            onFinish: () => {
                savingId.value = null;
            },
            onSuccess: () =>
                toast.add({
                    title: `Menu «${menu.name}» guardado`,
                    color: 'success',
                }),
            onError: () =>
                toast.add({
                    title: 'Itens inválidos — cada item precisa de label e URL.',
                    color: 'error',
                }),
        },
    );
}

async function deleteMenu(menu: Menu) {
    const confirmed = await confirm({
        title: `Apagar o menu «${menu.name}»?`,
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}/menus/${menu.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="`Menus — ${site.name}`" />

    <UDashboardPanel id="menus">
        <template #header>
            <UDashboardNavbar :title="`Menus — ${site.name}`">
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
                        label="Novo menu"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="mx-auto w-full max-w-3xl space-y-4 p-4 lg:p-6">
                <UEmpty
                    v-if="menus.length === 0"
                    icon="i-lucide-list-tree"
                    title="Ainda não há menus"
                    description="Cria um menu (ex.: main, footer) para a navegação do site."
                    :actions="[
                        {
                            label: 'Novo menu',
                            icon: 'i-lucide-plus',
                            onClick: () => (open = true),
                        },
                    ]"
                />

                <UPageCard
                    v-for="menu in menus"
                    :key="menu.id"
                    :title="menu.name"
                    variant="subtle"
                >
                    <div class="space-y-2">
                        <div
                            v-for="(item, index) in editing[menu.id]"
                            :key="index"
                            class="flex flex-wrap items-center gap-2"
                        >
                            <UInput
                                v-model="item.label"
                                placeholder="Label"
                                aria-label="Label do item"
                                class="min-w-32 flex-1"
                            />
                            <UInput
                                v-model="item.url"
                                placeholder="/url"
                                aria-label="URL do item"
                                class="min-w-32 flex-1"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="xs"
                                color="error"
                                variant="ghost"
                                aria-label="Remover item"
                                @click="removeItem(menu.id, index)"
                            />
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <UButton
                                label="Adicionar item"
                                icon="i-lucide-plus"
                                variant="soft"
                                size="sm"
                                @click="addItem(menu.id)"
                            />
                            <UButton
                                label="Guardar"
                                icon="i-lucide-save"
                                size="sm"
                                :loading="savingId === menu.id"
                                @click="saveMenu(menu)"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="sm"
                                color="error"
                                variant="ghost"
                                aria-label="Apagar menu"
                                @click="deleteMenu(menu)"
                            />
                        </div>
                    </div>
                </UPageCard>
            </div>

            <UModal v-model:open="open" title="Novo menu">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createMenu">
                        <UFormField
                            label="Nome (ex.: main, footer)"
                            required
                            :error="newMenu.errors.name"
                        >
                            <UInput
                                v-model="newMenu.name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UButton
                            type="submit"
                            label="Criar menu"
                            block
                            :loading="newMenu.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
