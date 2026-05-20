<script setup lang="ts">
import type { Member } from '@/types';
import MembersList from './MembersList.vue';

const members: Member[] = [
    {
        name: 'Benjamin Canac',
        username: 'benjamincanac',
        role: 'owner',
        avatar: {
            src: 'https://github.com/benjamincanac.png',
            alt: 'Benjamin Canac',
        },
    },
    {
        name: 'Sebastien Chopin',
        username: 'atinux',
        role: 'member',
        avatar: {
            src: 'https://github.com/atinux.png',
            alt: 'Sebastien Chopin',
        },
    },
];
</script>

<template>
    <Story
        title="Settings/MembersList"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <MembersList :members="members" />
        </Variant>
    </Story>
</template>
