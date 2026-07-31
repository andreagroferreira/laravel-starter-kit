<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface MenuItem {
    label: string;
    url: string;
}

interface Menu {
    id: string;
    name: string;
    items: MenuItem[] | null;
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    menus: Menu[];
}>();

const toast = useToast();

const open = ref(false);
const newMenu = reactive({ name: '' });

const editing = ref<Record<string, MenuItem[]>>(
    Object.fromEntries(
        props.menus.map((menu) => [menu.id, [...(menu.items ?? [])]]),
    ),
);

function createMenu() {
    router.post(
        `/sites/${props.site.id}/menus`,
        { ...newMenu },
        {
            onSuccess: () => {
                open.value = false;
                newMenu.name = '';
            },
        },
    );
}

function addItem(menuId: string) {
    editing.value[menuId]?.push({ label: '', url: '/' });
}

function removeItem(menuId: string, index: number) {
    editing.value[menuId]?.splice(index, 1);
}

function saveMenu(menu: Menu) {
    router.put(
        `/sites/${props.site.id}/menus/${menu.id}`,
        { items: editing.value[menu.id] ?? [] },
        {
            onSuccess: () =>
                toast.add({
                    title: `Menu "${menu.name}" saved`,
                    color: 'success',
                }),
        },
    );
}

function deleteMenu(menu: Menu) {
    router.delete(`/sites/${props.site.id}/menus/${menu.id}`);
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
                    />
                </template>
                <template #right>
                    <UButton
                        label="New menu"
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
                    title="No menus"
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
                            class="flex items-center gap-2"
                        >
                            <UInput
                                v-model="item.label"
                                placeholder="Label"
                                class="flex-1"
                            />
                            <UInput
                                v-model="item.url"
                                placeholder="/url"
                                class="flex-1"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="xs"
                                color="error"
                                variant="ghost"
                                @click="removeItem(menu.id, index)"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <UButton
                                label="Add item"
                                icon="i-lucide-plus"
                                variant="soft"
                                size="sm"
                                @click="addItem(menu.id)"
                            />
                            <UButton
                                label="Save"
                                icon="i-lucide-save"
                                size="sm"
                                @click="saveMenu(menu)"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="sm"
                                color="error"
                                variant="ghost"
                                @click="deleteMenu(menu)"
                            />
                        </div>
                    </div>
                </UPageCard>
            </div>

            <UModal v-model:open="open" title="New menu">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createMenu">
                        <UFormField label="Name (e.g. main, footer)" required>
                            <UInput
                                v-model="newMenu.name"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UButton type="submit" label="Create menu" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
