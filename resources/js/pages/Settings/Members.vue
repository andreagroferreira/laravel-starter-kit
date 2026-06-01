<script setup lang="ts">
import {computed, ref} from 'vue';
import {Head} from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import SettingsMembersList from '../../components/settings/SettingsMembersList.vue';
import type {Member} from '../../types';

defineOptions({layout: AppLayout});

const props = defineProps<{
    members: Member[];
}>();

const q = ref('');

const filteredMembers = computed(() => {
    const needle = q.value.trim().toLowerCase();

    if (needle === '') {
        return props.members;
    }

    return props.members.filter(
        (member) =>
            member.name.toLowerCase().includes(needle) ||
            member.username.toLowerCase().includes(needle),
    );
});
</script>

<template>
    <Head title="Members" />

    <SettingsLayout>
        <div>
            <UPageCard
                title="Members"
                description="Invite new members by email address."
                variant="naked"
                orientation="horizontal"
                class="mb-4"
            >
                <UButton
                    label="Invite people"
                    color="neutral"
                    class="w-fit lg:ms-auto"
                />
            </UPageCard>

            <UPageCard
                variant="subtle"
                :ui="{
                    container: 'p-0 sm:p-0 gap-y-0',
                    wrapper: 'items-stretch',
                    header: 'p-4 mb-0 border-b border-default',
                }"
            >
                <template #header>
                    <UInput
                        v-model="q"
                        icon="i-lucide-search"
                        placeholder="Search members"
                        autofocus
                        class="w-full"
                    />
                </template>

                <SettingsMembersList :members="filteredMembers" />
            </UPageCard>
        </div>
    </SettingsLayout>
</template>
