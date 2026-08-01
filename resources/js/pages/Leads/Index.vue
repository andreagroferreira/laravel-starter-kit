<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import PaginationBar from '@/components/shared/PaginationBar.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useTableQuery } from '@/composables/useTableQuery';
import type { Paginated } from '@/types/pagination';
import { formatDateTime } from '@/utils/format';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface LeadRow {
    id: string;
    site: string;
    form: string;
    data: Record<string, unknown>;
    status: 'new' | 'read' | 'spam';
    created_at: string;
}

const props = defineProps<{
    leads: Paginated<LeadRow>;
    sites: { id: string; name: string }[];
    filters: { site: string; status: string };
}>();

const page = usePage();
const toast = useToast();
const confirm = useConfirm();

const { state, goToPage } = useTableQuery({
    only: ['leads', 'filters'],
    filters: { site: props.filters.site, status: props.filters.status },
});

const STATUS_META: Record<
    LeadRow['status'],
    { label: string; color: 'info' | 'neutral' | 'warning' }
> = {
    new: { label: 'Novo', color: 'info' },
    read: { label: 'Lido', color: 'neutral' },
    spam: { label: 'Spam', color: 'warning' },
};

function setStatus(lead: LeadRow, status: LeadRow['status']) {
    router.put(
        `/leads/${lead.id}`,
        { status },
        { preserveScroll: true, only: ['leads'] },
    );
}

async function destroy(lead: LeadRow) {
    const confirmed = await confirm({
        title: 'Apagar este lead?',
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/leads/${lead.id}`, { preserveScroll: true });
    }
}

// Badge live: novas submissões chegam por Reverb no canal do tenant.
let leaveChannel: (() => void) | null = null;

onMounted(() => {
    const tenantId = page.props.auth.user?.current_tenant_id;

    if (!tenantId || !window.Echo) {
        return;
    }

    const channel = window.Echo.private(`tenant.${tenantId}`);
    channel.listen('LeadCaptured', () => {
        toast.add({ title: 'Novo lead recebido', color: 'info' });
        router.reload({ only: ['leads'] });
    });

    leaveChannel = () => window.Echo?.leave(`tenant.${tenantId}`);
});

onUnmounted(() => leaveChannel?.());
</script>

<template>
    <Head title="Leads" />

    <UDashboardPanel id="leads">
        <template #header>
            <UDashboardNavbar title="Leads">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="Exportar CSV"
                        icon="i-lucide-download"
                        variant="subtle"
                        :to="'/leads/export'"
                        external
                    />
                </template>
            </UDashboardNavbar>

            <UDashboardToolbar>
                <div class="flex w-full flex-wrap items-center gap-2 px-2">
                    <USelect
                        v-model="state.filters.site"
                        :items="[
                            { label: 'Todos os sites', value: '' },
                            ...sites.map((site) => ({
                                label: site.name,
                                value: site.id,
                            })),
                        ]"
                        class="w-48"
                        aria-label="Filtrar por site"
                    />
                    <USelect
                        v-model="state.filters.status"
                        :items="[
                            { label: 'Todos os estados', value: '' },
                            { label: 'Novo', value: 'new' },
                            { label: 'Lido', value: 'read' },
                            { label: 'Spam', value: 'spam' },
                        ]"
                        class="w-44"
                        aria-label="Filtrar por estado"
                    />
                </div>
            </UDashboardToolbar>
        </template>

        <template #body>
            <div class="p-4 lg:p-6">
                <UEmpty
                    v-if="leads.data.length === 0"
                    icon="i-lucide-inbox"
                    title="Sem leads"
                    description="As submissões dos formulários públicos aparecem aqui em tempo real."
                />

                <div
                    v-else
                    class="divide-y divide-default rounded-lg border border-default"
                >
                    <div
                        v-for="lead in leads.data"
                        :key="lead.id"
                        class="flex flex-wrap items-start justify-between gap-3 p-4"
                    >
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="text-sm text-muted">
                                {{ lead.site }} · {{ lead.form }} ·
                                {{ formatDateTime(lead.created_at) }}
                            </p>
                            <dl
                                class="grid grid-cols-1 gap-x-6 gap-y-1 text-sm sm:grid-cols-2"
                            >
                                <div
                                    v-for="(value, key) in lead.data"
                                    :key="key"
                                    class="flex gap-2"
                                >
                                    <dt class="shrink-0 font-medium">
                                        {{ key }}:
                                    </dt>
                                    <dd class="truncate text-muted">
                                        {{ value }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="flex items-center gap-2">
                            <UBadge
                                :label="STATUS_META[lead.status].label"
                                :color="STATUS_META[lead.status].color"
                                variant="subtle"
                            />
                            <UButton
                                v-if="lead.status === 'new'"
                                label="Marcar lido"
                                size="xs"
                                variant="ghost"
                                @click="setStatus(lead, 'read')"
                            />
                            <UButton
                                v-if="lead.status !== 'spam'"
                                label="Spam"
                                size="xs"
                                color="warning"
                                variant="ghost"
                                @click="setStatus(lead, 'spam')"
                            />
                            <UButton
                                icon="i-lucide-trash-2"
                                size="xs"
                                color="error"
                                variant="ghost"
                                aria-label="Apagar lead"
                                @click="destroy(lead)"
                            />
                        </div>
                    </div>
                </div>

                <PaginationBar :paginator="leads" @update:page="goToPage" />
            </div>
        </template>
    </UDashboardPanel>
</template>
