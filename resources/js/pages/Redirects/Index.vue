<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface RedirectRow {
    id: string;
    from_path: string;
    to_path: string;
    status_code: number;
}

const props = defineProps<{
    site: { id: string; name: string; slug: string };
    redirects: RedirectRow[];
}>();

const open = ref(false);
const form = reactive({
    from_path: '/',
    to_path: '/',
    status_code: '301',
});

function createRedirect() {
    router.post(
        `/sites/${props.site.id}/redirects`,
        { ...form },
        {
            onSuccess: () => {
                open.value = false;
                form.from_path = '/';
                form.to_path = '/';
                form.status_code = '301';
            },
        },
    );
}

function destroy(id: string) {
    router.delete(`/sites/${props.site.id}/redirects/${id}`);
}
</script>

<template>
    <Head :title="`Redirects — ${site.name}`" />

    <UDashboardPanel id="redirects">
        <template #header>
            <UDashboardNavbar :title="`Redirects — ${site.name}`">
                <template #leading>
                    <UDashboardSidebarCollapse />
                    <UButton
                        icon="i-lucide-arrow-left"
                        variant="ghost"
                        :to="`/sites/${site.id}`"
                    />
                </template>
                <template #right>
                    <UButton
                        label="New redirect"
                        icon="i-lucide-plus"
                        @click="open = true"
                    />
                </template>
            </UDashboardNavbar>
        </template>

        <template #body>
            <div class="mx-auto w-full max-w-3xl p-4 lg:p-6">
                <UEmpty
                    v-if="redirects.length === 0"
                    icon="i-lucide-corner-up-right"
                    title="No redirects"
                />

                <div
                    v-else
                    class="divide-y divide-default rounded-lg border border-default"
                >
                    <div
                        v-for="redirect in redirects"
                        :key="redirect.id"
                        class="flex items-center justify-between p-3"
                    >
                        <div class="flex items-center gap-3">
                            <UBadge
                                :label="String(redirect.status_code)"
                                variant="subtle"
                            />
                            <code class="text-sm">{{
                                redirect.from_path
                            }}</code>
                            <UIcon
                                name="i-lucide-arrow-right"
                                class="size-4 text-muted"
                            />
                            <code class="text-sm text-primary">{{
                                redirect.to_path
                            }}</code>
                        </div>
                        <UButton
                            icon="i-lucide-trash-2"
                            size="xs"
                            color="error"
                            variant="ghost"
                            @click="destroy(redirect.id)"
                        />
                    </div>
                </div>
            </div>

            <UModal v-model:open="open" title="New redirect">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createRedirect">
                        <UFormField label="From path" required>
                            <UInput v-model="form.from_path" class="w-full" />
                        </UFormField>
                        <UFormField label="To path" required>
                            <UInput v-model="form.to_path" class="w-full" />
                        </UFormField>
                        <UFormField label="Status code">
                            <USelect
                                v-model="form.status_code"
                                :items="['301', '302', '307', '308']"
                                class="w-full"
                            />
                        </UFormField>
                        <UButton type="submit" label="Create redirect" block />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
