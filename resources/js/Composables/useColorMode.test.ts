// @vitest-environment jsdom
import { beforeEach, describe, expect, it } from 'vitest';
import { setupColorMode, useColorMode } from './useColorMode';

describe('useColorMode', () => {
    beforeEach(() => {
        document.cookie = 'color-mode=; path=/; max-age=0';
        document.documentElement.classList.remove('dark');
    });

    it('defaults to system mode', () => {
        const { mode } = useColorMode();
        expect(mode.value).toBe('system');
    });

    it('persists the chosen mode to the cookie', () => {
        const { setMode } = useColorMode();
        setMode('dark');
        expect(document.cookie).toContain('color-mode=dark');
    });

    it('toggles the dark class on the html element', () => {
        const { setMode } = useColorMode();

        setMode('dark');
        expect(document.documentElement.classList.contains('dark')).toBe(true);

        setMode('light');
        expect(document.documentElement.classList.contains('dark')).toBe(false);
    });

    it('hydrates the mode from the cookie via setupColorMode', () => {
        document.cookie = 'color-mode=dark; path=/';

        setupColorMode();

        const { mode } = useColorMode();
        expect(mode.value).toBe('dark');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });
});
