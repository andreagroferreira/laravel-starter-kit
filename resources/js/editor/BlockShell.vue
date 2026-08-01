<script setup lang="ts">
defineProps<{
    label: string;
    selected: boolean;
    isFirst: boolean;
    isLast: boolean;
}>();

const emit = defineEmits<{
    select: [];
    moveUp: [];
    moveDown: [];
    duplicate: [];
    remove: [];
    insertAfter: [];
}>();
</script>

<template>
    <div
        class="group relative"
        :class="
            selected
                ? 'ring-2 ring-primary'
                : 'ring-1 ring-transparent hover:ring-primary/40'
        "
        role="group"
        :aria-label="label"
        @click="emit('select')"
    >
        <div
            class="pointer-events-none absolute -top-3 left-3 z-10 flex items-center gap-1 opacity-0 transition-opacity group-hover:pointer-events-auto group-hover:opacity-100"
            :class="selected ? 'pointer-events-auto opacity-100' : ''"
        >
            <span
                class="block-drag-handle cursor-grab rounded-md bg-inverted px-2 py-1 text-xs text-inverted"
                aria-hidden="true"
                >⠿ {{ label }}</span
            >
            <UButton
                icon="i-lucide-chevron-up"
                size="xs"
                color="neutral"
                :disabled="isFirst"
                aria-label="Mover bloco para cima"
                @click.stop="emit('moveUp')"
            />
            <UButton
                icon="i-lucide-chevron-down"
                size="xs"
                color="neutral"
                :disabled="isLast"
                aria-label="Mover bloco para baixo"
                @click.stop="emit('moveDown')"
            />
            <UButton
                icon="i-lucide-copy"
                size="xs"
                color="neutral"
                aria-label="Duplicar bloco"
                @click.stop="emit('duplicate')"
            />
            <UButton
                icon="i-lucide-trash-2"
                size="xs"
                color="error"
                aria-label="Remover bloco"
                @click.stop="emit('remove')"
            />
        </div>

        <slot />

        <div
            class="absolute inset-x-0 -bottom-3 z-10 flex justify-center opacity-0 transition-opacity group-hover:opacity-100"
        >
            <UButton
                icon="i-lucide-plus"
                size="xs"
                color="primary"
                aria-label="Inserir bloco a seguir"
                @click.stop="emit('insertAfter')"
            />
        </div>
    </div>
</template>
