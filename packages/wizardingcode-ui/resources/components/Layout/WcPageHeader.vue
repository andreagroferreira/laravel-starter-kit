<script setup lang="ts">
interface Crumb {
    label: string;
    to?: string;
    icon?: string;
}

defineProps<{
    title: string;
    description?: string;
    icon?: string;
    breadcrumbs?: Crumb[];
}>();
</script>

<template>
    <header
        class="flex flex-col gap-4 border-b border-default pb-6 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="min-w-0 flex-1">
            <nav
                v-if="breadcrumbs?.length"
                class="mb-2 flex items-center gap-1 text-xs text-muted"
                aria-label="Breadcrumb"
            >
                <template
                    v-for="(crumb, idx) in breadcrumbs"
                    :key="idx"
                >
                    <UIcon
                        v-if="idx > 0"
                        name="i-lucide-chevron-right"
                        class="text-muted"
                    />
                    <component
                        :is="crumb.to ? 'a' : 'span'"
                        :href="crumb.to"
                        class="hover:text-default"
                    >
                        {{ crumb.label }}
                    </component>
                </template>
            </nav>

            <div class="flex items-center gap-2">
                <UIcon
                    v-if="icon"
                    :name="icon"
                    class="text-2xl text-highlighted"
                />
                <h1 class="truncate text-2xl font-semibold text-highlighted">
                    {{ title }}
                </h1>
            </div>
            <p
                v-if="description"
                class="mt-1 text-sm text-muted"
            >
                {{ description }}
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
