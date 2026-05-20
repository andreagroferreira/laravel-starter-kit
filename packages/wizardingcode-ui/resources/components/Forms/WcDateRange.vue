<script setup lang="ts">
import { computed } from 'vue';

export interface WcDateRangeValue {
    start: Date | null;
    end: Date | null;
}

const props = defineProps<{
    modelValue: WcDateRangeValue;
    label?: string;
    error?: string;
    hint?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: WcDateRangeValue];
}>();

function fmt(d: Date | null): string {
    if (!d) return '';
    return d.toISOString().slice(0, 10);
}

const startStr = computed<string>({
    get: () => fmt(props.modelValue.start),
    set: (v: string) => {
        emit('update:modelValue', {
            start: v ? new Date(v) : null,
            end: props.modelValue.end,
        });
    },
});

const endStr = computed<string>({
    get: () => fmt(props.modelValue.end),
    set: (v: string) => {
        emit('update:modelValue', {
            start: props.modelValue.start,
            end: v ? new Date(v) : null,
        });
    },
});
</script>

<template>
    <UFormField :label="label" :error="error" :hint="hint">
        <div class="flex items-center gap-2">
            <UInput
                v-model="startStr"
                type="date"
                class="w-full"
            />
            <span class="text-sm text-muted">to</span>
            <UInput
                v-model="endStr"
                type="date"
                class="w-full"
            />
        </div>
    </UFormField>
</template>
