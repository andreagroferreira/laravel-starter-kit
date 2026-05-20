import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ui from '@nuxt/ui/vue-plugin';
import { createPinia } from 'pinia';
import { createApp, h, type DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'WizardingCode';

void createInertiaApp({
    title: (title) => `${title} · ${appName}`,
    resolve: (name) =>
        resolvePageComponent<DefineComponent>(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(createPinia());
        app.use(ui);
        app.mount(el);
    },
    progress: { color: '#7c3aed' },
});
