<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface SiteRow {
    id: string;
    name: string;
    slug: string;
    type: string;
    status: string;
    domain: string | null;
    pages_count: number;
    created_at: string;
}

defineProps<{
    sites: SiteRow[];
}>();

const open = ref(false);
const form = reactive({
    name: '',
    slug: '',
    type: 'site',
    domain: '',
});

function submit() {
    router.post(
        '/sites',
        { ...form, domain: form.domain || null },
        {
            onSuccess: () => {
                open.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Sites" />

    <UDashboardPanel id="sites">
        <template #header>
            <UDashboardNavbar title="Sites">
                <template #leading>
                    <UDashboardSidebarCollapse />
                </template>
                <template #right>
                    <UButton
                        label="New site"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="p-4">
                <UEmpty
                    v-if="sites.length === 0"
                    icon="i-lucide-globe"
                    title="No sites yet"
                    description="Create your first site to get started."
                />

                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <UPageCard
                        v-for="site in sites"
                        :key="site.id"
                        :title="site.name"
                        :description="`${site.slug} · ${site.pages_count} pages`"
                        :to="`/sites/${site.id}`"
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

            <UModal v-model:open="open" title="New site">
                <template #body>
                    <form class="space-y-4" @submit.prevent="submit">
                        <UFormField label="Name" required>
                            <UInput v-model="form.name" class="w-full" />
                        </UFormField>
                        <UFormField label="Slug" required>
                            <UInput v-model="form.slug" class="w-full" />
                        </UFormField>
                        <UFormField label="Type" required>
                            <USelect
                                v-model="form.type"
                                :items="[
                                    { label: 'Site', value: 'site' },
                                    { label: 'Landing page', value: 'landing' },
                                    { label: 'News', value: 'news' },
                                ]"
                                class="w-full"
                            />
                        </UFormField>
                        <UFormField label="Custom domain">
                            <UInput
                                v-model="form.domain"
                                placeholder="optional"
                                class="w-full"
                            />
                        </UFormField>
                        <UButton type="submit" label="Create site" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
