import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import ui from '@nuxt/ui/vite';
import path from 'node:path';
import {defineConfig} from 'vite-plus';

export default defineConfig({
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
            input: ['resources/js/app.ts', 'resources/css/app.css'],
            ssr: 'resources/js/ssr.ts',
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
        tailwindcss(),
        ui(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@wizardingcode/ui': path.resolve(__dirname, 'packages/wizardingcode-ui/resources/components'),
        },
    },
});
