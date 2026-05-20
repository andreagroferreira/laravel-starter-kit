<script setup lang="ts">
defineOptions({ inheritAttrs: false });

defineProps<{
    modelValue?: string | number;
    label?: string;
    error?: string;
    hint?: string;
    required?: boolean;
}>();

defineEmits<{
    'update:modelValue': [value: string | number];
}>();
</script>

<template>
    <UFormField
        :label="label"
        :error="error"
        :hint="hint"
        :required="required"
    >
        <UInput
            :model-value="modelValue"
            v-bind="$attrs"
            class="w-full"
            @update:model-value="
                (v: string | number) => $emit('update:modelValue', v)
            "
        />
    </UFormField>
</template>
