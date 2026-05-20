import { onMounted, ref, watch, type Ref } from 'vue';

export type ThemeMode = 'light' | 'dark' | 'system';

const COOKIE_NAME = 'color-mode';
const COOKIE_MAX_AGE_DAYS = 365;

const mode = ref<ThemeMode>('system');
const resolved = ref<'light' | 'dark'>('light');

function readCookie(name: string): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`));

    return match
        ? decodeURIComponent(match.split('=').slice(1).join('='))
        : null;
}

function writeCookie(name: string, value: string, days: number): void {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; samesite=lax`;
}

function prefersDark(): boolean {
    return (
        typeof window !== 'undefined' &&
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
    );
}

function applyMode(m: ThemeMode): void {
    const dark = m === 'dark' || (m === 'system' && prefersDark());

    resolved.value = dark ? 'dark' : 'light';

    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', dark);
    }
}

function setMode(m: ThemeMode): void {
    mode.value = m;
    writeCookie(COOKIE_NAME, m, COOKIE_MAX_AGE_DAYS);
    applyMode(m);
}

/**
 * Standalone color-mode helper for the @wizardingcode/ui package.
 *
 * Mirrors the algorithm in `resources/js/Composables/useColorMode.ts` so the
 * package can be consumed without depending on the host application. Plan 5
 * may consolidate these once the package has its own build entrypoint.
 */
export function useTheme(): {
    mode: Ref<ThemeMode>;
    resolved: Ref<'light' | 'dark'>;
    setMode: (m: ThemeMode) => void;
} {
    onMounted(() => {
        applyMode(mode.value);
    });

    watch(mode, applyMode);

    return { mode, resolved, setMode };
}
