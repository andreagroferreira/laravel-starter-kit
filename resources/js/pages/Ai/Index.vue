<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAiGeneration } from '@/composables/useAiGeneration';
import { useEchoChannel } from '@/composables/useEchoChannel';
import type { Site } from '@/types/models';
import { formatDateTime } from '@/utils/format';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface GenerationRow {
    id: string;
    agent: string;
    status: 'queued' | 'processing' | 'completed' | 'failed';
    site: string | null;
    output: Record<string, unknown> | null;
    error: string | null;
    created_at: string;
}

const props = defineProps<{
    sites: Pick<Site, 'id' | 'name'>[];
    generations: GenerationRow[];
    credits: { used: number; monthly: number };
}>();

const page = usePage();
const toast = useToast();
const ai = useAiGeneration();

const AGENT_LABELS: Record<string, string> = {
    article_writer: 'Artigo',
    copywriter: 'Copy',
    seo: 'SEO',
};

const STATUS_META: Record<
    GenerationRow['status'],
    { label: string; color: 'neutral' | 'info' | 'success' | 'error' }
> = {
    queued: { label: 'Em fila', color: 'neutral' },
    processing: { label: 'A gerar', color: 'info' },
    completed: { label: 'Concluído', color: 'success' },
    failed: { label: 'Falhou', color: 'error' },
};

const siteId = ref(props.sites[0]?.id ?? '');
const briefing = ref('');
const language = ref('pt-PT');
const running = ref(false);
const step = ref('');
const lastPostId = ref<string | null>(null);

const creditsLeft = computed(() =>
    Math.max(0, props.credits.monthly - props.credits.used),
);

// Live progress when Reverb is available; the composable always polls as
// a fallback so the result never depends on the websocket.
const live = useEchoChannel(page.props.auth.user?.current_tenant_id, {
    AiGenerationUpdated: (payload: { status: string; agent: string }) => {
        step.value =
            payload.status === 'processing'
                ? `A gerar (${AGENT_LABELS[payload.agent] ?? payload.agent})…`
                : '';

        if (payload.status === 'completed' || payload.status === 'failed') {
            router.reload({ only: ['generations', 'credits'] });
        }
    },
});

async function generate() {
    if (!siteId.value || briefing.value.trim() === '') {
        return;
    }

    running.value = true;
    step.value = 'Em fila…';
    lastPostId.value = null;

    try {
        const generation = await ai.start(`/sites/${siteId.value}/ai/article`, {
            briefing: briefing.value,
            language: language.value,
        });

        const postId = generation.output?.post_id;
        lastPostId.value = typeof postId === 'string' ? postId : null;

        toast.add({ title: 'Rascunho criado', color: 'success' });
        briefing.value = '';
        router.reload({ only: ['generations', 'credits'] });
    } catch (error) {
        toast.add({
            title: error instanceof Error ? error.message : 'A geração falhou.',
            color: 'error',
        });
    } finally {
        running.value = false;
        step.value = '';
    }
}
</script>

<template>
    <Head title="AI Copilot" />

    <UDashboardPanel id="ai">
        <template #header>
            <UDashboardNavbar title="AI Copilot">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UBadge
                        :label="`${creditsLeft} de ${credits.monthly} créditos`"
                        :color="creditsLeft === 0 ? 'error' : 'neutral'"
                        variant="subtle"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="mx-auto w-full max-w-3xl space-y-6 p-4 lg:p-6">
                <UPageCard
                    title="Gerar artigo"
                    description="Descreve o que queres e o Copilot escreve um rascunho na voz da tua marca."
                    variant="subtle"
                >
                    <form class="space-y-4" @submit.prevent="generate">
                        <UFormField label="Site" required>
                            <USelect
                                v-model="siteId"
                                :items="
                                    sites.map((site) => ({
                                        label: site.name,
                                        value: site.id,
                                    }))
                                "
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Briefing" required>
                            <UTextarea
                                v-model="briefing"
                                :rows="4"
                                placeholder="Ex.: artigo sobre como escolher um CMS para uma agência, com foco em SEO e AI"
                                class="w-full"
                            />
                        </UFormField>

                        <UFormField label="Idioma">
                            <USelect
                                v-model="language"
                                :items="[
                                    { label: 'Português (PT)', value: 'pt-PT' },
                                    { label: 'Inglês', value: 'en' },
                                    { label: 'Espanhol', value: 'es' },
                                ]"
                                class="w-full"
                            />
                        </UFormField>

                        <div class="flex flex-wrap items-center gap-3">
                            <UButton
                                type="submit"
                                label="Gerar rascunho"
                                icon="i-lucide-sparkles"
                                :loading="running"
                                :disabled="
                                    creditsLeft === 0 || sites.length === 0
                                "
                            />
                            <span v-if="running" class="text-sm text-muted">
                                {{ step || 'A processar…' }}
                            </span>
                            <span
                                v-else-if="!live"
                                class="text-xs text-muted"
                                title="Sem websocket: o estado é obtido por polling."
                            >
                                Progresso por polling
                            </span>
                        </div>

                        <UProgress v-if="running" animation="carousel" />

                        <UAlert
                            v-if="creditsLeft === 0"
                            color="warning"
                            variant="subtle"
                            icon="i-lucide-alert-triangle"
                            title="Sem créditos AI este mês"
                            description="Faz upgrade do plano para continuar a gerar conteúdo."
                        />
                    </form>
                </UPageCard>

                <UPageCard title="Histórico" variant="subtle">
                    <UEmpty
                        v-if="generations.length === 0"
                        icon="i-lucide-sparkles"
                        title="Ainda não geraste nada"
                        description="As gerações aparecem aqui com o respetivo estado."
                    />

                    <div v-else class="divide-y divide-default">
                        <div
                            v-for="generation in generations"
                            :key="generation.id"
                            class="flex flex-wrap items-center justify-between gap-2 py-3"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-medium">
                                    {{
                                        AGENT_LABELS[generation.agent] ??
                                        generation.agent
                                    }}
                                    <span
                                        v-if="generation.site"
                                        class="text-muted"
                                        >· {{ generation.site }}</span
                                    >
                                </p>
                                <p class="text-xs text-muted">
                                    {{ formatDateTime(generation.created_at) }}
                                    <span v-if="generation.error">
                                        · {{ generation.error }}</span
                                    >
                                </p>
                            </div>
                            <UBadge
                                :label="STATUS_META[generation.status].label"
                                :color="STATUS_META[generation.status].color"
                                variant="subtle"
                            />
                        </div>
                    </div>
                </UPageCard>
            </div>
        </template>
    </UDashboardPanel>
</template>
