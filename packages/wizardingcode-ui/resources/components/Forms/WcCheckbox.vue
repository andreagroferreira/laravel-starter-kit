<script setup lang="ts">
defineOptions({ inheritAttrs: false });

defineProps<{
    modelValue?: boolean;
    label?: string;
    description?: string;
    error?: string;
    required?: boolean;
}>();

defineEmits<{
    'update:modelValue': [value: boolean];
}>();
</script>

<template>
    <UFormField :error="error" :required="required">
        <UCheckbox
            :model-value="modelValue"
            :label="label"
            :description="description"
            v-bind="$attrs"
            @update:model-value="
                (v: boolean) => $emit('update:modelValue', v)
            "
        />
    </UFormField>
</template>
