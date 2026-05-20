import { HstVue } from '@histoire/plugin-vue';
import path from 'node:path';
import { defineConfig } from 'histoire';

/**
 * Histoire automatically loads `vite.config.ts` and merges it. That brings
 * the project's `@nuxt/ui/vite` plugin (which sets up `#imports` etc.) and
 * Tailwind. We just need to drop the Laravel plugin — it expects a Laravel
 * manifest and breaks histoire's HTML entry generation.
 */
export default defineConfig({
    plugins: [HstVue()],
    setupFile: 'resources/js/histoire.setup.ts',
    storyMatch: [
        'resources/js/**/*.story.vue',
        'packages/wizardingcode-ui/resources/components/**/*.story.vue',
    ],
    storyIgnored: [
        '**/node_modules/**',
        '**/.histoire/**',
        '**/vendor/**',
    ],
    viteIgnorePlugins: ['laravel', 'laravel:assets', 'laravel:fonts'],
    /**
     * Force `@nuxt/ui` (and friends) to be inlined through Vite during the
     * story-collection phase so the `nuxt:ui` plugin can resolve `#imports`
     * and `#build/*` virtuals. Without this, vite-node falls back to Node's
     * native resolver which has no knowledge of these aliases.
     */
    viteNodeInlineDeps: [
        /@nuxt\/ui/,
        /@nuxt\/icon/,
        /@iconify\//,
        /@unovis\//,
        /@inertiajs\//,
        /@internationalized\//,
    ],
    vite: {
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
                '@wizardingcode/ui': path.resolve(
                    __dirname,
                    'packages/wizardingcode-ui/resources/components',
                ),
            },
        },
    },
    backgroundPresets: [
        { label: 'Light', color: '#ffffff', contrastColor: '#0f172a' },
        { label: 'Dark', color: '#0f172a', contrastColor: '#ffffff' },
    ],
    responsivePresets: [
        { label: 'Mobile', width: 375, height: 800 },
        { label: 'Tablet', width: 820, height: 1180 },
        { label: 'Desktop', width: 1440, height: 900 },
    ],
    theme: {
        title: 'WizardingCode Boilerplate UI',
    },
});
