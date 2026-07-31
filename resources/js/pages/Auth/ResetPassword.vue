<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Reset password" />

    <AuthLayout title="Choose a new password">
        <form class="space-y-4" @submit.prevent="submit">
            <UFormField label="Email" :error="form.errors.email">
                <UInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    class="w-full"
                />
            </UFormField>

            <UFormField label="New password" :error="form.errors.password">
                <UInput
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Confirm password">
                <UInput
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full"
                />
            </UFormField>

            <UButton
                type="submit"
                label="Reset password"
                block
                :loading="form.processing"
            />
        </form>
    </AuthLayout>
</template>
