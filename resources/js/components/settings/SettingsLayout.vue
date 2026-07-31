<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui';

const links = [
    [
        {
            label: 'General',
            icon: 'i-lucide-user',
            to: '/settings',
            exact: true,
        },
        {
            label: 'Members',
            icon: 'i-lucide-users',
            to: '/settings/members',
        },
        {
            label: 'Brand voice',
            icon: 'i-lucide-sparkles',
            to: '/settings/brand',
        },
        {
            label: 'API & MCP',
            icon: 'i-lucide-key-round',
            to: '/settings/api',
        },
        {
            label: 'Notifications',
            icon: 'i-lucide-bell',
            to: '/settings/notifications',
        },
        {
            label: 'Security',
            icon: 'i-lucide-shield',
            to: '/settings/security',
        },
        {
            label: 'Billing',
            icon: 'i-lucide-credit-card',
            to: '/settings/billing',
        },
        {
            label: 'Audit log',
            icon: 'i-lucide-scroll-text',
            to: '/settings/audit',
        },
    ],
] satisfies NavigationMenuItem[][];
</script>

<template>
    <UDashboardPanel id="settings" :ui="{ body: 'lg:py-12' }">
        <template #header>
            <UDashboardNavbar title="Settings">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <UNavigationMenu
                    :items="links"
                    highlight
                    class="-mx-1 flex-1"
                />
            </UDashboardToolbar>
        </template>

        <template #body>
            <div
                class="mx-auto flex w-full flex-col gap-4 sm:gap-6 lg:max-w-2xl lg:gap-12"
            >
                <slot />
            </div>
        </template>
    </UDashboardPanel>
</template>
