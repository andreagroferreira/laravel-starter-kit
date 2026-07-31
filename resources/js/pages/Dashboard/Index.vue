<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface SiteRow {
    id: string;
    name: string;
    slug: string;
    type: string;
    status: string;
    pages_count: number;
    posts_count: number;
}

const props = defineProps<{
    stats: {
        sites: number;
        pages: number;
        posts: number;
        ai_credits_used: number;
        ai_credits_monthly: number;
    };
    sites: SiteRow[];
}>();

const creditsPercentage = computed(() =>
    props.stats.ai_credits_monthly > 0
        ? Math.min(
              100,
              Math.round(
                  (props.stats.ai_credits_used /
                      props.stats.ai_credits_monthly) *
                      100,
              ),
          )
        : 0,
);

const cards = computed(() => [
    {
        label: 'Sites',
        value: props.stats.sites,
        icon: 'i-lucide-globe',
        to: '/sites',
    },
    { label: 'Pages', value: props.stats.pages, icon: 'i-lucide-file' },
    { label: 'Posts', value: props.stats.posts, icon: 'i-lucide-newspaper' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <UDashboardPanel id="dashboard">
        <template #header>
            <UDashboardNavbar title="Dashboard">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="New site"
                        icon="i-lucide-plus"
                        to="/sites"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="space-y-6 p-4 lg:p-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <UPageCard
                        v-for="card in cards"
                        :key="card.label"
                        :to="card.to"
                        variant="subtle"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-muted">
                                    {{ card.label }}
                                </p>
                                <p class="mt-1 text-3xl font-semibold">
                                    {{ card.value }}
                                </p>
                            </div>
                            <UIcon
                                :name="card.icon"
                                class="size-8 text-primary/60"
                            />
                        </div>
                    </UPageCard>

                    <UPageCard variant="subtle">
                        <div>
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-muted">AI credits</p>
                                <UIcon
                                    name="i-lucide-sparkles"
                                    class="size-5 text-primary/60"
                                />
                            </div>
                            <p class="mt-1 text-3xl font-semibold">
                                {{ stats.ai_credits_used
                                }}<span class="text-base text-muted"
                                    >/{{ stats.ai_credits_monthly }}</span
                                >
                            </p>
                            <UProgress
                                :model-value="creditsPercentage"
                                class="mt-2"
                                size="sm"
                            />
                        </div>
                    </UPageCard>
                </div>

                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-semibold">Your sites</h2>
                        <UButton
                            label="View all"
                            variant="link"
                            to="/sites"
                            trailing-icon="i-lucide-arrow-right"
                        />
                    </div>

                    <UEmpty
                        v-if="sites.length === 0"
                        icon="i-lucide-globe"
                        title="No sites yet"
                        description="Create your first site and start publishing."
                        :actions="[
                            {
                                label: 'Create site',
                                icon: 'i-lucide-plus',
                                to: '/sites',
                            },
                        ]"
                    />

                    <div
                        v-else
                        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <UPageCard
                            v-for="site in sites"
                            :key="site.id"
                            :title="site.name"
                            :description="`${site.pages_count} pages · ${site.posts_count} posts`"
                            :to="`/sites/${site.id}`"
                            variant="outline"
                        >
                            <div class="flex items-center gap-2">
                                <UBadge
                                    :label="site.type"
                                    color="neutral"
                                    variant="subtle"
                                />
                                <UBadge
                                    :label="site.status"
                                    :color="
                                        site.status === 'published'
                                            ? 'success'
                                            : 'neutral'
                                    "
                                    variant="subtle"
                                />
                            </div>
                        </UPageCard>
                    </div>
                </div>
            </div>
        </template>
    </UDashboardPanel>
</template>
