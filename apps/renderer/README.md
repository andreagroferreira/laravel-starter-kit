# wizcms-renderer

Public site renderer. One deployment serves every site: the hostname is
resolved to a site through the CMS (`GET /api/v1/public/resolve?host=`),
and the published schema drives the page.

## Local

```bash
cd apps/renderer
bun install
NUXT_PUBLIC_API_BASE=http://cms-wiz-kimi-test.test bun run dev
```

Herd's dnsmasq resolves any `*.test` hostname to 127.0.0.1, so a site with
slug `demo` is reachable at `http://demo.wizcms.test:3000` with no host
file entries (`.wizcms.test` is in `config/renderer.php`'s local suffixes).

## Deploy

```bash
bun run build
wrangler pages deploy dist --project-name=wizcms-renderer
```

Cloudflare Pages has no wildcard custom domains, so the CMS attaches each
`{slug}.wizardincode.site` to the project on first publish (see
`CloudflareDeployer`). If the per-project domain limit ever bites, switch
the Nitro preset to `cloudflare_module` and use a single wildcard Worker
route — the app code stays the same.

## Caching

Content freshness comes from the CDN plus a 60s server cache of the
resolve call, invalidated by a purge-by-URL on publish. Nitro route rules
(`swr`/`isr`) are deliberately **not** used: that cache is keyed by path,
which in a multi-tenant renderer would serve one tenant's page to another.
