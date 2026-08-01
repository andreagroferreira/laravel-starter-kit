import { parseContent, registry, type BlockType } from '@blocks';
import { computed, ref } from 'vue';

export interface EditorBlock {
    /** Stable client-side id: server ids are absent for new blocks and
     *  the type is not unique, so neither can key the list. */
    uid: string;
    type: BlockType;
    content: Record<string, unknown>;
}

interface Snapshot {
    blocks: EditorBlock[];
}

function uid(): string {
    return crypto.randomUUID();
}

export function useEditorStore(
    initial: { type: string; content: Record<string, unknown> | null }[],
) {
    const blocks = ref<EditorBlock[]>(
        initial
            .filter(
                (
                    block,
                ): block is {
                    type: BlockType;
                    content: Record<string, unknown> | null;
                } => block.type in registry,
            )
            .map((block) => ({
                uid: uid(),
                type: block.type,
                content: parseContent(block.type, block.content),
            })),
    );

    const selectedUid = ref<string | null>(null);
    const dirty = ref(false);

    const undoStack = ref<Snapshot[]>([]);
    const redoStack = ref<Snapshot[]>([]);

    const selected = computed(
        () =>
            blocks.value.find((block) => block.uid === selectedUid.value) ??
            null,
    );

    function snapshot(): Snapshot {
        return { blocks: structuredClone(blocks.value) };
    }

    /** Commit one logical operation to history (never per keystroke). */
    function commit(mutate: () => void): void {
        undoStack.value.push(snapshot());

        if (undoStack.value.length > 50) {
            undoStack.value.shift();
        }

        redoStack.value = [];
        mutate();
        dirty.value = true;
    }

    function undo(): void {
        const previous = undoStack.value.pop();

        if (!previous) {
            return;
        }

        redoStack.value.push(snapshot());
        blocks.value = previous.blocks;
        dirty.value = true;
    }

    function redo(): void {
        const next = redoStack.value.pop();

        if (!next) {
            return;
        }

        undoStack.value.push(snapshot());
        blocks.value = next.blocks;
        dirty.value = true;
    }

    function addBlock(type: BlockType, index?: number): void {
        commit(() => {
            const block: EditorBlock = {
                uid: uid(),
                type,
                content: parseContent(type, registry[type].defaults()),
            };

            blocks.value.splice(index ?? blocks.value.length, 0, block);
            selectedUid.value = block.uid;
        });
    }

    function duplicateBlock(uidToCopy: string): void {
        const index = blocks.value.findIndex(
            (block) => block.uid === uidToCopy,
        );

        if (index === -1) {
            return;
        }

        commit(() => {
            const copy: EditorBlock = {
                uid: uid(),
                type: blocks.value[index].type,
                content: structuredClone(blocks.value[index].content),
            };

            blocks.value.splice(index + 1, 0, copy);
            selectedUid.value = copy.uid;
        });
    }

    function removeBlock(uidToRemove: string): void {
        commit(() => {
            blocks.value = blocks.value.filter(
                (block) => block.uid !== uidToRemove,
            );

            if (selectedUid.value === uidToRemove) {
                selectedUid.value = null;
            }
        });
    }

    function moveBlock(uidToMove: string, delta: number): void {
        const index = blocks.value.findIndex(
            (block) => block.uid === uidToMove,
        );
        const target = index + delta;

        if (index === -1 || target < 0 || target >= blocks.value.length) {
            return;
        }

        commit(() => {
            const [block] = blocks.value.splice(index, 1);
            blocks.value.splice(target, 0, block);
        });
    }

    function reorder(next: EditorBlock[]): void {
        commit(() => {
            blocks.value = next;
        });
    }

    function updateField(
        uidToUpdate: string,
        key: string,
        value: unknown,
    ): void {
        const block = blocks.value.find((item) => item.uid === uidToUpdate);

        if (
            !block ||
            JSON.stringify(block.content[key]) === JSON.stringify(value)
        ) {
            return;
        }

        commit(() => {
            block.content = { ...block.content, [key]: value };
        });
    }

    function select(uidToSelect: string | null): void {
        selectedUid.value = uidToSelect;
    }

    /** Payload shape expected by PUT /sites/{site}/pages/{page}. */
    function toPayload(): { type: string; content: Record<string, unknown> }[] {
        return blocks.value.map((block) => ({
            type: block.type,
            content: block.content,
        }));
    }

    function markClean(): void {
        dirty.value = false;
    }

    return {
        blocks,
        selected,
        selectedUid,
        dirty,
        canUndo: computed(() => undoStack.value.length > 0),
        canRedo: computed(() => redoStack.value.length > 0),
        addBlock,
        duplicateBlock,
        removeBlock,
        moveBlock,
        reorder,
        updateField,
        select,
        undo,
        redo,
        toPayload,
        markClean,
    };
}

export type EditorStore = ReturnType<typeof useEditorStore>;
