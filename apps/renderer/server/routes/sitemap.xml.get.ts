import { siteForEvent } from '../utils/site';

/**
 * Built from the published schema so every URL is absolute for this host —
 * the CMS feed is host-agnostic and the sitemap spec requires absolute URLs.
 */
export default defineEventHandler(async (event) => {
    const resolved = await siteForEvent(event);

    if (!resolved) {
        throw createError({ statusCode: 404, statusMessage: 'Unknown host.' });
    }

    const url = getRequestURL(event);
    const origin = `${url.protocol}//${url.host}`;

    const entries = (resolved.schema.pages ?? [])
        .map((page) => {
            const path = page.slug === 'home' ? '/' : `/${page.slug}`;

            return `  <url><loc>${origin}${path}</loc></url>`;
        })
        .join('\n');

    setResponseHeaders(event, {
        'Content-Type': 'application/xml; charset=utf-8',
        'Cache-Control': 'public, s-maxage=3600, stale-while-revalidate=86400',
    });

    return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${entries}
</urlset>`;
});
