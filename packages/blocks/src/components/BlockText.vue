<script setup lang="ts">
/**
 * Text primitive shared by every block. In the renderer it is inert
 * markup; in the editor (`editable`) it becomes a contenteditable that
 * commits on blur — never on keystroke, so Vue never fights the caret.
 */
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        value: string;
        tag?: string;
        editable?: boolean;
        placeholder?: string;
        multiline?: boolean;
    }>(),
    { tag: 'p', editable: false, placeholder: '', multiline: false },
);

const emit = defineEmits<{ commit: [value: string] }>();

const el = ref<HTMLElement | null>(null);

watch(
    () => props.value,
    (value) => {
        if (el.value && el.value.innerText !== value) {
            el.value.innerText = value;
        }
    },
);

function onBlur() {
    if (el.value) {
        emit('commit', el.value.innerText.trim());
    }
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        if (el.value) {
            el.value.innerText = props.value;
        }
        (event.target as HTMLElement).blur();
    }

    if (event.key === 'Enter' && !props.multiline) {
        event.preventDefault();
        (event.target as HTMLElement).blur();
    }
}
</script>

<template>
    <component
        :is="tag"
        v-if="editable"
        ref="el"
        contenteditable="plaintext-only"
        class="cursor-text rounded-sm outline-none focus:ring-2 focus:ring-[color:var(--site-accent)]"
        :data-placeholder="placeholder"
        :class="value === '' ? 'min-h-[1em] opacity-50' : ''"
        @blur="onBlur"
        @keydown="onKeydown"
        >{{ value }}</component
    >
    <component :is="tag" v-else-if="value !== ''">{{ value }}</component>
</template>
