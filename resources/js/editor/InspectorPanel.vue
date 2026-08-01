<script setup lang="ts">
import { registry, type FieldDefinition } from '@blocks';
import { computed, ref } from 'vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import MediaPickerModal from '@/components/shared/MediaPickerModal.vue';
import type { EditorBlock } from './useEditorStore';

const props = defineProps<{
    block: EditorBlock | null;
    forms: { id: string; name: string }[];
}>();

const emit = defineEmits<{ update: [key: string, value: unknown] }>();

const definition = computed(() =>
    props.block ? registry[props.block.type] : null,
);

const pickerOpen = ref(false);
const pickerTarget = ref<{
    key: string;
    index?: number;
    itemKey?: string;
} | null>(null);

function value(key: string): string {
    const raw = props.block?.content[key];

    return typeof raw === 'string' ? raw : '';
}

function rows(key: string): Record<string, unknown>[] {
    const raw = props.block?.content[key];

    return Array.isArray(raw) ? (raw as Record<string, unknown>[]) : [];
}

function rowValue(key: string, index: number, itemKey: string): string {
    const raw = rows(key)[index]?.[itemKey];

    return typeof raw === 'string' ? raw : '';
}

function setRowValue(
    key: string,
    index: number,
    itemKey: string,
    next: unknown,
): void {
    const list = rows(key).map((row, i) =>
        i === index ? { ...row, [itemKey]: next } : row,
    );

    emit('update', key, list);
}

function addRow(field: FieldDefinition): void {
    const blank = Object.fromEntries(
        (field.item ?? []).map((item) => [item.key, '']),
    );

    emit('update', field.key, [...rows(field.key), blank]);
}

function removeRow(key: string, index: number): void {
    emit(
        'update',
        key,
        rows(key).filter((_, i) => i !== index),
    );
}

function openPicker(key: string, index?: number, itemKey?: string): void {
    pickerTarget.value = { key, index, itemKey };
    pickerOpen.value = true;
}

function onPick(asset: { url: string }): void {
    const target = pickerTarget.value;

    if (!target) {
        return;
    }

    if (target.index !== undefined && target.itemKey) {
        setRowValue(target.key, target.index, target.itemKey, asset.url);
    } else {
        emit('update', target.key, asset.url);
    }

    pickerTarget.value = null;
}
</script>

<template>
    <aside
        class="w-full shrink-0 space-y-4 border-default lg:w-80 lg:border-l lg:pl-4"
        aria-label="Propriedades do bloco"
    >
        <div v-if="!block" class="p-4 text-sm text-muted">
            Seleciona um bloco no canvas para editar as suas propriedades.
        </div>

        <template v-else-if="definition">
            <div class="flex items-center gap-2">
                <UIcon :name="definition.icon" class="size-5 text-primary" />
                <h2 class="font-semibold">{{ definition.label }}</h2>
            </div>

            <div
                v-for="field in definition.fields"
                :key="field.key"
                class="space-y-2"
            >
                <UFormField :label="field.label">
                    <UInput
                        v-if="field.kind === 'text' || field.kind === 'url'"
                        :model-value="value(field.key)"
                        class="w-full"
                        @update:model-value="emit('update', field.key, $event)"
                    />

                    <UTextarea
                        v-else-if="field.kind === 'textarea'"
                        :model-value="value(field.key)"
                        :rows="3"
                        class="w-full"
                        @update:model-value="emit('update', field.key, $event)"
                    />

                    <USelect
                        v-else-if="field.kind === 'form-select'"
                        :model-value="value(field.key)"
                        :items="[
                            { label: 'Sem formulário', value: '' },
                            ...forms.map((form) => ({
                                label: form.name,
                                value: form.id,
                            })),
                        ]"
                        class="w-full"
                        @update:model-value="emit('update', field.key, $event)"
                    />

                    <div
                        v-else-if="field.kind === 'media'"
                        class="flex items-center gap-2"
                    >
                        <img
                            v-if="value(field.key)"
                            :src="value(field.key)"
                            alt=""
                            class="size-12 rounded-md object-cover"
                        />
                        <UButton
                            :label="value(field.key) ? 'Trocar' : 'Escolher'"
                            icon="i-lucide-image"
                            variant="subtle"
                            size="sm"
                            @click="openPicker(field.key)"
                        />
                        <UButton
                            v-if="value(field.key)"
                            icon="i-lucide-x"
                            variant="ghost"
                            size="sm"
                            aria-label="Remover imagem"
                            @click="emit('update', field.key, '')"
                        />
                    </div>

                    <RichTextEditor
                        v-else-if="field.kind === 'rich-text'"
                        :model-value="value(field.key)"
                        @update:model-value="emit('update', field.key, $event)"
                    />
                </UFormField>

                <div v-if="field.kind === 'repeater'" class="space-y-3">
                    <div
                        v-for="(_, index) in rows(field.key)"
                        :key="index"
                        class="space-y-2 rounded-lg border border-default p-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-muted"
                                >#{{ index + 1 }}</span
                            >
                            <UButton
                                icon="i-lucide-trash-2"
                                size="xs"
                                color="error"
                                variant="ghost"
                                aria-label="Remover item"
                                @click="removeRow(field.key, index)"
                            />
                        </div>

                        <UFormField
                            v-for="item in field.item ?? []"
                            :key="item.key"
                            :label="item.label"
                            size="sm"
                        >
                            <UTextarea
                                v-if="item.kind === 'textarea'"
                                :model-value="
                                    rowValue(field.key, index, item.key)
                                "
                                :rows="2"
                                class="w-full"
                                @update:model-value="
                                    setRowValue(
                                        field.key,
                                        index,
                                        item.key,
                                        $event,
                                    )
                                "
                            />
                            <div
                                v-else-if="item.kind === 'media'"
                                class="flex items-center gap-2"
                            >
                                <UButton
                                    label="Escolher"
                                    icon="i-lucide-image"
                                    variant="subtle"
                                    size="xs"
                                    @click="
                                        openPicker(field.key, index, item.key)
                                    "
                                />
                            </div>
                            <UInput
                                v-else
                                :model-value="
                                    rowValue(field.key, index, item.key)
                                "
                                class="w-full"
                                @update:model-value="
                                    setRowValue(
                                        field.key,
                                        index,
                                        item.key,
                                        $event,
                                    )
                                "
                            />
                        </UFormField>
                    </div>

                    <UButton
                        label="Adicionar item"
                        icon="i-lucide-plus"
                        variant="soft"
                        size="sm"
                        @click="addRow(field)"
                    />
                </div>
            </div>
        </template>

        <MediaPickerModal v-model:open="pickerOpen" @pick="onPick" />
    </aside>
</template>
