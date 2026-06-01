import '../css/app.css';
import './echo';

import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {createApp, h, type DefineComponent} from 'vue';
import {createHead} from '@unhead/vue/client';
import UApp from '@nuxt/ui/components/App.vue';
import ui from '@nuxt/ui/vue-plugin';

const appName = import.meta.env.VITE_APP_NAME || 'GBA';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({el, App, props, plugin}) {
        const head = createHead();

        createApp({render: () => h(UApp, () => h(App, props))})
            .use(plugin)
            .use(ui)
            .use(head)
            .mount(el);
    },
    progress: {
        color: '#10b981',
    },
});
