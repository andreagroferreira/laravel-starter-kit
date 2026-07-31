<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { NavigationMenuItem } from '@nuxt/ui';
import { computed, ref } from 'vue';
import TeamsMenu from '../components/TeamsMenu.vue';
import UserMenu from '../components/UserMenu.vue';

const page = usePage();

const open = ref(false);

const currentUrl = computed(() => page.url);

const links = computed<NavigationMenuItem[][]>(() => [
    [
        {
            label: 'Dashboard',
            icon: 'i-lucide-layout-dashboard',
            to: '/',
            exact: true,
            onSelect: () => {
                open.value = false;
            },
        },
        {
            label: 'Sites',
            icon: 'i-lucide-globe',
            to: '/sites',
            onSelect: () => {
                open.value = false;
            },
        },
        {
            label: 'Media',
            icon: 'i-lucide-image',
            to: '/media',
            onSelect: () => {
                open.value = false;
            },
        },
        {
            label: 'AI Copilot',
            icon: 'i-lucide-sparkles',
            to: '/ai',
            onSelect: () => {
                open.value = false;
            },
        },
    ],
    [
        {
            label: 'Settings',
            icon: 'i-lucide-settings',
            to: '/settings',
            defaultOpen: currentUrl.value.startsWith('/settings'),
            type: 'trigger',
            children: [
                { label: 'General', to: '/settings', exact: true },
                { label: 'Members', to: '/settings/members' },
                { label: 'Brand voice', to: '/settings/brand' },
                { label: 'API & MCP', to: '/settings/api' },
                { label: 'Security', to: '/settings/security' },
                { label: 'Billing', to: '/settings/billing' },
                { label: 'Audit log', to: '/settings/audit' },
            ],
        },
    ],
]);

const groups = computed(() => [
    {
        id: 'links',
        label: 'Go to',
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
