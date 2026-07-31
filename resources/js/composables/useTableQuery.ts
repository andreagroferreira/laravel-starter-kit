import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive, watch } from 'vue';

export interface TableQueryOptions {
    /** URL to visit; defaults to the current location. */
    url?: string;
    /** Inertia props to reload; keeps payloads small. */
    only?: string[];
    /** Extra initial filters, e.g. { status: '' }. */
    filters?: Record<string, string>;
}

/**
 * Server-side search/filter/pagination state synced to the query string.
 * Search is debounced; every change resets to page 1 except page changes.
 */
export function useTableQuery(initial: TableQueryOptions = {}) {
    const state = reactive({
        search: new URLSearchParams(window.location.search).get('search') ?? '',
        page: Number(
            new URLSearchParams(window.location.search).get('page') ?? '1',
        ),
        filters: {
            ...initial.filters,
            ...Object.fromEntries(
                Object.keys(initial.filters ?? {})
                    .map((key) => [
                        key,
                        new URLSearchParams(window.location.search).get(key),
                    ])
                    .filter(
                        (entry): entry is [string, string] => entry[1] !== null,
                    ),
            ),
        } as Record<string, string>,
    });

    function visit(): void {
        const params: Record<string, string> = {};

        if (state.search) {
            params.search = state.search;
        }

        for (const [key, value] of Object.entries(state.filters)) {
            if (value) {
                params[key] = value;
            }
        }

        if (state.page > 1) {
            params.page = String(state.page);
        }

        router.get(initial.url ?? window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: initial.only,
        });
    }

    watchDebounced(
        () => state.search,
        () => {
            state.page = 1;
            visit();
        },
        { debounce: 300 },
    );

    watch(
        () => ({ ...state.filters }),
        () => {
            state.page = 1;
            visit();
        },
        { deep: true },
    );

    function goToPage(page: number): void {
        state.page = page;
        visit();
    }

    return { state, goToPage };
}
