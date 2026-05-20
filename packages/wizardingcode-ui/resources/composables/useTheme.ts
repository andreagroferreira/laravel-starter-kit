import { ref, readonly } from 'vue';

export type ThemeMode = 'light' | 'dark' | 'system';

const mode = ref<ThemeMode>('system');

/**
 * Color-mode helper.
 *
 * Placeholder for Plan 2 — full SSR-safe, cookie-persisted color mode
 * lands in Task F1. The shape is stable so consumers can wire it now.
 */
export function useTheme(): {
    mode: Readonly<typeof mode>;
    setMode: (m: ThemeMode) => void;
} {
    function setMode(m: ThemeMode): void {
        mode.value = m;
    }

    return { mode: readonly(mode), setMode };
}
