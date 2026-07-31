<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <Head title="Forgot password" />

    <AuthLayout
        title="Reset your password"
        description="We will email you a reset link."
    >
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

            <UButton
                type="submit"
                label="Send reset link"
                block
                :loading="form.processing"
            />
        </form>

        <template #footer>
            <p class="text-center text-sm text-muted">
                <Link href="/login" class="text-primary hover:underline"
                    >Back to log in</Link
                >
            </p>
        </template>
    </AuthLayout>
</template>
