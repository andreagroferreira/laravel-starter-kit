import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineNuxtConfig({
    compatibilityDate: '2026-08-01',

    ssr: true,

    nitro: {
        preset: 'cloudflare-pages',
    },

    // The blocks package is the contract shared with the backoffice editor.
    alias: {
        '@blocks': fileURLToPath(new URL('../../packages/blocks/src', import.meta.url)),
    },

    runtimeConfig: {
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE ?? 'http://cms-wiz-kimi-test.test',
        },
    },

    css: ['~/assets/css/site.css'],

    vite: {
        plugins: [tailwindcss()],
        server: {
            // Every {slug}.wizcms.test hostname is a valid site in dev.
            allowedHosts: ['.wizcms.test', '.localhost'],
        },
    },

    devtools: { enabled: false },
});
