import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';

export type SaveState = 'idle' | 'saving' | 'saved' | 'error';

/**
 * Debounced autosave with an explicit save escape hatch. Guards against
 * leaving the page with unsaved changes (both browser unload and Inertia
 * navigation).
 */
export function useAutosave(options: {
    url: string;
    dirty: Ref<boolean>;
    payload: () => Record<string, unknown>;
    onSaved: () => void;
    debounceMs?: number;
}) {
    const state = ref<SaveState>('idle');
    const lastSavedAt = ref<Date | null>(null);

    function save(): void {
        if (state.value === 'saving') {
            return;
        }

        state.value = 'saving';

        router.put(options.url, options.payload(), {
            preserveState: true,
            preserveScroll: true,
            only: [],
            onSuccess: () => {
                state.value = 'saved';
                lastSavedAt.value = new Date();
                options.onSaved();
            },
            onError: () => {
                state.value = 'error';
            },
        });
    }

    watchDebounced(
        options.dirty,
        (isDirty) => {
            if (isDirty) {
                save();
            }
        },
        { debounce: options.debounceMs ?? 2000 },
    );

    function warnIfDirty(event: BeforeUnloadEvent): void {
        if (options.dirty.value) {
            event.preventDefault();
        }
    }

    let stopInertiaGuard: (() => void) | null = null;

    onMounted(() => {
        window.addEventListener('beforeunload', warnIfDirty);

        stopInertiaGuard = router.on('before', (event) => {
            if (
                options.dirty.value &&
                event.detail.visit.method === 'get' &&
                !confirm('Tens alterações por guardar. Sair mesmo assim?')
            ) {
                return false;
            }
        });
    });

    onBeforeUnmount(() => {
        window.removeEventListener('beforeunload', warnIfDirty);
        stopInertiaGuard?.();
    });

    return { state, lastSavedAt, save };
}
