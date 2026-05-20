<script setup lang="ts">
import WcEmptyState from './WcEmptyState.vue';
</script>

<template>
    <Story
        title="Data/WcEmptyState"
        :layout="{ type: 'single', iframe: true }"
    >
        <Variant title="Default">
            <WcEmptyState
                title="No customers yet"
                description="Add your first customer to get started."
                action-label="Add customer"
                icon="i-lucide-users"
            />
        </Variant>

        <Variant title="Minimal (no action)">
            <WcEmptyState title="All caught up" />
        </Variant>

        <Variant title="Custom icon">
            <WcEmptyState
                title="No notifications"
                description="You're all caught up."
                icon="i-lucide-bell-off"
            />
        </Variant>

        <Variant title="Long description">
            <WcEmptyState
                title="No results found"
                description="We could not find anything matching your filters. Try changing the search query or removing some filters to see more results."
                action-label="Reset filters"
                icon="i-lucide-search-x"
            />
        </Variant>
    </Story>
</template>
