import { defineSetupVue3 } from '@histoire/plugin-vue';
import { createPinia } from 'pinia';
import '../css/app.css';

/**
 * Nuxt UI components are auto-registered by `@nuxt/ui/vite` (loaded from
 * the project's root `vite.config.ts` via `unplugin-vue-components`).
 * There is no standalone Vue 3 plugin to install here — registering one
 * would conflict with the Vite-time auto-import pipeline.
 */
export const setupVue3 = defineSetupVue3(({ app }) => {
    app.use(createPinia());
});
