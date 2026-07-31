<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Register" />

    <AuthLayout
        title="Create your account"
        description="Start managing your digital presence."
    >
        <form class="space-y-4" @submit.prevent="submit">
            <UFormField label="Name" :error="form.errors.name">
                <UInput
                    v-model="form.name"
                    autocomplete="name"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Email" :error="form.errors.email">
                <UInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Password" :error="form.errors.password">
                <UInput
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
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
                label="Create account"
                block
                :loading="form.processing"
            />
        </form>

        <template #footer>
            <p class="text-center text-sm text-muted">
                Already registered?
                <Link href="/login" class="text-primary hover:underline"
                    >Log in</Link
                >
            </p>
        </template>
    </AuthLayout>
</template>
