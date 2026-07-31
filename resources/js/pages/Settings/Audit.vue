<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface LogRow {
    id: string;
    actor_type: string;
    action: string;
    user: { name: string; email: string } | null;
    subject_type: string | null;
    subject_id: string | null;
    payload: Record<string, unknown> | null;
    created_at: string;
}

const props = defineProps<{
    logs: {
        data: LogRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { actor: string | null };
}>();

function filterActor(actor: string | null) {
    router.get('/settings/audit', actor ? { actor } : {}, {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Audit log" />

    <SettingsLayout>
        <UPageCard
            title="Audit log"
            description="Quem fez o quê — e se foi um humano ou um agente."
            variant="naked"
            class="mb-4"
        />

        <div class="flex items-center gap-2">
            <UButton
                label="All"
                size="xs"
                :variant="filters.actor === null ? 'soft' : 'ghost'"
                @click="filterActor(null)"
            />
            <UButton
                label="Humans"
                size="xs"
                :variant="filters.actor === 'human' ? 'soft' : 'ghost'"
                @click="filterActor('human')"
            />
            <UButton
                label="Agents"
                size="xs"
                :variant="filters.actor === 'agent' ? 'soft' : 'ghost'"
                @click="filterActor('agent')"
            />
        </div>

        <UEmpty
            v-if="logs.data.length === 0"
            icon="i-lucide-scroll-text"
            title="Nothing recorded yet"
        />

        <div
            v-else
            class="divide-y divide-default rounded-lg border border-default"
        >
            <div
                v-for="log in logs.data"
                :key="log.id"
                class="flex items-center justify-between p-3"
            >
                <div class="flex items-center gap-3">
                    <UIcon
                        :name="
                            log.actor_type === 'agent'
                                ? 'i-lucide-bot'
                                : 'i-lucide-user'
                        "
                        :class="
                            log.actor_type === 'agent'
                                ? 'text-primary'
                                : 'text-muted'
                        "
                        class="size-5"
                    />
                    <div>
                        <p class="text-sm font-medium">{{ log.action }}</p>
                        <p class="text-xs text-muted">
                            {{ log.user?.name ?? 'system' }}
                            <span v-if="log.subject_type">
                                · {{ log.subject_type }}</span
                            >
                            <span v-if="log.payload">
                                · {{ JSON.stringify(log.payload) }}</span
                            >
                        </p>
                    </div>
                </div>
                <span class="text-xs text-muted">{{ log.created_at }}</span>
            </div>
        </div>

        <div v-if="logs.links.length > 3" class="flex items-center gap-1">
            <UButton
                v-for="link in logs.links"
                :key="link.label"
                :label="
                    link.label.replace('&laquo;', '«').replace('&raquo;', '»')
                "
                size="xs"
                :variant="link.active ? 'soft' : 'ghost'"
                :disabled="!link.url"
                :to="link.url ?? undefined"
            />
        </div>
    </SettingsLayout>
</template>
