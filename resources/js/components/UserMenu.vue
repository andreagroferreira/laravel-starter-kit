<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import type { DropdownMenuItem } from '@nuxt/ui';
import { computed } from 'vue';

defineProps<{
    collapsed?: boolean;
}>();

const page = usePage();

const user = computed(() => {
    const authUser = page.props.auth?.user as
        | { name?: string; email?: string }
        | undefined;

    return {
        name: authUser?.name ?? 'User',
        email: authUser?.email ?? '',
    };
});

const initials = computed(() =>
    user.value.name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase(),
);

function logout() {
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
            label: 'Profile',
            icon: 'i-lucide-user',
            to: '/settings',
        },
        {
            label: 'Billing',
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
            label: 'Log out',
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
        />
    </UDropdownMenu>
</template>
