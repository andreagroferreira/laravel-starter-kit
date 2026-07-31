<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import type { Redirect, Site } from '@/types/models';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    site: Pick<Site, 'id' | 'name' | 'slug'>;
    redirects: Redirect[];
}>();

const confirm = useConfirm();

const open = ref(false);
const form = useForm({
    from_path: '/',
    to_path: '/',
    status_code: '301',
});

function createRedirect() {
    form.post(`/sites/${props.site.id}/redirects`, {
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}

async function destroy(redirect: Redirect) {
    const confirmed = await confirm({
        title: `Apagar o redirect ${redirect.from_path}?`,
        confirmLabel: 'Apagar',
    });

    if (confirmed) {
        router.delete(`/sites/${props.site.id}/redirects/${redirect.id}`, {
            preserveScroll: true,
        });
    }
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
                        aria-label="Voltar ao site"
                    />
                </template>
                <template #right>
                    <UButton
                        label="Novo redirect"
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
                    title="Ainda não há redirects"
                    description="Redireciona URLs antigos para os novos caminhos."
                    :actions="[
                        {
                            label: 'Novo redirect',
                            icon: 'i-lucide-plus',
                            onClick: () => (open = true),
                        },
                    ]"
                />

                <div
                    v-else
                    class="divide-y divide-default rounded-lg border border-default"
                >
                    <div
                        v-for="redirect in redirects"
                        :key="redirect.id"
                        class="flex flex-wrap items-center justify-between gap-2 p-3"
                    >
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <UBadge
                                :label="String(redirect.status_code)"
                                variant="subtle"
                            />
                            <code class="truncate text-sm">{{
                                redirect.from_path
                            }}</code>
                            <UIcon
                                name="i-lucide-arrow-right"
                                class="size-4 shrink-0 text-muted"
                            />
                            <code class="truncate text-sm text-primary">{{
                                redirect.to_path
                            }}</code>
                        </div>
                        <UButton
                            icon="i-lucide-trash-2"
                            size="xs"
                            color="error"
                            variant="ghost"
                            aria-label="Apagar redirect"
                            @click="destroy(redirect)"
                        />
                    </div>
                </div>
            </div>

            <UModal v-model:open="open" title="Novo redirect">
                <template #body>
                    <form class="space-y-4" @submit.prevent="createRedirect">
                        <UFormField
                            label="De (caminho antigo)"
                            required
                            :error="form.errors.from_path"
                        >
                            <UInput
                                v-model="form.from_path"
                                class="w-full"
                                autofocus
                            />
                        </UFormField>
                        <UFormField
                            label="Para (caminho novo)"
                            required
                            :error="form.errors.to_path"
                        >
                            <UInput v-model="form.to_path" class="w-full" />
                        </UFormField>
                        <UFormField
                            label="Código HTTP"
                            :error="form.errors.status_code"
                        >
                            <USelect
                                v-model="form.status_code"
                                :items="['301', '302', '307', '308']"
                                class="w-full"
                            />
                        </UFormField>
                        <UButton
                            type="submit"
                            label="Criar redirect"
                            block
                            :loading="form.processing"
                        />
                    </form>
                </template>
            </UModal>
        </template>
    </UDashboardPanel>
</template>
