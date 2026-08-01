import type { ResolvedSite } from '~~/server/utils/site';

/**
 * Injects gtag only when the site actually has GA4 connected — no
 * third-party script on sites that never asked for one.
 */
export default defineNuxtPlugin(async () => {
    const { data } = useNuxtData<ResolvedSite>('site');

    const measurementId = (
        data.value?.schema?.site?.integrations as
            | Record<string, string>
            | undefined
    )?.ga4_measurement_id;

    if (!measurementId) {
        return;
    }

    useHead({
        script: [
            {
                src: `https://www.googletagmanager.com/gtag/js?id=${measurementId}`,
                async: true,
            },
            {
                innerHTML: `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${measurementId}');`,
            },
        ],
    });
});
