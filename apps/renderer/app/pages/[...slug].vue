<script setup lang="ts">
import { BlockRenderer, RESOLVE_FORM, SUBMIT_LEAD } from '@blocks';
import { useSite } from '~/composables/useSite';

const route = useRoute();
const config = useRuntimeConfig();
const { site, schema, forms } = await useSite();

const slug = computed(() => {
    const raw = Array.isArray(route.params.slug)
        ? route.params.slug.join('/')
        : String(route.params.slug ?? '');

    return raw === '' ? 'home' : raw;
});

const page = computed(
    () => schema.value?.pages?.find((item) => item.slug === slug.value) ?? null,
);

if (!page.value) {
    throw createError({
        statusCode: 404,
        statusMessage: 'Página não encontrada.',
        fatal: true,
    });
}

const url = useRequestURL();

useSeoMeta({
    title: page.value.seo?.title ?? page.value.title,
    description: page.value.seo?.description,
    ogTitle: page.value.seo?.title ?? page.value.title,
    ogDescription: page.value.seo?.description,
    ogUrl: url.href,
    ogType: 'website',
});

useHead({
    link: [{ rel: 'canonical', href: url.origin + url.pathname }],
});

// Blocks are agnostic: the renderer teaches the form block how to find a
// form definition and where to post leads.
provide(RESOLVE_FORM, (id: string) => forms.value.find((form) => form.id === id) ?? null);

provide(SUBMIT_LEAD, async (formId: string, values: Record<string, string>) => {
    await $fetch(
        `/api/v1/public/sites/${site.value?.id}/forms/${formId}/submissions`,
        {
            baseURL: config.public.apiBase,
            method: 'POST',
            body: values,
        },
    );
});
</script>

<template>
    <main>
        <BlockRenderer
            v-for="(block, index) in page?.blocks ?? []"
            :key="index"
            :type="block.type"
            :content="block.content"
        />
    </main>
</template>
