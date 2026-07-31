<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import type { Member } from '@/types/models';
import { initials } from '@/utils/format';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

defineProps<{
    members: Member[];
}>();

const page = usePage();
const toast = useToast();
const confirm = useConfirm();

const currentUserId = computed(() => page.props.auth.user?.id);

const open = ref(false);
const invite = useForm({
    email: '',
    role: 'editor',
});

const roles = [
    { label: 'Owner', value: 'owner' },
    { label: 'Editor', value: 'editor' },
    { label: 'Marketeer', value: 'marketeer' },
    { label: 'Jornalista', value: 'journalist' },
    { label: 'Chefe de redação', value: 'editor_in_chief' },
];

const ROLE_LABELS: Record<string, string> = {
    owner: 'Owner',
    editor: 'Editor',
    marketeer: 'Marketeer',
    journalist: 'Jornalista',
    editor_in_chief: 'Chefe de redação',
    member: 'Membro',
};

function inviteMember() {
    invite.post('/settings/members', {
        onSuccess: () => {
            open.value = false;
            invite.reset();
            toast.add({ title: 'Membro convidado', color: 'success' });
        },
    });
}

async function remove(member: Member) {
    const confirmed = await confirm({
        title: `Remover ${member.name} do workspace?`,
        description: 'A pessoa perde imediatamente o acesso.',
        confirmLabel: 'Remover',
    });

    if (confirmed) {
        router.delete(`/settings/members/${member.id}`, {
            preserveScroll: true,
            onSuccess: () =>
                toast.add({ title: 'Membro removido', color: 'success' }),
            onError: (errors) =>
                toast.add({
                    title:
                        Object.values(errors)[0] ?? 'Não foi possível remover.',
                    color: 'error',
                }),
        });
    }
}
</script>

<template>
    <Head title="Definições — Membros" />

    <SettingsLayout>
        <UPageCard
            title="Membros"
            description="Quem tem acesso a este workspace e com que role."
            variant="naked"
            orientation="horizontal"
            class="mb-4"
        >
            <UButton
                label="Convidar membro"
                icon="i-lucide-user-plus"
                class="w-fit lg:ms-auto"
                @click="open = true"
            />
        </UPageCard>

        <UEmpty
            v-if="members.length === 0"
            icon="i-lucide-users"
            title="Só tu por aqui"
            description="Convida a equipa para colaborar neste workspace."
        />

        <div
            v-else
            class="divide-y divide-default rounded-lg border border-default"
        >
            <div
                v-for="member in members"
                :key="member.id"
                class="flex flex-wrap items-center justify-between gap-2 p-3"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <UAvatar :text="initials(member.name)" size="sm" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">
                            {{ member.name }}
                            <span
                                v-if="member.id === currentUserId"
                                class="text-muted"
                                >(tu)</span
                            >
                        </p>
                        <p class="truncate text-xs text-muted">
                            {{ member.email }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <UBadge
                        :label="
                            ROLE_LABELS[member.role ?? 'member'] ??
                            String(member.role)
                        "
                        variant="subtle"
                    />
                    <UButton
                        v-if="member.id !== currentUserId"
                        icon="i-lucide-user-minus"
                        size="xs"
                        color="error"
                        variant="ghost"
                        aria-label="Remover membro"
                        @click="remove(member)"
                    />
                </div>
            </div>
        </div>

        <UModal v-model:open="open" title="Convidar membro">
            <template #body>
                <form class="space-y-4" @submit.prevent="inviteMember">
                    <UFormField
                        label="Email"
                        required
                        :error="invite.errors.email"
                    >
                        <UInput
                            v-model="invite.email"
                            type="email"
                            class="w-full"
                            autofocus
                        />
                    </UFormField>
                    <UFormField label="Role" :error="invite.errors.role">
                        <USelect
                            v-model="invite.role"
                            :items="roles"
                            class="w-full"
                        />
                    </UFormField>
                    <UButton
                        type="submit"
                        label="Convidar"
                        block
                        :loading="invite.processing"
                    />
                </form>
            </template>
        </UModal>
    </SettingsLayout>
</template>
