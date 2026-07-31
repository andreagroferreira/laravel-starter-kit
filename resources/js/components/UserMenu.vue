<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import type { DropdownMenuItem } from '@nuxt/ui';
import { useColorMode } from '@vueuse/core';
import { computed } from 'vue';
import { initials as makeInitials } from '@/utils/format';

defineProps<{
    collapsed?: boolean;
}>();

const page = usePage();
const mode = useColorMode();

const user = computed(() => ({
    name: page.props.auth.user?.name ?? 'Utilizador',
    email: page.props.auth.user?.email ?? '',
}));

const initials = computed(() => makeInitials(user.value.name));

const isDark = computed(() => mode.value === 'dark');

function toggleTheme(): void {
    mode.value = isDark.value ? 'light' : 'dark';
}

function logout(): void {
    router.post('/logout');
}

const items = computed<DropdownMenuItem[][]>(() => [
    [
        {
            type: 'label',
            label: user.value.name,
            avatar: { text: initials.value },
        },
    ],
    [
        {
            label: 'Perfil',
            icon: 'i-lucide-user',
            to: '/settings',
        },
        {
            label: 'Faturação',
            icon: 'i-lucide-credit-card',
            to: '/settings/billing',
        },
        {
            label: 'API & MCP',
            icon: 'i-lucide-key-round',
            to: '/settings/api',
        },
    ],
    [
        {
            label: isDark.value ? 'Tema claro' : 'Tema escuro',
            icon: isDark.value ? 'i-lucide-sun' : 'i-lucide-moon',
            onSelect: (event: Event) => {
                event.preventDefault();
                toggleTheme();
            },
        },
    ],
    [
        {
            label: 'Terminar sessão',
            icon: 'i-lucide-log-out',
            onSelect: logout,
        },
    ],
]);
</script>

<template>
    <UDropdownMenu
        :items="items"
        :content="{ align: 'center', collisionPadding: 12 }"
        :ui="{
            content: collapsed
                ? 'w-48'
                : 'w-(--reka-dropdown-menu-trigger-width)',
        }"
    >
        <UButton
            :label="collapsed ? undefined : user.name"
            :avatar="{ text: initials }"
            color="neutral"
            variant="ghost"
            block
            :square="collapsed"
            class="data-[state=open]:bg-elevated"
            :ui="{ leadingAvatarSize: 'xs' }"
            aria-label="Menu do utilizador"
        />
    </UDropdownMenu>
</template>
