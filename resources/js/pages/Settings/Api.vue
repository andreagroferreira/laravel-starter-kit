<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface TokenRow {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
}

const props = defineProps<{
    tokens: TokenRow[];
    availableAbilities: string[];
    mcpEndpoint: string;
    tenant: { name: string; slug: string };
}>();

const page = usePage();
const toast = useToast();

const confirm = useConfirm();

const open = ref(false);
const form = useForm({
    name: '',
    abilities: ['read'] as string[],
});

const newToken = computed(() => page.props.flash.token);

function createToken() {
    form.post('/settings/api/tokens', {
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}

async function revoke(id: string) {
    const confirmed = await confirm({
        title: 'Revogar este token?',
        description: 'Os agentes que o usam perdem o acesso imediatamente.',
        confirmLabel: 'Revogar',
    });

    if (confirmed) {
        router.delete(`/settings/api/tokens/${id}`, {
            preserveScroll: true,
            onSuccess: () =>
                toast.add({ title: 'Token revogado', color: 'success' }),
        });
    }
}

function copy(text: string) {
    navigator.clipboard.writeText(text);
    toast.add({ title: 'Copied', color: 'success' });
}

const claudeSnippet = computed(
    () => `claude mcp add --transport http wizard ${props.mcpEndpoint}`,
);
</script>

<template>
    <Head title="API & MCP" />

    <SettingsLayout>
        <UPageCard
            title="API tokens"
            description="Tokens Sanctum para a API headless e para agentes MCP (Claude, Codex, ChatGPT)."
            variant="naked"
            orientation="horizontal"
            class="mb-4"
        >
            <UButton
                label="Novo token"
                icon="i-lucide-plus"
                class="w-fit lg:ms-auto"
                @click="open = true"
            />
        </UPageCard>

        <UAlert
            v-if="newToken?.plainTextToken"
            title="Token criado — copia-o agora, não volta a ser mostrado"
            color="success"
            variant="subtle"
            :actions="[
                {
                    label: 'Copiar',
                    icon: 'i-lucide-copy',
                    onClick: () => copy(newToken!.plainTextToken),
                },
            ]"
        >
            <template #description>
                <code class="text-xs break-all">{{
                    newToken.plainTextToken
                }}</code>
            </template>
        </UAlert>

        <UPageCard title="MCP endpoint" variant="subtle">
            <div class="space-y-2">
                <p class="text-sm text-muted">
                    Liga o teu agente ao servidor MCP deste tenant:
                </p>
                <div
                    class="flex items-center gap-2 rounded-lg border border-default p-2"
                >
                    <code class="flex-1 truncate text-xs">{{
                        claudeSnippet
                    }}</code>
                    <UButton
                        icon="i-lucide-copy"
                        size="xs"
                        variant="ghost"
                        @click="copy(claudeSnippet)"
                    />
                </div>
                <p class="text-xs text-muted">
                    Scopes: <code>read</code> leitura ·
                    <code>write:draft</code> cria/edita rascunhos ·
                    <code>publish</code> publica · <code>admin</code> tudo
                </p>
            </div>
        </UPageCard>

        <UEmpty
            v-if="tokens.length === 0"
            icon="i-lucide-key-round"
            title="Ainda não há tokens"
        />

        <div
            v-else
            class="divide-y divide-default rounded-lg border border-default"
        >
            <div
                v-for="token in tokens"
                :key="token.id"
                class="flex items-center justify-between p-3"
            >
                <div>
                    <p class="font-medium">{{ token.name }}</p>
                    <p class="text-sm text-muted">
                        {{ token.abilities.join(', ') }}
                        <span v-if="token.last_used_at">
                            · usado {{ token.last_used_at }}</span
                        >
                        <span v-else> · nunca usado</span>
                    </p>
                </div>
                <UButton
                    icon="i-lucide-trash-2"
                    size="xs"
                    color="error"
                    variant="ghost"
                    aria-label="Revogar token"
                    @click="revoke(token.id)"
                />
            </div>
        </div>

        <UModal v-model:open="open" title="Novo token">
            <template #body>
                <form class="space-y-4" @submit.prevent="createToken">
                    <UFormField label="Nome" required :error="form.errors.name">
                        <UInput
                            v-model="form.name"
                            placeholder="ex.: Claude desktop, CI deploy"
                            class="w-full"
                            autofocus
                        />
                    </UFormField>
                    <UFormField label="Scopes" :error="form.errors.abilities">
                        <UCheckbox
                            v-for="ability in availableAbilities"
                            :key="ability"
                            :model-value="form.abilities.includes(ability)"
                            :label="ability"
                            @update:model-value="
                                form.abilities = $event
                                    ? [...form.abilities, ability]
                                    : form.abilities.filter(
                                          (a) => a !== ability,
                                      )
                            "
                        />
                    </UFormField>
                    <UButton
                        type="submit"
                        label="Criar token"
                        block
                        :loading="form.processing"
                    />
                </form>
            </template>
        </UModal>
    </SettingsLayout>
</template>
