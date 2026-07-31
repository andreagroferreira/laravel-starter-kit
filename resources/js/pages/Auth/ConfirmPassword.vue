<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

const form = useForm({
    password: '',
});

function submit() {
    form.post('/user/confirm-password', {
        onFinish: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Confirm password" />

    <AuthLayout
        title="Confirm your password"
        description="This is a secure area. Confirm your password to continue."
    >
        <form class="space-y-4" @submit.prevent="submit">
            <UFormField label="Password" :error="form.errors.password">
                <UInput
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UButton
                type="submit"
                label="Confirm"
                block
                :loading="form.processing"
            />
        </form>
    </AuthLayout>
</template>
