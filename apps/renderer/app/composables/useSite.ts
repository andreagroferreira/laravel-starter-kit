import type { ResolvedSite } from '~~/server/utils/site';

export async function useSite() {
    const { data, error } = await useFetch<ResolvedSite>('/api/site', {
        key: 'site',
    });

    if (error.value || !data.value) {
        throw createError({
            statusCode: 404,
            statusMessage: 'Site não encontrado.',
            fatal: true,
        });
    }

    return {
        site: computed(() => data.value?.site ?? null),
        schema: computed(() => data.value?.schema ?? null),
        forms: computed(() => data.value?.schema?.forms ?? []),
    };
}
