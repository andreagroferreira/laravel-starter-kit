import { siteForEvent } from '../utils/site';

/**
 * Proxies the CMS feed and rewrites relative links to absolute ones for
 * the host being served.
 */
export default defineEventHandler(async (event) => {
    const resolved = await siteForEvent(event);

    if (!resolved) {
        throw createError({ statusCode: 404, statusMessage: 'Unknown host.' });
    }

    const url = getRequestURL(event);
    const origin = `${url.protocol}//${url.host}`;
    const apiBase = useRuntimeConfig(event).public.apiBase;

    const feed = await $fetch<string>(
        `/api/v1/public/sites/${resolved.site.id}/feed.rss`,
        { baseURL: apiBase, responseType: 'text' },
    );

    setResponseHeaders(event, {
        'Content-Type': 'application/rss+xml; charset=utf-8',
        'Cache-Control': 'public, s-maxage=900, stale-while-revalidate=86400',
    });

    return feed.replace(/<link>(\/[^<]*)<\/link>/g, (_, path: string) => `<link>${origin}${path}</link>`);
});
