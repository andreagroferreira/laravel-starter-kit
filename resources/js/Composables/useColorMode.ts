import { onMounted, ref, watch, type Ref } from 'vue';

export type ColorMode = 'light' | 'dark' | 'system';

const COOKIE_NAME = 'color-mode';
const COOKIE_MAX_AGE_DAYS = 365;

const mode = ref<ColorMode>('system');
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

function applyMode(m: ColorMode): void {
    const dark = m === 'dark' || (m === 'system' && prefersDark());

    resolved.value = dark ? 'dark' : 'light';

    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', dark);
    }
}

function setMode(m: ColorMode): void {
    mode.value = m;
    writeCookie(COOKIE_NAME, m, COOKIE_MAX_AGE_DAYS);
    applyMode(m);
}

let mediaQueryWired = false;

/**
 * Initialise color mode from the cookie BEFORE the first render. Called from
 * `app.ts` to avoid the dark/light flash on Inertia SSR or hydration.
 */
export function setupColorMode(): void {
    const fromCookie = readCookie(COOKIE_NAME);
    if (
        fromCookie === 'light' ||
        fromCookie === 'dark' ||
        fromCookie === 'system'
    ) {
        mode.value = fromCookie;
    }

    applyMode(mode.value);

    if (
        !mediaQueryWired &&
        typeof window !== 'undefined' &&
        typeof window.matchMedia === 'function'
    ) {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', () => {
            if (mode.value === 'system') {
                applyMode('system');
            }
        });
        mediaQueryWired = true;
    }
}

/**
 * Composable for components/pages. Returns reactive mode + resolved + setter.
 */
export function useColorMode(): {
    mode: Ref<ColorMode>;
    resolved: Ref<'light' | 'dark'>;
    setMode: (m: ColorMode) => void;
} {
    onMounted(() => {
        // Re-apply on mount in case the cookie was changed in another tab.
        applyMode(mode.value);
    });

    watch(mode, applyMode);

    return { mode, resolved, setMode };
}
