/**
 * Nuxt UI 4 — runtime tokens (palette + component overrides).
 *
 * Per spec §11.1.5: palette is a placeholder (`violet` + `slate`) pending
 * Valentina (Brand) finalization. ALL semantic tokens declared explicitly
 * so light/dark behavior is deterministic (spec §6.6.E).
 *
 * NOTE: This project runs Nuxt UI 4 in Vue/Inertia mode (not full Nuxt),
 * so the Nuxt-only `defineAppConfig` macro is not auto-imported. We define
 * a local identity helper that preserves the typed object literal — this
 * file is read by the @nuxt/ui/vite plugin when wired via the `ui()` call
 * in vite.config.ts (Task B2). Keeping the file at root matches the Nuxt
 * convention so future migration to full Nuxt is a zero-touch move.
 */
const defineAppConfig = <T extends Record<string, unknown>>(config: T): T =>
    config;

export default defineAppConfig({
    ui: {
        colors: {
            // Placeholder palette (Plan 2). Final colors land before Plan 4 (Install Wizard).
            primary: 'violet',
            neutral: 'slate',
        },
        button: {
            slots: {
                base: 'font-medium',
            },
        },
    },
});
