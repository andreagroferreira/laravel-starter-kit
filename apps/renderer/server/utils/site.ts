import type { H3Event } from 'h3';

export interface ResolvedSite {
    site: { id: string; name: string; slug: string; type: string };
    version: { id: string; published_at: string | null };
    schema: {
        site?: Record<string, unknown>;
        menus?: Record<string, { label: string; url: string }[]>;
        forms?: { id: string; name: string; fields: { name: string; type: string; required?: boolean }[] }[];
        pages?: {
            slug: string;
            title: string;
            seo?: Record<string, string> | null;
            blocks: { type: string; content: Record<string, unknown> | null }[];
        }[];
    };
}

/**
 * Resolve the site for a hostname. Cached per host (never per path — a
 * path-keyed cache would serve tenant A's page to tenant B) for a minute,
 * which is also the worst-case staleness after a publish that fails to
 * purge.
 */
const resolveSite = defineCachedFunction(
    async (host: string, apiBase: string): Promise<ResolvedSite | null> => {
        try {
            return await $fetch<ResolvedSite>('/api/v1/public/resolve', {
                baseURL: apiBase,
                query: { host },
            });
        } catch {
            return null;
        }
    },
    {
        maxAge: 60,
        name: 'site-by-host',
        getKey: (host: string) => host,
    },
);

export async function siteForEvent(event: H3Event): Promise<ResolvedSite | null> {
    const host = getRequestHost(event, { xForwardedHost: true }).toLowerCase();
    const apiBase = useRuntimeConfig(event).public.apiBase;

    return resolveSite(host, apiBase);
}
