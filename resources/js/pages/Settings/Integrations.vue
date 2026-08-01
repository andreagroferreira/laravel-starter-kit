<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatDate } from '@/utils/format';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface IntegrationRow {
    id: string;
    provider: string;
    label: string;
    site: string | null;
    site_id: string | null;
    external_id: string | null;
    status: string;
    connected_at: string | null;
}

interface MetricRow {
    id: string;
    site: string;
    provider: string;
    date: string;
    metrics: Record<string, unknown>;
}

defineProps<{
    sites: { id: string; name: string }[];
    providers: { value: string; label: string; configured: boolean }[];
    integrations: IntegrationRow[];
    metrics: MetricRow[];
}>();

const confirm = useConfirm();

const EXTERNAL_LABEL: Record<string, string> = {
    google_analytics: 'GA4 measurement ID (G-XXXX)',
    search_console: 'Código de verificação Search Console',
    meta: 'ID da página Facebook',
};

const editing = ref<IntegrationRow | null>(null);
const form = useForm({ site_id: '', external_id: '' });

function edit(integration: IntegrationRow) {
    editing.value = integration;
    form.site_id = integration.site_id ?? '';
    form.external_id = integration.external_id ?? '';
}

function save() {
    if (!editing.value) {
        return;
    }

    form.transform((data) => ({
        ...data,
        site_id: data.site_id || null,
        external_id: data.external_id || null,
    })).put(`/settings/integrations/${editing.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        },
    });
}

async function disconnect(integration: IntegrationRow) {
    const confirmed = await confirm({
        title: `Desligar ${integration.label}?`,
        description: 'Os tokens são apagados e o sync pára.',
        confirmLabel: 'Desligar',
    });

    if (confirmed) {
        router.delete(`/settings/integrations/${integration.id}`, {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Definições — Integrações" />

    <SettingsLayout>
        <div class="space-y-6">
            <UPageCard
                title="Ligar plataformas"
                description="Analytics, Search Console e redes sociais por tenant."
                variant="subtle"
            >
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="provider in providers"
                        :key="provider.value"
                        class="space-y-2 rounded-lg border border-default p-4"
                    >
                        <p class="font-medium">{{ provider.label }}</p>
                        <UButton
                            v-if="provider.configured"
                            label="Ligar"
                            icon="i-lucide-plug"
                            size="sm"
                            variant="subtle"
                            :to="`/settings/integrations/${provider.value}/redirect`"
                            external
                        />
                        <UBadge
                            v-else
                            label="Pendente de aprovação"
                            color="warning"
                            variant="subtle"
                            title="A app ainda não tem credenciais OAuth configuradas."
                        />
                    </div>
                </div>
            </UPageCard>

            <UPageCard title="Ligações ativas" variant="subtle">
                <UEmpty
                    v-if="integrations.length === 0"
                    icon="i-lucide-plug"
                    title="Sem integrações"
                    description="Liga o Google Analytics para veres métricas no dashboard."
                />

                <div v-else class="divide-y divide-default">
                    <div
                        v-for="integration in integrations"
                        :key="integration.id"
                        class="flex flex-wrap items-center justify-between gap-2 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium">
                                {{ integration.label }}
                                <span v-if="integration.site" class="text-muted"
                                    >· {{ integration.site }}</span
                                >
                            </p>
                            <p class="text-xs text-muted">
                                {{
                                    integration.external_id ??
                                    'Falta escolher site e propriedade'
                                }}
                                <span v-if="integration.connected_at">
                                    ·
                                    {{
                                        formatDate(integration.connected_at)
                                    }}</span
                                >
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="
                                    integration.status === 'connected'
                                        ? 'Ligado'
                                        : 'Erro'
                                "
                                :color="
                                    integration.status === 'connected'
                                        ? 'success'
                                        : 'error'
                                "
                                variant="subtle"
                            />
                            <UButton
                                icon="i-lucide-settings-2"
                                size="xs"
                                variant="ghost"
                                aria-label="Configurar integração"
                                @click="edit(integration)"
                            />
                            <UButton
                                icon="i-lucide-unplug"
                                size="xs"
                                color="error"
                                variant="ghost"
                                aria-label="Desligar integração"
                                @click="disconnect(integration)"
                            />
                        </div>
                    </div>
                </div>
            </UPageCard>

            <UPageCard
                v-if="metrics.length"
                title="Métricas recentes"
                description="Sincronizadas diariamente às 03:00."
                variant="subtle"
            >
                <div class="divide-y divide-default">
                    <div
                        v-for="metric in metrics"
                        :key="metric.id"
                        class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm"
                    >
                        <span class="text-muted">
                            {{ metric.site }} · {{ formatDate(metric.date) }}
                        </span>
                        <span class="flex flex-wrap gap-3">
                            <span
                                v-for="(value, key) in metric.metrics"
                                :key="key"
                            >
                                <template v-if="typeof value !== 'object'">
                                    <strong>{{ value }}</strong>
                                    {{ key }}
                                </template>
                            </span>
                        </span>
                    </div>
                </div>
            </UPageCard>
        </div>

        <UModal
            :open="editing !== null"
            title="Configurar integração"
            description="Escolhe o site e identifica a propriedade."
            @update:open="editing = null"
        >
            <template #body>
                <form class="space-y-4" @submit.prevent="save">
                    <UFormField label="Site" :error="form.errors.site_id">
                        <USelect
                            v-model="form.site_id"
                            :items="[
                                { label: 'Todos os sites', value: '' },
                                ...sites.map((site) => ({
                                    label: site.name,
                                    value: site.id,
                                })),
                            ]"
                            class="w-full"
                        />
                    </UFormField>
                    <UFormField
                        :label="
                            EXTERNAL_LABEL[editing?.provider ?? ''] ??
                            'Identificador'
                        "
                        :error="form.errors.external_id"
                    >
                        <UInput v-model="form.external_id" class="w-full" />
                    </UFormField>
                    <UButton
                        type="submit"
                        label="Guardar"
                        block
                        :loading="form.processing"
                    />
                </form>
            </template>
        </UModal>
    </SettingsLayout>
</template>
