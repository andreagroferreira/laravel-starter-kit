<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    twoFactorEnabled: boolean;
    twoFactorConfirmed: boolean;
}>();

const toast = useToast();

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updatePassword() {
    passwordForm.put('/user/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.add({ title: 'Password atualizada', color: 'success' });
        },
    });
}

const qrCode = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);
const confirmForm = useForm({ code: '' });
const twoFactorBusy = ref(false);

async function loadTwoFactorArtifacts() {
    const [qrResponse, codesResponse] = await Promise.all([
        fetch('/user/two-factor-qr-code', {
            headers: { Accept: 'application/json' },
        }),
        fetch('/user/two-factor-recovery-codes', {
            headers: { Accept: 'application/json' },
        }),
    ]);

    if (qrResponse.ok) {
        qrCode.value = ((await qrResponse.json()) as { svg: string }).svg;
    }

    if (codesResponse.ok) {
        recoveryCodes.value = (await codesResponse.json()) as string[];
    }
}

const enableForm = useForm({});

function enableTwoFactor() {
    twoFactorBusy.value = true;
    enableForm.post('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => loadTwoFactorArtifacts(),
        onFinish: () => {
            twoFactorBusy.value = false;
        },
    });
}

function confirmTwoFactor() {
    confirmForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            confirmForm.reset();
            qrCode.value = null;
            toast.add({
                title: 'Autenticação de dois fatores ativa',
                color: 'success',
            });
        },
    });
}

const disableForm = useForm({});

function disableTwoFactor() {
    disableForm.delete('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            qrCode.value = null;
            recoveryCodes.value = [];
            toast.add({
                title: 'Autenticação de dois fatores desativada',
                color: 'success',
            });
        },
    });
}
</script>

<template>
    <Head title="Definições — Segurança" />

    <SettingsLayout>
        <div class="space-y-6">
            <UPageCard
                title="Password"
                description="Usa uma password longa e única."
                variant="subtle"
            >
                <form
                    class="max-w-md space-y-4"
                    @submit.prevent="updatePassword"
                >
                    <UFormField
                        label="Password atual"
                        required
                        :error="passwordForm.errors.current_password"
                    >
                        <UInput
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full"
                        />
                    </UFormField>
                    <UFormField
                        label="Nova password"
                        required
                        :error="passwordForm.errors.password"
                    >
                        <UInput
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="w-full"
                        />
                    </UFormField>
                    <UFormField label="Confirmar nova password" required>
                        <UInput
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="w-full"
                        />
                    </UFormField>
                    <UButton
                        type="submit"
                        label="Atualizar password"
                        :loading="passwordForm.processing"
                    />
                </form>
            </UPageCard>

            <UPageCard
                title="Autenticação de dois fatores"
                description="Proteção extra com uma app TOTP (ex.: 1Password, Google Authenticator)."
                variant="subtle"
            >
                <div class="space-y-4">
                    <div v-if="!props.twoFactorEnabled">
                        <UButton
                            label="Ativar 2FA"
                            icon="i-lucide-shield-check"
                            :loading="twoFactorBusy"
                            @click="enableTwoFactor"
                        />
                    </div>

                    <template v-else>
                        <UAlert
                            v-if="props.twoFactorConfirmed"
                            color="success"
                            variant="subtle"
                            icon="i-lucide-shield-check"
                            title="2FA ativa nesta conta."
                        />

                        <div v-if="qrCode" class="space-y-3">
                            <p class="text-sm text-muted">
                                Lê o código QR na tua app de autenticação e
                                confirma com um código.
                            </p>
                            <!-- eslint-disable-next-line vue/no-v-html -->
                            <div
                                class="w-fit rounded-lg bg-white p-3"
                                v-html="qrCode"
                            />
                            <form
                                class="flex max-w-xs items-end gap-2"
                                @submit.prevent="confirmTwoFactor"
                            >
                                <UFormField
                                    label="Código"
                                    :error="confirmForm.errors.code"
                                    class="flex-1"
                                >
                                    <UInput
                                        v-model="confirmForm.code"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        class="w-full"
                                    />
                                </UFormField>
                                <UButton
                                    type="submit"
                                    label="Confirmar"
                                    :loading="confirmForm.processing"
                                />
                            </form>
                        </div>

                        <div v-if="recoveryCodes.length" class="space-y-2">
                            <p class="text-sm font-medium">
                                Códigos de recuperação
                            </p>
                            <div
                                class="grid max-w-md grid-cols-2 gap-1 rounded-lg border border-default p-3 font-mono text-xs"
                            >
                                <span
                                    v-for="code in recoveryCodes"
                                    :key="code"
                                    >{{ code }}</span
                                >
                            </div>
                        </div>

                        <UButton
                            label="Desativar 2FA"
                            color="error"
                            variant="soft"
                            :loading="disableForm.processing"
                            @click="disableTwoFactor"
                        />
                    </template>
                </div>
            </UPageCard>
        </div>
    </SettingsLayout>
</template>
