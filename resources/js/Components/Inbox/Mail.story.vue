<script setup lang="ts">
import type { Mail as MailType } from '@/types';
import Mail from './Mail.vue';

const mail: MailType = {
    id: 1,
    unread: true,
    from: {
        id: 1,
        name: 'Alice Johnson',
        email: 'alice@example.com',
        status: 'subscribed',
        location: 'Lisbon, Portugal',
        avatar: {
            src: 'https://github.com/benjamincanac.png',
            alt: 'Alice',
        },
    },
    subject: 'Welcome to the platform',
    body: 'Hi there — just wanted to drop a quick note to say welcome aboard. Let us know if you need anything to get started.',
    date: new Date('2026-01-15T09:30:00Z').toISOString(),
};
</script>

<template>
    <Story
        title="Inbox/Mail"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <Mail :mail="mail" />
        </Variant>
    </Story>
</template>
