<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const page = usePage();
const toast = useToast();

const form = useForm({
    name: page.props.auth.user?.name ?? '',
    email: page.props.auth.user?.email ?? '',
});

function submit() {
    form.put('/user/profile-information', {
        preserveScroll: true,
        onSuccess: () =>
            toast.add({ title: 'Perfil atualizado', color: 'success' }),
    });
}
</script>

<template>
    <Head title="Definições — Perfil" />

    <SettingsLayout>
        <UPageCard
            title="Perfil"
            description="O teu nome e email de acesso."
            variant="subtle"
        >
            <form class="max-w-md space-y-4" @submit.prevent="submit">
                <UFormField label="Nome" required :error="form.errors.name">
                    <UInput v-model="form.name" class="w-full" />
                </UFormField>
                <UFormField label="Email" required :error="form.errors.email">
                    <UInput v-model="form.email" type="email" class="w-full" />
                </UFormField>
                <UButton
                    type="submit"
                    label="Guardar"
                    :loading="form.processing"
                />
            </form>
        </UPageCard>
    </SettingsLayout>
</template>
