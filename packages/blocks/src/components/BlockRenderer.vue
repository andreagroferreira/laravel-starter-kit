<script setup lang="ts">
import { computed } from 'vue';
import { isBlockType, parseContent, registry } from '../registry';

const props = defineProps<{
    type: string;
    content: Record<string, unknown> | null;
    editable?: boolean;
}>();

const emit = defineEmits<{ edit: [field: string, value: string] }>();

const definition = computed(() =>
    isBlockType(props.type) ? registry[props.type] : null,
);

const parsed = computed(() =>
    isBlockType(props.type) ? parseContent(props.type, props.content) : {},
);
</script>

<template>
    <component
        :is="definition.component"
        v-if="definition"
        :content="parsed"
        :editable="editable"
        @edit="(field: string, value: string) => emit('edit', field, value)"
    />
    <div v-else class="p-6 text-center text-sm opacity-60">
        Bloco desconhecido: {{ type }}
    </div>
</template>
