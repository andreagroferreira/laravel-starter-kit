<script setup lang="ts">
defineOptions({ inheritAttrs: false });

interface SelectItem {
    label: string;
    value: string | number;
    [key: string]: unknown;
}

defineProps<{
    modelValue?: string | number | null;
    items: SelectItem[] | string[];
    label?: string;
    error?: string;
    hint?: string;
    placeholder?: string;
    required?: boolean;
}>();

defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();
</script>

<template>
    <UFormField
        :label="label"
        :error="error"
        :hint="hint"
        :required="required"
    >
        <USelect
            :model-value="modelValue"
            :items="items"
            :placeholder="placeholder"
            v-bind="$attrs"
            class="w-full"
            @update:model-value="
                (v: string | number | null) => $emit('update:modelValue', v)
            "
        />
    </UFormField>
</template>
