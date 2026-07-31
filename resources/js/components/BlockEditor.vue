<script setup lang="ts">
import RichTextEditor from './RichTextEditor.vue';

interface BlockContent {
    [key: string]: unknown;
}

const props = defineProps<{
    type: string;
    modelValue: BlockContent;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: BlockContent];
}>();

function set(key: string, value: unknown) {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
}

function get(key: string, fallback = ''): string {
    const value = props.modelValue[key];

    return typeof value === 'string' ? value : fallback;
}

function getList(key: string): { title?: string; description?: string }[] {
    const value = props.modelValue[key];

    return Array.isArray(value)
        ? (value as { title?: string; description?: string }[])
        : [];
}

function setListItem(key: string, index: number, field: string, value: string) {
    const list = [...getList(key)];
    list[index] = { ...list[index], [field]: value };
    set(key, list);
}

function addListItem(key: string) {
    set(key, [...getList(key), { title: '', description: '' }]);
}

function removeListItem(key: string, index: number) {
    const list = [...getList(key)];
    list.splice(index, 1);
    set(key, list);
}
</script>

<template>
    <div class="space-y-3">
        <template v-if="type === 'hero'">
            <UFormField label="Heading">
                <UInput
                    :model-value="get('heading')"
                    class="w-full"
                    @update:model-value="set('heading', $event)"
                />
            </UFormField>
            <UFormField label="Subheading">
                <UTextarea
                    :model-value="get('subheading')"
                    class="w-full"
                    @update:model-value="set('subheading', $event)"
                />
            </UFormField>
            <UFormField label="Button label">
                <UInput
                    :model-value="get('cta_label')"
                    class="w-full"
                    @update:model-value="set('cta_label', $event)"
                />
            </UFormField>
            <UFormField label="Button URL">
                <UInput
                    :model-value="get('cta_url')"
                    class="w-full"
                    @update:model-value="set('cta_url', $event)"
                />
            </UFormField>
        </template>

        <template v-else-if="type === 'rich_text'">
            <RichTextEditor
                :model-value="get('html')"
                placeholder="Write…"
                @update:model-value="set('html', $event)"
            />
        </template>

        <template v-else-if="type === 'image'">
            <UFormField label="Image URL">
                <UInput
                    :model-value="get('url')"
                    class="w-full"
                    @update:model-value="set('url', $event)"
                />
            </UFormField>
            <UFormField label="Alt text">
                <UInput
                    :model-value="get('alt')"
                    class="w-full"
                    @update:model-value="set('alt', $event)"
                />
            </UFormField>
            <UFormField label="Caption">
                <UInput
                    :model-value="get('caption')"
                    class="w-full"
                    @update:model-value="set('caption', $event)"
                />
            </UFormField>
        </template>

        <template v-else-if="type === 'cta'">
            <UFormField label="Label">
                <UInput
                    :model-value="get('label')"
                    class="w-full"
                    @update:model-value="set('label', $event)"
                />
            </UFormField>
            <UFormField label="URL">
                <UInput
                    :model-value="get('url')"
                    class="w-full"
                    @update:model-value="set('url', $event)"
                />
            </UFormField>
            <UFormField label="Supporting text">
                <UTextarea
                    :model-value="get('text')"
                    class="w-full"
                    @update:model-value="set('text', $event)"
                />
            </UFormField>
        </template>

        <template
            v-else-if="
                type === 'features' ||
                type === 'testimonials' ||
                type === 'faq' ||
                type === 'pricing'
            "
        >
            <div
                v-for="(item, index) in getList('items')"
                :key="index"
                class="flex items-start gap-2 rounded-lg border border-default p-3"
            >
                <div class="flex-1 space-y-2">
                    <UInput
                        :model-value="item.title"
                        placeholder="Title"
                        class="w-full"
                        @update:model-value="
                            setListItem('items', index, 'title', $event)
                        "
                    />
                    <UTextarea
                        :model-value="item.description"
                        placeholder="Description"
                        :rows="2"
                        class="w-full"
                        @update:model-value="
                            setListItem('items', index, 'description', $event)
                        "
                    />
                </div>
                <UButton
                    icon="i-lucide-trash-2"
                    size="xs"
                    color="error"
                    variant="ghost"
                    @click="removeListItem('items', index)"
                />
            </div>
            <UButton
                label="Add item"
                icon="i-lucide-plus"
                variant="soft"
                block
                @click="addListItem('items')"
            />
        </template>

        <template v-else-if="type === 'form'">
            <UFormField label="Form name">
                <UInput
                    :model-value="get('form_name')"
                    class="w-full"
                    @update:model-value="set('form_name', $event)"
                />
            </UFormField>
            <UFormField label="Submit label">
                <UInput
                    :model-value="get('submit_label')"
                    class="w-full"
                    @update:model-value="set('submit_label', $event)"
                />
            </UFormField>
        </template>

        <template v-else>
            <UTextarea
                :model-value="JSON.stringify(modelValue, null, 2)"
                class="w-full font-mono text-xs"
                :rows="4"
                @update:model-value="
                    (value: string) => {
                        try {
                            emit('update:modelValue', JSON.parse(value));
                        } catch {}
                    }
                "
            />
        </template>
    </div>
</template>
