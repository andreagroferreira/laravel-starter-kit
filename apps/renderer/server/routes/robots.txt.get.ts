export default defineEventHandler((event) => {
    const url = getRequestURL(event);

    setResponseHeader(event, 'Content-Type', 'text/plain; charset=utf-8');

    return `User-agent: *\nAllow: /\nSitemap: ${url.protocol}//${url.host}/sitemap.xml\n`;
});
