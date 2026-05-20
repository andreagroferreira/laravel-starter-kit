<script setup lang="ts">
import WcPageHeader from './WcPageHeader.vue';
</script>

<template>
    <Story
        title="Layout/WcPageHeader"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcPageHeader
                title="Customers"
                description="Manage your customer base and track engagement."
                icon="i-lucide-users"
            />
        </Variant>

        <Variant title="With breadcrumbs">
            <WcPageHeader
                title="John Doe"
                description="Customer details and order history."
                icon="i-lucide-user"
                :breadcrumbs="[
                    { label: 'Dashboard', to: '/admin' },
                    { label: 'Customers', to: '/admin/customers' },
                    { label: 'John Doe' },
                ]"
            />
        </Variant>

        <Variant title="With actions slot">
            <WcPageHeader
                title="Orders"
                description="All recent orders from your store."
                icon="i-lucide-shopping-cart"
            >
                <template #actions>
                    <UButton color="primary" icon="i-lucide-plus">
                        New order
                    </UButton>
                </template>
            </WcPageHeader>
        </Variant>

        <Variant title="Minimal (title only)">
            <WcPageHeader title="Settings" />
        </Variant>
    </Story>
</template>
