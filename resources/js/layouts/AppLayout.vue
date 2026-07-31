<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { NavigationMenuItem } from '@nuxt/ui';
import { computed, ref } from 'vue';
import TeamsMenu from '@/components/TeamsMenu.vue';
import UserMenu from '@/components/UserMenu.vue';
import { useFlashToasts } from '@/composables/useFlashToasts';
import { mainNavigation, settingsNavigation } from '@/navigation';

const page = usePage();

const open = ref(false);

const currentUrl = computed(() => page.url);

useFlashToasts();

function closeOnSelect(item: NavigationMenuItem): NavigationMenuItem {
    return {
        ...item,
        onSelect: () => {
            open.value = false;
        },
    };
}

const links = computed<NavigationMenuItem[][]>(() => [
    mainNavigation.map(closeOnSelect),
    [
        {
            label: 'Definições',
            icon: 'i-lucide-settings',
            to: '/settings',
            defaultOpen: currentUrl.value.startsWith('/settings'),
            type: 'trigger',
            children: settingsNavigation.map(closeOnSelect),
        },
    ],
]);

const groups = computed(() => [
    {
        id: 'links',
        label: 'Ir para',
        items: links.value.flatMap((group) =>
            group.flatMap((item) =>
                item.children ? [item, ...item.children] : [item],
            ),
        ),
    },
]);
</script>

<template>
    <UDashboardGroup unit="rem" storage="local">
        <UDashboardSidebar
            id="default"
            v-model:open="open"
            collapsible
            resizable
            class="bg-elevated/25"
            :ui="{ footer: 'lg:border-t lg:border-default' }"
        >
            <template #header="{ collapsed }">
                <TeamsMenu :collapsed="collapsed" />
            </template>

            <template #default="{ collapsed }">
                <UDashboardSearchButton
                    :collapsed="collapsed"
                    class="bg-transparent ring-default"
                />

                <UNavigationMenu
                    :collapsed="collapsed"
                    :items="links[0]"
                    orientation="vertical"
                    tooltip
                    popover
                />

                <UNavigationMenu
                    :collapsed="collapsed"
                    :items="links[1]"
                    orientation="vertical"
                    tooltip
                    class="mt-auto"
                />
            </template>

            <template #footer="{ collapsed }">
                <UserMenu :collapsed="collapsed" />
            </template>
        </UDashboardSidebar>

        <UDashboardSearch :groups="groups" />

        <slot />
    </UDashboardGroup>
</template>
