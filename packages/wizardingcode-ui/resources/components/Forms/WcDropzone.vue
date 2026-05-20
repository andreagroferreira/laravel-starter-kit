<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    accept?: string;
    maxSize?: number;
    multiple?: boolean;
    modelValue?: File[];
}>();

const emit = defineEmits<{
    'update:modelValue': [files: File[]];
}>();

const dragover = ref(false);

function onDrop(e: DragEvent): void {
    dragover.value = false;
    if (e.dataTransfer?.files) {
        emit('update:modelValue', Array.from(e.dataTransfer.files));
    }
}

function onChange(e: Event): void {
    const input = e.target as HTMLInputElement;
    if (input.files) {
        emit('update:modelValue', Array.from(input.files));
    }
}
</script>

<template>
    <label
        :class="[
            'flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-6 py-10 transition-colors cursor-pointer',
            dragover
                ? 'border-primary bg-primary/10'
                : 'border-default bg-muted hover:bg-accented',
        ]"
        @dragover.prevent="dragover = true"
        @dragleave.prevent="dragover = false"
        @drop.prevent="onDrop"
    >
        <UIcon
            name="i-lucide-upload-cloud"
            class="text-4xl text-muted"
        />
        <p class="text-sm text-default">
            <span class="font-medium text-primary">Click to upload</span>
            or drag and drop
        </p>
        <p v-if="accept" class="text-xs text-muted">{{ accept }}</p>
        <input
            type="file"
            :accept="accept"
            :multiple="multiple"
            class="sr-only"
            @change="onChange"
        />
    </label>
</template>
