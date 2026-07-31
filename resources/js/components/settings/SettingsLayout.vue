<script setup lang="ts">
import { settingsNavigation } from '@/navigation';
</script>

<template>
    <UDashboardPanel id="settings" :ui="{ body: 'lg:py-12' }">
        <template #header>
            <UDashboardNavbar title="Definições">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <UNavigationMenu
                    :items="[settingsNavigation]"
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
