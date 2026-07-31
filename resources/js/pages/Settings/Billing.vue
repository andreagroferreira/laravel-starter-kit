<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import AppLayout from '../../layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

interface PlanDetails {
    name: string;
    stripe_price_id: string | null;
    ai_credits_monthly: number;
    sites: number;
}

defineProps<{
    plan: string;
    planDetails: PlanDetails;
    availablePlans: Record<string, PlanDetails>;
    subscribed: boolean;
    onGracePeriod: boolean;
    endsAt: string | null;
}>();
</script>

<template>
    <Head title="Billing" />

    <SettingsLayout>
        <UPageCard
            title="Current plan"
            :description="`You are on the ${planDetails.name} plan.`"
            variant="naked"
            orientation="horizontal"
            class="mb-4"
        >
            <UButton
                v-if="subscribed"
                label="Manage subscription"
                color="neutral"
                href="/settings/billing/portal"
                class="w-fit lg:ms-auto"
            />
        </UPageCard>

        <UAlert
            v-if="onGracePeriod"
            :title="`Your subscription ends on ${endsAt}.`"
            color="warning"
            variant="subtle"
        />

        <UPageCard variant="subtle">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-muted">AI credits / month</p>
                    <p class="text-2xl font-semibold">
                        {{ planDetails.ai_credits_monthly.toLocaleString() }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted">Sites</p>
                    <p class="text-2xl font-semibold">
                        {{ planDetails.sites }}
                    </p>
                </div>
            </div>
        </UPageCard>

        <div class="grid gap-4 sm:grid-cols-3">
            <UPageCard
                v-for="(details, key) in availablePlans"
                :key="key"
                :title="details.name"
                :description="`${details.ai_credits_monthly.toLocaleString()} AI credits · ${details.sites} sites`"
                variant="outline"
                :highlight="key === plan"
            >
                <UButton
                    v-if="key !== plan && details.stripe_price_id"
                    :label="`Upgrade to ${details.name}`"
                    block
                    :href="`/settings/billing/checkout/${key}`"
                />
                <UBadge
                    v-else-if="key === plan"
                    label="Current plan"
                    color="primary"
                    variant="subtle"
                />
            </UPageCard>
        </div>
    </SettingsLayout>
</template>
