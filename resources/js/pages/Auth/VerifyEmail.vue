<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

defineProps<{
    status?: string;
}>();

const form = useForm({});

function resend() {
    form.post('/email/verification-notification');
}

function logout() {
    useForm({}).post('/logout');
}
</script>

<template>
    <Head title="Verify email" />

    <AuthLayout
        title="Verify your email"
        description="Check your inbox for a verification link."
    >
        <UAlert
            v-if="status === 'verification-link-sent'"
            title="A new verification link has been sent."
            color="success"
            variant="subtle"
            class="mb-4"
        />

        <div class="space-y-3">
            <UButton
                label="Resend verification email"
                block
                :loading="form.processing"
                @click="resend"
            />
            <UButton
                label="Log out"
                color="neutral"
                variant="ghost"
                block
                @click="logout"
            />
        </div>
    </AuthLayout>
</template>
