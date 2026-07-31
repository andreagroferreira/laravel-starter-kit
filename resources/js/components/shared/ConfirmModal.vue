<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        destructive?: boolean;
    }>(),
    {
        title: 'Tem a certeza?',
        description: 'Esta ação não pode ser revertida.',
        confirmLabel: 'Confirmar',
        cancelLabel: 'Cancelar',
        destructive: true,
    },
);

const emit = defineEmits<{ close: [confirmed: boolean] }>();
</script>

<template>
    <UModal
        :title="props.title"
        :description="props.description"
        :close="false"
        :ui="{ footer: 'justify-end gap-2' }"
    >
        <template #footer>
            <UButton
                :label="props.cancelLabel"
                color="neutral"
                variant="ghost"
                @click="emit('close', false)"
            />
            <UButton
                :label="props.confirmLabel"
                :color="props.destructive ? 'error' : 'primary'"
                @click="emit('close', true)"
            />
        </template>
    </UModal>
</template>
