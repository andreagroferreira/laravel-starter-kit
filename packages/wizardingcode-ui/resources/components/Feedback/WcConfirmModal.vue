<script setup lang="ts">
defineProps<{
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    confirmColor?: 'primary' | 'error';
    resourceName?: string;
}>();

defineEmits<{
    'update:open': [open: boolean];
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <UModal
        :open="open"
        @update:open="$emit('update:open', $event)"
    >
        <template #content>
            <UCard>
                <template #header>
                    <h3 class="text-lg font-semibold text-highlighted">
                        {{ title }}
                    </h3>
                </template>
                <p v-if="description" class="text-sm text-default">
                    {{ description }}
                </p>
                <template #footer>
                    <div class="flex justify-end gap-3">
                        <UButton
                            variant="ghost"
                            @click="
                                $emit('cancel');
                                $emit('update:open', false);
                            "
                        >
                            {{ cancelLabel ?? 'Cancel' }}
                        </UButton>
                        <UButton
                            :color="confirmColor ?? 'primary'"
                            @click="$emit('confirm')"
                        >
                            {{ confirmLabel ?? 'Confirm' }}
                        </UButton>
                    </div>
                </template>
            </UCard>
        </template>
    </UModal>
</template>
