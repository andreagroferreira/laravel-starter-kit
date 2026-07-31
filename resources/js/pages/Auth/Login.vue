<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

defineProps<{
    canResetPassword: boolean;
    canRegister: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Log in" />

    <AuthLayout title="Welcome back" description="Log in to your account.">
        <UAlert
            v-if="status"
            :title="status"
            color="success"
            variant="subtle"
            class="mb-4"
        />

        <form class="space-y-4" @submit.prevent="submit">
            <UFormField label="Email" :error="form.errors.email">
                <UInput
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UFormField label="Password" :error="form.errors.password">
                <UInput
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full"
                />
            </UFormField>

            <div class="flex items-center justify-between">
                <UCheckbox v-model="form.remember" label="Remember me" />
                <Link
                    v-if="canResetPassword"
                    href="/forgot-password"
                    class="text-sm text-primary hover:underline"
                >
                    Forgot password?
                </Link>
            </div>

            <UButton
                type="submit"
                label="Log in"
                block
                :loading="form.processing"
            />
        </form>

        <template v-if="canRegister" #footer>
            <p class="text-center text-sm text-muted">
                No account yet?
                <Link href="/register" class="text-primary hover:underline"
                    >Create one</Link
                >
            </p>
        </template>
    </AuthLayout>
</template>
