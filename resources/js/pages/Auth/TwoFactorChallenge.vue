<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthLayout from '../../layouts/AuthLayout.vue';

defineOptions({ layout: false });

const usingRecoveryCode = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function submit() {
    form.post('/two-factor-challenge');
}
</script>

<template>
    <Head title="Two-factor challenge" />

    <AuthLayout
        :title="usingRecoveryCode ? 'Recovery code' : 'Authentication code'"
        :description="
            usingRecoveryCode
                ? 'Enter one of your emergency recovery codes.'
                : 'Enter the code from your authenticator app.'
        "
    >
        <form class="space-y-4" @submit.prevent="submit">
            <UFormField
                v-if="!usingRecoveryCode"
                label="Code"
                :error="form.errors.code"
            >
                <UInput
                    v-model="form.code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UFormField
                v-else
                label="Recovery code"
                :error="form.errors.recovery_code"
            >
                <UInput
                    v-model="form.recovery_code"
                    autocomplete="off"
                    autofocus
                    class="w-full"
                />
            </UFormField>

            <UButton
                type="submit"
                label="Verify"
                block
                :loading="form.processing"
            />

            <UButton
                :label="
                    usingRecoveryCode
                        ? 'Use an authentication code'
                        : 'Use a recovery code'
                "
                color="neutral"
                variant="ghost"
                block
                @click="usingRecoveryCode = !usingRecoveryCode"
            />
        </form>
    </AuthLayout>
</template>
