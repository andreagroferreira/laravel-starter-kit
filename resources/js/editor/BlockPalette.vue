<script setup lang="ts">
import { blockTypes, registry, type BlockType } from '@blocks';

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{ pick: [type: BlockType] }>();

function pick(type: BlockType) {
    emit('pick', type);
    open.value = false;
}
</script>

<template>
    <UModal
        v-model:open="open"
        title="Adicionar bloco"
        description="Escolhe o tipo de bloco a inserir na página."
    >
        <template #body>
            <div class="grid gap-3 sm:grid-cols-2">
                <button
                    v-for="type in blockTypes"
                    :key="type"
                    type="button"
                    class="flex items-start gap-3 rounded-lg border border-default p-3 text-left hover:border-primary hover:bg-elevated/40"
                    @click="pick(type)"
                >
                    <UIcon
                        :name="registry[type].icon"
                        class="mt-0.5 size-5 text-primary"
                    />
                    <span class="min-w-0">
                        <span class="block font-medium">{{
                            registry[type].label
                        }}</span>
                        <span class="block text-sm text-muted">{{
                            registry[type].description
                        }}</span>
                    </span>
                </button>
            </div>
        </template>
    </UModal>
</template>
