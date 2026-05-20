<script setup lang="ts">
import { ref } from 'vue';
import type { Mail } from '@/types';
import List from './List.vue';

const mails: Mail[] = [
    {
        id: 1,
        unread: true,
        from: {
            id: 1,
            name: 'Alice Johnson',
            email: 'alice@example.com',
            status: 'subscribed',
            location: 'Lisbon, Portugal',
            avatar: { src: 'https://github.com/atinux.png', alt: 'Alice' },
        },
        subject: 'Welcome to the platform',
        body: 'Hi there — just wanted to drop a quick note to say welcome aboard.',
        date: new Date('2026-01-15T09:30:00Z').toISOString(),
    },
    {
        id: 2,
        from: {
            id: 2,
            name: 'Bob Smith',
            email: 'bob@example.com',
            status: 'subscribed',
            location: 'Porto, Portugal',
            avatar: { src: 'https://github.com/benjamincanac.png', alt: 'Bob' },
        },
        subject: 'Your invoice is ready',
        body: 'Your invoice for January is now available in your dashboard.',
        date: new Date('2026-01-14T16:00:00Z').toISOString(),
    },
];

const selected = ref<Mail | null>(null);
</script>

<template>
    <Story
        title="Inbox/List"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <List v-model="selected" :mails="mails" />
        </Variant>
    </Story>
</template>
