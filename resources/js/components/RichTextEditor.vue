<script setup lang="ts">
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Link.configure({ openOnClick: false }),
        Image,
        Placeholder.configure({
            placeholder: props.placeholder ?? 'Start writing…',
        }),
    ],
    onUpdate: ({ editor: instance }) => {
        emit('update:modelValue', instance.getHTML());
    },
    editorProps: {
        attributes: {
            class: 'prose prose-invert max-w-none focus:outline-none min-h-[320px] px-4 py-3',
        },
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function setLink() {
    if (!editor.value) return;

    const previousUrl = editor.value.getAttributes('link').href as
        | string
        | undefined;
    const url = window.prompt('URL', previousUrl ?? 'https://');

    if (url === null) return;

    if (url === '') {
        editor.value.chain().focus().unsetLink().run();
        return;
    }

    editor.value.chain().focus().setLink({ href: url }).run();
}
</script>

<template>
    <div class="rounded-lg border border-default bg-default">
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b border-default p-2"
        >
            <UButton
                icon="i-lucide-bold"
                size="xs"
                :variant="editor.isActive('bold') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleBold().run()"
            />
            <UButton
                icon="i-lucide-italic"
                size="xs"
                :variant="editor.isActive('italic') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleItalic().run()"
            />
            <UButton
                icon="i-lucide-strikethrough"
                size="xs"
                :variant="editor.isActive('strike') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleStrike().run()"
            />
            <USeparator orientation="vertical" class="mx-1 h-5" />
            <UButton
                icon="i-lucide-heading-2"
                size="xs"
                :variant="
                    editor.isActive('heading', { level: 2 }) ? 'soft' : 'ghost'
                "
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
            />
            <UButton
                icon="i-lucide-heading-3"
                size="xs"
                :variant="
                    editor.isActive('heading', { level: 3 }) ? 'soft' : 'ghost'
                "
                @click="
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                "
            />
            <USeparator orientation="vertical" class="mx-1 h-5" />
            <UButton
                icon="i-lucide-list"
                size="xs"
                :variant="editor.isActive('bulletList') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleBulletList().run()"
            />
            <UButton
                icon="i-lucide-list-ordered"
                size="xs"
                :variant="editor.isActive('orderedList') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleOrderedList().run()"
            />
            <UButton
                icon="i-lucide-text-quote"
                size="xs"
                :variant="editor.isActive('blockquote') ? 'soft' : 'ghost'"
                @click="editor.chain().focus().toggleBlockquote().run()"
            />
            <USeparator orientation="vertical" class="mx-1 h-5" />
            <UButton
                icon="i-lucide-link"
                size="xs"
                variant="ghost"
                @click="setLink"
            />
            <UButton
                icon="i-lucide-undo-2"
                size="xs"
                variant="ghost"
                @click="editor.chain().focus().undo().run()"
            />
            <UButton
                icon="i-lucide-redo-2"
                size="xs"
                variant="ghost"
                @click="editor.chain().focus().redo().run()"
            />
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>
