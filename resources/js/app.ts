import '../css/app.css';
import './echo';
import { createInertiaApp } from '@inertiajs/vue3';
import UApp from '@nuxt/ui/components/App.vue';
import ui from '@nuxt/ui/vue-plugin';
import { createHead } from '@unhead/vue/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, type DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'WizardInCode';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const head = createHead();

        createApp({ render: () => h(UApp, () => h(App, props)) })
            .use(plugin)
            .use(ui)
            .use(head)
            .mount(el);
    },
    progress: {
        color: 'var(--ui-primary)',
    },
});
