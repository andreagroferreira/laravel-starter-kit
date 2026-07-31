<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface Member {
    id: string;
    name: string;
    email: string;
    role: string;
}

const props = defineProps<{
    members: Member[];
}>();

const page = usePage();
const toast = useToast();

const currentUserId = computed(
    () => (page.props.auth?.user as { id?: string } | undefined)?.id,
);

const open = ref(false);
const invite = reactive({
    email: '',
    role: 'editor',
});

const roles = [
    { label: 'Owner', value: 'owner' },
    { label: 'Editor', value: 'editor' },
    { label: 'Marketeer', value: 'marketeer' },
    { label: 'Journalist', value: 'journalist' },
    { label: 'Editor in chief', value: 'editor_in_chief' },
];

function inviteMember() {
    router.post(
        '/settings/members',
        { ...invite },
        {
            onSuccess: () => {
                open.value = false;
                invite.email = '';
                invite.role = 'editor';
                toast.add({ title: 'Member invited', color: 'success' });
            },
        },
    );
}

function remove(member: Member) {
    router.delete(`/settings/members/${member.id}`, {
        onSuccess: () =>
            toast.add({ title: 'Member removed', color: 'success' }),
    });
}
</script>

<template>
    <Head title="Members" />

    <SettingsLayout>
        <UPageCard
            title="Members"
            description="Quem tem acesso a este workspace e com que role."
            variant="naked"
            orientation="horizontal"
            class="mb-4"
        >
            <UButton
                label="Invite member"
                icon="i-lucide-user-plus"
                class="w-fit lg:ms-auto"
                @click="open = true"
            />
        </UPageCard>

        <div class="divide-y divide-default rounded-lg border border-default">
            <div
                v-for="member in members"
                :key="member.id"
                class="flex items-center justify-between p-3"
            >
                <div class="flex items-center gap-3">
                    <UAvatar
                        :text="
                            member.name
                                .split(' ')
                                .map((p) => p[0])
                                .join('')
                                .slice(0, 2)
                                .toUpperCase()
                        "
                        size="sm"
                    />
                    <div>
                        <p class="text-sm font-medium">
                            {{ member.name }}
                            <span
                                v-if="member.id === currentUserId"
                                class="text-muted"
                                >(you)</span
                            >
                        </p>
                        <p class="text-xs text-muted">{{ member.email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <UBadge
                        :label="member.role.replace('_', ' ')"
                        variant="subtle"
                    />
                    <UButton
                        v-if="member.id !== currentUserId"
                        icon="i-lucide-user-minus"
                        size="xs"
                        color="error"
                        variant="ghost"
                        @click="remove(member)"
                    />
                </div>
            </div>
        </div>

        <UModal v-model:open="open" title="Invite member">
            <template #body>
                <form class="space-y-4" @submit.prevent="inviteMember">
                    <UFormField label="Email" required>
                        <UInput
                            v-model="invite.email"
                            type="email"
                            class="w-full"
                            autofocus
                        />
                    </UFormField>
                    <UFormField label="Role">
                        <USelect
                            v-model="invite.role"
                            :items="roles"
                            class="w-full"
                        />
                    </UFormField>
                    <UButton type="submit" label="Invite" block />
                </form>
            </template>
        </UModal>
    </SettingsLayout>
</template>
