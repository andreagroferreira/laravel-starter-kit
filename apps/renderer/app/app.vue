<script setup lang="ts">
import { toCssVars } from '@blocks';
import { useSite } from '~/composables/useSite';

const { site, schema } = await useSite();

const styleVars = computed(() =>
    toCssVars((schema.value?.site?.settings as Record<string, unknown>)?.design ?? {}),
);

const menu = computed(() => schema.value?.menus?.main ?? []);

useHead(() => ({
    htmlAttrs: { lang: 'pt-PT' },
    style: [
        {
            innerHTML: `:root{${Object.entries(styleVars.value)
                .map(([key, value]) => `${key}:${value}`)
                .join(';')}}`,
        },
    ],
}));
</script>

<template>
    <div class="min-h-screen">
        <header
            v-if="menu.length"
            class="border-b border-current/10 px-6 py-4 sm:px-10"
        >
            <nav class="mx-auto flex max-w-5xl items-center justify-between">
                <NuxtLink to="/" class="font-semibold">{{ site?.name }}</NuxtLink>
                <ul class="flex gap-5 text-sm">
                    <li v-for="item in menu" :key="item.url">
                        <NuxtLink :to="item.url">{{ item.label }}</NuxtLink>
                    </li>
                </ul>
            </nav>
        </header>

        <NuxtPage />

        <footer
            class="border-t border-current/10 px-6 py-8 text-center text-sm opacity-60 sm:px-10"
        >
            © {{ new Date().getFullYear() }} {{ site?.name }}
        </footer>
    </div>
</template>
