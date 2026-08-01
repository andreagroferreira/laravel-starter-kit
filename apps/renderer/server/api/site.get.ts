import { siteForEvent } from '../utils/site';

/**
 * Internal endpoint the app uses to hydrate: keeps the API base URL and
 * the host→site resolution on the server.
 */
export default defineEventHandler(async (event) => {
    const resolved = await siteForEvent(event);

    if (!resolved) {
        throw createError({
            statusCode: 404,
            statusMessage: 'No published site is bound to this hostname.',
        });
    }

    // Cached at the CDN by full URL (host included), never in a path-keyed
    // route cache.
    setResponseHeaders(event, {
        'Cache-Control': 'public, s-maxage=300, stale-while-revalidate=86400',
    });

    return resolved;
});
