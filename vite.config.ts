import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import ui from '@nuxt/ui/vite';
import {defineConfig} from 'vite-plus';

export default defineConfig({
    resolve: {
        alias: {
            '@blocks': fileURLToPath(new URL('./packages/blocks/src', import.meta.url)),
        },
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        useTabs: false,
        semi: true,
        singleQuote: true,
        overrides: [
            {
                files: ["**/*.yml"],
                options: {
                    tabWidth: 2,
                },
            },
        ],
        sortTailwindcss: {
            functions: ["clsx", "cn"],
            stylesheet: "resources/css/app.css",
        },
        sortImports: {
            groups: ["builtin", "external", "internal", "parent", "sibling", "index"],
            newlinesBetween: false,
        },
        ignorePatterns: ["resources/views/mail/*"],
    },
    plugins: [
        laravel({
            input: ["resources/js/app.ts"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        ui({
            router: 'inertia',
            ui: {
                colors: {
                    primary: 'green',
                    neutral: 'zinc',
                },
            },
        }),
    ],
    optimizeDeps: {
        exclude: ['@tailwindcss/oxide'],
    },
    build: {
        rollupOptions: {
            external: [/@tailwindcss\/oxide/, /\.node$/],
            output: {
                codeSplitting: false,
            },
        },
    },
});
