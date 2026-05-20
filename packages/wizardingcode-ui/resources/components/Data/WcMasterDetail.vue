<script setup lang="ts">
defineProps<{
    masterWidth?: string;
}>();
</script>

<template>
    <div
        class="grid grid-cols-1 gap-4 lg:grid-cols-12"
    >
        <aside
            :class="[
                'lg:col-span-4 xl:col-span-3 rounded-lg border border-default bg-default',
            ]"
            :style="masterWidth ? `width: ${masterWidth}` : undefined"
        >
            <slot name="master" />
        </aside>
        <section
            class="lg:col-span-8 xl:col-span-9 rounded-lg border border-default bg-default"
        >
            <slot name="detail" />
        </section>
    </div>
</template>
