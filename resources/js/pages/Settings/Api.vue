<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
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

const open = ref(false);
const form = reactive({
    name: '',
    abilities: ['read'] as string[],
});

const newToken = computed(() => page.props.flash.token);

function createToken() {
    router.post(
        '/settings/api/tokens',
        { ...form },
        {
            onSuccess: () => {
                open.value = false;
                form.name = '';
                form.abilities = ['read'];
            },
        },
    );
}

function revoke(id: string) {
    router.delete(`/settings/api/tokens/${id}`, {
        onSuccess: () =>
            toast.add({ title: 'Token revoked', color: 'success' }),
    });
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
                label="New token"
                icon="i-lucide-plus"
                class="w-fit lg:ms-auto"
                @click="open = true"
            />
        </UPageCard>

        <UAlert
            v-if="newToken?.plainTextToken"
            title="Token created — copy it now, it will not be shown again"
            color="success"
            variant="subtle"
            :actions="[
                {
                    label: 'Copy',
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
            title="No tokens yet"
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
                            · last used {{ token.last_used_at }}</span
                        >
                        <span v-else> · never used</span>
                    </p>
                </div>
                <UButton
                    icon="i-lucide-trash-2"
                    size="xs"
                    color="error"
                    variant="ghost"
                    @click="revoke(token.id)"
                />
            </div>
        </div>

        <UModal v-model:open="open" title="New token">
            <template #body>
                <form class="space-y-4" @submit.prevent="createToken">
                    <UFormField label="Name" required>
                        <UInput
                            v-model="form.name"
                            placeholder="e.g. Claude desktop, CI deploy"
                            class="w-full"
                            autofocus
                        />
                    </UFormField>
                    <UFormField label="Scopes">
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
                    <UButton type="submit" label="Create token" block />
                </form>
            </template>
        </UModal>
    </SettingsLayout>
</template>
